# API Reference — Project Manager

**Base URL:** `https://manager.carminegl.com/api`
**Formato de Intercambio:** `JSON`
**Codificación:** `UTF-8`
**Correo de usuario de prueba:** `testing@test.com`
**Contraseña de usuario de prueba:** `password`

---

## Tabla de Contenidos

1. [Estándares de Respuesta](#1-estándares-de-respuesta)
2. [Códigos de Error Globales](#2-códigos-de-error-globales)
3. [Reglas de Negocio](#3-reglas-de-negocio)
4. [Autenticación](#4-autenticación)
5. [Equipos](#5-equipos)
6. [Proyectos](#6-proyectos)
7. [Objetivos](#7-objetivos)
8. [Tareas](#8-tareas)
9. [Disputas](#9-disputas)

---

## 1. Estándares de Respuesta

Todas las respuestas de la API siguen una estructura uniforme para facilitar el parseo en el Frontend.

### Respuesta Exitosa `200 / 201`

```json
{
  "success": true,
  "data": { },
  "message": "Descripción de la operación"
}
```

> El campo `data` puede ser un objeto `{}` o un arreglo `[]` según el endpoint.

### Respuesta de Error de Validación `422`

```json
{
  "success": false,
  "message": "The given data was invalid.",
  "errors": {
    "field_name": ["Mensaje específico de error"]
  }
}
```

---

## 2. Códigos de Error Globales

| Código | Estado | Causa Común |
|--------|--------|-------------|
| `401` | Unauthorized | Token ausente, inválido o expirado. |
| `403` | Forbidden | El usuario no tiene permisos para este recurso específico. |
| `404` | Not Found | El ID proporcionado no existe en la base de datos. |
| `422` | Unprocessable Entity | Error de validación en los datos enviados. |
| `500` | Server Error | Error inesperado en el servidor. Reportar al administrador. |

---

## 3. Reglas de Negocio

Esta sección define los estados válidos de cada entidad, las restricciones de edición por jerarquía y los permisos por rol. Estas reglas aplican transversalmente a todos los endpoints de la API.

---

### 3.1 Estados por Entidad

Cada entidad del sistema maneja un conjunto específico de estados válidos. El campo `status` en los requests y responses siempre corresponde a alguno de los valores definidos a continuación.

| Entidad | Estados Válidos |
|---------|----------------|
| **Team** | `Active` · `Inactive` |
| **Project** | `Active` · `CancelInProgress` · `Canceled` · `Completed` |
| **Objective** | `Completed` · `NotCompleted` · `Canceled` |
| **Task** | `Pending` · `Assigned` · `InProgress` · `Completed` · `Canceled` |

---

### 3.2 Restricciones por Jerarquía

El sistema aplica una jerarquía de estados en cascada: si una entidad padre se encuentra en un estado bloqueante, **ninguna de sus entidades hijas puede ser creada, editada ni modificada**, independientemente del rol del usuario.

> Intentar modificar una entidad bloqueada retorna un error `403 Forbidden`.

#### Team → Project → Objective → Task

| Entidad Padre | Estado Bloqueante | Efecto sobre sus Hijos |
|---------------|-------------------|------------------------|
| **Team** | `Inactive` | No se pueden crear ni editar Projects, Objectives ni Tasks pertenecientes al equipo. |
| **Project** | `CancelInProgress` · `Canceled` · `Completed` | No se pueden crear ni editar Objectives ni Tasks pertenecientes al proyecto. |
| **Objective** | `Completed` · `Canceled` | No se pueden crear ni editar Tasks pertenecientes al objetivo. |
| **Task** | `Completed` · `Canceled` | No se puede editar la propia tarea ni su estado. |

**Ejemplo de flujo bloqueado:**
Un proyecto en estado `Completed` bloquea la creación de nuevos objetivos y tareas, aunque el equipo esté `Active` y el usuario tenga permisos de `Manager`.

---

### 3.3 Roles y Permisos

El sistema maneja dos niveles de roles: roles a nivel de **Team** y roles a nivel de **Project**. Un usuario puede tener distintos roles en distintos equipos o proyectos.

#### Roles de Team

| Rol | Descripción | Permisos |
|-----|-------------|----------|
| **Owner** | Propietario del equipo. | Acceso total: puede crear, editar y eliminar el equipo, sus proyectos, objetivos y tareas. |
| **Admin** | Administrador del equipo. | Puede ver la información del equipo y crear proyectos. No puede crear objetivos ni tareas directamente. |
| **Member** | Miembro del equipo. | Acceso básico de lectura según los permisos que se le asignen a nivel de proyecto. |

#### Roles de Project

| Rol | Descripción | Permisos |
|-----|-------------|----------|
| **Manager** | Gestor del proyecto. | Puede crear, editar, actualizar el estado y cancelar objetivos y tareas. También puede editar el proyecto padre. |
| **User** | Colaborador del proyecto. | Puede editar tareas asignadas y actualizar su estado. No puede gestionar objetivos ni el proyecto. |
| **Viewer** | Observador del proyecto. | Solo puede visualizar el proyecto, sus objetivos y tareas. No puede realizar ninguna acción de escritura. |

#### Matriz de Permisos Combinada

| Acción | Owner | Admin | Member + Manager | Member + User | Member + Viewer |
|--------|:-----:|:-----:|:----------------:|:-------------:|:---------------:|
| Ver equipo | ✅ | ✅ | ✅ | ✅ | ✅ |
| Editar / eliminar equipo | ✅ | ❌ | ❌ | ❌ | ❌ |
| Crear proyecto en equipo | ✅ | ✅ | ❌ | ❌ | ❌ |
| Ver proyecto | ✅ | ✅ | ✅ | ✅ | ✅ |
| Editar proyecto | ✅ | ❌ | ✅ | ❌ | ❌ |
| Crear / editar objetivo | ✅ | ❌ | ✅ | ❌ | ❌ |
| Cancelar objetivo | ✅ | ❌ | ✅ | ❌ | ❌ |
| Crear tarea | ✅ | ❌ | ✅ | ❌ | ❌ |
| Editar tarea | ✅ | ❌ | ✅ | ✅ | ❌ |
| Actualizar estado de tarea | ✅ | ❌ | ✅ | ✅ | ❌ |
| Cancelar tarea | ✅ | ❌ | ✅ | ❌ | ❌ |

---

### 3.4 Flujo de Cancelación de Proyectos

La cancelación de un proyecto tiene dos caminos posibles según quién la solicite.

#### Cancelación Directa (por el creador del proyecto)

Si el usuario que solicita la cancelación es el **creador del proyecto**, la cancelación se ejecuta de forma inmediata: el proyecto pasa a estado `Canceled` sin generar ninguna disputa.

#### Cancelación con Disputa (por un Manager externo)

Si un usuario con rol **Manager** intenta cancelar un proyecto que **no creó**, el sistema inicia un proceso de disputa con el siguiente flujo:

```
Manager solicita cancelación
        │
        ▼
Proyecto → CancelInProgress
Disputa creada y notificada al creador
        │
        ├──► Creador acepta → Proyecto pasa a Canceled
        │
        ├──► Creador rechaza → Disputa resuelta, Proyecto regresa a Active
        │
        └──► 15 días sin respuesta → Proyecto se cancela automáticamente → Canceled
```

> Durante el período `CancelInProgress`, el proyecto queda **completamente bloqueado**: no se pueden crear, editar ni modificar ni el proyecto, ni sus objetivos ni sus tareas, hasta que la disputa sea resuelta.

El endpoint para que el creador responda a la disputa es:

**`PUT/PATCH /projects/dispute/{dispute}`** — ver sección [9. Disputas](#9-disputas).

---

### 3.5 Cancelación en Cascada

Cuando una entidad es cancelada, el sistema propaga automáticamente el estado `Canceled` hacia sus hijos que aún estén activos, respetando los estados finales ya establecidos.

#### Al cancelar un Project

Todos los **Objectives** del proyecto que **no** estén en estado `Completed` o `Canceled` pasan automáticamente a `Canceled`.

```
Project → Canceled
    │
    ├── Objective (NotCompleted)  →  Canceled  ✅
    ├── Objective (Completed)     →  sin cambio ⛔
    └── Objective (Canceled)      →  sin cambio ⛔
```

#### Al cancelar un Objective

Todas las **Tasks** del objetivo que **no** estén en estado `Completed` o `Canceled` pasan automáticamente a `Canceled`.

```
Objective → Canceled
    │
    ├── Task (Pending)    →  Canceled  ✅
    ├── Task (Assigned)   →  Canceled  ✅
    ├── Task (InProgress) →  Canceled  ✅
    ├── Task (Completed)  →  sin cambio ⛔
    └── Task (Canceled)   →  sin cambio ⛔
```

> La cancelación en cascada es automática y no requiere ninguna acción adicional por parte del usuario. Los cambios se reflejan inmediatamente en las respuestas de los endpoints de listado.

---

## 4. Autenticación

### POST `/login`

Autentica a un usuario en el sistema. En caso de éxito, retorna un **Bearer Token** requerido para los endpoints protegidos.

**Autenticación requerida:** No

**Request Body:**
```json
{
  "email": "user@example.com",
  "password": "string (mín. 8 caracteres)"
}
```

**Respuesta Exitosa `200`:**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "Usuario Ejemplo",
      "email": "user@example.com"
    },
    "token": "BearerToken"
  },
  "message": "Login successfull"
}
```

**Errores posibles:**

| Código | Descripción |
|--------|-------------|
| `401` | Credenciales inválidas |

---

### POST `/logout`

Cierra la sesión del usuario autenticado e invalida el Bearer Token actual.

**Autenticación requerida:** Sí `Bearer Token`

**Respuesta Exitosa `200`:**
```json
{
  "success": true,
  "data": [],
  "message": "Logout successfull"
}
```

**Errores posibles:**

| Código | Descripción |
|--------|-------------|
| `401` | Token inválido o expirado |

---

## 5. Equipos

> Todos los endpoints de esta sección requieren **Bearer Token**.

### GET `/teams`

Obtiene todos los equipos asociados al usuario autenticado.

**Respuesta Exitosa `200`:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Equipo A",
      "description": "Descripción del equipo",
      "status": "Active"
    }
  ],
  "message": "Data retrieved successfuly"
}
```

---

### POST `/teams`

Crea un nuevo equipo. El usuario creador se asigna automáticamente como propietario.

**Request Body:**
```json
{
  "name": "string (mín. 3, máx. 255, único)",
  "description": "string (opcional, máx. 1000)"
}
```

**Respuesta Exitosa `201`:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Equipo Nuevo",
    "description": "Descripción",
    "status": "Active"
  },
  "message": "Team created successfully"
}
```

**Errores posibles:**

| Código | Descripción |
|--------|-------------|
| `422` | Datos de entrada inválidos |

---

### GET `/teams/{team}`

Obtiene los detalles de un equipo específico.

**Respuesta Exitosa `200`:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Equipo A",
    "description": "Descripción",
    "status": "Active"
  },
  "message": "Team retrieved successfully"
}
```

**Errores posibles:**

| Código | Descripción |
|--------|-------------|
| `403` | Sin permisos para ver este equipo |
| `404` | Equipo no encontrado |

---

### PUT `/teams/{team}` · PATCH `/teams/{team}`

Modifica los datos de un equipo existente.

**Request Body:**
```json
{
  "name": "string (mín. 3, máx. 255, único)",
  "description": "string (opcional, máx. 1000)"
}
```

**Respuesta Exitosa `200`:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Equipo Actualizado",
    "description": "Nueva descripción",
    "status": "Active"
  },
  "message": "Team updated successfully."
}
```

**Errores posibles:**

| Código | Descripción |
|--------|-------------|
| `403` | Sin permisos para modificar este equipo |
| `422` | Datos de entrada inválidos |

---

### DELETE `/teams/{team}`

Inactiva o elimina un equipo. Solo usuarios con permisos de propietario pueden realizar esta acción.

**Respuesta Exitosa `200`:**
```json
{
  "success": true,
  "data": [],
  "message": "Team inactivated successfully"
}
```

**Errores posibles:**

| Código | Descripción |
|--------|-------------|
| `403` | Sin permisos para eliminar este equipo |
| `404` | Equipo no encontrado |

---

## 6. Proyectos

> Todos los endpoints de esta sección requieren **Bearer Token**.

### GET `/projects`

Obtiene todos los proyectos asociados al usuario autenticado, incluyendo información del equipo y el rol del usuario en cada proyecto.

**Respuesta Exitosa `200`:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Proyecto A",
      "description": "Descripción del proyecto",
      "status": "Active",
      "team": { }
    }
  ],
  "message": "Projects retrieved successfully"
}
```

---

### POST `/teams/{team}/projects`

Crea un nuevo proyecto dentro de un equipo. Solo los **administradores del equipo** pueden ejecutar esta acción.

**Request Body:**
```json
{
  "name": "string (mín. 3, máx. 255, único)",
  "description": "string (opcional, máx. 1000)"
}
```

**Respuesta Exitosa `201`:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Proyecto Nuevo",
    "description": "Descripción",
    "status": "Active",
    "team_id": 2,
    "user_id": 1
  },
  "message": "Project created successfully"
}
```

**Errores posibles:**

| Código | Descripción |
|--------|-------------|
| `403` | El usuario no es administrador del equipo |
| `422` | Datos de entrada inválidos |

---

### GET `/teams/{team}/projects/{project}`

Obtiene los detalles de un proyecto específico, incluyendo información del equipo y su estado actual.

**Respuesta Exitosa `200`:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Proyecto A",
    "description": "Descripción",
    "status": "Active",
    "team": { }
  },
  "message": "Project retrieved successfully"
}
```

**Errores posibles:**

| Código | Descripción |
|--------|-------------|
| `403` | Sin permisos para ver este proyecto |
| `404` | Proyecto no encontrado |

---

### PUT `/teams/{team}/projects/{project}` · PATCH `/teams/{team}/projects/{project}`

Modifica los datos de un proyecto existente.

**Request Body:**
```json
{
  "name": "string (mín. 3, máx. 255, único)",
  "description": "string (opcional, máx. 1000)"
}
```

**Respuesta Exitosa `200`:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Proyecto Actualizado",
    "description": "Nueva descripción"
  },
  "message": "Project updated successfully"
}
```

**Errores posibles:**

| Código | Descripción |
|--------|-------------|
| `403` | Sin permisos para modificar este proyecto |
| `422` | Datos de entrada inválidos |

---

### DELETE `/teams/{team}/projects/{project}`

Cancela o elimina un proyecto. Solo usuarios con los permisos correspondientes pueden realizar esta acción.

**Respuesta Exitosa `200`:**
```json
{
  "success": true,
  "data": [],
  "message": "Project cancelled successfully"
}
```

**Errores posibles:**

| Código | Descripción |
|--------|-------------|
| `403` | Sin permisos para eliminar este proyecto |
| `404` | Proyecto no encontrado |

---

## 7. Objetivos

> Todos los endpoints de esta sección requieren **Bearer Token**.

### GET `/projects/{project}/objectives`

Obtiene todos los objetivos asociados a un proyecto, incluyendo el conteo de tareas por objetivo.

**Respuesta Exitosa `200`:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Objetivo 1",
      "description": "Descripción",
      "priority": "Medium",
      "tasks_count": 3
    }
  ],
  "message": "Objectives retrieved successfully"
}
```

