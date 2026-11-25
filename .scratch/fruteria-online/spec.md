# Spec: Frutería Online

Status: ready-for-agent

## Problem Statement

The Frutería Online is a small greengrocer that wants to show what it sells on the internet and manage its stock without spreadsheets or paper. Clientes have no way to see which Productos are available, what they cost per kilo, or whether they are in stock. The Administrador has no single place to add new Productos, remove ones the shop no longer sells, or keep each Producto's Cantidad up to date.

## Solution

A small website, deployed on the shop's AWS Ubuntu server under Apache with a MariaDB database:

- A **home page** with the shop's name and a link to the Catálogo (plus, for a logged-in Administrador, a link to Inventario and "Cerrar sesión").
- A public **Catálogo** that anyone can read and search by nombre, showing every Producto with its image, nombre, Tipo, Precio (€/kg) and Cantidad (kg), and marking empty ones as "Agotado".
- An **Administrador login** on its own page, `admin.php`, not linked from anywhere (the Administrador types its address; changed in ticket 11, it was on the Catálogo before). Once logged in, the Administrador can change each Producto's Cantidad and delete Productos directly from their rows.
- An **Inventario** page, only for the Administrador, with a form to add a new Producto.
- The database starts with one Producto already in it: Fruta de la pasión.
- **Pedidos** (added after ticket 06): a Cliente, still without an account, adds Productos to a **Carrito** and places a **Pedido** with their nombre and teléfono. Placing it takes the kilos out of the Cantidad. The Pedido is collected and paid at the shop; nothing is charged online. The Administrador sees every Pedido on a **Pedidos** page and moves it through its Estados.

## User Stories

### Cliente: viewing

1. As a Cliente, I want to open the home page and see the frutería's name, so that I know I'm on the right website.
2. As a Cliente, I want a link from the home page to the Catálogo, so that I can see what the shop sells.
3. As a Cliente, I want to see the Catálogo without logging in or typing any password, so that browsing is effortless.
4. As a Cliente, I want to see every Producto in a table with its image, nombre, Tipo, Precio and Cantidad, so that I can decide what to buy.
5. As a Cliente, I want the Precio shown in euros per kilo, so that I can compare Productos fairly.
6. As a Cliente, I want the Cantidad shown in kilos, so that I know how much is available.
7. As a Cliente, I want each Producto's Tipo (fruta or verdura) shown, so that I can tell fruits from vegetables.
8. As a Cliente, I want the Catálogo sorted alphabetically by nombre, so that I can find Productos quickly.
9. As a Cliente, I want Productos with Cantidad 0 to stay in the Catálogo marked "Agotado", so that I know the shop sells them even if they're out right now.
10. As a Cliente, I want to see Fruta de la pasión in the Catálogo from the first day, so that the shop never looks empty.
11. As a Cliente, I want a broken or slow image not to break the table, so that I can still read the rest of the row.

### Cliente: searching

12. As a Cliente, I want a search bar on the Catálogo, so that I can find a Producto without scrolling.
13. As a Cliente, I want my search to match part of a nombre ("plat" finds "Plátano"), so that I don't need to type the full name.
14. As a Cliente, I want my search to ignore upper/lower case, so that "PLATANO" and "platano" give the same results.
15. As a Cliente, I want my search to ignore accents, so that "platano" finds "Plátano".
16. As a Cliente, I want to run the search by pressing Enter or a "Buscar" button, so that it behaves like a normal web form.
17. As a Cliente, I want a clear "No se ha encontrado ningún Producto" message when nothing matches, so that I know the search worked.
18. As a Cliente, I want the search to be part of the page address, so that I can share or bookmark a search.
19. As a Cliente, I want a way to clear the search and see the whole Catálogo again, so that I'm not stuck in filtered results.

### Cliente: limits

20. As a Cliente, I want not to see any Administrador controls (Actualizar, Eliminar, Inventario link), so that the Catálogo stays simple for me.
21. As the frutería, I want a Cliente who calls an Administrador action directly (e.g. a crafted request) to be refused, so that only the Administrador can change data.
22. As the frutería, I want a Cliente who opens the Inventario page directly to be redirected to the Catálogo, without any message, so that nobody else can add Productos.

### Administrador: session

