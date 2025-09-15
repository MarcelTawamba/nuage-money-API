# ---- Builder Stage ----
# 1. Install composer dependencies
FROM composer:2 as builder

WORKDIR /app
# Copy only necessary files to leverage Docker cache
COPY database/ database/
COPY composer.json composer.lock ./
RUN composer install --no-interaction --no-plugins --no-scripts --no-dev --prefer-dist


# ---- Final Production Image ----
FROM php:8.1-fpm-alpine

# Install Nginx, Supervisor, and other system packages
RUN apk add --no-cache nginx supervisor

# Install required PHP extensions
RUN docker-php-ext-install pdo pdo_mysql mysqli zip exif pcntl

# Set working directory
WORKDIR /var/www/html

# Copy application code and dependencies
COPY . .
COPY --from=builder /app/vendor/ /var/www/html/vendor/

# Set correct permissions for storage and bootstrap/cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Copy service configurations into the image
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisord.conf

# Expose port 80 for Nginx
EXPOSE 80

# Start supervisor to manage Nginx and PHP-FPM
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]

