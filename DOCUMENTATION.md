# 📚 Project Manager - Documentación Completa

## 📋 Índice

1. [Descripción General](#descripción-general)
2. [Requisitos Previos](#requisitos-previos)
3. [Guía de Instalación](#guía-de-instalación)
4. [Estructura del Proyecto](#estructura-del-proyecto)
5. [Flujos de Trabajo Principales](#flujos-de-trabajo-principales)
6. [Autenticación y Autorización](#autenticación-y-autorización)
7. [Documentación de API Endpoints](#documentación-de-api-endpoints)
8. [Ejemplos de Uso](#ejemplos-de-uso)
9. [Troubleshooting](#troubleshooting)

---

## 🎯 Descripción General

**Project Manager** es una aplicación backend construida con Laravel 12 para gestionar proyectos de forma jerárquica y colaborativa.

### Características Principales:
- ✅ Gestión de equipos con roles y permisos
- ✅ Creación y seguimiento de proyectos
- ✅ Descomposición de proyectos en objetivos
- ✅ Asignación y seguimiento de tareas
- ✅ Control de acceso basado en roles (RBAC)
- ✅ Sistema de disputas para conflictos de proyectos
- ✅ API RESTful completa
- ✅ Autenticación con Laravel Sanctum

### Stack Tecnológico:
- **Backend**: Laravel 12 (PHP 8.2+)
- **Base de datos**: SQLite (desarrollo) / MySQL (producción)
- **Autenticación**: Laravel Sanctum
- **Permisos**: Spatie Laravel Permission
- **Frontend Assets**: Vite + Tailwind CSS
- **Testing**: PHPUnit 11

---

## 📦 Requisitos Previos

### Software Requerido:
- **PHP 8.2+** - [Descargar](https://www.php.net/)
- **Composer** - [Descargar](https://getcomposer.org/) (gestor de dependencias PHP)
- **Node.js 18+** - [Descargar](https://nodejs.org/) (para assets Frontend)
- **Git** - [Descargar](https://git-scm.com/)

### Base de Datos (Elija una):
- **SQLite** - Incluido, no requiere instalación
- **MySQL 8.0+** - Para producción
- **PostgreSQL 12+** - Alternativa a MySQL

### Extensiones PHP Necesarias:
```
- PDO (para acceso a base de datos)
- XML, Ctype, JSON (requeridas por Laravel)
- OpenSSL (para encriptación)
- Mbstring (para manipulación de strings)
```

### Verificar Requisitos:
```bash
php -v                    # Verificar versión PHP
composer --version        # Verificar Composer
node -v && npm -v        # Verificar Node.js
php artisan check        # Laravel completará el check
```

---

## 🚀 Guía de Instalación

### Opción 1: Instalación Rápida (Recomendada)

```bash
# 1. Clonar repositorio (si aplica)
git clone <URL_REPO> project-manager-back
cd project-manager-back

# 2. Ejecutar setup automático
composer run setup

# 3. Iniciar servidor de desarrollo
composer run dev
```

**La instalación rápida realiza:**
- ✅ Instala dependencias PHP (Composer)
- ✅ Copia `.env.example` a `.env`
- ✅ Genera clave de aplicación
- ✅ Ejecuta migraciones de base de datos
- ✅ Instala dependencias NPM
- ✅ Compila assets (Vite)

### Opción 2: Instalación Manual Paso a Paso

#### Paso 1: Descargar Dependencias PHP
```bash
composer install
```

#### Paso 2: Configurar Archivo .env
```bash
cp .env.example .env
```

**Editar `.env` según tu ambiente:**

```dotenv
APP_NAME=ProjectManager
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Base de datos (SQLite por defecto)
DB_CONNECTION=sqlite
# Para MySQL, descomentar y ajustar:
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=project_manager
# DB_USERNAME=root
# DB_PASSWORD=

# Mailgun/SendGrid (opcional)
MAIL_MAILER=log
```

#### Paso 3: Generar Clave de Aplicación
```bash
php artisan key:generate
```

#### Paso 4: Crear Base de Datos SQLite
```bash
# SQLite: crear archivo automáticamente
touch database/database.sqlite
```

#### Paso 5: Ejecutar Migraciones
```bash
php artisan migrate
```

Esto crea todas las tablas en la base de datos.

#### Paso 6: Instalar Dependencias Frontend
```bash
npm install
```

#### Paso 7: Compilar Assets
```bash
npm run build    # Producción
npm run dev      # Desarrollo (con watch)
```

#### Paso 8: Iniciar Servidor
```bash
php artisan serve
```

El servidor estará disponible en: `http://localhost:8000`

---

## 📁 Estructura del Proyecto

```
project-manager-back/
├── app/
│   ├── Console/          → Comandos Artisan
│   ├── Enums/            → Estados de la aplicación
│   │   ├── ProjectStatus.php
│   │   ├── ObjectiveStatus.php
│   │   ├── TaskStatus.php
│   │   ├── TeamStatus.php
│   │   └── DisputeStatus.php
│   ├── Http/
│   │   ├── Controllers/  → Lógica de endpoints
│   │   │   ├── ProjectController.php
│   │   │   ├── ObjectiveController.php
│   │   │   ├── TaskController.php
│   │   │   └── TeamController.php
│   │   ├── Middleware/   → Validaciones HTTP
│   │   └── Requests/     → Validación de entrada
│   ├── Models/           → Modelos Eloquent ORM
│   │   ├── Project.php
│   │   ├── Objective.php
│   │   ├── Task.php
│   │   ├── Team.php
│   │   ├── User.php
│   │   └── ...
│   ├── Observers/        → Event listeners de modelos
│   ├── Policies/         → Autorización
│   ├── Traits/           → Código reutilizable
│   └── Notifications/    → Notificaciones
├── config/               → Configuraciones
├── database/
│   ├── migrations/       → Scripts de BD
│   ├── factories/        → Generadores de datos de prueba
│   └── seeders/          → Datos seed iniciales
├── routes/
│   ├── api.php          → Rutas API
│   ├── web.php          → Rutas web
│   └── console.php      → Comandos console
├── tests/               → Tests automatizados
│   ├── Feature/         → Tests de funcionalidad
│   └── Unit/            → Tests unitarios
├── storage/             → Archivos de aplicación
├── public/              → Archivos públicos
├── .env                 → Variables de entorno
├── composer.json        → Dependencias PHP
├── package.json         → Dependencias Node.js
└── artisan              → CLI de Laravel
```

### Archivos Importantes:

| Archivo | Descripción |
|---------|-------------|
| `.env` | Configuración del entorno |
| `routes/api.php` | Definición de endpoints API |
| `database/migrations/` | Scripts de creación de tablas |
| `app/Models/` | Definición de modelos de datos |
| `app/Http/Controllers/` | Lógica de negocio |

---

## 🔄 Flujos de Trabajo Principales

### Flujo 1: Creación de Equipo y Proyecto

```
┌─────────────────────────────────────────────────────────┐
│ 1. ADMINISTRADOR CREA EQUIPO                            │
├─────────────────────────────────────────────────────────┤
│ POST /teams/create                                      │
│ {                                                       │
│   "name": "Backend Team",                              │
│   "description": "Equipo de desarrollo backend"        │
│ }                                                       │
│ ↓ Respuesta: Team creado con rol Admin                 │
└─────────────────────────────────────────────────────────┘
              ↓
┌─────────────────────────────────────────────────────────┐
│ 2. MANAGER CREA PROYECTO                                │
├─────────────────────────────────────────────────────────┤
│ POST /projects/store                                    │
│ {                                                       │
│   "name": "Mobile App v2.0",                           │
│   "description": "Nueva versión de la app móvil",      │
│   "team_id": 1                                          │
│ }                                                       │
│ ↓ Respuesta: Proyecto creado, status=Active            │
└─────────────────────────────────────────────────────────┘
              ↓
┌─────────────────────────────────────────────────────────┐
│ 3. MANAGER CREA OBJETIVO                                │
├─────────────────────────────────────────────────────────┤
│ POST /projects/1/objectives/store                       │
│ {                                                       │
│   "title": "Módulo de Autenticación",                  │
│   "description": "OAuth2 y JWT"                         │
│ }                                                       │
│ ↓ Respuesta: Objetivo creado, status=NotCompleted     │
└─────────────────────────────────────────────────────────┘
              ↓
┌─────────────────────────────────────────────────────────┐
│ 4. MANAGER CREA TAREAS                                  │
├─────────────────────────────────────────────────────────┤
│ POST /projects/1/objectives/1/tasks/store              │
│ [                                                       │
│   {title: "Diseñar BD", due_date: "2026-02-15"},       │
│   {title: "Implementar API", due_date: "2026-02-20"},  │
│   {title: "Tests", due_date: "2026-02-25"}             │
│ ]                                                       │
│ ↓ Respuesta: Tareas creadas, status=Pending            │
└─────────────────────────────────────────────────────────┘
```

### Flujo 2: Asignación y Ejecución de Tareas

```
┌─────────────────────────────────────────────────────────┐
│ 1. MANAGER ASIGNA TAREA                                 │
├─────────────────────────────────────────────────────────┤
│ PATCH /projects/1/objectives/1/tasks/1/status          │
│ {                                                       │
│   "status": "Assigned",                                 │
│   "assigned_user_id": 5                                 │
│ }                                                       │
│ ↓ Status: Pending → Assigned                           │
└─────────────────────────────────────────────────────────┘
              ↓
┌─────────────────────────────────────────────────────────┐
│ 2. USUARIO VE TAREA ASIGNADA                            │
├─────────────────────────────────────────────────────────┤
│ GET /projects/1/objectives/1/tasks/1                    │
│ ↓ Respuesta: Tarea con datos completos                 │
└─────────────────────────────────────────────────────────┘
              ↓
┌─────────────────────────────────────────────────────────┐
│ 3. USUARIO INICIA TAREA                                 │
├─────────────────────────────────────────────────────────┤
│ PATCH /projects/1/objectives/1/tasks/1/status          │
│ {                                                       │
│   "status": "InProgress"                                │
│ }                                                       │
│ ↓ Status: Assigned → InProgress                        │
│ ↓ Usuario comienza a trabajar                          │
└─────────────────────────────────────────────────────────┘
              ↓
┌─────────────────────────────────────────────────────────┐
│ 4. USUARIO MARCA COMO COMPLETADA                        │
├─────────────────────────────────────────────────────────┤
│ PATCH /projects/1/objectives/1/tasks/1/status          │
│ {                                                       │
│   "status": "Completed"                                 │
│ }                                                       │
│ ↓ Status: InProgress → Completed                       │
│ ↓ Sistema verifica si objetivo completo                │
└─────────────────────────────────────────────────────────┘
              ↓
┌─────────────────────────────────────────────────────────┐
│ 5. [AUTOMÁTICO] VERIFICA OBJETIVO                       │
├─────────────────────────────────────────────────────────┤
│ ¿Todas las tareas = Completed?                          │
│ SÍ → Objective.status = Completed (automático)          │
│      ↓ Verifica proyecto                               │
│      ¿Todos objetivos = Completed?                      │
│      SÍ → Project.status = Completed ✅                │
│      NO → Project sigue Active                          │
│ NO → Objective sigue NotCompleted                       │
└─────────────────────────────────────────────────────────┘
```

### Flujo 3: Gestión de Equipos

```
┌─────────────────────────────────────────────────────────┐
│ 1. ADMIN VISUALIZA EQUIPOS                              │
├─────────────────────────────────────────────────────────┤
│ GET /teams                                              │
│ ↓ Respuesta: Lista de equipos del usuario               │
└─────────────────────────────────────────────────────────┘
              ↓
┌─────────────────────────────────────────────────────────┐
│ 2. ADMIN VE DETALLES DEL EQUIPO                         │
├─────────────────────────────────────────────────────────┤
│ GET /teams/1                                            │
│ ↓ Respuesta: Equipo con miembros y proyectos           │
└─────────────────────────────────────────────────────────┘
              ↓
┌─────────────────────────────────────────────────────────┐
│ 3. ADMIN ACTUALIZA EQUIPO                               │
├─────────────────────────────────────────────────────────┤
│ PATCH /teams/1/update                                   │
│ {                                                       │
│   "name": "Backend Team v2.0",                          │
│   "description": "Actualizado"                          │
│ }                                                       │
│ ↓ Respuesta: Equipo actualizado                        │
└─────────────────────────────────────────────────────────┘
```

---

## 🔐 Autenticación y Autorización

### Autenticación con Laravel Sanctum

El proyecto usa **Laravel Sanctum** para autenticación API basada en tokens.

#### Obtener Token:
```bash
# Supuesto: existe usuario con email/password
POST /api/login
{
  "email": "user@example.com",
  "password": "password123"
}

Respuesta:
{
  "token": "1|eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "user": { ... }
}
```

#### Usar Token en Requests:
```bash
# Incluir en header Authorization
curl -H "Authorization: Bearer <TOKEN>" \
     -H "Accept: application/json" \
     https://api.example.com/projects
```

### Roles y Permisos

El proyecto usa **Spatie Laravel Permission** con 3 roles principales:

| Rol | Descripción | Permisos |
|-----|-------------|----------|
| **Admin** | Administrador del equipo | Crear equipos, gestionar miembros |
| **Manager** | Gestor de proyectos | Crear proyectos, objetivos, tareas |
| **Member** | Miembro del equipo | Ejecutar tareas asignadas |

#### Verificar Permiso en Código:
```php
// En controlador
Gate::authorize('createProject', $team);

// En modelo
$user->can('updateTask', $task);

// En request
if ($request->user()->cannot('updateProject', $project)) {
    abort(403, 'Unauthorized');
}
```

---

## 📡 Documentación de API Endpoints

### Autenticación Base

Todos los endpoints requieren autenticación excepto los especificados.

```
Authentication: Bearer {token}
Content-Type: application/json
Accept: application/json
```

### 1. EQUIPOS (Teams)

#### Listar equipos del usuario
```
GET /api/teams

Respuesta (200):
[
  {
    "id": 1,
    "name": "Backend Team",
    "description": "Equipo de desarrollo",
    "status": "Active",
    "created_at": "2026-02-08T10:00:00Z",
    "pivot": {
      "role_id": 1,
      "role_name": "Admin"
    }
  }
]
```

#### Crear equipo
```
POST /api/teams/create

Body:
{
  "name": "Frontend Team",
  "description": "Equipo de desarrollo frontend"
}

Respuesta (201):
{
  "id": 2,
  "name": "Frontend Team",
  "description": "Equipo de desarrollo frontend",
  "status": "Active",
  "created_at": "2026-02-08T10:05:00Z"
}

Errores:
- 400: Validación fallida
- 422: Email o data duplicada
```

#### Ver detalles del equipo
```
GET /api/teams/{team_id}

Respuesta (200):
{
  "id": 1,
  "name": "Backend Team",
  "status": "Active",
  "members": [
    {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "role": "Admin"
    }
  ],
  "projects": [...]
}

Errores:
- 404: Equipo no encontrado
```

#### Actualizar equipo
```
PATCH /api/teams/{team_id}/update

Body:
{
  "name": "Backend Team v2",
  "description": "Equipo actualizado"
}

Respuesta (200):
{
  "id": 1,
  "name": "Backend Team v2",
  ...
}

Errores:
- 403: No tienes permisos
- 404: Equipo no encontrado
```

#### Desactivar equipo
```
DELETE /api/teams/inactive/{team_id}

Respuesta (200):
{
  "message": "Team inactivated successfully",
  "team": {...}
}

Errores:
- 403: No tienes permisos
```

---

### 2. PROYECTOS (Projects)

#### Listar proyectos del usuario
```
GET /api/projects/index

Respuesta (200):
[
  {
    "id": 1,
    "name": "Mobile App v2.0",
    "description": "Nueva versión de la app",
    "status": "Active",
    "team_id": 1,
    "user_id": 1,
    "objectives_count": 3,
    "created_at": "2026-02-08T10:00:00Z"
  }
]

Parámetros:
- (Ninguno requerido)
```

#### Crear proyecto
```
POST /api/projects/store

Body:
{
  "name": "Mobile App v2.0",
  "description": "Nueva versión de la aplicación móvil",
  "team_id": 1
}

Respuesta (201):
{
  "id": 1,
  "name": "Mobile App v2.0",
  "status": "Active",
  "team_id": 1,
  "user_id": 1,
  "created_at": "2026-02-08T10:00:00Z"
}

Validaciones:
- name: requerido, string, máx 255
- description: opcional, string
- team_id: requerido, entero, debe existir

Errores:
- 403: No tienes permisos en el equipo
- 422: Validación fallida
```

#### Ver detalles del proyecto
```
GET /api/projects/show/{project_id}

Respuesta (200):
{
  "id": 1,
  "name": "Mobile App v2.0",
  "description": "Nueva versión",
  "status": "Active",
  "team": { ... },
  "creator": { ... },
  "objectives": [
    {
      "id": 1,
      "title": "Módulo Autenticación",
      "status": "NotCompleted",
      "tasks_count": 3
    }
  ]
}

Errores:
- 404: Proyecto no encontrado
```

#### Actualizar proyecto
```
PUT/PATCH /api/projects/{project_id}

Body:
{
  "name": "Mobile App v2.1",
  "description": "Versión mejorada"
}

Respuesta (200):
{
  "id": 1,
  "name": "Mobile App v2.1",
  ...
}

Errores:
- 403: No tienes permisos
- 404: Proyecto no encontrado
```

#### Cancelar proyecto
```
DELETE /api/projects/cancel/{project_id}

Respuesta (200):
{
  "message": "Project canceled successfully",
  "project": {
    "id": 1,
    "status": "CancelInProgress"
  }
}

Errores:
- 403: No tienes permisos
```

---

### 3. OBJETIVOS (Objectives)

#### Listar objetivos del proyecto
```
GET /api/projects/{project_id}/objectives

Respuesta (200):
[
  {
    "id": 1,
    "title": "Módulo Autenticación",
    "description": "Implementar OAuth2 y JWT",
    "status": "NotCompleted",
    "tasks_count": 3,
    "created_at": "2026-02-08T10:00:00Z"
  }
]

Parámetros:
- (Ninguno requerido)
```

#### Crear objetivo
```
POST /api/projects/{project_id}/objectives/store

Body:
{
  "title": "Módulo de Pagos",
  "description": "Integración con Stripe"
}

Respuesta (201):
{
  "id": 2,
  "title": "Módulo de Pagos",
  "status": "NotCompleted",
  "project_id": 1,
  "created_at": "2026-02-08T10:05:00Z"
}

Validaciones:
- title: requerido, string, máx 255
- description: opcional, string

Errores:
- 403: No tienes permisos
- 404: Proyecto no encontrado
```

#### Ver detalles del objetivo
```
GET /api/projects/{project_id}/objectives/{objective_id}

Respuesta (200):
{
  "id": 1,
  "title": "Módulo Autenticación",
  "description": "OAuth2 y JWT",
  "status": "NotCompleted",
  "tasks": [
    {
      "id": 1,
      "title": "Diseñar BD",
      "status": "Pending",
      "due_date": "2026-02-15"
    }
  ]
}

Errores:
- 404: Objetivo no encontrado
```

#### Actualizar objetivo
```
PUT/PATCH /api/projects/{project_id}/objectives/{objective_id}

Body:
{
  "title": "Módulo Autenticación v2",
  "description": "OAuth2, JWT y SSO"
}

Respuesta (200):
{
  "id": 1,
  "title": "Módulo Autenticación v2",
  ...
}

Errores:
- 403: No tienes permisos
- 404: Objetivo no encontrado
```

#### Cancelar objetivo
```
DELETE /api/projects/{project_id}/objectives/{objective_id}

Respuesta (200):
{
  "message": "Objective canceled successfully",
  "objective": {
    "id": 1,
    "status": "Canceled"
  }
}

Errores:
- 403: No tienes permisos
```

---

### 4. TAREAS (Tasks)

#### Listar tareas del objetivo
```
GET /api/projects/{project_id}/objectives/{objective_id}/tasks

Respuesta (200):
[
  {
    "id": 1,
    "title": "Diseñar esquema BD",
    "description": "Crear diagrama E-R",
    "status": "Pending",
    "due_date": "2026-02-15",
    "objective_id": 1,
    "assigned_user": null,
    "created_at": "2026-02-08T10:00:00Z"
  }
]

Parámetros:
- (Ninguno requerido)
```

#### Crear tarea
```
POST /api/projects/{project_id}/objectives/{objective_id}/store

Body:
{
  "title": "Implementar API",
  "description": "Endpoints REST",
  "due_date": "2026-02-20",
  "user_id": 5  (opcional)
}

Respuesta (201):
{
  "id": 2,
  "title": "Implementar API",
  "status": "Pending",  // o "Assigned" si user_id proporcionado
  "objective_id": 1,
  "created_at": "2026-02-08T10:05:00Z"
}

Validaciones:
- title: requerido, string, máx 255
- description: opcional, string
- due_date: opcional, fecha válida
- user_id: opcional, usuario debe existir

Errores:
- 403: No tienes permisos
- 404: Objetivo no encontrado
```

#### Ver detalles de la tarea
```
GET /api/projects/{project_id}/objectives/{objective_id}/tasks/{task_id}

Respuesta (200):
{
  "id": 1,
  "title": "Diseñar esquema BD",
  "description": "Crear diagrama E-R",
  "status": "Pending",
  "due_date": "2026-02-15",
  "assigned_user": null,
  "comments": [],
  "created_at": "2026-02-08T10:00:00Z"
}

Errores:
- 404: Tarea no encontrada
```

#### Actualizar tarea
```
PUT/PATCH /api/projects/{project_id}/objectives/{objective_id}/tasks/{task_id}/update

Body:
{
  "title": "Diseñar esquema BD (Revisado)",
  "description": "Crear diagrama E-R detallado",
  "due_date": "2026-02-16"
}

Respuesta (200):
{
  "id": 1,
  "title": "Diseñar esquema BD (Revisado)",
  ...
}

Errores:
- 403: No tienes permisos
- 404: Tarea no encontrada
```

#### Cambiar estado de la tarea ⭐
```
PATCH /api/projects/{project_id}/objectives/{objective_id}/tasks/{task_id}/status

Body:
{
  "status": "InProgress"
}

Respuesta (200):
{
  "id": 1,
  "title": "Diseñar esquema BD",
  "status": "InProgress",
  "updated_at": "2026-02-08T14:30:00Z"
}

Transiciones Válidas:
- Pending → Assigned
- Assigned → InProgress
- InProgress → Completed
- (No se pueden deshacer cambios)

Errores:
- 400: Transición de estado no válida
- 403: No tienes permisos (solo asignado puede cambiar estados)
- 404: Tarea no encontrada
```

#### Cancelar tarea
```
DELETE /api/projects/{project_id}/objectives/{objective_id}/tasks/{task_id}/cancel

Respuesta (200):
{
  "message": "Task canceled successfully",
  "task": {
    "id": 1,
    "status": "Canceled"
  }
}

Errores:
- 403: No tienes permisos
- 404: Tarea no encontrada
```

---

## 💡 Ejemplos de Uso

### Ejemplo 1: Crear un Proyecto Completo

```bash
# 1. Obtener token (requiere credenciales)
TOKEN="1|eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."

# 2. Crear equipo
curl -X POST http://localhost:8000/api/teams/create \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Development Team",
    "description": "Équipo principal de desarrollo"
  }'

# Respuesta: {"id": 1, "name": "Development Team", "status": "Active"}

# 3. Crear proyecto
curl -X POST http://localhost:8000/api/projects/store \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "E-Commerce Platform",
    "description": "Plataforma de tienda online",
    "team_id": 1
  }'

# Respuesta: {"id": 1, "name": "E-Commerce Platform", "status": "Active"}

# 4. Crear objetivo
curl -X POST http://localhost:8000/api/projects/1/objectives/store \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Backend API",
    "description": "Desarrollar API REST"
  }'

# Respuesta: {"id": 1, "title": "Backend API", "status": "NotCompleted"}

# 5. Crear tareas
curl -X POST http://localhost:8000/api/projects/1/objectives/1/store \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Diseñar Base de Datos",
    "description": "Crear schema con tablas principales",
    "due_date": "2026-02-15",
    "user_id": 2
  }'

# Respuesta: {"id": 1, "title": "Diseñar Base de Datos", "status": "Assigned"}
```

### Ejemplo 2: Flujo de Ejecución de Tarea

```bash
TOKEN="1|eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."

# 1. Ver tarea asignada
curl -X GET http://localhost:8000/api/projects/1/objectives/1/tasks/1 \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"

# Respuesta: {"id": 1, "status": "Assigned", "assigned_user": {...}}

# 2. Cambiar a InProgress
curl -X PATCH http://localhost:8000/api/projects/1/objectives/1/tasks/1/status \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "status": "InProgress"
  }'

# Respuesta: {"id": 1, "status": "InProgress"}

# 3. Marcar como completada
curl -X PATCH http://localhost:8000/api/projects/1/objectives/1/tasks/1/status \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "status": "Completed"
  }'

# Respuesta: {"id": 1, "status": "Completed"}
# [Sistema automáticamente verifica si objetivo se completa]
```

### Ejemplo 3: Verificar Progreso de Proyecto

```bash
TOKEN="1|eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."

# Ver detalles del proyecto
curl -X GET http://localhost:8000/api/projects/show/1 \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"

# Respuesta muestra:
# - status: "Active" o "Completed"
# - objetivos con sus estados
# - tareas dentro de cada objetivo

# Respuesta:
{
  "id": 1,
  "name": "E-Commerce Platform",
  "status": "Active",
  "objectives": [
    {
      "id": 1,
      "title": "Backend API",
      "status": "NotCompleted",
      "tasks": [
        {
          "id": 1,
          "title": "Diseñar BD",
          "status": "Completed"
        },
        {
          "id": 2,
          "title": "Implementar API",
          "status": "InProgress"
        }
      ]
    }
  ]
}
```

### Ejemplo 4: Con cURL en Postman

```
# Crear variable de entorno:
BASE_URL = http://localhost:8000/api
TOKEN = 1|eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...

# Crear proyecto:
POST {{BASE_URL}}/projects/store
Headers:
  Authorization: Bearer {{TOKEN}}
  Content-Type: application/json
Body (JSON):
{
  "name": "Mi Proyecto",
  "description": "Descripción",
  "team_id": 1
}
```

---

## 🐛 Troubleshooting

### Problema: "Class not found" o "Fatal error"

**Causa**: Dependencias PHP no instaladas

**Solución**:
```bash
composer install
composer dump-autoload
```

### Problema: "SQLSTATE[HY000]: General error - file is encrypted or is not a database"

**Causa**: Archivo SQLite corrupto

**Solución**:
```bash
# Eliminar base de datos
rm database/database.sqlite

# Recrear
touch database/database.sqlite
php artisan migrate
```

### Problema: "Table 'XXX' doesn't exist"

**Causa**: Migraciones no ejecutadas

**Solución**:
```bash
php artisan migrate
php artisan migrate:refresh  # Si necesitas resetear
```

### Problema: "419 | Page Expired" en CSRF

**Causa**: Token CSRF expirado (típicamente en web, no en API)

**Solución**:
- Incluir header `Accept: application/json`
- Para API, usar Sanctum tokens en lugar de sessions

### Problema: 403 Unauthorized en endpoints

**Causa**: Permisos insuficientes

**Verificar**:
1. Usuario tiene rol asignado en el equipo
2. Token es válido y pertenece al usuario
3. Usuario intenta acción permitida para su rol

```bash
# Verificar permisos del usuario
php artisan tinker
> Auth::user()->getAllPermissions();
```

### Problema: Npm no instala dependencias

**Causa**: Versión incompatible de Node.js

**Solución**:
```bash
# Actualizar Node.js a versión LTS
nvm install --lts
nvm use --lts

# Limpiar caché npm
npm cache clean --force
npm install
```

### Problema: Assets (CSS/JS) no carga en desarrollo

**Causa**: Vite no está corriendo

**Solución**:
```bash
# Terminal 1: Servidor PHP
php artisan serve

# Terminal 2: Vite dev server
npm run dev

# Luego acceder a: http://localhost:8000
```

### Problema: "No application encryption key has been specified"

**Causa**: Clave de aplicación no generada

**Solución**:
```bash
php artisan key:generate
```

### Resetear la Base de Datos Completamente

```bash
# 1. Rollback de todas las migraciones
php artisan migrate:reset

# 2. Ejecutar migraciones nuevamente
php artisan migrate

# 3. (Opcional) Seed con datos de prueba
php artisan db:seed
```

### Ver Logs en Tiempo Real

```bash
# Terminal dedicada
php artisan pail

# O ver archivo de log
tail -f storage/logs/laravel.log
```

### Validar Configuración

```bash
# Verificar que todo esté bien configurado
php artisan config:cache
php artisan config:clear

# Revisar estado de la aplicación
php artisan about

# Verificar rutas registradas
php artisan route:list
```

---

## 📞 Soporte Adicional

### Documentación Oficial:
- [Laravel 12](https://laravel.com/docs/12.x)
- [Laravel Sanctum](https://laravel.com/docs/12.x/sanctum)
- [Spatie Permission](https://spatie.be/docs/laravel-permission)

### Comandos Útiles:

```bash
# Generar código
php artisan make:controller MyController
php artisan make:model MyModel
php artisan make:migration create_my_table
php artisan make:request MyRequest

# Testing
php artisan test
php artisan test --filter=TestName

# Database
php artisan db:seed
php artisan tinker

# Development
php artisan serve --port=8001
php artisan queue:listen

# Production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 📝 Notas Importantes

1. **Seguridad en Producción**:
   - Cambiar `APP_DEBUG=false`
   - Usar variables de entorno seguras
   - Habilitar HTTPS
   - Configurar CORS apropiadamente

2. **Base de Datos**:
   - SQLite es solo para desarrollo
   - En producción usar MySQL o PostgreSQL
   - Hacer backups regularmente

3. **Rendimiento**:
   - Habilitar caché en producción
   - Usar queue para tareas largas
   - Implementar rate limiting

4. **Mantenimiento**:
   - Actualizar dependencias regularmente
   - Revisar logs periódicamente
   - Ejecutar tests antes de deploy

---

**Última actualización**: 8 de febrero de 2026
**Versión del documento**: 1.0
