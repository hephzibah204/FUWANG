# 08. Deployment Guide

## 8.1 Overview
**Fuwa.NG** is designed for modern, cloud-native deployment. We use a combination of GitHub Actions for CI/CD and Vercel for fast, scalable application hosting.

## 8.2 CI/CD Pipeline (`.github/workflows/ci.yml`)
Our automated pipeline ensures every change is tested and verified before deployment:
1.  **Build**: Installs PHP and Node dependencies.
2.  **Lint**: Runs `laravel/pint` to enforce consistent coding standards.
3.  **Test**: Executes the full PHPUnit test suite.
4.  **Security Scan**: Checks for known vulnerabilities in dependencies.
5.  **Artifact Generation**: Prepares the build for deployment.

## 8.3 Environment Variables
The following variables are critical for production:
- **`APP_NAME`**, **`APP_ENV`** (set to `production`), **`APP_KEY`** (Secret).
- **`DB_HOST`**, **`DB_DATABASE`**, **`DB_USERNAME`**, **`DB_PASSWORD`** (Secret).
- **`MAIL_HOST`**, **`MAIL_USERNAME`**, **`MAIL_PASSWORD`** (for notifications).
- **`REDIS_URL`** (for caching and queues).
- **Third-Party API Keys**:
    - **`MONNIFY_API_KEY`**, **`PAYVESSEL_API_KEY`** (Payments).
    - **`ROBOSTTECH_API_KEY`**, **`DATAVERIFY_API_KEY`** (Identity).
    - **`GEMINI_API_KEY`** (AI).
    - **`SMS_AI_KEY`**, **`SMS_AI_ENDPOINT`** (SMS).

## 8.4 Deployment Procedure (`deploy.sh`)
To deploy the application to a VPS or dedicated server:
1.  **SSH into the server**: `ssh user@your-server-ip`.
2.  **Navigate to the project directory**: `cd /var/www/fuwa-ng`.
3.  **Run the deployment script**:
    ```bash
    ./deploy.sh
    ```
    The script typically performs:
    - `git pull origin production`
    - `composer install --no-dev --optimize-autoloader`
    - `php artisan migrate --force`
    - `npm install && npm run build`
    - `php artisan config:cache && php artisan route:cache && php artisan view:cache`
    - `php artisan queue:restart`

## 8.5 Vercel Deployment (`.vercel/project.json`)
The platform is fully compatible with Vercel for zero-configuration deployments:
1.  **Link to Vercel**: `vercel link`.
2.  **Deploy to Production**: `vercel --prod`.
3.  **Note**: Ensure your environment variables are configured in the Vercel project settings dashboard.

## 8.6 Rollback Procedures
In the event of a failed deployment:
1.  **Code Rollback**: `git checkout tags/last-stable-tag && composer install && npm run build`.
2.  **Database Rollback**: If migrations were destructive, you may need to restore from the latest automated backup in `storage/app/backups/`.
3.  **Cache Clear**: `php artisan optimize:clear`.

## 8.7 Release Checklist
- [ ] Run all tests locally: `php artisan test`.
- [ ] Check for linting errors: `vendor/bin/pint --test`.
- [ ] Ensure all new migrations are safe (non-destructive).
- [ ] Update `.env` with any new third-party API keys.
- [ ] Verify frontend assets are built and minified: `npm run build`.
- [ ] Restart queue workers: `php artisan queue:restart`.
- [ ] Monitor logs via `php artisan pail` or Telescope for any immediate errors.
