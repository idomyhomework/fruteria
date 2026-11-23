# Each Línea of a Pedido copies the Producto's nombre and Precio

A Línea keeps its own copy of the Producto's nombre and Precio from the moment the Pedido is placed, plus a nullable link to the Producto that becomes empty if the Producto is deleted. We chose this over forbidding the Administrador from deleting a Producto that has Pedidos, because Eliminar must keep working as it does today, and a Pedido must stay readable and keep the same total forever. The duplicated data is deliberate: don't "normalise" it away by reading nombre and Precio from the Producto table.

## Consequences

- Cancelling a Pedido gives kilos back only to Líneas whose Producto still exists.
- A Pedido's total is always computed from the copied Precio, never from the current one.
