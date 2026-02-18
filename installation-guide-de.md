# Project Manager — Installationsanleitung

**Backend**: Laravel 12 · **PHP**: 8.2+ · **Auth**: Laravel Sanctum

---

## Inhaltsverzeichnis

1. [Voraussetzungen](#1-voraussetzungen)
2. [Installation](#2-installation)
3. [Umgebungskonfiguration](#3-umgebungskonfiguration)
4. [Datenbank](#4-datenbank)
5. [Überprüfung](#5-überprüfung)
6. [Projektstruktur](#6-projektstruktur)
7. [Troubleshooting](#7-troubleshooting)

---

## 1. Voraussetzungen

### Erforderliche Software

| Tool | Mindestversion | Link |
|------|---------------|------|
| PHP | 8.2+ | [php.net](https://www.php.net/) |
| Composer | Latest | [getcomposer.org](https://getcomposer.org/) |
| MySQL | 8.0+ | [mysql.com](https://www.mysql.com/) |
| Git | Any | [git-scm.com](https://git-scm.com/) |

### Benötigte PHP-Erweiterungen

```
PDO        → Datenbankzugriff
PDO_MySQL  → MySQL-Treiber für PDO
XML        → Von Laravel benötigt
Ctype      → Von Laravel benötigt
JSON       → Von Laravel benötigt
OpenSSL    → Verschlüsselung
Mbstring   → String-Verarbeitung
```

### Installation überprüfen

```bash
php -v             # PHP 8.2+
composer --version # Composer
mysql --version    # MySQL 8.0+
git --version      # Git
```

---

## 2. Installation

### Option A: Schnellinstallation (Empfohlen)

```bash
# 1. Repository klonen
git clone https://github.com/gerhern/project_manager_back.git
cd project_manager_back

# 2. Automatisches Setup ausführen
composer run setup

# 3. Entwicklungsserver starten
php artisan serve
```

Der Befehl `composer run setup` führt automatisch alle manuellen Installationsschritte durch: installiert PHP-Abhängigkeiten, kopiert die `.env`-Datei, generiert den Anwendungsschlüssel und führt die Datenbankmigrationen aus.

> **Hinweis:** Vor der Ausführung von `composer run setup` sicherstellen, dass die Datenbank in MySQL erstellt und die Zugangsdaten in der `.env`-Datei konfiguriert wurden.

---

### Option B: Manuelle Installation

#### Schritt 1 — Repository klonen

```bash
git clone https://github.com/gerhern/project_manager_back.git
cd project_manager_back
```

#### Schritt 2 — PHP-Abhängigkeiten installieren

```bash
composer install
```

#### Schritt 3 — Umgebungsdatei einrichten

```bash
cp .env.example .env
```

Die `.env`-Datei vor dem Fortfahren mit den Datenbankzugangsdaten bearbeiten (siehe Abschnitt [3. Umgebungskonfiguration](#3-umgebungskonfiguration)).

#### Schritt 4 — Anwendungsschlüssel generieren

```bash
php artisan key:generate
```

#### Schritt 5 — MySQL-Datenbank erstellen

```sql
CREATE DATABASE project_manager CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

#### Schritt 6 — Migrationen ausführen

```bash
php artisan migrate
```

#### Schritt 7 — Server starten

```bash
php artisan serve
```

Die API ist erreichbar unter: `http://localhost:8000/api`

---

## 3. Umgebungskonfiguration

Die `.env`-Datei steuert die gesamte Anwendungskonfiguration. Nachfolgend werden die relevanten Variablen beschrieben.

### Allgemeine Konfiguration

```dotenv
APP_NAME=ProjectManager
APP_ENV=local          # local | production
APP_DEBUG=true         # false in der Produktion
APP_URL=http://localhost:8000
```

### Datenbank

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=project_manager
DB_USERNAME=root
DB_PASSWORD=
```

### Hinweise für die Produktion

Vor dem Deployment in einer Produktionsumgebung sicherstellen, dass:

- `APP_DEBUG=false` gesetzt ist
- Sichere Zugangsdaten für alle Umgebungsvariablen verwendet werden
- HTTPS aktiviert ist
- CORS entsprechend konfiguriert ist
- Der MySQL-Benutzer nur die notwendigen Berechtigungen besitzt

---

## 4. Datenbank

### Migrationen

```bash
# Alle ausstehenden Migrationen ausführen
php artisan migrate

# Datenbank zurücksetzen und alle Migrationen erneut ausführen
php artisan migrate:refresh

# Vollständiges Rollback
php artisan migrate:reset
```

### Seeds (Testdaten)

```bash
php artisan db:seed
```

### Interaktive Konsole

```bash
php artisan tinker
```

---

## 5. Überprüfung

Nach Abschluss der Installation folgende Befehle ausführen, um sicherzustellen, dass alles korrekt funktioniert:

```bash
# Allgemeinen Anwendungsstatus prüfen
php artisan about

# Alle registrierten API-Routen auflisten
php artisan route:list --path=api

# Automatisierte Tests ausführen
php artisan test

# Konfigurationscache leeren und neu aufbauen
php artisan config:clear
php artisan config:cache
```

Wenn alle Befehle ohne Fehler reagieren, war die Installation erfolgreich. Zur Validierung der API eine Testanfrage an den Login-Endpunkt senden:

```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email": "user@example.com", "password": "password"}'
```

---

## 6. Projektstruktur

```
project_manager_back/
├── app/
│   ├── Enums/            → Entitätsstatus (ProjectStatus, TaskStatus, etc.)
│   ├── Http/
│   │   ├── Controllers/  → Endpunkt-Logik
│   │   ├── Middleware/   → HTTP-Validierungen
│   │   └── Requests/     → Eingabevalidierung
│   ├── Models/           → Eloquent-ORM-Modelle
│   ├── Observers/        → Modell-Event-Listener
│   ├── Policies/         → Autorisierung
│   ├── Traits/           → Wiederverwendbarer Code
│   └── Notifications/    → Systembenachrichtigungen
├── config/               → Konfigurationsdateien
├── database/
│   ├── migrations/       → Tabellenerstellungsskripte
│   ├── factories/        → Testdatengeneratoren
│   └── seeders/          → Initiale Seed-Daten
├── routes/
│   ├── api.php           → API-Endpunktdefinitionen
│   └── console.php       → Konsolenbefehle
├── tests/
│   ├── Feature/          → Funktionstests
│   └── Unit/             → Unit-Tests
├── .env                  → Umgebungsvariablen (nicht versionieren)
├── .env.example          → Vorlage für Umgebungsvariablen
└── composer.json         → PHP-Abhängigkeiten
```

---

## 7. Troubleshooting

### "Class not found" oder fataler Fehler beim Start

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

### MySQL-Verbindungsfehler

Sicherstellen, dass der MySQL-Dienst aktiv ist und die Zugangsdaten in der `.env`-Datei korrekt sind:

```bash
mysql -u root -p -e "SHOW DATABASES;"
```

Falls die Datenbank nicht existiert, diese erstellen:

```sql
CREATE DATABASE project_manager CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Fehler 419 / CSRF bei API-Endpunkten

Sicherstellen, dass der Header `Accept: application/json` in allen Anfragen enthalten ist. Die API verwendet Sanctum-Tokens, keine CSRF-basierten Sessions.

### Logs in Echtzeit anzeigen

```bash
php artisan pail
# oder
tail -f storage/logs/laravel.log
```

---

## Hauptabhängigkeiten

| Paket | Version | Zweck |
|-------|---------|-------|
| Laravel | 12 | Haupt-Framework |
| Laravel Sanctum | Latest | Token-basierte API-Authentifizierung |
| Spatie Laravel Permission | Latest | Rollen- und Berechtigungsverwaltung (RBAC) |
| PHPUnit | 11 | Testing |

---

## Weiterführende Ressourcen

- [Projekt-Repository](https://github.com/gerhern/project_manager_back)
- [Laravel 12 Dokumentation](https://laravel.com/docs/12.x)
- [Laravel Sanctum](https://laravel.com/docs/12.x/sanctum)
- [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission)

---
