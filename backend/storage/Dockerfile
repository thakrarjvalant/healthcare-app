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
RUN bash -c 'cat > /etc/nginx/conf.d/default.conf <<"EOF"
server {
	listen 8000;
	server_name _;
	root /var/www/html;
	index index.php index.html index.htm;

	location / {
		try_files $uri $uri/ /index.php?$query_string;
	}

	location ~ \.php$ {
		include fastcgi_params;
		fastcgi_pass 127.0.0.1:9000;
		fastcgi_index index.php;
		fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
	}

	location ~ /\.ht {
		deny all;
	}
}
EOF'

# Expose the same port as before
EXPOSE 8000

# Start php-fpm in foreground and nginx (nginx in foreground keeps container alive)
CMD ["sh", "-c", "php-fpm -F & nginx -g 'daemon off;'"]