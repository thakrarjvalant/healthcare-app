# Use PHP 8.3.3 FPM and run nginx in the same image
FROM php:8.3.3-fpm

# Install system packages (nginx) and PHP extensions
RUN apt-get update \
	&& apt-get install -y --no-install-recommends nginx ca-certificates zip unzip \
	&& docker-php-ext-install pdo pdo_mysql \
	&& rm -rf /var/lib/apt/lists/*

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Copy Composer binary from the official composer image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install PHP dependencies (if composer.json exists)
RUN if [ -f composer.json ]; then composer install --no-interaction --no-ansi --no-dev --prefer-dist --optimize-autoloader || true; fi

# Ensure web server user owns files
RUN chown -R www-data:www-data /var/www/html

# Configure nginx to use php-fpm and listen on port 8000
RUN rm -f /etc/nginx/conf.d/default.conf
COPY nginx.conf /etc/nginx/conf.d/default.conf
# Expose the correct port for billing-service
EXPOSE 8005

# Start php-fpm in foreground and nginx (nginx in foreground keeps container alive)
CMD ["sh", "-c", "php-fpm -F & nginx -g 'daemon off;'"]