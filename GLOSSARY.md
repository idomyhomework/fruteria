# Frutería Online

A small greengrocer that shows what it sells to anyone online, and manages its stock internally.

## Language

### Lo que se vende

**Producto**:
Anything the frutería sells, identified by a nombre that is unique regardless of case or surrounding spaces.
_Avoid_: Fruta (for the general concept), artículo, item

**Tipo**:
Whether a Producto is a _fruta_ or a _verdura_.
_Avoid_: Categoría, clase

**Precio**:
What one kilo of a Producto costs, in euros.
_Avoid_: Coste, tarifa

**Cantidad**:
How many kilos of a Producto are in stock right now.
_Avoid_: Stock, existencias, unidades

**Catálogo**:
The list of every Producto the frutería sells, with its Precio, Cantidad and image.
_Avoid_: Tienda, listado, inventario

**Inventario**:
Adding a new Producto to what the frutería sells.
_Avoid_: Alta, catálogo

### Quién

**Cliente**:
Anyone visiting the website without logging in; can see and search the Catálogo and place Pedidos, but not change the Catálogo. Has no account: a Pedido identifies them by nombre and teléfono.
_Avoid_: Usuario, visitante, comprador

**Administrador**:
The one person who logs in, and who can add and delete Productos, change their Cantidad and manage Pedidos, besides seeing everything a Cliente sees.
_Avoid_: Admin, gestor, dueño

### Pedidos

**Carrito**:
The Líneas a Cliente has chosen but not ordered yet; it reserves nothing and lasts only while the browser stays open. The Administrador has none.
_Avoid_: Cesta, bolsa

**Pedido**:
A Cliente's request for kilos of one or more Productos, placed online, then collected and paid at the shop (no delivery, no online payment). Placing it takes those kilos out of the Cantidad, all or nothing.
_Avoid_: Orden, compra, venta, reserva

**Línea**:
One Producto and its kilos inside a Carrito or a Pedido. In a Pedido it keeps the Producto's nombre and Precio as they were when ordering, even if the Producto is later deleted.
_Avoid_: Artículo, item, detalle

**Estado**:
Where a Pedido is: _En espera_ (every new Pedido), _Preparado para entrega_ (ready to collect at the shop), _Entregado_ or _Cancelado_. Entregado and Cancelado are final; cancelling gives the kilos back to the Cantidad.
_Avoid_: Situación, status
