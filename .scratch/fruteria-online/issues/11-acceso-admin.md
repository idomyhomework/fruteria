# 11: Acceso del Administrador en admin.php

**What to build:** The Administrador login moves out of the Catálogo to its own page, `admin.php`, which is not linked from anywhere: the Administrador types its address. Clientes no longer see any login form. "Cerrar sesión" stays in the header. Hiding the address is cosmetic, not security: the password is what protects the Administrador pages. See `../spec.md` (stories 15, 22, 23, 25; Implementation Decisions → Pages).

**Blocked by:** 10 (Administrador: Pedidos)

**Status:** ready-for-agent

- [x] `admin.php` shows the login form (usuario, contraseña, "Entrar") in the existing `<section class="acceso">`; nothing links to it
- [x] `admin.php` with the Administrador already logged in redirects to the home page
- [x] The Catálogo no longer has the login form, for anyone
- [x] Successful login redirects to the home page; failed login redirects back to `admin.php` with "Usuario o contraseña incorrectos" (identical page for wrong usuario or wrong contraseña); `login.php` opened with GET redirects to `admin.php`
- [x] Opening an Administrador page or action without the Administrador session redirects to the Catálogo **without** a message ("Inicia sesión como Administrador" is removed) and changes nothing
- [x] "Cerrar sesión" stays in the header and logout behaves as before
- [x] Tests: `test_03` login tests use `admin.php`; Catálogo never shows `name="contrasena"`; `admin.php` redirects home when logged in; failed login returns to `admin.php`; tests 04/05/06 no longer expect "Inicia sesión como Administrador"
- [ ] Manual check: log in from `admin.php`, reach Inventario and Pedidos from home, log out from the header
