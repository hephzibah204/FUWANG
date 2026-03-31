#!/bin/bash
# Production Deployment Script

echo "🚀 Starting deployment..."

# Enter maintenance mode (requires laravel 8+)
echo "=> Entering maintenance mode..."
php artisan down || true

# Install dependencies (no dev dependencies)
echo "=> Installing composer dependencies..."
composer install --optimize-autoloader --no-dev

# Run database migrations
echo "=> Running database migrations..."
php artisan migrate --force

# Clear and cache configurations
echo "=> Optimizing configurations..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Build production assets
echo "=> Building assets..."
npm ci
npm run build

# Bring application back up
echo "=> Bringing application online..."
php artisan up

echo "✅ Deployment completed successfully!"