23. As the Administrador, I want a login form (usuario and contraseña) on `admin.php`, a page I reach by typing its address, so that Clientes never see any Administrador access.
24. As the Administrador, I want a single "Usuario o contraseña incorrectos" message on a failed login, so that nobody can tell which part was wrong.
25. As the Administrador, I want a successful login to take me to the home page, and `admin.php` to send me there if I'm already logged in, so that I land on my links (Inventario, Pedidos).
26. As the Administrador, I want to stay logged in while I move between home, Catálogo and Inventario, so that I don't have to log in on every page.
27. As the Administrador, I want a "Cerrar sesión" link on the home page and the Catálogo, so that I can end my session on a shared computer.
28. As the Administrador, I want my session to end when I close the browser, so that the next person on the computer isn't logged in as me.
29. As the Administrador, I want to see everything a Cliente sees (table, search), so that I can check how the Catálogo looks to customers.
30. As the Administrador, I want an Inventario link on the home page once I'm logged in, so that I can reach the add form easily.

### Administrador: changing Cantidad

31. As the Administrador, I want a Cantidad field and an "Actualizar" button on each Producto's row, so that I can correct stock right where I see it.
32. As the Administrador, I want Actualizar to set the new total Cantidad (not add or subtract), so that I can type what I counted.
33. As the Administrador, I want to enter Cantidad with up to 2 decimals (e.g. 2,5 kg), so that I can record partial kilos.
34. As the Administrador, I want a negative or non-numeric Cantidad to be rejected with a message, so that stock is never nonsense.
35. As the Administrador, I want to set Cantidad to 0, so that a Producto shows as "Agotado".
36. As the Administrador, I want to stay on my current search after Actualizar, so that I can update several matching Productos in a row.
37. As the Administrador, I want a confirmation message after a successful update, so that I know it was saved.

### Administrador: deleting

38. As the Administrador, I want an "Eliminar" button on each Producto's row, so that I can remove a Producto the shop no longer sells.
39. As the Administrador, I want to be asked "¿Eliminar <nombre>?" before a Producto is deleted, so that a misclick doesn't delete the wrong one.
40. As the Administrador, I want cancelling that question to leave the Producto untouched, so that I can back out.
41. As the Administrador, I want to stay on my current search after deleting, so that I don't lose my place.
42. As the Administrador, I want a confirmation message after a successful delete, so that I know it's gone.
43. As the Administrador, I want deleting a Producto that someone else already deleted to show a message rather than an error page, so that the site never crashes on stale pages.
44. As the Administrador, I want Fruta de la pasión to stay deleted if I delete it, so that the default seed never overrides my decisions.

### Administrador: Inventario (adding)

45. As the Administrador, I want an Inventario page with a form for nombre, Tipo, Precio, Cantidad and imagen URL, so that I can add a new Producto.
46. As the Administrador, I want to choose the Tipo from fruta/verdura, so that I can't type an invalid Tipo.
47. As the Administrador, I want a "Guardar" button that saves the new Producto, so that it appears in the Catálogo.
48. As the Administrador, I want adding a Producto whose nombre already exists (ignoring case and surrounding spaces) to be rejected with "Ya existe un Producto con ese nombre", so that there are never two "Manzana".
49. As the Administrador, I want the form to keep what I typed when it's rejected, so that I only fix the wrong field.
50. As the Administrador, I want an empty nombre or one longer than 50 characters to be rejected, so that names stay readable.
51. As the Administrador, I want a Precio of 0 or less, or with more than 2 decimals, to be rejected, so that prices are always real.
52. As the Administrador, I want a negative Cantidad or one with more than 2 decimals to be rejected, so that stock is always valid.
53. As the Administrador, I want an imagen URL that doesn't start with http:// or https:// to be rejected, so that images can actually load.
54. As the Administrador, I want each rejected field to show its own message, so that I know exactly what to fix.
55. As the Administrador, I want surrounding spaces in the nombre removed before saving, so that " Pera " is stored as "Pera".
56. As the Administrador, I want a confirmation after saving, so that I know the new Producto was added. (Changed in ticket 11: the "Volver al Catálogo" link was replaced by the "Principal > Inventario" path; to check the Producto I go to the Catálogo from Principal.)
57. As the Administrador, I want to be able to type Precio and Cantidad with a comma or a dot as decimal separator, so that Spanish-style numbers work.

### Operator / deployment

