
FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    nginx ca-certificates zip unzip \
    && docker-php-ext-install pdo pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

# Copy Composer binary from the official composer image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

# Install PHP dependencies (if composer.json exists)
RUN if [ -f composer.json ]; then composer install --no-interaction --no-ansi --no-dev --prefer-dist --optimize-autoloader || true; fi

# Configure nginx
RUN rm -f /etc/nginx/conf.d/default.conf
COPY nginx.conf /etc/nginx/conf.d/default.conf

RUN chown -R www-data:www-data /var/www/html

EXPOSE 8000

CMD ["sh", "-c", "php-fpm & nginx -g 'daemon off;'"]
