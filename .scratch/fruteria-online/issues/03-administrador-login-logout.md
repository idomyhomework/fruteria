# 03: Administrador login and logout

**What to build:** The Administrador can log in with usuario and contraseña from a form on the Catálogo page, stay logged in across pages, and log out with "Cerrar sesión". Once logged in, the home page also shows an Inventario link and "Cerrar sesión". Clientes never log in and see no Administrador controls. Establishes the shared server-side check that later write actions use to refuse anyone who isn't the logged-in Administrador. See `../spec.md`.

**Blocked by:** 01 (Walking skeleton: home page and read-only Catálogo)

**Status:** ready-for-agent

- [x] The config file holds the Administrador username (`admin`) and a password hash produced by `password_hash()`; login checks with `password_verify()`; no plaintext password anywhere in the code
- [x] Login form (usuario + contraseña) shown on the Catálogo only when not logged in
- [x] Wrong username or wrong password both show "Usuario o contraseña incorrectos"
- [x] Successful login regenerates the session ID; the session cookie ends when the browser closes
- [x] When logged in, the login form is replaced by "Cerrar sesión"; the home page shows an Inventario link and "Cerrar sesión"
- [x] "Cerrar sesión" destroys the session and returns to the page with Cliente view
- [x] A reusable server-side "require Administrador" check exists for write actions and pages
- [x] Tests: successful login shows logged-in state; wrong password and wrong username give the identical message; logout returns to Cliente view; home page links differ for Cliente vs Administrador
