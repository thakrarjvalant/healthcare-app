# Use a minimal PHP image
FROM php:8.3.3-cli

# Install system packages and PHP extensions needed for database operations
RUN apt-get update \
	&& apt-get install -y --no-install-recommends \
		libzip-dev \
		zip \
		unzip \
	&& docker-php-ext-install pdo pdo_mysql \
	&& rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy the database directory which contains migrations and seeders
# The database directory is one level up from the init directory
COPY ../ /var/www/html/database

# Install PHP dependencies for the database scripts
WORKDIR /var/www/html/database
RUN if [ -f composer.json ]; then composer install --no-interaction --no-ansi --no-dev --prefer-dist --optimize-autoloader || true; fi

# Ensure proper permissions
RUN chown -R root:root /var/www/html

# Set the default command to run the initialization script
CMD ["/docker-entrypoint-init.d/init-database.sh"]