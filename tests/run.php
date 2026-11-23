<?php
// Ejecuta los tests HTTP:  php tests/run.php
// Cada test parte de una base de datos de test recién creada con sql/setup.sql.

const RAIZ = __DIR__ . '/..';
const BD_TEST = 'fruteria_test';

// La app (servidor) y este script usan la base de datos de test.
putenv('FRUTERIA_DB_NAME=' . BD_TEST);
// Administrador de test: el hash se genera aquí, no se guarda en ningún fichero.
const CLAVE_TEST = 'clave-de-test';
putenv('FRUTERIA_ADMIN_USER=admin');
putenv('FRUTERIA_ADMIN_HASH=' . password_hash(CLAVE_TEST, PASSWORD_DEFAULT));

// ---------- Base de datos de test ----------

function bd_test(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $c = (require RAIZ . '/includes/config.php')['db'];
        $pdo = new PDO(
            "mysql:host={$c['host']};dbname={$c['nombre']};charset=utf8mb4",
            $c['usuario'],
            $c['password'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    }
    return $pdo;
}

function reiniciar_bd(): void
{
    bd_test()->exec('DROP TABLE IF EXISTS producto');
    bd_test()->exec(file_get_contents(RAIZ . '/sql/setup.sql'));
}

// Para preparar escenarios mientras no exista la página de Inventario.
function insertar_producto(
    string $nombre,
    string $tipo = 'fruta',
    string $precio = '1.00',
    string $cantidad = '1.00',
    string $imagen = 'https://example.com/a.jpg'
): void {
    bd_test()->prepare('INSERT INTO producto (nombre, tipo, precio, cantidad, imagen_url)
                        VALUES (?, ?, ?, ?, ?)')
        ->execute([$nombre, $tipo, $precio, $cantidad, $imagen]);
}

// ---------- Servidor y peticiones HTTP ----------

function arrancar_servidor(): array
{
    // Pedimos al sistema un puerto libre.
    $s = stream_socket_server('tcp://127.0.0.1:0');
    $puerto = (int) substr(strrchr(stream_socket_get_name($s, false), ':'), 1);
    fclose($s);

    $proceso = proc_open(
        ['php', '-S', "127.0.0.1:$puerto", '-t', RAIZ],
        [1 => ['file', '/dev/null', 'w'], 2 => ['file', '/dev/null', 'w']],
        $tuberias,
        null,
        getenv() // incluye FRUTERIA_DB_NAME=fruteria_test
    );

    // Esperamos a que acepte conexiones.
    for ($i = 0; $i < 50; $i++) {
        if ($c = @fsockopen('127.0.0.1', $puerto)) {
            fclose($c);
            return [$proceso, "http://127.0.0.1:$puerto"];
        }
        usleep(100_000);
    }
    exit("No se pudo arrancar php -S\n");
}

// Cookies que el "navegador" de los tests guarda entre peticiones (se vacían en cada test).
$COOKIES = [];

// Hace una petición sin seguir redirecciones y devuelve [estado, HTML, cabeceras].
function peticion(string $metodo, string $ruta, array $campos = []): array
{
    global $BASE, $COOKIES;

    $cabeceras = [];
    if ($COOKIES) {
        $pares = array_map(fn($n, $v) => "$n=$v", array_keys($COOKIES), $COOKIES);
        $cabeceras[] = 'Cookie: ' . implode('; ', $pares);
    }
    if ($metodo === 'POST') {
        $cabeceras[] = 'Content-Type: application/x-www-form-urlencoded';
    }

    $ctx = stream_context_create([
        'http' => [
            'method' => $metodo,
            'header' => implode("\r\n", $cabeceras),
            'content' => http_build_query($campos),
            'follow_location' => 0,
            'ignore_errors' => true,
        ]
    ]);
    $html = file_get_contents($BASE . $ruta, false, $ctx);
    $respuesta = $http_response_header;

    // Guardamos o borramos las cookies que mande el servidor.
    foreach ($respuesta as $linea) {
        if (preg_match('#^Set-Cookie:\s*([^=]+)=([^;]*)#i', $linea, $m)) {
            if (stripos($linea, 'Max-Age=0') !== false) {
                unset($COOKIES[$m[1]]);
            } else {
                $COOKIES[$m[1]] = $m[2];
            }
        }
    }

    preg_match('#HTTP/\S+ (\d{3})#', $respuesta[0], $m);
    return [(int) $m[1], $html, $respuesta];
}

function get(string $ruta): array
{
    return peticion('GET', $ruta);
}

function post(string $ruta, array $campos): array
{
    return peticion('POST', $ruta, $campos);
}

// Valor de una cabecera de la respuesta (p. ej. 'Location'), o '' si no está.
function cabecera_http(array $cabeceras, string $nombre): string
{
    foreach ($cabeceras as $linea) {
        if (stripos($linea, "$nombre:") === 0) {
            return trim(substr($linea, strlen($nombre) + 1));
        }
    }
    return '';
}

// Envía el formulario de login del Catálogo.
function login(string $usuario = 'admin', string $contrasena = CLAVE_TEST): array
{
    return post('/login.php', ['usuario' => $usuario, 'contrasena' => $contrasena]);
}

// La fila <tr> del Catálogo que contiene ese nombre (ya escapado).
function fila(string $html, string $nombre): string
{
    preg_match('#<tr>(?:(?!</tr>).)*' . preg_quote($nombre, '#') . '.*?</tr>#s', $html, $m);
    return $m[0] ?? '';
}

// ---------- Aserciones ----------

function comprobar(bool $condicion, string $mensaje): void
{
    if (!$condicion) {
        throw new Exception($mensaje);
    }
}

function contiene(string $html, string $texto): void
{
    comprobar(str_contains($html, $texto), "No se encontró: $texto");
}

function no_contiene(string $html, string $texto): void
{
    comprobar(!str_contains($html, $texto), "No debería aparecer: $texto");
}

// ---------- Runner ----------

foreach (glob(__DIR__ . '/test_*.php') as $fichero) {
    require $fichero;
}

[$servidor, $BASE] = arrancar_servidor();
$fallos = 0;

foreach (get_defined_functions()['user'] as $funcion) {
    if (!str_starts_with($funcion, 'test_')) {
        continue;
    }
    reiniciar_bd();
    $COOKIES = [];
    try {
        $funcion();
        echo "OK    $funcion\n";
    } catch (Throwable $ex) {
        $fallos++;
        echo "FALLO $funcion\n      {$ex->getMessage()}\n";
    }
}

proc_terminate($servidor);
echo $fallos === 0 ? "\nTodo correcto.\n" : "\n$fallos test(s) fallidos.\n";
exit($fallos === 0 ? 0 : 1);