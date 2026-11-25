<?php
// Acceso del Administrador. No hay enlaces a esta página: se escribe la dirección.
require_once __DIR__ . '/includes/vista.php';

if (es_administrador()) {
    redirigir('index.php');
}

cabecera('Acceso del Administrador');
?>
<section class="acceso">
    <h2>Acceso del Administrador</h2>
    <form method="post" action="login.php">
        <label for="usuario">Usuario</label>
        <input type="text" id="usuario" name="usuario" required>
        <label for="contrasena">Contraseña</label>
        <input type="password" id="contrasena" name="contrasena" required>
        <button type="submit">Entrar</button>
    </form>
</section>
<?php
pie();