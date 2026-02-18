# API Reference — Project Manager

**Base URL:** `https://manager.carminegl.com/api`
**Exchange Format:** `JSON`
**Encoding:** `UTF-8`
**Demo User Email:** `testing@test.com`
**Demo User Password:** `password` 

---

## Table of Contents

1. [Response Standards](#1-response-standards)
2. [Global Error Codes](#2-global-error-codes)
3. [Business Rules](#3-business-rules)
4. [Authentication](#4-authentication)
5. [Teams](#5-teams)
6. [Projects](#6-projects)
7. [Objectives](#7-objectives)
8. [Tasks](#8-tasks)
9. [Disputes](#9-disputes)

---

## 1. Response Standards

All API responses follow a uniform structure to simplify parsing on the Frontend.

### Successful Response `200 / 201`

```json
{
  "success": true,
  "data": { },
  "message": "Operation description"
}
```

> The `data` field can be either an object `{}` or an array `[]` depending on the endpoint.

### Validation Error Response `422`

```json
{
  "success": false,
  "message": "The given data was invalid.",
  "errors": {
    "field_name": ["Specific error message"]
  }
}
```

---

## 2. Global Error Codes

| Code | Status | Common Cause |
|------|--------|--------------|
| `401` | Unauthorized | Token missing, invalid, or expired. |
| `403` | Forbidden | The user does not have permission for this specific resource. |
| `404` | Not Found | The provided ID does not exist in the database. |
| `422` | Unprocessable Entity | Validation error in the submitted data. |
| `500` | Server Error | Unexpected server error. Report to the administrator. |

---

## 3. Business Rules

This section defines valid states for each entity, hierarchy-based editing restrictions, and role-based permissions. These rules apply across all API endpoints.

---

### 3.1 Entity Statuses

Each entity in the system handles a specific set of valid statuses. The `status` field in requests and responses always corresponds to one of the values defined below.

| Entity | Valid Statuses |
|--------|---------------|
| **Team** | `Active` · `Inactive` |
| **Project** | `Active` · `CancelInProgress` · `Canceled` · `Completed` |
| **Objective** | `Completed` · `NotCompleted` · `Canceled` |
| **Task** | `Pending` · `Assigned` · `InProgress` · `Completed` · `Canceled` |

---

### 3.2 Hierarchy Restrictions

The system applies a cascading status hierarchy: if a parent entity is in a blocking state, **none of its child entities can be created, edited, or modified**, regardless of the user's role.

> Attempting to modify a blocked entity returns a `403 Forbidden` error.

#### Team → Project → Objective → Task

| Parent Entity | Blocking Status | Effect on Children |
|---------------|-----------------|-------------------|
| **Team** | `Inactive` | Projects, Objectives, and Tasks belonging to the team cannot be created or edited. |
| **Project** | `CancelInProgress` · `Canceled` · `Completed` | Objectives and Tasks belonging to the project cannot be created or edited. |
| **Objective** | `Completed` · `Canceled` | Tasks belonging to the objective cannot be created or edited. |
| **Task** | `Completed` · `Canceled` | The task itself and its status cannot be edited. |

**Example of a blocked flow:**
A project in `Completed` status blocks the creation of new objectives and tasks, even if the team is `Active` and the user has `Manager` permissions.

---

### 3.3 Roles and Permissions

The system manages two levels of roles: **Team**-level roles and **Project**-level roles. A user may have different roles across different teams or projects.

#### Team Roles

| Role | Description | Permissions |
|------|-------------|-------------|
| **Owner** | Team owner. | Full access: can create, edit, and delete the team, its projects, objectives, and tasks. |
| **Admin** | Team administrator. | Can view team information and create projects. Cannot create objectives or tasks directly. |
| **Member** | Team member. | Basic read access based on permissions assigned at the project level. |

#### Project Roles

| Role | Description | Permissions |
|------|-------------|-------------|
| **Manager** | Project manager. | Can create, edit, update the status of, and cancel objectives and tasks. Can also edit the parent project. |
| **User** | Project collaborator. | Can edit assigned tasks and update their status. Cannot manage objectives or the project itself. |
| **Viewer** | Project observer. | Can only view the project, its objectives, and tasks. Cannot perform any write actions. |

#### Combined Permissions Matrix

| Action | Owner | Admin | Member + Manager | Member + User | Member + Viewer |
|--------|:-----:|:-----:|:----------------:|:-------------:|:---------------:|
| View team | ✅ | ✅ | ✅ | ✅ | ✅ |
| Edit / delete team | ✅ | ❌ | ❌ | ❌ | ❌ |
| Create project in team | ✅ | ✅ | ❌ | ❌ | ❌ |
| View project | ✅ | ✅ | ✅ | ✅ | ✅ |
| Edit project | ✅ | ❌ | ✅ | ❌ | ❌ |
| Create / edit objective | ✅ | ❌ | ✅ | ❌ | ❌ |
| Cancel objective | ✅ | ❌ | ✅ | ❌ | ❌ |
| Create task | ✅ | ❌ | ✅ | ❌ | ❌ |
| Edit task | ✅ | ❌ | ✅ | ✅ | ❌ |
| Update task status | ✅ | ❌ | ✅ | ✅ | ❌ |
| Cancel task | ✅ | ❌ | ✅ | ❌ | ❌ |

---

### 3.4 Project Cancellation Flow

A project cancellation follows two different paths depending on who initiates it.

#### Direct Cancellation (by the project creator)

If the user requesting the cancellation is the **project creator**, the cancellation is executed immediately: the project moves to `Canceled` status without generating any dispute.

#### Cancellation with Dispute (by an external Manager)

If a user with the **Manager** role attempts to cancel a project they **did not create**, the system initiates a dispute process with the following flow:

```
Manager requests cancellation
        │
        ▼
Project → CancelInProgress
Dispute created and creator is notified
        │
        ├──► Creator accepts → Project moves to Canceled
        │
        ├──► Creator rejects → Dispute resolved, Project returns to Active
        │
        └──► 15 days with no response → Project is automatically canceled → Canceled
```

> During the `CancelInProgress` period, the project is **completely locked**: the project, its objectives, and its tasks cannot be created, edited, or modified until the dispute is resolved.

The endpoint for the creator to respond to the dispute is:

**`PUT/PATCH /projects/dispute/{dispute}`** — see section [9. Disputes](#9-disputes).

---

### 3.5 Cascade Cancellation

When an entity is canceled, the system automatically propagates the `Canceled` status to its active children, preserving any already-final states.

#### When canceling a Project

All **Objectives** in the project that are **not** in `Completed` or `Canceled` status are automatically set to `Canceled`.

```
Project → Canceled
    │
    ├── Objective (NotCompleted)  →  Canceled     ✅
    ├── Objective (Completed)     →  no change    ⛔
    └── Objective (Canceled)      →  no change    ⛔
```

#### When canceling an Objective

All **Tasks** in the objective that are **not** in `Completed` or `Canceled` status are automatically set to `Canceled`.

```
Objective → Canceled
    │
    ├── Task (Pending)    →  Canceled     ✅
    ├── Task (Assigned)   →  Canceled     ✅
    ├── Task (InProgress) →  Canceled     ✅
    ├── Task (Completed)  →  no change    ⛔
    └── Task (Canceled)   →  no change    ⛔
```

> Cascade cancellation is automatic and requires no additional action from the user. Changes are immediately reflected in the responses of listing endpoints.

---

## 4. Authentication

### POST `/login`

Authenticates a user in the system. On success, returns a **Bearer Token** required for protected endpoints.

**Authentication required:** No

**Request Body:**
```json
{
  "email": "user@example.com",
  "password": "string (min. 8 characters)"
}
```

**Successful Response `200`:**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "Example User",
      "email": "user@example.com"
    },
    "token": "BearerToken"
  },
  "message": "Login successfull"
}
```

**Possible Errors:**

| Code | Description |
|------|-------------|
| `401` | Invalid credentials |

---

### POST `/logout`

Ends the authenticated user's session and invalidates the current Bearer Token.

**Authentication required:** Yes `Bearer Token`

**Successful Response `200`:**
```json
{
  "success": true,
  "data": [],
  "message": "Logout successfull"
}
```

**Possible Errors:**

| Code | Description |
|------|-------------|
| `401` | Invalid or expired token |

---

## 5. Teams

> All endpoints in this section require a **Bearer Token**.

### GET `/teams`

Retrieves all teams associated with the authenticated user.

**Successful Response `200`:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Team A",
      "description": "Team description",
      "status": "Active"
    }
  ],
  "message": "Data retrieved successfuly"
}
```

---

### POST `/teams`

Creates a new team. The creating user is automatically assigned as the owner.

**Request Body:**
```json
{
  "name": "string (min. 3, max. 255, unique)",
  "description": "string (optional, max. 1000)"
}
```

**Successful Response `201`:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "New Team",
    "description": "Description",
    "status": "Active"
  },
  "message": "Team created successfully"
}
```

**Possible Errors:**

| Code | Description |
|------|-------------|
| `422` | Invalid input data |

---

### GET `/teams/{team}`

Retrieves the details of a specific team.

**Successful Response `200`:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Team A",
    "description": "Description",
    "status": "Active"
  },
  "message": "Team retrieved successfully"
}
```

**Possible Errors:**

| Code | Description |
|------|-------------|
| `403` | No permission to view this team |
| `404` | Team not found |

---

### PUT `/teams/{team}` · PATCH `/teams/{team}`

Updates the data of an existing team.

**Request Body:**
```json
{
  "name": "string (min. 3, max. 255, unique)",
  "description": "string (optional, max. 1000)"
}
```

**Successful Response `200`:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Updated Team",
    "description": "New description",
    "status": "Active"
  },
  "message": "Team updated successfully."
}
```

**Possible Errors:**

| Code | Description |
|------|-------------|
| `403` | No permission to edit this team |
| `422` | Invalid input data |

---

### DELETE `/teams/{team}`

Deactivates or deletes a team. Only users with owner permissions can perform this action.

**Successful Response `200`:**
```json
{
  "success": true,
  "data": [],
  "message": "Team inactivated successfully"
}
```

**Possible Errors:**

| Code | Description |
|------|-------------|
| `403` | No permission to delete this team |
| `404` | Team not found |

---

## 6. Projects

> All endpoints in this section require a **Bearer Token**.

### GET `/projects`

Retrieves all projects associated with the authenticated user, including team information and the user's role in each project.

**Successful Response `200`:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Project A",
      "description": "Project description",
      "status": "Active",
      "team": { }
    }
  ],
  "message": "Projects retrieved successfully"
}
```

---

### POST `/teams/{team}/projects`

Creates a new project within a team. Only **team administrators** can perform this action.

**Request Body:**
```json
{
  "name": "string (min. 3, max. 255, unique)",
  "description": "string (optional, max. 1000)"
}
```

**Successful Response `201`:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "New Project",
    "description": "Description",
    "status": "Active",
    "team_id": 2,
    "user_id": 1
  },
  "message": "Project created successfully"
}
```

**Possible Errors:**

| Code | Description |
|------|-------------|
| `403` | User is not a team administrator |
| `422` | Invalid input data |

---

### GET `/teams/{team}/projects/{project}`

Retrieves the details of a specific project, including team information and its current status.

**Successful Response `200`:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Project A",
    "description": "Description",
    "status": "Active",
    "team": { }
  },
  "message": "Project retrieved successfully"
}
```

**Possible Errors:**

| Code | Description |
|------|-------------|
| `403` | No permission to view this project |
| `404` | Project not found |

---

### PUT `/teams/{team}/projects/{project}` · PATCH `/teams/{team}/projects/{project}`

Updates the data of an existing project.

**Request Body:**
```json
{
  "name": "string (min. 3, max. 255, unique)",
  "description": "string (optional, max. 1000)"
}
```

**Successful Response `200`:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Updated Project",
    "description": "New description"
  },
  "message": "Project updated successfully"
}
```

**Possible Errors:**

| Code | Description |
|------|-------------|
| `403` | No permission to edit this project |
| `422` | Invalid input data |

---

### DELETE `/teams/{team}/projects/{project}`

Cancels or deletes a project. Only users with the appropriate permissions can perform this action.

**Successful Response `200`:**
```json
{
  "success": true,
  "data": [],
  "message": "Project cancelled successfully"
}
```

**Possible Errors:**

| Code | Description |
|------|-------------|
| `403` | No permission to delete this project |
| `404` | Project not found |

---

## 7. Objectives

> All endpoints in this section require a **Bearer Token**.

### GET `/projects/{project}/objectives`

Retrieves all objectives associated with a project, including the task count per objective.

**Successful Response `200`:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Objective 1",
      "description": "Description",
      "priority": "Medium",
      "tasks_count": 3
    }
  ],
  "message": "Objectives retrieved successfully"
}
```

