# Barbería - Reserva de Citas

## Descripción

Aplicación para gestionar una barbería con **PHP 8.2 + MySQL + Bootstrap 5**. Los clientes pueden crear citas y el administrador puede gestionar usuarios, servicios y reservas.

Esta versión incluye la parte de login, registro y gestión básica de citas.

---

**Nota de despliegue (25-05-2026):** La configuración de hosting (`hosting_config.php`) se ha integrado directamente en `config.php` en las copias locales del proyecto. Si prefieres mantener un archivo separado con credenciales, crea `hosting_config.php` y elimina las credenciales de `config.php`.

No publiques ni subas a un repositorio público los valores de `DB_USER`, `DB_PASS` o `DB_NAME`. Para compartir el proyecto deja esos valores vacíos o utiliza un `hosting_config.php.sample` con valores genéricos.



## Cómo empezar

### 1️⃣ Requisitos
- PHP 8.0+ (recomendado 8.2)
- MySQL 5.7+ o MariaDB
- Apache (XAMPP/WAMP recomendado)

### 2️⃣ Crear Base de Datos
```bash
#  phpMyAdmin
# - Ir a http://localhost/phpmyadmin
# - Crear BD "barberia"
# - Importar: base_de_datos/barberia_mejorada.sql



### 3️⃣ Configurar Conexión
Editar `config.php` (líneas 12-14):
```php
define('DB_USER', 'root');      // Tu usuario MySQL
define('DB_PASS', '');          // Tu contraseña
define('DB_NAME', 'barberia');  // Nombre BD
```

### 4️⃣ Copiar a Servidor Web
```
C:\xampp\htdocs\barberia\   (XAMPP)
C:\wamp\www\barberia\       (WAMP)
```

### 4️⃣ Probar la aplicación
Abre tu navegador en:

```
http://localhost/barberia/login.php
```

Usuarios de prueba:
- Cliente: `juan@email.com` / `123456`
- Admin: `admin@email.com` / `1234`

Con esos datos podrás entrar y ver las funciones básicas.

---

## � Estructura de Carpetas

```
Proyecto/
│
├── 🔐 AUTENTICACIÓN 
│   ├── login.php          # Login con validación
│   ├── logout.php         # Cerrar sesión
│   └── registro.php       # Registro clientes (diseño)
│
├── ⚙️  CONFIGURACIÓN
│   ├── config.php         # Conexión BD + sesiones
│   ├── funciones.php      # 13 funciones globales
│   └── plantilla.php      # Template HTML (pendiente)
│
├── 👤 CLIENTE 
│   ├── index.php
│   ├── nueva_reserva.php
│   ├── reservas.php
│   └── mi_perfil.php
│
├── 🛡️  ADMIN 
│   └── admin/
│       ├── dashboard.php
│       ├── usuarios.php
│       ├── barberos.php
│       ├── servicios.php
│       ├── citas.php
│       └── configuracion.php
│
└── 💾 BASE DE DATOS
    └── base_de_datos/
        ├── barberia.sql         
        └── barberia_mejorada.sql 
```

---

## 🗄️ Base de Datos - Tablas

### 7 Tablas Relacionales

| Tabla | Descripción | Campos Clave |
|-------|-------------|--------------|
| `usuarios` | Clientes + admin | ID, email, contraseña (bcrypt), rol |
| `barberos` | Profesionales | DNI, nombre, especialidad, horarios |
| `servicios` | Oferta de servicios | ID, nombre, precio, duración |
| `citas` | Reservas de clientes | ID, fecha, hora, estado, precios |
| `horarios_disponibles` | Disponibilidad | DNI_barbero, fecha, bloques libres |
| `configuracion_sistema` | Ajustes globales | horario apertura/cierre, duración slot |
| `historial_acceso` | Auditoría de logins | ID_usuario, fecha, IP, navegador |

**Hash de Contraseña (bcrypt)**:
```
Todos: 123456
Hash: $2y$10$rS9Pv3M4.8qL9nX2K5tN.OW1jZ6mP7kL2hG5dY8wQ3rE4tU7nO
```

---

## 🔑 Credenciales de Prueba

### Cliente
```
Email: juan@email.com
Contraseña: 123456
```

### Administrador
```
Email: admin@email.com
Contraseña: 1234
```

⚠️ **IMPORTANTE**: Cambiar en producción.

---

## Cómo funciona

La aplicación usa PHP y MySQL para gestionar usuarios, sesiones y citas.

- `config.php` controla la conexión a la base de datos y maneja las sesiones.
- `funciones.php` agrupa funciones comunes para seguridad, consulta y formato.
- `login.php` y `registro.php` gestionan el acceso de los usuarios.
- `nueva_reserva.php` y `reservas.php` se ocupan de las citas.
- Las páginas dentro de `admin/` son para el administrador.

### Funciones principales en `funciones.php`
- `validar_email($email)` — comprueba que el email tenga formato correcto.
- `hashear_contrasena($pass)` — guarda la contraseña en hash bcrypt.
- `verificar_contrasena($pass, $hash)` — comprueba contraseña contra hash.
- `obtener_usuario_por_email($email)` — carga los datos del usuario.
- `registrar_acceso($id)` — guarda el registro de entradas.
- `verificar_autenticacion()` — redirige al login si no hay sesión.
- `verificar_admin()` — bloquea accesos a usuarios sin permisos.

### Flujo de inicio de sesión
1. El usuario introduce email y contraseña.
2. El sistema busca el usuario en la base de datos.
3. Si coincide, inicia sesión y redirige.
4. Si no, muestra un mensaje de error.

1. **Usuario abre** → login.php (GET)
2. **Llena formulario** → email + contraseña
3. **Presiona enviar** → POST a login.php
4. **PHP valida**:
   - Email no vacío y formato válido
   - SELECT * FROM usuarios WHERE email
   - password_verify() con bcrypt
   - Usuario activo (activo=1)
5. **Si OK**: Crea $_SESSION y redirige
6. **Si Error**: Muestra mensaje y vuelve al form

### Ejemplo de Código - Usar Funciones

```php
<?php
require_once 'config.php';          // Conexión BD
require_once 'funciones.php';       // Funciones globales

// Validar que usuario está logueado
verificar_autenticacion();           // Si no, redirige a login

// Obtener datos usuario
$usuario = obtener_usuario($_SESSION['usuario_id']);

// Usar en plantilla
ob_start();
?>
  <h1>Bienvenido, <?= $usuario['nombre'] ?></h1>
<?php
$contenido = ob_get_clean();
include 'plantilla.php';
?>
```

---

## 🆘 Solución de Problemas

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

**Nota**: Las contraseñas están hasheadas con bcrypt. Usa las credenciales por defecto:
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

## 📚 Documentación Adicional

- **INICIO_RAPIDO.txt** - Guía de 5 minutos para setup
- **base_de_datos/barberia_mejorada.sql** - Script completo de BD

---



## 📞 Soporte y Documentación

### Archivos Útiles

| Archivo | Descripción |
|---------|-------------|
| `config.php` | Configuración principal |
| `funciones.php` | Funciones reutilizables |
| `barberia_mejorada.sql` | BD con todas las tablas |

### Recursos Externos
- [Documentación PHP](https://www.php.net/manual/es/)
- [Documentación MySQL](https://dev.mysql.com/doc/)
- [Bootstrap 5](https://getbootstrap.com/docs/5.3/)
- [SPA phpMyAdmin](https://www.phpmyadmin.net/)