**Errores posibles:**

| Código | Descripción |
|--------|-------------|
| `403` | Sin permisos para ver los objetivos |
| `404` | Proyecto no encontrado |

---

### POST `/projects/{project}/objectives`

Crea un nuevo objetivo dentro de un proyecto.

**Request Body:**
```json
{
  "title": "string (mín. 3, máx. 255)",
  "description": "string (opcional, mín. 3, máx. 1000)",
  "priority": "Low | Medium | High (opcional)"
}
```

**Respuesta Exitosa `201`:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "Objetivo Nuevo",
    "description": "Descripción",
    "priority": "Medium",
    "project_id": 1
  },
  "message": "Objective created successfully"
}
```

**Errores posibles:**

| Código | Descripción |
|--------|-------------|
| `403` | Sin permisos para crear objetivos |
| `422` | Datos de entrada inválidos |

---

### GET `/projects/{project}/objectives/{objective}`

Obtiene los detalles de un objetivo específico, incluyendo sus tareas asociadas.

**Respuesta Exitosa `200`:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "Objetivo 1",
    "description": "Descripción",
    "priority": "Medium",
    "tasks": []
  },
  "message": "Objective retrieved successfully"
}
```

**Errores posibles:**

| Código | Descripción |
|--------|-------------|
| `403` | Sin permisos para ver este objetivo |
| `404` | Objetivo no encontrado |

