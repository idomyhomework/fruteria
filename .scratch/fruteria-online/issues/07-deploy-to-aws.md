# 07: Deploy to AWS

**What to build:** The Frutería Online runs on the AWS Ubuntu machine under Apache with MariaDB, and its home page URL is ready to submit to Aules. A README explains setup so it can be repeated. See `../spec.md`.

**Blocked by:** 04 (Change Cantidad from the Catálogo), 05 (Delete a Producto from the Catálogo), 06 (Inventario: add a Producto), 10 (Administrador: Pedidos)

**Status:** ready-for-human

- [ ] README: required packages (Apache, PHP with PDO MySQL, MariaDB), running the setup SQL script, creating a dedicated MariaDB user for the app, filling in the config file
- [ ] README: how to generate a new Administrador password hash with `password_hash()` and put it in the config file
- [ ] README: how to run the HTTP tests locally
- [ ] App deployed under Apache on the AWS machine; security group allows HTTP (port 80)
- [ ] Manual smoke test on the live URL: home page, Catálogo with Fruta de la pasión, search, login, Actualizar, Eliminar, Inventario, Carrito, hacer un Pedido, Pedidos (cambiar Estado y cancelar), Cerrar sesión
- [ ] Home page URL submitted to Aules
- [ ] Known limitation noted in README: plain HTTP, so the Administrador password travels unencrypted