**Possible Errors:**

| Code | Description |
|------|-------------|
| `403` | No permission to view objectives |
| `404` | Project not found |

---

### POST `/projects/{project}/objectives`

Creates a new objective within a project.

**Request Body:**
```json
{
  "title": "string (min. 3, max. 255)",
  "description": "string (optional, min. 3, max. 1000)",
  "priority": "Low | Medium | High (optional)"
}
```

**Successful Response `201`:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "New Objective",
    "description": "Description",
    "priority": "Medium",
    "project_id": 1
  },
  "message": "Objective created successfully"
}
```

**Possible Errors:**

| Code | Description |
|------|-------------|
| `403` | No permission to create objectives |
| `422` | Invalid input data |

---

### GET `/projects/{project}/objectives/{objective}`

Retrieves the details of a specific objective, including its associated tasks.

**Successful Response `200`:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "Objective 1",
    "description": "Description",
    "priority": "Medium",
    "tasks": []
  },
  "message": "Objective retrieved successfully"
}
```

**Possible Errors:**

| Code | Description |
|------|-------------|
| `403` | No permission to view this objective |
| `404` | Objective not found |

---

### PUT `/projects/{project}/objectives/{objective}` · PATCH `/projects/{project}/objectives/{objective}`

Updates the data of an existing objective.

