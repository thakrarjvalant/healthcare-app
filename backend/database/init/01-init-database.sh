#!/bin/bash
set -e

echo "=== Healthcare App Database Initialization ==="

# Wait for MySQL to be ready
echo "Waiting for MySQL to be ready..."
while ! mysqladmin ping -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" --silent; do
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