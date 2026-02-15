#!/bin/bash
# Production Deployment Script for newScience

echo "=== newScience Production Deployment ==="

# Check if .env exists
if [ ! -f ".env" ]; then
    echo "ERROR: .env file not found!"
    echo "Please copy .env.example to .env and configure for production"
    exit 1
fi

# Set production environment
export CI_ENVIRONMENT=production

# Clear caches
echo "Clearing caches..."
rm -rf writable/cache/*
rm -rf writable/session/*

# Set proper permissions
echo "Setting permissions..."
chmod -R 755 writable/
chmod -R 755 public/uploads/

# Optimize autoloader
echo "Optimizing autoloader..."
composer dump-autoload --optimize --no-dev

# Clear CodeIgniter cache
echo "Clearing CodeIgniter cache..."
php spark cache:clear

# Run database migrations if needed
echo "Checking database migrations..."
php spark migrate:status

echo "=== Deployment Complete ==="
echo "Make sure to:"
echo "1. Configure .env with production database settings"
echo "2. Set proper encryption.key in .env"
echo "3. Configure email settings in .env"
echo "4. Set up proper web server configuration"
echo "5. Test all functionality"
