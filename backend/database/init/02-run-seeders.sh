#!/bin/bash
set -e

# Wait for MySQL to be ready
echo "Waiting for MySQL to be ready..."
while ! mysqladmin ping -h"$DB_HOST" -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" --silent; do
    sleep 1
done

echo "MySQL is ready. Running seeders..."

# Run seeders
cd /var/www/html/database
php seed.php

echo "Seeders completed successfully!"