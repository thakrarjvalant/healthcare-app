#!/bin/bash
set -e

echo "=== Healthcare App Database Initialization ==="

# Wait for MySQL to be ready using PHP
echo "Waiting for MySQL to be ready..."
until php -r "try { new PDO('mysql:host=db;dbname=healthcare_db', 'healthcare_user', 'your_strong_password'); echo 'success'; } catch (Exception \$e) { exit(1); }" >/dev/null 2>&1; do
  echo "MySQL is unavailable - sleeping"
  sleep 1
done

echo "MySQL is ready. Initializing database..."

# Change to the database directory
cd /var/www/html/database

# Install Composer dependencies if needed
if [ ! -d "vendor" ]; then
    echo "Installing Composer dependencies..."
    composer install
fi

# Run migrations
echo "Running database migrations..."
php migrate.php

# Run seeders
echo "Running database seeders..."
php seed.php

echo "=== Database initialization completed successfully! ==="