# cPanel Deployment Guide for Fuwa.NG

This guide outlines the steps to successfully deploy and run the **Fuwa.NG** platform on a standard cPanel-based shared or private hosting environment.

## 1. Directory Structure Setup
Most cPanel environments use `public_html` as the web root. However, Laravel's core files should stay outside the web root for security.

### Recommended Layout:
```text
/home/username/
├── fuwa_core/          <-- Place all Laravel project files here (except 'public')
└── public_html/        <-- Place the contents of Laravel's 'public' folder here
```

### Steps:
1.  Create a folder named `fuwa_core` in your home directory.
2.  Upload all project files (excluding the `public` folder) into `fuwa_core`.
3.  Upload the contents of the `public` folder into `public_html`.
4.  Edit `public_html/index.php`:
    - Change `require __DIR__.'/../vendor/autoload.php';` to `require __DIR__.'/../fuwa_core/vendor/autoload.php';`
    - Change `$app = require_once __DIR__.'/../bootstrap/app.php';` to `$app = require_once __DIR__.'/../fuwa_core/bootstrap/app.php';`

## 2. Environment Configuration
1.  Rename `.env.example` to `.env` in `fuwa_core`.
2.  Update `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` with the credentials created in cPanel's **MySQL® Databases** wizard.
3.  Set `APP_ENV=production` and `APP_DEBUG=false`.
4.  Run `php artisan key:generate` via terminal or a cron job.

## 3. Storage Permissions
Laravel needs write access to `storage` and `bootstrap/cache`.
```bash
chmod -R 775 /home/username/fuwa_core/storage
chmod -R 775 /home/username/fuwa_core/bootstrap/cache
```
*Note: Some cPanel hosts require `755` instead of `775`.*

## 4. Symlink for Storage
Since the web root is now `public_html`, you need to create a symlink for public storage:
```bash
ln -s /home/username/fuwa_core/storage/app/public /home/username/public_html/storage
```
If you don't have SSH access, you can create a PHP script in `public_html` to run the command:
```php
<?php symlink('/home/username/fuwa_core/storage/app/public', 'storage'); ?>
```

## 5. Background Tasks (Cron Jobs)
Add the following cron job in cPanel's **Cron Jobs** section to run every minute:
```bash
* * * * * /usr/local/bin/php /home/username/fuwa_core/artisan schedule:run >> /dev/null 2>&1
```
*(Verify the PHP path with your host, e.g., `/usr/bin/php8.2`)*

## 6. Database Migrations
If you have terminal access, run:
```bash
php artisan migrate --force
```
Otherwise, use the **Import Database Schema** command in the Admin Dashboard if available, or import the SQL manually via **phpMyAdmin**.

## 7. Performance Optimizations
Once deployed, run these commands to speed up the site:
- `php artisan config:cache`
- `php artisan route:cache`
- `php artisan view:cache`

## 8. Common Issues
- **404 on Routes**: Ensure the `.htaccess` file was copied from `public` to `public_html`.
- **Mix/Vite Assets Not Loading**: Ensure `ASSET_URL` in `.env` is set correctly if using a CDN, otherwise, standard relative paths should work after `npm run build`.
- **Database Lock/Hanging**: Ensure your MySQL version is 8.0+ or MariaDB 10.4+. If issues persist, check the `admin_audit_logs` to see if requests are completing.
