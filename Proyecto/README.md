# Barbería - Reserva de Citas

## Descripción

Aplicación PHP para gestionar una barbería con **MySQL + Bootstrap 5**. Los clientes pueden ver servicios, reservar citas y gestionar su perfil, mientras que el administrador puede gestionar usuarios, barberos, servicios, citas y configuración.

Esta versión incluye:
- Página principal (`index.php`) con servicios y barberos.
- Login y registro de usuarios.
- Gestión de reservas para clientes.
- Panel administrativo básico.
- Configuración de sesión y conexión a base de datos en `config.php`.

---

## Nota de configuración

La configuración principal está en `config.php`.

- `DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME` para la conexión MySQL.
- `APP_URL` con la URL base de la aplicación.
- `USE_HTTPS`, `SESSION_TIMEOUT` y `HASH_ALGORITHM`.


---

### 1️⃣ Requisitos
- PHP 8.0+ (recomendado 8.2)
- MySQL 5.7+ o MariaDB
- Apache (XAMPP/WAMP recomendado)

### 2️⃣ Crear la base de datos
- Abre phpMyAdmin o tu cliente MySQL.
- Crea una base de datos nueva.
- Importa `base_de_datos/barberia_mejorada.sql`.

### 3️⃣ Configurar la conexión
Editar `config.php` y ajustar:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'barberia');

define('APP_URL', 'http://localhost/Proyecto');
```

### 4️⃣ Copiar al servidor web
Colocar la carpeta del proyecto en el directorio web:
```
C:\xampp\htdocs\Proyecto\
C:\wamp\www\Proyecto\
```

### 5️⃣ Probar la aplicación
Abrir en el navegador:
```
http://localhost/Proyecto/login.php
```

---

## Estructura del proyecto

```
Proyecto/
├── admin/
│   ├── barberos.php
│   ├── citas.php
│   ├── configuracion.php
│   ├── dashboard.php
│   ├── servicios.php
│   └── usuarios.php
├── api/
│   └── horarios_disponibles.php
├── assets/
├── base_de_datos/
│   ├── barberia.sql
│   └── barberia_mejorada.sql
├── config.php
├── ESTRUCTURA.txt
├── funciones.php
├── index.php
├── login.php
├── logout.php
├── mi_perfil.php
├── nueva_reserva.php
├── plantilla.php
├── README.md
├── registro.php
└── reservas.php
```

---

## Navegación principal

- `index.php` — Página de inicio con servicios, barberos y citas próximas del usuario.
- `login.php` — Inicio de sesión.
- `registro.php` — Registro de nuevos clientes.
- `logout.php` — Cierra sesión.
- `nueva_reserva.php` — Crear una reserva.
- `reservas.php` — Ver reservas del usuario.
- `mi_perfil.php` — Editar datos del usuario.
- `admin/*` — Panel administrativo.
- `api/horarios_disponibles.php` — Endpoint para obtener horarios disponibles.

---

## Panel administrativo

Las páginas dentro de `admin/` permiten al administrador:
- Ver el dashboard general.
- Gestionar `usuarios`.
- Gestionar `barberos`.
- Gestionar `servicios`.
- Ver y editar `citas`.
- Ajustar `configuracion` del sistema.

---

## Base de datos

Los archivos SQL disponibles son:
- `base_de_datos/barberia.sql`
- `base_de_datos/barberia_mejorada.sql`

Estas bases de datos contienen las tablas necesarias para usuarios, barberos, servicios, citas, horarios y configuración.

---

## Funcionalidades principales

- Gestión de sesiones con `session_start()` y expiración definida en `config.php`.
- Conexión MySQL con `$mysqli`.
- Uso de funciones compartidas en `funciones.php`.
- Redirección de usuarios no autenticados.
- Control de roles para administrador.

---

## Configuración recomendada

En `config.php` ajusta:
- `DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME`.
- `APP_URL` para que refleje la ruta de tu servidor.
- `USE_HTTPS` si tu sitio usa HTTPS.
- `SESSION_TIMEOUT` para cambiar la duración de la sesión.

---

## Avisos

- No uses las credenciales reales en repositorios públicos.
- En un entorno de desarrollo local, actualiza `APP_URL` a `http://localhost/Proyecto`.
- Si migras a otro hosting, revisa también las rutas de imagen y los enlaces en `index.php`.

---

## Pruebas

Para ejecutar las pruebas usa PHP desde la raíz del proyecto:

```bash
C:\xampp\php\php.exe tests/run_tests.php
```

Las pruebas verifican:
- escape HTML seguro
- validación de email
- hashing y verificación de contraseñas
- token CSRF
- conversiones de hora/minutos
- formateo de fecha y hora
- generación de slots libres
- mensajes Flash

## Solución de Problemas

### "Error de conexión: Access denied for user 'root'@'localhost'"

**Causa**: Credenciales incorrectas en config.php

**Solución**:
1. Verifica tu usuario MySQL real
2. Edita config.php líneas 12-13
3. Aplica credenciales correctas:
```php
define('DB_USER', 'root');    // Cambiar si es diferente
define('DB_PASS', '');        // Cambiar si tiene contraseña
```

### "Base de datos no existe / tabla no encontrada"

**Solución**:
1. Abre phpMyAdmin: http://localhost/phpmyadmin
2. Verifica que BD "barberia" existe
3. Si no existe, crea nueva BD
4. Importa el archivo: `base_de_datos/barberia_mejorada.sql`

### "Contraseña incorrecta pero debería ser correcta"

 Las contraseñas están hasheadas con bcrypt. Usa las credenciales por defecto:
- Cliente: `juan@email.com` / `123456`
- Admin: `admin@email.com` / `1234`

### "No puedo acceder a login.php"

**Verificar**:
- [ ] Apache está iniciado (XAMPP/WAMP)
- [ ] MySQL está iniciado
- [ ] Carpeta está en `htdocs/` o `www/`
- [ ] URL es: `http://localhost/barberia/login.php`

### "Sesión se cierra después de 1 hora"

**Funcionalidad**: Por diseño, las sesiones expiran tras 1 hora inactiva. Es seguridad.

**Para cambiar timeout**: Editar config.php línea 16:
```php
ini_set('session.gc_maxlifetime', 3600); // Cambiar a tu valor
```

---

### Recursos Externos
- [Documentación PHP](https://www.php.net/manual/es/)
- [Documentación MySQL](https://dev.mysql.com/doc/)
- [Bootstrap 5](https://getbootstrap.com/docs/5.3/)
- [SPA phpMyAdmin](https://www.phpmyadmin.net/)











