# 02: Catálogo search

**What to build:** A Cliente can type part of a Producto's nombre into a search bar on the Catálogo and see only matching Productos. Matching ignores case and accents ("platano", "PLAT" find "Plátano"). The search lives in the page address so it can be shared or bookmarked. See `../spec.md`.

**Blocked by:** 01 (Walking skeleton: home page and read-only Catálogo)

**Status:** resolved

- [x] Search bar with a "Buscar" button (Enter also submits); the search is a GET `q` parameter
- [x] Matches any nombre containing the text, ignoring case and accents
- [x] Empty `q` shows the whole Catálogo
- [x] No matches shows "No se ha encontrado ningún Producto"
- [x] A visible way to clear the search and return to the full Catálogo
- [x] The search box keeps the current search text; it is HTML-escaped
- [x] Tests: partial match; case-insensitive; accent-insensitive; no-results message; empty search shows everything

## Comments

**2026-11-18: resuelto.** Implementado test-first en modo guía.
- `listar_productos(string $busqueda = '')` en `includes/productos.php`: `WHERE nombre LIKE ?` con `%`, `_` y `\` escapados (`addcslashes`); la collation `utf8mb4_spanish_ci` ignora mayúsculas y acentos.
- `catalogo.php`: formulario GET `q` con "Buscar", enlace "Quitar búsqueda", mensaje "No se ha encontrado ningún Producto.", `value` escapado; un `q` que no es string (`?q[]=x`) se trata como vacío.
- Tests en `tests/test_02_busqueda.php`: 7 tests (además de los pedidos: `%` se busca literalmente y el texto del buscador sale escapado). Suite completa: 12 OK.
