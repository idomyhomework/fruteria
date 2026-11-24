# 09: Hacer un Pedido

**What to build:** From the Carrito page, a Cliente enters nombre and teléfono and presses "Hacer pedido". In one transaction the Cantidad of every Línea is re-checked and subtracted and the Pedido is saved (Estado En espera), or nothing happens at all. On success the Cliente sees a confirmation with "Pedido nº N", the Líneas, the total and "Se paga al recoger", and the Carrito is emptied. See `../spec.md` (stories 72–77; Implementation Decisions → Placing a Pedido, Pedido fields, Schema additions) and `docs/adr/0001-lineas-copian-nombre-y-precio.md`.

**Blocked by:** 08 (Carrito)

**Status:** ready-for-agent

- [x] `sql/setup.sql` creates the `pedido` and `linea_pedido` tables (Líneas copy nombre and Precio; producto id nullable with `ON DELETE SET NULL`); the test reset drops them in the right order
- [x] nombre: trimmed, required, ≤ 50 characters; teléfono: spaces and optional `+34` removed, then exactly 9 digits, stored as 9 digits
- [x] On a nombre/teléfono error the Carrito page is re-shown with a message next to the field and the typed values
- [x] Transaction locks the Productos involved, re-checks every Línea, subtracts kilos, inserts Pedido and Líneas; any failure rolls back
- [x] If a Línea exceeds the Cantidad: nothing is subtracted or saved, the Carrito is unchanged, and that Línea shows "Solo quedan X kg de <nombre>"
- [x] An empty Carrito can't be ordered; a deleted Producto's Línea is dropped with a message first (as in 08)
- [x] Success: PRG to a confirmation page showing only this Pedido (from the session): "Pedido nº N", Líneas, total, "Se paga al recoger"; Carrito emptied
- [x] The Administrador can't place Pedidos
- [x] Tests: valid Pedido subtracts Cantidad and shows confirmation; Carrito emptied; each nombre/teléfono rule rejected with values kept; teléfono with spaces and +34 accepted; over-Cantidad rejects the whole Pedido with nothing subtracted; empty Carrito refused