**Request Body:**
```json
{
  "title": "string (min. 3, max. 255, unique)",
  "description": "string (optional, max. 1000)",
  "priority": "Low | Medium | High"
}
```

**Successful Response `200`:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "Updated Objective",
    "description": "New description",
    "priority": "High"
  },
  "message": "Objective updated successfully"
}
```

**Possible Errors:**

| Code | Description |
|------|-------------|
| `403` | No permission to edit this objective |
| `422` | Invalid input data |

---

### DELETE `/projects/{project}/objectives/{objective}`

Cancels or deletes an objective.

**Successful Response `200`:**
```json
{
  "success": true,
  "data": [],
  "message": "Objective cancelled successfully"
}
```

**Possible Errors:**

| Code | Description |
|------|-------------|
| `403` | No permission to delete this objective |
| `404` | Objective not found |

---

## 8. Tasks

> All endpoints in this section require a **Bearer Token**.

### GET `/objectives/{objective}/tasks`

Retrieves all tasks associated with an objective.

**Successful Response `200`:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Task 1",
      "description": "Description",
      "due_date": "2026-02-20",
      "status": "Pending"
    }
  ],
  "message": "Tasks retrieved successfully"
}
```

**Possible Errors:**

| Code | Description |
|------|-------------|
| `403` | No permission to view tasks |
| `404` | Objective not found |

