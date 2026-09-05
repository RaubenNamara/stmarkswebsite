#!/bin/bash

# Laravel Deployment Script
# This script prepares your Laravel application for production deployment

echo "Starting Laravel deployment process..."

# Install production dependencies
echo "Installing PHP dependencies..."
composer install --optimize-autoloader --no-dev --no-interaction

# Install and build frontend assets
echo "Installing and building frontend assets..."
npm ci --production
npm run build

# Clear and cache Laravel configurations
echo "Optimizing Laravel application..."
php artisan config:clear
php artisan config:cache
php artisan route:clear
php artisan route:cache
php artisan view:clear
php artisan view:cache

# Run database migrations
echo "Running database migrations..."
php artisan migrate --force

# Optimize for production
echo "Optimizing for production..."
php artisan optimize

# Clear application cache
echo "Clearing application cache..."
php artisan cache:clear

# Set correct permissions
echo "Setting file permissions..."
chmod -R 755 storage
chmod -R 755 bootstrap/cache

echo "Deployment completed successfully!"
