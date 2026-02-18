# API-Referenz — Project Manager

**Basis-URL:** `https://manager.carminegl.com/api`
**Austauschformat:** `JSON`
**Kodierung:** `UTF-8`
**E-Mail des Demo-Benutzers:** `testing@test.com`
**Passwort des Demo-Benutzers:** `password`

---

## Inhaltsverzeichnis

1. [Antwortstandards](#1-antwortstandards)
2. [Globale Fehlercodes](#2-globale-fehlercodes)
3. [Geschäftsregeln](#3-geschäftsregeln)
4. [Authentifizierung](#4-authentifizierung)
5. [Teams](#5-teams)
6. [Projekte](#6-projekte)
7. [Ziele](#7-ziele)
8. [Aufgaben](#8-aufgaben)
9. [Streitigkeiten](#9-streitigkeiten)

---

## 1. Antwortstandards

Alle API-Antworten folgen einer einheitlichen Struktur, um das Parsen im Frontend zu vereinfachen.

### Erfolgreiche Antwort `200 / 201`

```json
{
  "success": true,
  "data": { },
  "message": "Beschreibung der Operation"
}
```

> Das Feld `data` kann je nach Endpunkt entweder ein Objekt `{}` oder ein Array `[]` sein.

### Validierungsfehler-Antwort `422`

```json
{
  "success": false,
  "message": "The given data was invalid.",
  "errors": {
    "field_name": ["Spezifische Fehlermeldung"]
  }
}
```

---

## 2. Globale Fehlercodes

| Code | Status | Häufige Ursache |
|------|--------|-----------------|
| `401` | Unauthorized | Token fehlt, ist ungültig oder abgelaufen. |
| `403` | Forbidden | Der Benutzer hat keine Berechtigung für diese spezifische Ressource. |
| `404` | Not Found | Die angegebene ID existiert nicht in der Datenbank. |
| `422` | Unprocessable Entity | Validierungsfehler in den übermittelten Daten. |
| `500` | Server Error | Unerwarteter Serverfehler. Bitte dem Administrator melden. |

---

## 3. Geschäftsregeln

Dieser Abschnitt definiert die gültigen Zustände jeder Entität, hierarchiebasierte Bearbeitungseinschränkungen und rollenbasierte Berechtigungen. Diese Regeln gelten übergreifend für alle API-Endpunkte.

---

### 3.1 Entitätsstatus

Jede Entität im System verwaltet einen spezifischen Satz gültiger Zustände. Das Feld `status` in Anfragen und Antworten entspricht immer einem der unten definierten Werte.

| Entität | Gültige Zustände |
|---------|-----------------|
| **Team** | `Active` · `Inactive` |
| **Project** | `Active` · `CancelInProgress` · `Canceled` · `Completed` |
| **Objective** | `Completed` · `NotCompleted` · `Canceled` |
| **Task** | `Pending` · `Assigned` · `InProgress` · `Completed` · `Canceled` |

---

### 3.2 Hierarchische Einschränkungen

Das System wendet eine kaskadierende Statushierarchie an: Befindet sich eine übergeordnete Entität in einem blockierenden Zustand, **können keine ihrer untergeordneten Entitäten erstellt, bearbeitet oder geändert werden**, unabhängig von der Rolle des Benutzers.

> Der Versuch, eine blockierte Entität zu ändern, gibt einen `403 Forbidden`-Fehler zurück.

#### Team → Project → Objective → Task

| Übergeordnete Entität | Blockierender Status | Auswirkung auf untergeordnete Entitäten |
|----------------------|---------------------|----------------------------------------|
| **Team** | `Inactive` | Projects, Objectives und Tasks des Teams können weder erstellt noch bearbeitet werden. |
| **Project** | `CancelInProgress` · `Canceled` · `Completed` | Objectives und Tasks des Projekts können weder erstellt noch bearbeitet werden. |
| **Objective** | `Completed` · `Canceled` | Tasks des Ziels können weder erstellt noch bearbeitet werden. |
| **Task** | `Completed` · `Canceled` | Die Aufgabe selbst und ihr Status können nicht bearbeitet werden. |

**Beispiel eines blockierten Ablaufs:**
Ein Projekt im Status `Completed` blockiert die Erstellung neuer Ziele und Aufgaben, auch wenn das Team `Active` ist und der Benutzer `Manager`-Berechtigungen hat.

---

### 3.3 Rollen und Berechtigungen

Das System verwaltet zwei Rollenebenen: Rollen auf **Team**-Ebene und Rollen auf **Projekt**-Ebene. Ein Benutzer kann in verschiedenen Teams oder Projekten unterschiedliche Rollen haben.

#### Team-Rollen

| Rolle | Beschreibung | Berechtigungen |
|-------|-------------|----------------|
| **Owner** | Teambesitzer. | Vollzugriff: kann das Team, seine Projekte, Ziele und Aufgaben erstellen, bearbeiten und löschen. |
| **Admin** | Teamadministrator. | Kann Teaminformationen einsehen und Projekte erstellen. Kann keine Ziele oder Aufgaben direkt erstellen. |
| **Member** | Teammitglied. | Grundlegender Lesezugriff gemäß den auf Projektebene zugewiesenen Berechtigungen. |

#### Projekt-Rollen

| Rolle | Beschreibung | Berechtigungen |
|-------|-------------|----------------|
| **Manager** | Projektmanager. | Kann Ziele und Aufgaben erstellen, bearbeiten, deren Status aktualisieren und stornieren. Kann auch das übergeordnete Projekt bearbeiten. |
| **User** | Projektmitarbeiter. | Kann zugewiesene Aufgaben bearbeiten und deren Status aktualisieren. Kann keine Ziele oder das Projekt verwalten. |
| **Viewer** | Projektbeobachter. | Kann nur das Projekt, seine Ziele und Aufgaben einsehen. Kann keine Schreibaktionen durchführen. |

#### Kombinierte Berechtigungsmatrix

| Aktion | Owner | Admin | Member + Manager | Member + User | Member + Viewer |
|--------|:-----:|:-----:|:----------------:|:-------------:|:---------------:|
| Team anzeigen | ✅ | ✅ | ✅ | ✅ | ✅ |
| Team bearbeiten / löschen | ✅ | ❌ | ❌ | ❌ | ❌ |
| Projekt im Team erstellen | ✅ | ✅ | ❌ | ❌ | ❌ |
| Projekt anzeigen | ✅ | ✅ | ✅ | ✅ | ✅ |
| Projekt bearbeiten | ✅ | ❌ | ✅ | ❌ | ❌ |
| Ziel erstellen / bearbeiten | ✅ | ❌ | ✅ | ❌ | ❌ |
| Ziel stornieren | ✅ | ❌ | ✅ | ❌ | ❌ |
| Aufgabe erstellen | ✅ | ❌ | ✅ | ❌ | ❌ |
| Aufgabe bearbeiten | ✅ | ❌ | ✅ | ✅ | ❌ |
| Aufgabenstatus aktualisieren | ✅ | ❌ | ✅ | ✅ | ❌ |
| Aufgabe stornieren | ✅ | ❌ | ✅ | ❌ | ❌ |

---

### 3.4 Projekt-Stornierungsablauf

Die Stornierung eines Projekts folgt je nach Initiator zwei verschiedenen Wegen.

#### Direkte Stornierung (durch den Projektersteller)

Ist der Benutzer, der die Stornierung anfordert, der **Projektersteller**, wird die Stornierung sofort durchgeführt: Das Projekt wechselt in den Status `Canceled`, ohne eine Streitigkeit auszulösen.

#### Stornierung mit Streitigkeit (durch einen externen Manager)

Versucht ein Benutzer mit der Rolle **Manager**, ein Projekt zu stornieren, das er **nicht erstellt hat**, leitet das System einen Streitigkeitsprozess mit folgendem Ablauf ein:

```
Manager fordert Stornierung an
        │
        ▼
Projekt → CancelInProgress
Streitigkeit erstellt und Ersteller benachrichtigt
        │
        ├──► Ersteller akzeptiert → Projekt wechselt zu Canceled
        │
        ├──► Ersteller lehnt ab → Streitigkeit gelöst, Projekt kehrt zu Active zurück
        │
        └──► 15 Tage ohne Antwort → Projekt wird automatisch storniert → Canceled
```

> Während des `CancelInProgress`-Zeitraums ist das Projekt **vollständig gesperrt**: Das Projekt, seine Ziele und Aufgaben können weder erstellt, bearbeitet noch geändert werden, bis die Streitigkeit gelöst ist.

Der Endpunkt, über den der Ersteller auf die Streitigkeit reagieren kann, lautet:

**`PUT/PATCH /projects/dispute/{dispute}`** — siehe Abschnitt [9. Streitigkeiten](#9-streitigkeiten).

---

### 3.5 Kaskadenstornierung

Wenn eine Entität storniert wird, propagiert das System automatisch den Status `Canceled` auf die noch aktiven untergeordneten Entitäten und respektiert dabei bereits abgeschlossene Zustände.

#### Beim Stornieren eines Projekts

Alle **Objectives** des Projekts, die sich **nicht** im Status `Completed` oder `Canceled` befinden, werden automatisch auf `Canceled` gesetzt.

```
Project → Canceled
    │
    ├── Objective (NotCompleted)  →  Canceled       ✅
    ├── Objective (Completed)     →  keine Änderung ⛔
    └── Objective (Canceled)      →  keine Änderung ⛔
```

#### Beim Stornieren eines Ziels

Alle **Tasks** des Ziels, die sich **nicht** im Status `Completed` oder `Canceled` befinden, werden automatisch auf `Canceled` gesetzt.

```
Objective → Canceled
    │
    ├── Task (Pending)    →  Canceled       ✅
    ├── Task (Assigned)   →  Canceled       ✅
    ├── Task (InProgress) →  Canceled       ✅
    ├── Task (Completed)  →  keine Änderung ⛔
    └── Task (Canceled)   →  keine Änderung ⛔
```

> Die Kaskadenstornierung erfolgt automatisch und erfordert keine weiteren Aktionen des Benutzers. Änderungen werden sofort in den Antworten der Listenendpunkte angezeigt.

---

## 4. Authentifizierung

### POST `/login`

Authentifiziert einen Benutzer im System. Bei Erfolg wird ein **Bearer Token** zurückgegeben, der für geschützte Endpunkte benötigt wird.

**Authentifizierung erforderlich:** Nein

**Anfrage-Body:**
```json
{
  "email": "user@example.com",
  "password": "string (mind. 8 Zeichen)"
}
```

**Erfolgreiche Antwort `200`:**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "Beispielbenutzer",
      "email": "user@example.com"
    },
    "token": "BearerToken"
  },
  "message": "Login successfull"
}
```

**Mögliche Fehler:**

| Code | Beschreibung |
|------|-------------|
| `401` | Ungültige Anmeldedaten |

---

### POST `/logout`

Beendet die Sitzung des authentifizierten Benutzers und invalidiert den aktuellen Bearer Token.

**Authentifizierung erforderlich:** Ja `Bearer Token`

**Erfolgreiche Antwort `200`:**
```json
{
  "success": true,
  "data": [],
  "message": "Logout successfull"
}
```

**Mögliche Fehler:**

| Code | Beschreibung |
|------|-------------|
| `401` | Ungültiger oder abgelaufener Token |

---

## 5. Teams

> Alle Endpunkte in diesem Abschnitt erfordern einen **Bearer Token**.

### GET `/teams`

Ruft alle Teams ab, die dem authentifizierten Benutzer zugeordnet sind.

**Erfolgreiche Antwort `200`:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Team A",
      "description": "Teambeschreibung",
      "status": "Active"
    }
  ],
  "message": "Data retrieved successfuly"
}
```

---

### POST `/teams`

Erstellt ein neues Team. Der erstellende Benutzer wird automatisch als Besitzer zugewiesen.

**Anfrage-Body:**
```json
{
  "name": "string (mind. 3, max. 255, eindeutig)",
  "description": "string (optional, max. 1000)"
}
```

**Erfolgreiche Antwort `201`:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Neues Team",
    "description": "Beschreibung",
    "status": "Active"
  },
  "message": "Team created successfully"
}
```

**Mögliche Fehler:**

| Code | Beschreibung |
|------|-------------|
| `422` | Ungültige Eingabedaten |

---

### GET `/teams/{team}`

Ruft die Details eines bestimmten Teams ab.

**Erfolgreiche Antwort `200`:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Team A",
    "description": "Beschreibung",
    "status": "Active"
  },
  "message": "Team retrieved successfully"
}
```

**Mögliche Fehler:**

| Code | Beschreibung |
|------|-------------|
| `403` | Keine Berechtigung zum Anzeigen dieses Teams |
| `404` | Team nicht gefunden |

---

### PUT `/teams/{team}` · PATCH `/teams/{team}`

Aktualisiert die Daten eines bestehenden Teams.

**Anfrage-Body:**
```json
{
  "name": "string (mind. 3, max. 255, eindeutig)",
  "description": "string (optional, max. 1000)"
}
```

**Erfolgreiche Antwort `200`:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Aktualisiertes Team",
    "description": "Neue Beschreibung",
    "status": "Active"
  },
  "message": "Team updated successfully."
}
```

**Mögliche Fehler:**

| Code | Beschreibung |
|------|-------------|
| `403` | Keine Berechtigung zum Bearbeiten dieses Teams |
| `422` | Ungültige Eingabedaten |

---

### DELETE `/teams/{team}`

Deaktiviert oder löscht ein Team. Nur Benutzer mit Besitzerrechten können diese Aktion ausführen.

**Erfolgreiche Antwort `200`:**
```json
{
  "success": true,
  "data": [],
  "message": "Team inactivated successfully"
}
```

**Mögliche Fehler:**

| Code | Beschreibung |
|------|-------------|
| `403` | Keine Berechtigung zum Löschen dieses Teams |
| `404` | Team nicht gefunden |

---

## 6. Projekte

> Alle Endpunkte in diesem Abschnitt erfordern einen **Bearer Token**.

### GET `/projects`

Ruft alle Projekte ab, die dem authentifizierten Benutzer zugeordnet sind, einschließlich Teaminformationen und der Rolle des Benutzers in jedem Projekt.

**Erfolgreiche Antwort `200`:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Projekt A",
      "description": "Projektbeschreibung",
      "status": "Active",
      "team": { }
    }
  ],
  "message": "Projects retrieved successfully"
}
```

---

### POST `/teams/{team}/projects`

Erstellt ein neues Projekt innerhalb eines Teams. Nur **Teamadministratoren** können diese Aktion ausführen.

**Anfrage-Body:**
```json
{
  "name": "string (mind. 3, max. 255, eindeutig)",
  "description": "string (optional, max. 1000)"
}
```

**Erfolgreiche Antwort `201`:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Neues Projekt",
    "description": "Beschreibung",
    "status": "Active",
    "team_id": 2,
    "user_id": 1
  },
  "message": "Project created successfully"
}
```

**Mögliche Fehler:**

| Code | Beschreibung |
|------|-------------|
| `403` | Benutzer ist kein Teamadministrator |
| `422` | Ungültige Eingabedaten |

---

### GET `/teams/{team}/projects/{project}`

Ruft die Details eines bestimmten Projekts ab, einschließlich Teaminformationen und aktuellem Status.

**Erfolgreiche Antwort `200`:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Projekt A",
    "description": "Beschreibung",
    "status": "Active",
    "team": { }
  },
  "message": "Project retrieved successfully"
}
```

**Mögliche Fehler:**

| Code | Beschreibung |
|------|-------------|
| `403` | Keine Berechtigung zum Anzeigen dieses Projekts |
| `404` | Projekt nicht gefunden |

---

### PUT `/teams/{team}/projects/{project}` · PATCH `/teams/{team}/projects/{project}`

Aktualisiert die Daten eines bestehenden Projekts.

**Anfrage-Body:**
```json
{
  "name": "string (mind. 3, max. 255, eindeutig)",
  "description": "string (optional, max. 1000)"
}
```

**Erfolgreiche Antwort `200`:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Aktualisiertes Projekt",
    "description": "Neue Beschreibung"
  },
  "message": "Project updated successfully"
}
```

**Mögliche Fehler:**

| Code | Beschreibung |
|------|-------------|
| `403` | Keine Berechtigung zum Bearbeiten dieses Projekts |
| `422` | Ungültige Eingabedaten |

---

### DELETE `/teams/{team}/projects/{project}`

Storniert oder löscht ein Projekt. Nur Benutzer mit den entsprechenden Berechtigungen können diese Aktion ausführen.

**Erfolgreiche Antwort `200`:**
```json
{
  "success": true,
  "data": [],
  "message": "Project cancelled successfully"
}
```

**Mögliche Fehler:**

| Code | Beschreibung |
|------|-------------|
| `403` | Keine Berechtigung zum Löschen dieses Projekts |
| `404` | Projekt nicht gefunden |

---

## 7. Ziele

> Alle Endpunkte in diesem Abschnitt erfordern einen **Bearer Token**.

### GET `/projects/{project}/objectives`

Ruft alle Ziele ab, die einem Projekt zugeordnet sind, einschließlich der Aufgabenanzahl pro Ziel.

**Erfolgreiche Antwort `200`:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Ziel 1",
      "description": "Beschreibung",
      "priority": "Medium",
      "tasks_count": 3
    }
  ],
  "message": "Objectives retrieved successfully"
}
```

