# Project Manager — Guía de Instalación

**Backend**: Laravel 12 · **PHP**: 8.2+ · **Auth**: Laravel Sanctum

---

## Tabla de Contenidos

1. [Requisitos Previos](#1-requisitos-previos)
2. [Instalación](#2-instalación)
3. [Configuración del Entorno](#3-configuración-del-entorno)
4. [Base de Datos](#4-base-de-datos)
5. [Verificación](#5-verificación)
6. [Estructura del Proyecto](#6-estructura-del-proyecto)
7. [Troubleshooting](#7-troubleshooting)

---

## 1. Requisitos Previos

### Software Requerido

| Herramienta | Versión Mínima | Enlace |
|-------------|---------------|--------|
| PHP | 8.2+ | [php.net](https://www.php.net/) |
| Composer | Latest | [getcomposer.org](https://getcomposer.org/) |
| MySQL | 8.0+ | [mysql.com](https://www.mysql.com/) |
| Git | Any | [git-scm.com](https://git-scm.com/) |

### Extensiones PHP Necesarias

```
PDO        → Acceso a base de datos
PDO_MySQL  → Driver MySQL para PDO
XML        → Requerido por Laravel
Ctype      → Requerido por Laravel
JSON       → Requerido por Laravel
OpenSSL    → Encriptación
Mbstring   → Manipulación de strings
```

### Verificar Instalación

```bash
php -v             # PHP 8.2+
composer --version # Composer
mysql --version    # MySQL 8.0+
git --version      # Git
```

---

## 2. Instalación

### Opción A: Instalación Rápida (Recomendada)

```bash
# 1. Clonar repositorio
git clone https://github.com/gerhern/project_manager_back.git
cd project_manager_back

# 2. Ejecutar setup automático
composer run setup

# 3. Iniciar servidor de desarrollo
php artisan serve
```

El comando `composer run setup` realiza automáticamente todos los pasos de la instalación manual: instala dependencias PHP, copia el archivo `.env`, genera la clave de aplicación y ejecuta las migraciones.

> **Nota:** Antes de ejecutar `composer run setup`, asegúrate de haber creado la base de datos en MySQL y de haber configurado las credenciales en el archivo `.env`.

---

### Opción B: Instalación Manual

#### Paso 1 — Clonar el repositorio

```bash
git clone https://github.com/gerhern/project_manager_back.git
cd project_manager_back
```

#### Paso 2 — Instalar dependencias PHP

```bash
composer install
```

#### Paso 3 — Configurar el archivo de entorno

```bash
cp .env.example .env
```

Editar el archivo `.env` con las credenciales de la base de datos antes de continuar (ver sección [3. Configuración del Entorno](#3-configuración-del-entorno)).

#### Paso 4 — Generar clave de aplicación

```bash
php artisan key:generate
```

#### Paso 5 — Crear la base de datos en MySQL

```sql
CREATE DATABASE project_manager CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

#### Paso 6 — Ejecutar migraciones

```bash
php artisan migrate
```

#### Paso 7 — Iniciar el servidor

```bash
php artisan serve
```

La API estará disponible en: `http://localhost:8000/api`

---

## 3. Configuración del Entorno

El archivo `.env` controla toda la configuración de la aplicación. A continuación se describen las variables relevantes.

### Configuración General

```dotenv
APP_NAME=ProjectManager
APP_ENV=local          # local | production
APP_DEBUG=true         # false en producción
APP_URL=http://localhost:8000
```

### Base de Datos

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=project_manager
DB_USERNAME=root
DB_PASSWORD=
```

### Notas para Producción

Antes de desplegar en un entorno de producción, asegurarse de:

- Establecer `APP_DEBUG=false`
- Usar credenciales seguras en todas las variables de entorno
- Habilitar HTTPS
- Configurar CORS apropiadamente
- Asegurarse de que el usuario de MySQL tenga únicamente los permisos necesarios

---

## 4. Base de Datos

### Migraciones

```bash
# Ejecutar todas las migraciones pendientes
php artisan migrate

# Reiniciar la base de datos desde cero
php artisan migrate:refresh

# Rollback completo
php artisan migrate:reset
```

### Seeds (datos de prueba)

```bash
php artisan db:seed
```

### Consola Interactiva

```bash
php artisan tinker
```

---

## 5. Verificación

Una vez completada la instalación, ejecutar los siguientes comandos para confirmar que todo funciona correctamente:

```bash
# Revisar estado general de la aplicación
php artisan about

# Listar todas las rutas API registradas
php artisan route:list --path=api

# Ejecutar tests automatizados
php artisan test

# Limpiar y regenerar caché de configuración
php artisan config:clear
php artisan config:cache
```

Si todos los comandos responden sin errores, la instalación fue exitosa. Para validar la API, realizar una petición de prueba al endpoint de login:

```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email": "user@example.com", "password": "password"}'
```

---

## 6. Estructura del Proyecto

```
project_manager_back/
├── app/
│   ├── Enums/            → Estados de entidades (ProjectStatus, TaskStatus, etc.)
│   ├── Http/
│   │   ├── Controllers/  → Lógica de endpoints
│   │   ├── Middleware/   → Validaciones HTTP
│   │   └── Requests/     → Validación de entrada
│   ├── Models/           → Modelos Eloquent ORM
│   ├── Observers/        → Event listeners de modelos
│   ├── Policies/         → Autorización
│   ├── Traits/           → Código reutilizable
│   └── Notifications/    → Notificaciones del sistema
├── config/               → Archivos de configuración
├── database/
│   ├── migrations/       → Scripts de creación de tablas
│   ├── factories/        → Generadores de datos de prueba
│   └── seeders/          → Datos iniciales
├── routes/
│   ├── api.php           → Definición de endpoints API
│   └── console.php       → Comandos de consola
├── tests/
│   ├── Feature/          → Tests de funcionalidad
│   └── Unit/             → Tests unitarios
├── .env                  → Variables de entorno (no versionar)
├── .env.example          → Plantilla de variables de entorno
└── composer.json         → Dependencias PHP
```

---

## 7. Troubleshooting

### "Class not found" o error fatal al iniciar

```bash
composer install
composer dump-autoload
```

### "No application encryption key has been specified"

```bash
php artisan key:generate
```

### "Table 'XXX' doesn't exist"

```bash
php artisan migrate
```

### Error de conexión a MySQL

Verificar que el servicio de MySQL esté activo y que las credenciales en `.env` sean correctas:

```bash
mysql -u root -p -e "SHOW DATABASES;"
```

Si la base de datos no existe, crearla:

```sql
CREATE DATABASE project_manager CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Error 419 / CSRF en endpoints API

Asegurarse de incluir el header `Accept: application/json` en todas las peticiones. La API usa Sanctum tokens, no sessions con CSRF.

### Ver logs en tiempo real

```bash
php artisan pail
# o
tail -f storage/logs/laravel.log
```

---

## Dependencias Principales

| Paquete | Versión | Propósito |
|---------|---------|-----------|
| Laravel | 12 | Framework principal |
| Laravel Sanctum | Latest | Autenticación API por tokens |
| Spatie Laravel Permission | Latest | Control de roles y permisos (RBAC) |
| PHPUnit | 11 | Testing |

---

## Recursos Adicionales

- [Repositorio del Proyecto](https://github.com/gerhern/project_manager_back)
- [Documentación Laravel 12](https://laravel.com/docs/12.x)
- [Laravel Sanctum](https://laravel.com/docs/12.x/sanctum)
- [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission)

---