---

### POST `/objectives/{objective}/tasks`

Creates a new task within an objective. Can be assigned to a project member.

**Request Body:**
```json
{
  "title": "string (min. 3, max. 255)",
  "description": "string (optional, min. 3, max. 1000)",
  "due_date": "date (must be today or later)",
  "user_id": "int (optional, must be a project member)"
}
```

**Successful Response `201`:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "New Task",
    "description": "Description",
    "due_date": "2026-02-20",
    "status": "Assigned",
    "objective_id": 1,
    "user_id": 2
  },
  "message": "Task created successfully"
}
```

**Possible Errors:**

| Code | Description |
|------|-------------|
| `403` | No permission to create tasks |
| `422` | Invalid input data |

---

### GET `/objectives/{objective}/tasks/{task}`

Retrieves the details of a specific task.

**Successful Response `200`:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "Task 1",
    "description": "Description",
    "due_date": "2026-02-20",
    "status": "Pending"
  },
  "message": "Task retrieved successfully"
}
```

**Possible Errors:**

| Code | Description |
|------|-------------|
| `403` | No permission to view this task |
| `404` | Task not found |

---

### PUT `/objectives/{objective}/tasks/{task}` · PATCH `/objectives/{objective}/tasks/{task}`

Updates the data of an existing task.

**Request Body:**
```json
{
  "title": "string (min. 3, max. 255, unique)",
  "description": "string (optional, min. 3, max. 1000)",
  "due_date": "date (must be today or later)",
  "user_id": "int (optional, must be a project member)"
}
```

**Successful Response `200`:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "Updated Task",
    "description": "New description",
    "due_date": "2026-02-22",
    "status": "Assigned"
  },
  "message": "Task updated successfully"
}
```

**Possible Errors:**

| Code | Description |
|------|-------------|
| `403` | No permission to edit this task |
| `422` | Invalid input data |

---

### DELETE `/objectives/{objective}/tasks/{task}`

Cancels or deletes a task.

**Successful Response `200`:**
```json
{
  "success": true,
  "data": [],
  "message": "Task cancelled successfully"
}
```

**Possible Errors:**

| Code | Description |
|------|-------------|
| `403` | No permission to delete this task |
| `404` | Task not found |

---

### PATCH `/objectives/{objective}/tasks/status/{task}`

Updates only the status of a specific task.

**Request Body:**
```json
{
  "status": "string (allowed values based on business logic)"
}
```

**Successful Response `200`:**
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

**Possible Errors:**

| Code | Description |
|------|-------------|
| `403` | No permission to update the status |
| `422` | Invalid or not permitted status |

---

## 9. Disputes

> All endpoints in this section require a **Bearer Token**.

### PUT `/projects/dispute/{dispute}` · PATCH `/projects/dispute/{dispute}`

Resolves a dispute associated with a project.

**Successful Response `200`:**
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

**Possible Errors:**

| Code | Description |
|------|-------------|
| `403` | No permission to resolve this dispute |
| `404` | Dispute not found |

---