**Mögliche Fehler:**

| Code | Beschreibung |
|------|-------------|
| `403` | Keine Berechtigung zum Anzeigen der Ziele |
| `404` | Projekt nicht gefunden |

---

### POST `/projects/{project}/objectives`

Erstellt ein neues Ziel innerhalb eines Projekts.

**Anfrage-Body:**
```json
{
  "title": "string (mind. 3, max. 255)",
  "description": "string (optional, mind. 3, max. 1000)",
  "priority": "Low | Medium | High (optional)"
}
```

**Erfolgreiche Antwort `201`:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "Neues Ziel",
    "description": "Beschreibung",
    "priority": "Medium",
    "project_id": 1
  },
  "message": "Objective created successfully"
}
```

**Mögliche Fehler:**

| Code | Beschreibung |
|------|-------------|
| `403` | Keine Berechtigung zum Erstellen von Zielen |
| `422` | Ungültige Eingabedaten |

---

### GET `/projects/{project}/objectives/{objective}`

Ruft die Details eines bestimmten Ziels ab, einschließlich der zugehörigen Aufgaben.

**Erfolgreiche Antwort `200`:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "Ziel 1",
    "description": "Beschreibung",
    "priority": "Medium",
    "tasks": []
  },
  "message": "Objective retrieved successfully"
}
```