---

### PUT `/projects/{project}/objectives/{objective}` · PATCH `/projects/{project}/objectives/{objective}`

Modifica los datos de un objetivo existente.

**Request Body:**
```json
{
  "title": "string (mín. 3, máx. 255, único)",
  "description": "string (opcional, máx. 1000)",
  "priority": "Low | Medium | High"
}
```

**Respuesta Exitosa `200`:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "Objetivo Actualizado",
    "description": "Nueva descripción",
    "priority": "High"
  },
  "message": "Objective updated successfully"
}
```

**Errores posibles:**

| Código | Descripción |
|--------|-------------|
| `403` | Sin permisos para modificar este objetivo |
| `422` | Datos de entrada inválidos |

---

### DELETE `/projects/{project}/objectives/{objective}`

Cancela o elimina un objetivo.

**Respuesta Exitosa `200`:**
```json
{
  "success": true,
  "data": [],
  "message": "Objective cancelled successfully"
}
```

**Errores posibles:**

| Código | Descripción |
|--------|-------------|
| `403` | Sin permisos para eliminar este objetivo |
| `404` | Objetivo no encontrado |

---

## 8. Tareas

> Todos los endpoints de esta sección requieren **Bearer Token**.

### GET `/objectives/{objective}/tasks`

Obtiene todas las tareas asociadas a un objetivo.

**Respuesta Exitosa `200`:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Tarea 1",
      "description": "Descripción",
      "due_date": "2026-02-20",
      "status": "Pending"
    }
  ],
  "message": "Tasks retrieved successfully"
}
```

