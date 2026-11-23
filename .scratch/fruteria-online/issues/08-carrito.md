# 08: Carrito

**What to build:** A Cliente adds kilos of Productos to a Carrito from the Catálogo, sees it on a Carrito page with subtotals and total, changes kilos or removes Líneas, and sees "Carrito (n)" in the header. The Carrito lives in the session and reserves nothing. The Administrador sees none of it. See `../spec.md` (stories 63–71, 78; Implementation Decisions → Carrito).

**Blocked by:** 06 (Inventario: add a Producto)

**Status:** ready-for-agent

- [ ] Each Catálogo row with Cantidad > 0 has a kilos field and an "Añadir al carrito" button (POST, keeps `q`, PRG back to the Catálogo with a message); Agotado rows have none
- [ ] Adding a Producto already in the Carrito adds the kilos to its Línea
- [ ] Kilos: comma or dot, > 0, at most 2 decimals, and the resulting Línea ≤ current Cantidad; otherwise a message and nothing changes
- [ ] Header shows "Carrito (n)" (n = number of Líneas) linking to the Carrito page, only for a Cliente
- [ ] Carrito page: each Línea's nombre, kilos, Precio, subtotal and the total; empty Carrito shows a message and a link to the Catálogo
- [ ] Change a Línea's kilos (same rules) or remove it from the Carrito page
- [ ] A Línea whose Producto was deleted is dropped with "<nombre> ya no está a la venta y se ha quitado del Carrito"
- [ ] A logged-in Administrador sees no "Añadir al carrito", no Carrito link, and Carrito actions are refused
- [ ] Tests: add; add again sums; each invalid kilos rule rejected; over-Cantidad rejected; Agotado has no button; change and remove; deleted Producto dropped with message; totals shown; Administrador sees no Carrito
