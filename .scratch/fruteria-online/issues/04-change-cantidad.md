# 04: Change Cantidad from the Catálogo

**What to build:** The logged-in Administrador sees a Cantidad field and an "Actualizar" button on each Producto row and can set the new total Cantidad in kilos. After saving they return to the same Catálogo search with a confirmation message. Anyone else trying the action is refused and nothing changes. See `../spec.md`.

**Blocked by:** 02 (Catálogo search), 03 (Administrador login and logout)

**Status:** ready-for-agent

- [x] Each row shows a Cantidad field + "Actualizar" button only for the logged-in Administrador
- [x] Actualizar sets the absolute Cantidad (not an increment), identifying the Producto by its id, via POST
- [x] Accepts up to 2 decimals with comma or dot as separator; 0 is allowed (then shows "Agotado")
- [x] Negative, non-numeric, or more-than-2-decimal values are rejected with a message and nothing changes
- [x] After the POST, redirect back to the Catálogo keeping the current `q`, with a one-time success or error message
- [x] Updating a Producto that no longer exists shows a message, not an error page
- [x] The action is refused server-side when not logged in as Administrador, and Cantidad is unchanged
- [x] Tests: update sets value; comma decimal accepted; invalid values rejected; `q` kept after update; refused without login