**Errores posibles:**

| Código | Descripción |
|--------|-------------|
| `403` | Sin permisos para ver las tareas |
| `404` | Objetivo no encontrado |

---

### POST `/objectives/{objective}/tasks`

Crea una nueva tarea dentro de un objetivo. Puede ser asignada a un usuario miembro del proyecto.

**Request Body:**
```json
{
  "title": "string (mín. 3, máx. 255)",
  "description": "string (opcional, mín. 3, máx. 1000)",
  "due_date": "date (debe ser igual o posterior a hoy)",
  "user_id": "int (opcional, debe ser miembro del proyecto)"
}
```

**Respuesta Exitosa `201`:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "Tarea Nueva",
    "description": "Descripción",
    "due_date": "2026-02-20",
    "status": "Assigned",
    "objective_id": 1,
    "user_id": 2
  },
  "message": "Task created successfully"
}
```

**Errores posibles:**

| Código | Descripción |
|--------|-------------|
| `403` | Sin permisos para crear tareas |
| `422` | Datos de entrada inválidos |

---

### GET `/objectives/{objective}/tasks/{task}`

Obtiene los detalles de una tarea específica.

**Respuesta Exitosa `200`:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "Tarea 1",
    "description": "Descripción",
    "due_date": "2026-02-20",
    "status": "Pending"
  },
  "message": "Task retrieved successfully"
}
```

