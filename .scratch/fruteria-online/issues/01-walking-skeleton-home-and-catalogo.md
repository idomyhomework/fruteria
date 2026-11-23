# 01: Walking skeleton: home page and read-only Catálogo

**What to build:** A Cliente can open the home page, see the frutería's name and follow a link to the Catálogo, which lists every Producto (imagen, nombre, Tipo, Precio €/kg, Cantidad kg) sorted by nombre. A fresh install already contains Fruta de la pasión. This ticket also lays down the setup SQL script, the single config file (DB credentials), and the HTTP test harness every later ticket builds on. See `../spec.md` and `GLOSSARY.md`.

**Blocked by:** None (can start immediately)

**Status:** resolved

- [x] One SQL setup script creates the database and the Producto table (nota: las bases de datos se crean a mano; el script crea la tabla): internal id, nombre (≤ 50 chars, unique, case- and accent-insensitive collation), tipo (fruta | verdura), precio (2 decimals), cantidad (2 decimals), imagen URL
- [x] The setup script inserts Fruta de la pasión once: tipo fruta, 9.90 €/kg, 5.00 kg, a Wikimedia Commons image URL
- [x] A single config file holds the database connection settings; all queries use PDO prepared statements
- [x] Home page shows the frutería's name and a link to the Catálogo
- [x] Catálogo shows a table with Imagen, Nombre, Tipo, Precio (€/kg), Cantidad (kg), ordered by nombre
- [x] A Producto with Cantidad 0 shows "Agotado"
- [x] A broken image doesn't break the row layout (comprobación manual, sin test)
- [x] All Producto values are HTML-escaped on output; imagen URLs are only rendered when they start with http:// or https://
- [x] HTTP test harness: starts the app on PHP's built-in server against a throwaway MariaDB test database rebuilt from the setup script before each test
- [x] Tests: home page shows name + Catálogo link; fresh Catálogo lists Fruta de la pasión; ordering by nombre; Cantidad 0 shows "Agotado"; a nombre containing HTML is shown escaped

## Comments

**2026-11-18: resuelto.** Implementado en modo guía (el usuario aplicó el código).
- Ficheros: `sql/setup.sql`, `includes/config.php`, `includes/db.php`, `includes/productos.php`, `includes/vista.php`, `index.php`, `catalogo.php`, `style.css`.
- Las bases `fruteria` y `fruteria_test` y el usuario `fruteria`@localhost se crearon a mano (paso 1); `setup.sql` solo crea la tabla e inserta la semilla, sin `CREATE DATABASE` ni `DROP`.
- Harness: `tests/run.php` (runner en PHP plano, sin PHPUnit; `php -S` en puerto libre con `FRUTERIA_DB_NAME=fruteria_test`; borra y recrea la tabla antes de cada test). Tests en `tests/test_01_catalogo.php`: 5 OK.
- La imagen rota se comprobó solo a mano (sin test); la URL de Wikimedia de la semilla sigue sin verificar.