58. As the person deploying, I want one SQL script that creates the database, the Producto table and the Fruta de la pasión seed, so that setting up a new server is one command.
59. As the person deploying, I want the Administrador's password stored only as a hash in a single config file, so that the real password never appears in the code.
60. As the person deploying, I want the database credentials in that same config file, so that moving between my machine and AWS means editing one file.
61. As the person deploying, I want instructions for generating a new password hash, so that I can change the Administrador password without a UI.
62. As the person deploying, I want the app to run under Apache on Ubuntu on AWS, so that I can submit its home page URL.

### Cliente: Carrito

63. As a Cliente, I want a kilos field and an "Añadir al carrito" button on each Producto row, so that I can choose what to order while I browse.
64. As a Cliente, I want Agotado Productos to have no "Añadir al carrito" button, so that I can't choose what isn't there.
65. As a Cliente, I want adding a Producto that is already in my Carrito to add the kilos to its Línea, so that I don't get two Líneas for the same Producto.
66. As a Cliente, I want kilos that are 0 or less, have more than 2 decimals, or exceed the current Cantidad to be rejected with a message, so that I find out early. (This check reserves nothing.)
67. As a Cliente, I want a "Carrito (n)" link in the header on every page, so that I can see how many Líneas I have and open the Carrito.
68. As a Cliente, I want a Carrito page with each Línea's nombre, kilos, Precio and subtotal, plus the total, so that I know what I'm about to order.
69. As a Cliente, I want to change a Línea's kilos or remove it on the Carrito page, so that I can fix my choice before ordering.
70. As a Cliente, I want a Línea whose Producto was deleted to disappear from my Carrito with a message, so that I never order something the shop no longer sells.
71. As a Cliente, I want my Carrito to last until I close the browser, so that I can keep browsing without losing it.

### Cliente: placing a Pedido

72. As a Cliente, I want to place my Pedido from the Carrito page by giving my nombre and teléfono, so that the shop knows who will collect it.
73. As a Cliente, I want an empty nombre, one longer than 50 characters, or a teléfono that isn't 9 digits (spaces and an optional +34 allowed) to be rejected with a message next to the field while keeping what I typed, so that I only fix the wrong field.
74. As a Cliente, I want my Pedido to be rejected entirely if any Línea now exceeds the Cantidad, with "Solo quedan X kg de <nombre>" next to that Línea and my Carrito left as it was, so that I decide how to adjust it.
75. As a Cliente, I want a confirmation page with "Pedido nº N", the Líneas, the total and "Se paga al recoger", so that I know the order went through and what I'll pay.
76. As a Cliente, I want my Carrito emptied after the Pedido is placed, so that I don't order the same thing twice by accident.
77. As the frutería, I want placing a Pedido to take its kilos out of each Producto's Cantidad all at once, so that two Clientes can never order the same last kilos.

### Administrador: Pedidos

78. As the Administrador, I want no Carrito or "Añadir al carrito" controls, so that my Catálogo stays a management view.
79. As the Administrador, I want a "Pedidos" link on the home page, next to Inventario, so that I can reach the Pedidos page.
80. As the Administrador, I want the Pedidos page to list every Pedido newest first with its number, date and time, nombre, teléfono, Líneas, total and Estado, so that I can prepare them.
81. As the Administrador, I want to move a Pedido between En espera and Preparado para entrega, and from either to Entregado or Cancelado, so that the Estado reflects reality.
82. As the Administrador, I want Entregado and Cancelado to be final, and to be asked to confirm before setting either, so that a misclick can't be undone wrongly.
83. As the Administrador, I want cancelling a Pedido to give its kilos back to each Producto's Cantidad (if the Producto still exists), so that the stock is right again.
84. As the Administrador, I want a Pedido to keep showing each Línea's nombre and Precio even after I delete or change that Producto, so that old Pedidos stay readable and their totals never change.
85. As the frutería, I want a Cliente who opens the Pedidos page or changes an Estado to be refused, so that only the Administrador manages Pedidos.

### Administrador: navigation

86. As the Administrador, I want a "Principal > <page>" path at the top of the Catálogo, Inventario and Pedidos, linking back to the home page, so that I always know where I am and get home in one click. It replaces the old "Ir al Inventario", "Volver al Catálogo" and "Volver al inicio" links (added in ticket 11). Clientes don't see it.

## Implementation Decisions

