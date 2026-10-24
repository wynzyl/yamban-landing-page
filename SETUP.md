# Standalone Development Setup Guide

This guide will help you set up the project for development using VS Code, standalone PHP, and PostgreSQL.

## Prerequisites

### 1. Install PHP 8.4 with PostgreSQL Support

**Download PHP:**
1. Visit [windows.php.net/download](https://windows.php.net/download/)
2. Download **PHP 8.4.x Non Thread Safe (NTS) x64** ZIP
3. Extract to `C:\php` (or your preferred location)

**Enable PostgreSQL Extensions:**
1. Open `C:\php\php.ini-development`
2. Rename it to `php.ini`
3. Find and uncomment these lines (remove the `;`):
   ```ini
   extension=pdo_pgsql
   extension=pgsql
   extension=openssl
   extension=mbstring
   extension=curl
   extension=fileinfo
   ```
4. Add PHP to your PATH:
   - Open System Environment Variables
   - Add `C:\php` to your PATH variable

**Verify PHP Installation:**
```bash
php -v
php -m | findstr pdo_pgsql
```

### 2. Install PostgreSQL

**Download and Install:**
1. Visit [postgresql.org/download/windows](https://www.postgresql.org/download/windows/)
2. Download PostgreSQL 16.x installer
3. Run installer with these settings:
   - Port: `5432`
   - Password: `722811` (or update `.env` with your chosen password)
   - Locale: Default

**Verify PostgreSQL:**
```bash
psql --version
```

### 3. Install Composer

1. Visit [getcomposer.org/download](https://getcomposer.org/download/)
2. Download and run the Windows installer
3. Verify installation:
   ```bash
   composer --version
   ```

### 4. Install Node.js

1. Visit [nodejs.org](https://nodejs.org/)
2. Download and install LTS version (v20.x or later)
3. Verify installation:
   ```bash
   node -v
   npm -v
   ```

## Project Setup

### 1. Create PostgreSQL Database

```bash
# Connect to PostgreSQL
psql -U postgres

# Create database (in psql prompt)
CREATE DATABASE "yamban-site";

# Exit psql
\q
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install Node dependencies
npm install
```

### 3. Configure Environment

The `.env` file is already configured for PostgreSQL:
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=yamban-site
DB_USERNAME=postgres
DB_PASSWORD=722811
```

Update `DB_PASSWORD` if you used a different password during PostgreSQL installation.

### 4. Run Migrations and Seed Database

```bash
php artisan migrate:fresh --seed
```

### 5. Generate Application Key (if not already set)

```bash
php artisan key:generate
```

## VS Code Setup

### Recommended Extensions

Install these VS Code extensions:

1. **PHP Intelephense** - PHP language support
2. **Laravel Extension Pack** - Laravel helpers
3. **Tailwind CSS IntelliSense** - Tailwind autocomplete
4. **ESLint** - JavaScript linting
5. **PostgreSQL** - Database management

### VS Code Settings

Create `.vscode/settings.json`:
```json
{
  "php.validate.executablePath": "C:\\php\\php.exe",
  "intelephense.stubs": [
    "apache",
    "bcmath",
    "Core",
    "ctype",
    "curl",
    "date",
    "dom",
    "fileinfo",
    "filter",
    "hash",
    "iconv",
    "json",
    "libxml",
    "mbstring",
    "openssl",
    "pcre",
    "PDO",
    "pdo_pgsql",
    "pgsql",
    "Phar",
    "SimpleXML",
    "SPL",
    "tokenizer",
    "xml",
    "xmlreader",
    "xmlwriter",
    "zip",
    "zlib"
  ],
  "files.associations": {
    "*.blade.php": "blade"
  }
}
```

### Launch Configuration

Create `.vscode/launch.json` for debugging:
```json
{
  "version": "0.2.0",
  "configurations": [
    {
      "name": "Launch Laravel",
      "type": "php",
      "request": "launch",
      "port": 9003,
      "pathMappings": {
        "/var/www/html": "${workspaceFolder}"
      }
    }
  ]
}
```

## Development Workflow

### Start Development Server

**Terminal 1 - Laravel Backend:**
```bash
php artisan serve
```
App available at: http://localhost:8000

**Terminal 2 - Vite Frontend (Hot Reload):**
```bash
npm run dev
```

**Or run both together:**
```bash
composer run dev
```

### Common Commands

```bash
# Run tests
composer test

# Code formatting
composer run lint

# Type checking
composer run types:check

# Clear cache
php artisan optimize:clear

# View routes
php artisan route:list

# Database commands
php artisan migrate          # Run migrations
php artisan db:seed          # Seed database
php artisan migrate:fresh    # Reset and re-run migrations
```

## PostgreSQL Management

### Using psql CLI

```bash
# Connect to database
psql -U postgres -d yamban-site

# Useful commands in psql:
\dt                    # List tables
\d table_name          # Describe table
SELECT * FROM users;   # Query data
\q                     # Quit
```

### Using pgAdmin (GUI)

1. Install [pgAdmin](https://www.pgadmin.org/download/)
2. Connect to local PostgreSQL server
3. Manage databases visually

## Troubleshooting

### PostgreSQL Connection Issues

If you get "could not connect to server":
```bash
# Check if PostgreSQL service is running
# Services → PostgreSQL → Start

# Or via command line
net start postgresql-x64-16
```

### PHP Extension Errors

If extensions don't load:
1. Check `php.ini` location: `php --ini`
2. Verify extension files exist in `C:\php\ext\`
3. Ensure no spaces in extension names in `php.ini`

### Port Already in Use

If port 8000 is busy:
```bash
php artisan serve --port=8080
```
Update `APP_URL` in `.env` accordingly.

## Next Steps

1. Open project in VS Code
2. Install recommended extensions
3. Start development servers
4. Visit http://localhost:8000

Happy coding!
