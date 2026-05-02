# 🏆 PROYECTO BARBERÍA - Sistema de Reservas Online

## 📋 Descripción

Sistema de gestión y reservas para una barbería, desarrollado con **PHP 8.2 + MySQL + Bootstrap 5**. Permite a los clientes reservar citas online y a los administradores gestionar el negocio de forma integral.

**Estado**: ✅ **BLOQUE 1 COMPLETADO** (Backend básico + Autenticación)

---

## 🎯 Características - Estado Actual

### ✅ IMPLEMENTADO (Bloque 1 - Backend Básico)
- ✅ Autenticación segura con login/logout
- ✅ Contraseñas hasheadas con bcrypt
- ✅ Gestión de sesiones con timeout (1 hora)
- ✅ Base de datos relacional (7 tablas)
- ✅ Control de roles (cliente/admin)
- ✅ Historial de acceso (auditoría)
- ✅ Validaciones seguras contra SQL injection

### 📋 PRÓXIMAMENTE (Bloques 2-4)
- 📦 Sistema de reservas con calendario
- 📦 CRUD de barberos y servicios
- 📦 Dashboard admin con estadísticas
- 📦 Gestión de citas y cancelaciones
- 📦 Perfil de usuario y edición de datos

---

## 🚀 Inicio Rápido (5 minutos)

### 1️⃣ Requisitos
- PHP 8.0+ (recomendado 8.2)
- MySQL 5.7+ o MariaDB
- Apache (XAMPP/WAMP recomendado)

### 2️⃣ Crear Base de Datos
```bash
# Opción A: phpMyAdmin
# - Ir a http://localhost/phpmyadmin
# - Crear BD "barberia"
# - Importar: base_de_datos/barberia_mejorada.sql

# Opción B: Línea de comandos
mysql -u root -e "CREATE DATABASE barberia CHARACTER SET utf8mb4;"
mysql -u root barberia < base_de_datos/barberia_mejorada.sql
```

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

### 5️⃣ Probar Login
```
URL: http://localhost/barberia/login.php
Email: juan@email.com
Pass: 1234
```

✅ ¡Listo! Estás autenticado.

---

## � Estructura de Carpetas

```
Proyecto/
│
├── 🔐 AUTENTICACIÓN (Bloque 1 ✅)
│   ├── login.php          # Login con validación
│   ├── logout.php         # Cerrar sesión
│   └── registro.php       # Registro clientes (diseño)
│
├── ⚙️  CONFIGURACIÓN
│   ├── config.php         # Conexión BD + sesiones
│   ├── funciones.php      # 13 funciones globales
│   └── plantilla.php      # Template HTML (pendiente)
│
├── 👤 CLIENTE (Bloques 2-3 - Próximamente)
│   ├── index.php
│   ├── nueva_reserva.php
│   ├── reservas.php
│   └── mi_perfil.php
│
├── 🛡️  ADMIN (Bloque 4 - Próximamente)
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
        ├── barberia.sql          # Versión original (no usar)
        └── barberia_mejorada.sql # ⭐ USAR ESTA
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
Todos: 1234
Hash: $2y$10$rS9Pv3M4.8qL9nX2K5tN.OW1jZ6mP7kL2hG5dY8wQ3rE4tU7nO
```

---

## 🔑 Credenciales de Prueba

### Cliente
```
Email: juan@email.com
Contraseña: 1234
```

### Administrador
```
Email: admin@email.com
Contraseña: 1234
```

⚠️ **IMPORTANTE**: Cambiar en producción.

---

## 🔧 Guía Técnica - Bloque 1

### Arquitectura

```
Browser (formulario) 
    ↓ POST (email, password)
config.php (sesión + BD)
    ↓ mysqli connection
funciones.php (validar_email, verificar_contrasena, etc)
    ↓
MySQL Database (7 tablas)
```

### Funciones Disponibles (13 en funciones.php)

**Validación & Seguridad:**
- `validar_email($email)` → bool
- `hashear_contrasena($pass)` → string bcrypt
- `verificar_contrasena($pass, $hash)` → bool
- `escapar($texto)` → string safe

**Base de Datos:**
- `obtener_usuario_por_email($email)` → array
- `obtener_usuario($id)` → array
- `registrar_acceso($id)` → void

**Control de Acceso:**
- `verificar_autenticacion()` → redirect si no logueado
- `verificar_admin()` → exit si no admin

**UI/UX:**
- `redirigir_con_mensaje($url, $msg, $tipo)` → redirect
- `mostrar_mensaje()` → HTML alert
- `formatear_fecha($fecha)` → string
- `formatear_hora($hora)` → string

### Flujo de Login

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
- Cliente: `juan@email.com` / `1234`
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

## 📈 Fases de Desarrollo

### Bloque 1 ✅ (COMPLETADO)
- Backend: Config, sesiones, login, logout
- BD: 7 tablas relacionales
- Funciones: 13 utilidades globales

### Bloque 2 📦 (Próximo)
- Frontend: Página principal, perfil, plantilla
- Mostrar servicios y barberos desde BD
- Interfaz responsive

### Bloque 3 📦
- Sistema de reservas con calendario
- Crear/ver/cancelar citas
- Validaciones complejas

### Bloque 4 📦
- Panel admin: CRUD completo
- Dashboard con estadísticas
- Gestión integral del negocio

---

## 🤝 Contribuir / Reportar Errores

Si encuentras un error o tienes sugerencias:
1. Verifica la Solución de Problemas arriba
2. Consulta la BD en phpMyAdmin
3. Revisa los logs de Apache/MySQL

---

## 📄 Licencia

Proyecto educativo - Libre para uso académico.

---

**Última actualización**: Mayo 2026  
**Versión**: 1.0 - Bloque 1 Completado
- [ ] Reportes avanzados (PDF, Excel)

### Fase 3
- [ ] Multi-idioma (i18n/l10n)
- [ ] Sistema de reseñas y valoraciones
- [ ] Programa de fidelización/puntos
- [ ] Integración con Google Calendar
- [ ] Dashboard móvil responsivo mejorado

### Fase 4
- [ ] Chat en vivo con soporte
- [ ] Integración redes sociales (login)
- [ ] Promociones y cupones descuento
- [ ] Análisis avanzado de negocio
- [ ] Sistema de turnos y cola virtual

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

---

## 📝 Notas Importantes

1. **Seguridad**: En producción, cambiar `DB_PASS` y usar HTTPS
2. **Respaldos**: Hacer backup regular de `barberia.sql`
3. **Updates PHP**: Usar PHP 8.2+ para máxima seguridad
4. **Validación**: Formularios críticos validados en servidor
5. **Logs**: Revisar archivo `historial_acceso` para auditoría

---

## 👨‍💻 Autor

Proyecto Final de Curso - 2DAW DWEC  
Desarrollado como demostración de competencias en PHP, MySQL y frontends Web.

---

## 📄 Licencia

Este proyecto es para uso educativo.

---

## ✅ Conclusión

El sistema está listo para validación final en entorno real.

Para comenzar:
1. Configura la BD (barberia_mejorada.sql)
2. Ajusta credenciales en config.php
3. Accede a http://localhost/barberia/
4. Prueba con las credenciales por defecto

¿Preguntas? Revisa la sección **Solución de Problemas**.

**Happy Coding! 💪**
