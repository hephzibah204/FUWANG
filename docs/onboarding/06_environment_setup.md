# 06. Environment Setup Guide

## 6.1 Prerequisites
Before you begin, ensure you have the following installed on your local machine:
- **PHP 8.2+** with necessary extensions (bcmath, ctype, fileinfo, json, mbstring, openssl, pdo, tokenizer, xml).
- **Composer** (PHP dependency manager).
- **Node.js (v18+)** and **npm** (for frontend assets).
- **MySQL (v8.0+)**.
- **Redis** (optional, for high-speed caching and queues).

## 6.2 Local Installation Steps
1.  **Clone the Repository**:
    ```bash
    git clone https://github.com/hephzibah204/FUWANG.git
    cd fuwa-ng
    ```
2.  **Install PHP Dependencies**:
    ```bash
    composer install
    ```
3.  **Install Node Dependencies**:
    ```bash
    npm install
    ```
4.  **Configure Environment**:
    - Copy the example `.env` file:
      ```bash
      cp .env.example .env
      ```
    - Update your database credentials in the `.env` file:
      ```env
      DB_CONNECTION=mysql
      DB_HOST=127.0.0.1
      DB_PORT=3306
      DB_DATABASE=fuwa_db
      DB_USERNAME=root
      DB_PASSWORD=
      ```
5.  **Generate Application Key**:
    ```bash
    php artisan key:generate
    ```
6.  **Run Database Migrations & Seeders**:
    ```bash
    php artisan migrate --seed
    ```
7.  **Build Frontend Assets**:
    ```bash
    npm run build
    ```

## 6.3 Local Development Workflow
To start the local development server and background processes:
- **Server**: `php artisan serve` (Starts the app at http://localhost:8000).
- **Frontend (Vite)**: `npm run dev` (Hot-reloading for CSS/JS).
- **Queue Worker**: `php artisan queue:listen` (Required for emails and campaigns).
- **Telescope**: Visit `/telescope` to monitor requests, jobs, and exceptions locally.

## 6.4 Key Configuration Files
- **`.env`**: Local environment variables (Database, API keys, App status).
- **`config/app.php`**: Global application settings.
- **`config/database.php`**: Connection details for MySQL and Redis.
- **`config/auth.php`**: Authentication guards and providers (web, admin, api).
- **`config/services.php`**: Credentials for third-party services (Monnify, Payvessel, Gemini).

## 6.5 Troubleshooting
- **Missing `.env`**: Ensure you have copied `.env.example` and set a valid `APP_KEY`.
- **Database Connection Refused**: Verify your MySQL service is running and credentials are correct.
- **Vite/Build Errors**: Clear npm cache and re-run `npm install`.
- **Permissions**: Ensure `storage` and `bootstrap/cache` directories are writable by the web server.
