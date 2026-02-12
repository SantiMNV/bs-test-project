# Project Structure

This repository is a small PHP application that lists users from MySQL and allows creating new users.

## High-Level Architecture

- Entry points: `index.php` (list) and `create.php` (create).
- Core framework-like layer: files in `core/`.
- Domain model: `models/user.php`.
- Presentation: `views/` with a shared `layout.php`.
- Database schema and seed data: `database/schema.sql`.
- Local runtime: Docker (`Dockerfile`, `docker-compose.yml`, `docker/entrypoint.sh`).

## Directory and File Breakdown

### Root

- `index.php`
  - Main page controller.
  - Boots app, loads users, renders `views/index.php`.
- `create.php`
  - Form handler.
  - Boots app, creates `User`, inserts record using `$_POST`, redirects to `index.php`.
- `README.md`
  - Assignment and setup instructions.
- `Dockerfile`
  - PHP 8.2 + Apache image, installs `pdo_mysql`, runs custom entrypoint.
- `docker-compose.yml`
  - Multi-container setup (`app`, `db`, `adminer`).
- `.dockerignore`
  - Build context exclusions.

### `core/`

- `core/app.php`
  - App bootstrap and dependency wiring.
  - Loads models.
  - Initializes config (`core/config.php`) and DB (`core/database.php`).
  - Provides `renderView($viewfile, $vars)` with output buffering and shared layout.
- `core/config.php`
  - Loads database credentials from `config/database.php` into `Config::$database`.
- `core/database.php`
  - `Database` wrapper around PDO.
  - Connect, execute parameterized queries, convert results to arrays, basic error handling.
- `core/base_model.php`
  - Minimal ORM-like base model.
  - Includes `find`, `findFirst`, `insert`, `update`, `delete`, SQL builder helpers.

### `models/`

- `models/user.php`
  - `User` model extending `BaseModel`.
  - Maps to table `users`.
  - Provides getters: `getName()`, `getEmail()`, `getCity()`.

### `views/`

- `views/layout.php`
  - Shared HTML shell.
  - Includes Bootstrap/CSS/JS assets and prints page content (`$content`).
- `views/index.php`
  - Users table rendering.
  - Form posting to `create.php`.

### `config/`

- `config/database`
  - Template config file (not loaded directly by app).
- `config/database.php`
  - Runtime DB config actually loaded by `core/config.php`.
  - In Docker flow, auto-generated at container startup by `docker/entrypoint.sh`.

### `database/`

- `database/schema.sql`
  - Defines `users` table and sample seed rows.
  - In Docker flow, imported automatically by MySQL container on first init.

### `docker/`

- `docker/entrypoint.sh`
  - Generates `config/database.php` from environment variables.
  - Then starts Apache.

### Frontend assets

- `css/`
  - `bootstrap.min.css`, `application.css`.
- `js/`
  - `jquery.min.js`, `bootstrap.min.js`.
- `fonts/`
  - Bootstrap glyphicon font files.
- `favicon.ico`
  - Site icon.

## Runtime Request Flow

### 1) `GET /` (`index.php`)

1. `index.php` requires `core/app.php`.
2. `App` initializes config and DB connection.
3. `User::find($app->db, '*')` loads all users.
4. `App::renderView('index', ['users' => $users])` renders:
   - `views/index.php` into `$content`.
   - wraps it with `views/layout.php`.

### 2) `POST /create.php`

1. `create.php` requires `core/app.php`.
2. Creates `new User($app->db)`.
3. Calls `$user->insert([...])` with form fields.
4. Redirects back to `index.php`.

## Database and Environment Notes

- The app expects MySQL credentials in `config/database.php`.
- Docker environment variables used:
  - `DB_HOST`, `DB_USER`, `DB_PASSWORD`, `DB_NAME`.
- `docker-compose.yml` mounts `database/schema.sql` into MySQL init directory:
  - `/docker-entrypoint-initdb.d/01-schema.sql`
- Schema import runs on first database initialization (new volume).