**Mögliche Fehler:**

| Code | Beschreibung |
|------|-------------|
| `403` | Keine Berechtigung zum Anzeigen dieses Ziels |
| `404` | Ziel nicht gefunden |

---

### PUT `/projects/{project}/objectives/{objective}` · PATCH `/projects/{project}/objectives/{objective}`

Aktualisiert die Daten eines bestehenden Ziels.

**Anfrage-Body:**
```json
{
  "title": "string (mind. 3, max. 255, eindeutig)",
  "description": "string (optional, max. 1000)",
  "priority": "Low | Medium | High"
}
```

**Erfolgreiche Antwort `200`:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "Aktualisiertes Ziel",
    "description": "Neue Beschreibung",
    "priority": "High"
  },
  "message": "Objective updated successfully"
}
```

**Mögliche Fehler:**

| Code | Beschreibung |
|------|-------------|
| `403` | Keine Berechtigung zum Bearbeiten dieses Ziels |
| `422` | Ungültige Eingabedaten |

---

### DELETE `/projects/{project}/objectives/{objective}`

Storniert oder löscht ein Ziel.

**Erfolgreiche Antwort `200`:**
```json
{
  "success": true,
  "data": [],
  "message": "Objective cancelled successfully"
}
```

**Mögliche Fehler:**

| Code | Beschreibung |
|------|-------------|
| `403` | Keine Berechtigung zum Löschen dieses Ziels |
| `404` | Ziel nicht gefunden |

---

## 8. Aufgaben

> Alle Endpunkte in diesem Abschnitt erfordern einen **Bearer Token**.

### GET `/objectives/{objective}/tasks`

Ruft alle Aufgaben ab, die einem Ziel zugeordnet sind.

**Erfolgreiche Antwort `200`:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Aufgabe 1",
      "description": "Beschreibung",
      "due_date": "2026-02-20",
      "status": "Pending"
    }
  ],
  "message": "Tasks retrieved successfully"
}
```

