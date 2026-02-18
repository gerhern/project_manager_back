# Project Manager — Installation Guide

**Backend**: Laravel 12 · **PHP**: 8.2+ · **Auth**: Laravel Sanctum

---

## Table of Contents

1. [Prerequisites](#1-prerequisites)
2. [Installation](#2-installation)
3. [Environment Configuration](#3-environment-configuration)
4. [Database](#4-database)
5. [Verification](#5-verification)
6. [Project Structure](#6-project-structure)
7. [Troubleshooting](#7-troubleshooting)

---

## 1. Prerequisites

### Required Software

| Tool | Minimum Version | Link |
|------|----------------|------|
| PHP | 8.2+ | [php.net](https://www.php.net/) |
| Composer | Latest | [getcomposer.org](https://getcomposer.org/) |
| MySQL | 8.0+ | [mysql.com](https://www.mysql.com/) |
| Git | Any | [git-scm.com](https://git-scm.com/) |

### Required PHP Extensions

```
PDO        → Database access
PDO_MySQL  → MySQL driver for PDO
XML        → Required by Laravel
Ctype      → Required by Laravel
JSON       → Required by Laravel
OpenSSL    → Encryption
Mbstring   → String manipulation
```

### Verify Installation

```bash
php -v             # PHP 8.2+
composer --version # Composer
mysql --version    # MySQL 8.0+
git --version      # Git
```

---

## 2. Installation

### Option A: Quick Installation (Recommended)

```bash
# 1. Clone repository
git clone https://github.com/gerhern/project_manager_back.git
cd project_manager_back

# 2. Run automatic setup
composer run setup

# 3. Start development server
php artisan serve
```

The `composer run setup` command automatically handles all manual installation steps: installs PHP dependencies, copies the `.env` file, generates the application key, and runs the database migrations.

> **Note:** Before running `composer run setup`, make sure you have created the database in MySQL and configured your credentials in the `.env` file.

---

### Option B: Manual Installation

#### Step 1 — Clone the repository

```bash
git clone https://github.com/gerhern/project_manager_back.git
cd project_manager_back
```

#### Step 2 — Install PHP dependencies

```bash
composer install
```

#### Step 3 — Set up the environment file

```bash
cp .env.example .env
```

Edit the `.env` file with your database credentials before continuing (see section [3. Environment Configuration](#3-environment-configuration)).

#### Step 4 — Generate the application key

```bash
php artisan key:generate
```

#### Step 5 — Create the MySQL database

```sql
CREATE DATABASE project_manager CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

#### Step 6 — Run migrations

```bash
php artisan migrate
```

#### Step 7 — Start the server

```bash
php artisan serve
```

The API will be available at: `http://localhost:8000/api`

---

## 3. Environment Configuration

The `.env` file controls the entire application configuration. The relevant variables are described below.

### General Configuration

```dotenv
APP_NAME=ProjectManager
APP_ENV=local          # local | production
APP_DEBUG=true         # false in production
APP_URL=http://localhost:8000
```

### Database

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=project_manager
DB_USERNAME=root
DB_PASSWORD=
```

### Production Notes

Before deploying to a production environment, make sure to:

- Set `APP_DEBUG=false`
- Use secure credentials for all environment variables
- Enable HTTPS
- Configure CORS appropriately
- Ensure the MySQL user has only the necessary permissions

---

## 4. Database

### Migrations

```bash
# Run all pending migrations
php artisan migrate

# Reset and re-run all migrations
php artisan migrate:refresh

# Roll back all migrations
php artisan migrate:reset
```

### Seeds (test data)

```bash
php artisan db:seed
```

### Interactive Console

```bash
php artisan tinker
```

---

## 5. Verification

Once the installation is complete, run the following commands to confirm everything is working correctly:

```bash
# Review general application status
php artisan about

# List all registered API routes
php artisan route:list --path=api

# Run automated tests
php artisan test

# Clear and rebuild configuration cache
php artisan config:clear
php artisan config:cache
```

If all commands respond without errors, the installation was successful. To validate the API, send a test request to the login endpoint:

```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email": "user@example.com", "password": "password"}'
```

---

## 6. Project Structure

```
project_manager_back/
├── app/
│   ├── Enums/            → Entity statuses (ProjectStatus, TaskStatus, etc.)
│   ├── Http/
│   │   ├── Controllers/  → Endpoint logic
│   │   ├── Middleware/   → HTTP validations
│   │   └── Requests/     → Input validation
│   ├── Models/           → Eloquent ORM models
│   ├── Observers/        → Model event listeners
│   ├── Policies/         → Authorization
│   ├── Traits/           → Reusable code
│   └── Notifications/    → System notifications
├── config/               → Configuration files
├── database/
│   ├── migrations/       → Table creation scripts
│   ├── factories/        → Test data generators
│   └── seeders/          → Initial seed data
├── routes/
│   ├── api.php           → API endpoint definitions
│   └── console.php       → Console commands
├── tests/
│   ├── Feature/          → Functional tests
│   └── Unit/             → Unit tests
├── .env                  → Environment variables (do not version)
├── .env.example          → Environment variables template
└── composer.json         → PHP dependencies
```

---

## 7. Troubleshooting

### "Class not found" or fatal error on startup

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

### MySQL connection error

Verify that the MySQL service is running and the credentials in `.env` are correct:

```bash
mysql -u root -p -e "SHOW DATABASES;"
```

If the database does not exist, create it:

```sql
CREATE DATABASE project_manager CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Error 419 / CSRF on API endpoints

Make sure to include the `Accept: application/json` header in all requests. The API uses Sanctum tokens, not CSRF-based sessions.

### View logs in real time

```bash
php artisan pail
# or
tail -f storage/logs/laravel.log
```

---

## Main Dependencies

| Package | Version | Purpose |
|---------|---------|---------|
| Laravel | 12 | Main framework |
| Laravel Sanctum | Latest | Token-based API authentication |
| Spatie Laravel Permission | Latest | Role and permission control (RBAC) |
| PHPUnit | 11 | Testing |

---

## Additional Resources

- [Project Repository](https://github.com/gerhern/project_manager_back)
- [Laravel 12 Documentation](https://laravel.com/docs/12.x)
- [Laravel Sanctum](https://laravel.com/docs/12.x/sanctum)
- [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission)

---

