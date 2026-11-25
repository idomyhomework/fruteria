# 10: Administrador: Pedidos

**What to build:** The logged-in Administrador opens a Pedidos page (linked from home, next to Inventario) listing every Pedido newest first, and changes each Pedido's Estado. Entregado and Cancelado are final; cancelling gives the kilos back to the Cantidad. Anyone else is refused. See `../spec.md` (stories 79–85; Implementation Decisions → Estados, Pedidos page) and `docs/adr/0001-lineas-copian-nombre-y-precio.md`.

**Blocked by:** 09 (Hacer un Pedido)

**Status:** ready-for-agent

- [x] Home page shows a "Pedidos" link only for the Administrador
- [x] Pedidos page lists every Pedido newest first: "Pedido nº N", date and time, nombre, teléfono, Líneas (nombre, kilos, Precio, subtotal), total and Estado; a message when there are none
- [x] Non-final Pedidos have an Estado select and a "Cambiar" button (POST, PRG with a message)
- [x] Allowed: En espera ↔ Preparado para entrega; either → Entregado or Cancelado. Changes out of Entregado or Cancelado, or to an unknown Estado, are refused with a message
- [x] Moving to Entregado or Cancelado asks "¿Marcar el Pedido nº N como <Estado>?" via `confirm()`
- [x] Cancelling adds each Línea's kilos back to its Producto's Cantidad, in the same transaction, skipping deleted Productos
- [x] A Pedido keeps showing its Líneas' nombre and Precio after the Producto is deleted
- [x] Opening Pedidos or changing an Estado without being the Administrador redirects to the Catálogo and changes nothing
- [x] Tests: list visible only to the Administrador, newest first; each allowed change works; changes out of final Estados refused; cancel gives kilos back; deleted Producto still shown in its Pedido; refused without login
- [x] Manual check: the confirm question appears and cancel works