**Mögliche Fehler:**

| Code | Beschreibung |
|------|-------------|
| `403` | Keine Berechtigung zum Anzeigen der Aufgaben |
| `404` | Ziel nicht gefunden |

---

### POST `/objectives/{objective}/tasks`

Erstellt eine neue Aufgabe innerhalb eines Ziels. Kann einem Projektmitglied zugewiesen werden.

**Anfrage-Body:**
```json
{
  "title": "string (mind. 3, max. 255)",
  "description": "string (optional, mind. 3, max. 1000)",
  "due_date": "Datum (muss heute oder später sein)",
  "user_id": "int (optional, muss Projektmitglied sein)"
}
```

**Erfolgreiche Antwort `201`:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "Neue Aufgabe",
    "description": "Beschreibung",
    "due_date": "2026-02-20",
    "status": "Assigned",
    "objective_id": 1,
    "user_id": 2
  },
  "message": "Task created successfully"
}
```

**Mögliche Fehler:**

| Code | Beschreibung |
|------|-------------|
| `403` | Keine Berechtigung zum Erstellen von Aufgaben |
| `422` | Ungültige Eingabedaten |

---

### GET `/objectives/{objective}/tasks/{task}`

Ruft die Details einer bestimmten Aufgabe ab.

**Erfolgreiche Antwort `200`:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "Aufgabe 1",
    "description": "Beschreibung",
    "due_date": "2026-02-20",
    "status": "Pending"
  },
  "message": "Task retrieved successfully"
}
```

