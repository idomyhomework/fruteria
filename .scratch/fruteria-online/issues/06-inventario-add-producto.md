# 06: Inventario: add a Producto

**What to build:** The logged-in Administrador opens the Inventario page, fills in nombre, Tipo, Precio, Cantidad and imagen URL, presses "Guardar", and the new Producto appears in the Catálogo. Invalid or duplicate input is rejected with a message per field while keeping what was typed. Anyone not logged in who opens Inventario is redirected to the Catálogo login. See `../spec.md`.

**Blocked by:** 03 (Administrador login and logout)

**Status:** ready-for-agent

- [x] Inventario page with fields nombre, Tipo (select: fruta / verdura), Precio, Cantidad, imagen URL and a "Guardar" button
- [x] Opening Inventario or posting to it without being the logged-in Administrador redirects to the Catálogo and saves nothing
- [x] nombre is trimmed; required; max 50 characters
- [x] Precio > 0 with at most 2 decimals; Cantidad ≥ 0 with at most 2 decimals; comma or dot accepted as decimal separator
- [x] imagen URL must start with http:// or https://
- [x] Tipo must be fruta or verdura
- [x] A nombre that already exists ignoring case, accents and surrounding spaces is rejected with "Ya existe un Producto con ese nombre" (the DB unique constraint is the source of truth)
- [x] On any error the form is re-shown with the entered values and a message next to each wrong field
- [x] On success, a confirmation and a link back to the Catálogo; the new Producto is listed there
- [x] Tests: valid add appears in Catálogo; duplicate with different case/spaces rejected; each validation rule rejected with its message; values kept on error; comma decimals accepted; redirect without login
