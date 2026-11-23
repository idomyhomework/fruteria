# 05: Delete a Producto from the Catálogo

**What to build:** The logged-in Administrador sees an "Eliminar" button on each Producto row. Clicking it asks "¿Eliminar <nombre>?"; confirming deletes the Producto and returns to the same Catálogo search with a confirmation message. Anyone else trying the action is refused. A deleted Fruta de la pasión stays deleted. See `../spec.md`.

**Blocked by:** 02 (Catálogo search), 03 (Administrador login and logout)

**Status:** ready-for-agent

- [x] Each row shows an "Eliminar" button only for the logged-in Administrador
- [x] Clicking it asks "¿Eliminar <nombre>?" via JavaScript `confirm()`; cancelling leaves the Producto untouched
- [x] Confirming deletes the Producto by id via POST
- [x] After the POST, redirect back to the Catálogo keeping the current `q`, with a one-time success message
- [x] Deleting a Producto that was already deleted shows a message, not an error page
- [x] The action is refused server-side when not logged in as Administrador, and nothing is deleted
- [x] Nothing re-inserts Fruta de la pasión after it is deleted
- [x] Tests: delete removes the Producto from the Catálogo; `q` kept after delete; already-deleted id gives a message; refused without login
- [x] Manual check: the confirm question appears and cancel works (not covered by automated tests)