- **Stack**: plain PHP (no framework), MariaDB accessed through PDO with prepared statements for every query, served by Apache on Ubuntu on AWS. Plain HTTP; no HTTPS.
- **Pages**: home, Catálogo, Inventario, Carrito, Pedido confirmation, Pedidos and the Administrador login (`admin.php`, unlinked), plus a logout action. On the Catálogo, Inventario and Pedidos the Administrador sees a "Principal > <page>" path at the top that links to the home page. Each page is a server-rendered PHP page; the only JavaScript is `confirm()` on the row Eliminar button and on moving a Pedido to a final Estado.
- **Config**: one config file holds the database connection settings and the Administrador's username (`admin`) and password hash produced by `password_hash()`. Login uses `password_verify()`. There is no admin table and no password-change screen.
- **Accounts**: exactly one account, the Administrador. Clientes never log in.
- **Session**: standard PHP session cookie (session-lifetime, ends on browser close). On successful login, the session ID is regenerated. Logout destroys the session.
- **Authorisation**: every write action (update Cantidad, delete, add) checks for a logged-in Administrador on the server and refuses otherwise, independent of whether the UI showed the control. Unauthenticated access to Inventario redirects to the Catálogo.
- **Schema**: one Producto table:
  - numeric auto-increment id (internal identity),
  - nombre: up to 50 characters, unique; stored trimmed; uniqueness and search are case- and accent-insensitive by using an accent/case-insensitive utf8mb4 collation (e.g. `utf8mb4_general_ci` or `utf8mb4_unicode_ci`),
  - tipo: enum of `fruta`, `verdura`,
  - precio: decimal with 2 decimals, > 0 (euros per kilo),
  - cantidad: decimal with 2 decimals, ≥ 0 (kilos),
  - imagen URL: text.
- **Seed**: the setup script inserts "Fruta de la pasión", tipo fruta, precio 9.90, cantidad 5.00, with a Wikimedia Commons image URL. It runs only when creating the database; nothing re-inserts it later.
- **Catálogo**:
  - lists all Productos ordered by nombre; Cantidad 0 is displayed as "Agotado";
  - search is a GET parameter `q` matching nombre with "contains", case- and accent-insensitive; empty `q` shows everything;
  - for the Administrador, each row has an update-Cantidad form and an Eliminar form (both POST, identifying the Producto by id), and both carry the current `q` so the redirect afterwards keeps the search;
  - after every POST the server redirects back to the Catálogo (Post/Redirect/Get) with a one-time flash message (success or error).
- **Inventario**: POST form with server-side validation of all fields; on any error the form is re-rendered with the entered values and per-field messages; on duplicate nombre the database unique constraint is the source of truth and is translated into "Ya existe un Producto con ese nombre". Decimal input accepts comma or dot.
- **Output safety**: all user-provided values (nombre, imagen URL, search text) are HTML-escaped on output; imagen URLs are only rendered if they start with http:// or https://.
- **Deliberate departures from the brief**: deletion uses one Eliminar button per row instead of the brief's text box plus delete button; Tipo, search and Pedidos are additions. These were the user's explicit choices.
- **Carrito**: kept in the PHP session as Producto id → kilos; it is never written to the database and reserves nothing. Only shown to a Cliente (not to a logged-in Administrador). Kilos accept comma or dot, > 0, up to 2 decimals, ≤ current Cantidad when added or changed. Lines whose Producto no longer exists are dropped (with a flash message) whenever the Carrito is shown or a Pedido is placed. Carrito actions are POST + Post/Redirect/Get.
- **Placing a Pedido**: one database transaction that locks the Productos involved (`SELECT … FOR UPDATE`), re-checks every Línea against the Cantidad, subtracts it, and inserts the Pedido and its Líneas; if any Línea fails, it rolls back and nothing changes. On error the Carrito page is re-rendered with per-field and per-Línea messages and the typed nombre/teléfono; on success, redirect to a confirmation page that shows only that Pedido (stored in the session, so nobody can view other Pedidos by changing a number).
- **Pedido fields**: nombre (trimmed, required, ≤ 50 characters), teléfono (required; spaces removed, optional `+34` prefix removed, must then be exactly 9 digits; stored as those 9 digits), creation date-time, Estado. No email, notes or address.
- **Estados**: `en_espera` (initial), `preparado`, `entregado`, `cancelado`, shown as "En espera", "Preparado para entrega", "Entregado", "Cancelado". Allowed changes: en_espera ↔ preparado; en_espera or preparado → entregado or cancelado. entregado and cancelado are final. Changing to cancelado, in the same transaction, adds each Línea's kilos back to its Producto's Cantidad when that Producto still exists. Moving to a final Estado asks a JavaScript `confirm()`.
- **Schema additions** (same collation as Producto):
  - `pedido`: id (auto-increment, shown as "Pedido nº N"), nombre, teléfono, creado (date-time), estado (enum above);
  - `linea_pedido`: id, pedido id (foreign key, cascade on delete), producto id (nullable, `ON DELETE SET NULL`), nombre and precio copied from the Producto at order time, kilos (decimal, 2 decimals, > 0). See ADR 0001.
