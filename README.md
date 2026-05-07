# EDINCA — Backend (Laravel)

API REST del sistema de gestión de EDINCA. Maneja autenticación, clientes, proyectos, solicitudes, cotizaciones y documentos.

---

## Tecnologías

| Tecnología | Uso |
|---|---|
| Laravel 12 | Framework PHP |
| PHP 8.2+ | Lenguaje |
| MySQL | Base de datos |
| Laravel Sanctum | Autenticación JWT |
| Laravel Mail | Envío de correos (SMTP) |

---

## Estructura del proyecto

```
backend-laravel/
├── app/
│   ├── Http/Controllers/
│   │   ├── AuthController.php          # Login / logout
│   │   ├── ClienteController.php       # CRUD clientes
│   │   ├── SolicitudController.php     # CRUD solicitudes + cambio de estado
│   │   ├── ProyectoController.php      # CRUD proyectos + cambio de estado
│   │   ├── CotizacionController.php    # CRUD cotizaciones + envío por correo
│   │   ├── DocumentoController.php     # Subida de PDF/DWG + envío al cliente
│   │   └── NotificacionController.php  # Notificaciones del sistema
│   │
│   ├── Models/
│   │   ├── Cliente.php                 # hasMany: proyectos, solicitudes, cotizaciones
│   │   ├── Proyecto.php                # belongsTo: cliente
│   │   ├── Solicitud.php               # belongsTo: cliente
│   │   ├── Cotizacion.php              # belongsTo: cliente
│   │   ├── Documento.php               # belongsTo: proyecto (→ cliente)
│   │   ├── Notificacion.php
│   │   └── Usuario.php                 # Administradores del sistema
│   │
│   └── Mail/
│       ├── CotizacionEnviada.php       # Email con PDF adjunto de cotización
│       └── DocumentoSubido.php         # Email con PDF/DWG adjunto al cliente
│
├── database/
│   └── migrations/
│       ├── 2024_01_01_000001_create_usuarios_table.php
│       ├── 2024_01_01_000002_create_clientes_table.php
│       ├── 2024_01_01_000003_create_solicitudes_table.php
│       ├── 2024_01_01_000004_create_proyectos_table.php
│       ├── 2024_01_01_000005_create_cotizaciones_table.php
│       ├── 2024_01_01_000006_create_documentos_table.php
│       ├── 2024_01_01_000007_create_notificaciones_table.php
│       ├── 2026_05_05_000001_add_tipos_to_proyectos_table.php  # Agrega EDIFICIO, LOCAL_COMERCIAL
│       └── 2026_05_06_000001_add_atrasado_to_proyectos_estado.php # Agrega estado ATRASADO
│
├── config/
│   └── cors.php                        # Permite peticiones desde www.edinca.cl
│
├── routes/
│   └── api.php                         # Todas las rutas de la API
│
├── storage/
│   └── app/public/documentos/          # Archivos PDF y DWG subidos
│
├── .env                                # Variables locales (Laragon) — NO subir a Git
├── .env.production                     # Variables de producción (HostGator) — NO subir a Git
└── .env.example                        # Plantilla de variables de entorno
```

---

## Base de datos

### Modelos y relaciones

```
Usuario          → Administradores del panel
Cliente          → hasMany Proyectos, Solicitudes, Cotizaciones
Solicitud        → belongsTo Cliente (opcional al momento de crear)
Proyecto         → belongsTo Cliente
Cotizacion       → belongsTo Cliente
Documento        → belongsTo Proyecto → belongsTo Cliente
Notificacion     → general del sistema
```

### ENUMs importantes

**Proyecto - tipo:**
```
CONSTRUCCION_NUEVA | AMPLIACION | REGULARIZACION | REMODELACION | EDIFICIO | LOCAL_COMERCIAL
```

**Proyecto - estado:**
```
PENDIENTE | EN_EJECUCION | ATRASADO | FINALIZADO | CANCELADO
```

**Solicitud - tipo:**
```
CASA | AMPLIACION | REGULARIZACION | EDIFICIO | LOCAL_COMERCIAL
```

**Solicitud - estado:**
```
PENDIENTE | EN_REVISION | APROBADA | RECHAZADA
```

---

## Variables de entorno

Copiar `.env.example` a `.env` y configurar:

```env
APP_NAME=EDINCA
APP_ENV=local
APP_KEY=           # Generar con: php artisan key:generate
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=edinca_local
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=1025      # Mailpit en local

FRONTEND_URL=http://localhost:3000
SANCTUM_STATEFUL_DOMAINS=localhost:3000
```

---

## Correr en local (Laragon)

```bash
# 1. Instalar dependencias
composer install

# 2. Copiar variables de entorno
copy .env.example .env

# 3. Generar clave de la app
php artisan key:generate

# 4. Correr migraciones
php artisan migrate

# 5. Crear usuario administrador
php artisan db:seed

# 6. Crear symlink de storage (para documentos)
php artisan storage:link

# El servidor corre automáticamente en Laragon → http://localhost:8000
```

---

## Endpoints principales de la API

Todas las rutas (excepto login) requieren header:
```
Authorization: Bearer {token}
```

| Método | Ruta | Descripción |
|---|---|---|
| POST | /api/login | Login administrador |
| POST | /api/logout | Cerrar sesión |
| GET | /api/clientes | Listar clientes |
| POST | /api/clientes | Crear cliente |
| GET | /api/solicitudes | Listar solicitudes |
| PATCH | /api/solicitudes/{id}/estado | Cambiar estado solicitud |
| GET | /api/proyectos | Listar proyectos (filtrable por ?cliente_id=X) |
| POST | /api/proyectos | Crear proyecto |
| PATCH | /api/proyectos/{id}/estado | Cambiar estado proyecto |
| GET | /api/cotizaciones | Listar cotizaciones |
| POST | /api/cotizaciones | Crear y enviar cotización por correo |
| GET | /api/documentos | Listar documentos |
| POST | /api/documentos/upload | Subir PDF/DWG y enviar al cliente |
| DELETE | /api/documentos/{id} | Eliminar documento |

---

## Despliegue en producción (HostGator)

```bash
# En local: preparar para producción
composer install --no-dev --optimize-autoloader

# Subir archivos al servidor via Git o File Manager
# En el servidor ejecutar:
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
```

### Configuración del servidor
- **Dominio:** api.edinca.cl
- **Document Root:** public_html/api/public
- **PHP:** 8.3 (ea-php83)
- **Base de datos:** dbacbbmi_edinca_db
- **Usuario BD:** dbacbbmi_edinca_user

---

## CORS

Configurado en `config/cors.php` para aceptar peticiones desde:
- `https://www.edinca.cl`
- `https://edinca.cl`
- `http://localhost:3000` (desarrollo local)

---

## Correos corporativos

El sistema envía correos desde `contacto@edinca.cl` via SMTP de HostGator:
- **Servidor:** mail.edinca.cl
- **Puerto:** 465 (SSL)

Correos disponibles:
- contacto@edinca.cl
- departamentolegal@edinca.cl
- leonardo@edinca.cl
- eduardo@edinca.cl
- rodrigo@edinca.cl