**Errores posibles:**

| Código | Descripción |
|--------|-------------|
| `403` | Sin permisos para ver esta tarea |
| `404` | Tarea no encontrada |

---

### PUT `/objectives/{objective}/tasks/{task}` · PATCH `/objectives/{objective}/tasks/{task}`

Modifica los datos de una tarea existente.

**Request Body:**
```json
{
  "title": "string (mín. 3, máx. 255, único)",
  "description": "string (opcional, mín. 3, máx. 1000)",
  "due_date": "date (debe ser igual o posterior a hoy)",
  "user_id": "int (opcional, debe ser miembro del proyecto)"
}
```

**Respuesta Exitosa `200`:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "Tarea Actualizada",
    "description": "Nueva descripción",
    "due_date": "2026-02-22",
    "status": "Assigned"
  },
  "message": "Task updated successfully"
}
```

**Errores posibles:**

| Código | Descripción |
|--------|-------------|
| `403` | Sin permisos para modificar esta tarea |
| `422` | Datos de entrada inválidos |

---

### DELETE `/objectives/{objective}/tasks/{task}`

Cancela o elimina una tarea.

**Respuesta Exitosa `200`:**
```json
{
  "success": true,
  "data": [],
  "message": "Task cancelled successfully"
}
```

**Errores posibles:**

| Código | Descripción |
|--------|-------------|
| `403` | Sin permisos para eliminar esta tarea |
| `404` | Tarea no encontrada |

---

### PATCH `/objectives/{objective}/tasks/status/{task}`

Actualiza únicamente el estado de una tarea específica.

**Request Body:**
```json
{
  "status": "string (valores permitidos según lógica de negocio)"
}
```

**Respuesta Exitosa `200`:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "status": "Completed"
  },
  "message": "Task status updated successfully"
}
```

**Errores posibles:**

| Código | Descripción |
|--------|-------------|
| `403` | Sin permisos para actualizar el estado |
| `422` | Estado inválido o no permitido |

---

## 9. Disputas

> Todos los endpoints de esta sección requieren **Bearer Token**.

### PUT `/projects/dispute/{dispute}` · PATCH `/projects/dispute/{dispute}`

Resuelve una disputa asociada a un proyecto.

**Respuesta Exitosa `200`:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "status": "Resolved"
  },
  "message": "Dispute resolved successfully"
}
```

**Errores posibles:**

| Código | Descripción |
|--------|-------------|
| `403` | Sin permisos para resolver esta disputa |
| `404` | Disputa no encontrada |

---