- **Pedidos page**: Administrador only (redirect to Catálogo otherwise, like Inventario); all Pedidos newest first, no filters or pagination; each non-final Pedido has an Estado select and a "Cambiar" button (POST, PRG). Totals are computed from the copied precio × kilos.

## Testing Decisions

- **One seam: HTTP.** Tests are black-box: they start the app on PHP's built-in web server against a throwaway MariaDB test database, rebuilt from the same setup SQL script before each test, then send real HTTP requests and check status codes, redirects and the rendered HTML.
- A good test describes behaviour a Cliente or the Administrador can observe (what the page shows, whether an action is refused, where a redirect goes) and never relies on internal function names, file layout or SQL.
- Tests log in as the Administrador through the real login form (with a test password whose hash is placed in the test config), and keep the session cookie between requests.
- Behaviours to cover at minimum:
  - home page shows the shop name and Catálogo link; Inventario link and "Cerrar sesión" only when logged in;
  - Catálogo shows Fruta de la pasión after a fresh setup; Cantidad 0 shows "Agotado"; ordering by nombre;
  - search: partial, case-insensitive, accent-insensitive, no-results message, `q` kept after Administrador actions;
  - login: on `admin.php`; success goes home, wrong password and wrong username give the same message on `admin.php`; the Catálogo never shows the login form; logout;
  - the Administrador sees the "Principal > <page>" path on Catálogo, Inventario and Pedidos; a Cliente doesn't;
  - every write action without login is refused and changes nothing; Inventario without login redirects;
  - Actualizar sets Cantidad; invalid Cantidad is rejected;
  - Eliminar removes the Producto; deleting an already-deleted id shows a message;
  - Inventario: valid add appears in Catálogo; duplicate nombre (different case / spaces) rejected; each validation rule rejected with its message; entered values kept on error; comma decimals accepted;
  - HTML in a nombre is shown escaped, not executed;
  - Carrito: add, add again sums, invalid kilos rejected, over-Cantidad rejected, Agotado has no button, change and remove Líneas, deleted Producto dropped with a message, Administrador sees no Carrito;
  - Pedido: valid Pedido subtracts Cantidad and shows the confirmation; Carrito emptied; nombre/teléfono rules each rejected with values kept; Pedido exceeding Cantidad rejected with nothing subtracted and the Carrito unchanged; empty Carrito can't be ordered;
  - Pedidos: list visible only to the Administrador; allowed Estado changes work; changes out of a final Estado are refused; cancelling gives kilos back; a Pedido still shows nombre and Precio after its Producto is deleted.
- **Test helpers**: the HTTP tests keep the session cookie, so the Carrito lives in the test "browser" like in a real one.
- **Prior art**: tickets 01–06 (`tests/test_0N_*.php`). The JavaScript `confirm()` prompts are not covered by automated tests (checked manually).

## Out of Scope

- Cliente accounts; a Cliente looking up their Pedido after the confirmation page.
- Online payments, delivery to an address, email or SMS notifications.
- The Administrador placing Pedidos, editing a Pedido's Líneas, or deleting Pedidos.
- More than one Administrador, account management, password-change screen, password reset.
- Editing a Producto's nombre, Tipo, Precio or imagen after creation (only Cantidad can change).
- Selling by unit/piece; every Producto is per kilo.
- Filtering by Tipo, sorting options, pagination.
- Uploading image files (only URLs).
- HTTPS / a domain name.
- Multi-language UI (Spanish only).

## Further Notes

- Domain vocabulary is defined in `GLOSSARY.md` (Producto, Tipo, Precio, Cantidad, Catálogo, Inventario, Cliente, Administrador, Carrito, Pedido, Línea, Estado). UI text is Spanish.
- Plain HTTP means the Administrador password travels unencrypted; accepted for this coursework project.
- The deliverable for the course is the public URL of the home page on the AWS machine, submitted to Aules.