**Mögliche Fehler:**

| Code | Beschreibung |
|------|-------------|
| `403` | Keine Berechtigung zum Anzeigen dieser Aufgabe |
| `404` | Aufgabe nicht gefunden |

---

### PUT `/objectives/{objective}/tasks/{task}` · PATCH `/objectives/{objective}/tasks/{task}`

Aktualisiert die Daten einer bestehenden Aufgabe.

**Anfrage-Body:**
```json
{
  "title": "string (mind. 3, max. 255, eindeutig)",
  "description": "string (optional, mind. 3, max. 1000)",
  "due_date": "Datum (muss heute oder später sein)",
  "user_id": "int (optional, muss Projektmitglied sein)"
}
```

**Erfolgreiche Antwort `200`:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "Aktualisierte Aufgabe",
    "description": "Neue Beschreibung",
    "due_date": "2026-02-22",
    "status": "Assigned"
  },
  "message": "Task updated successfully"
}
```

**Mögliche Fehler:**

| Code | Beschreibung |
|------|-------------|
| `403` | Keine Berechtigung zum Bearbeiten dieser Aufgabe |
| `422` | Ungültige Eingabedaten |

---

### DELETE `/objectives/{objective}/tasks/{task}`

Storniert oder löscht eine Aufgabe.

**Erfolgreiche Antwort `200`:**
```json
{
  "success": true,
  "data": [],
  "message": "Task cancelled successfully"
}
```

**Mögliche Fehler:**

| Code | Beschreibung |
|------|-------------|
| `403` | Keine Berechtigung zum Löschen dieser Aufgabe |
| `404` | Aufgabe nicht gefunden |

---

### PATCH `/objectives/{objective}/tasks/status/{task}`

Aktualisiert ausschließlich den Status einer bestimmten Aufgabe.

**Anfrage-Body:**
```json
{
  "status": "string (zulässige Werte gemäß Geschäftslogik)"
}
```

**Erfolgreiche Antwort `200`:**
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

**Mögliche Fehler:**

| Code | Beschreibung |
|------|-------------|
| `403` | Keine Berechtigung zum Aktualisieren des Status |
| `422` | Ungültiger oder nicht erlaubter Status |

---

## 9. Streitigkeiten

> Alle Endpunkte in diesem Abschnitt erfordern einen **Bearer Token**.

### PUT `/projects/dispute/{dispute}` · PATCH `/projects/dispute/{dispute}`

Löst eine mit einem Projekt verbundene Streitigkeit auf.

**Erfolgreiche Antwort `200`:**
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

**Mögliche Fehler:**

| Code | Beschreibung |
|------|-------------|
| `403` | Keine Berechtigung zum Lösen dieser Streitigkeit |
| `404` | Streitigkeit nicht gefunden |

---
