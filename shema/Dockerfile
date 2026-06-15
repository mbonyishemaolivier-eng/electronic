# Use official lightweight PHP Apache production image
FROM php:8.2-apache

# Install system dependencies and required PHP database extensions
RUN apt-get update && apt-get install -y --no-install-recommends \
    curl \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install pdo pdo_mysql mysqli \
    && a2enmod rewrite

# Copy project files into the container webserver root
COPY . /var/www/html/

# Ensure strict file permissions for the Apache runtime worker
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Expose standard web server port
EXPOSE 80

# REMOVED: Native Docker HEALTHCHECK directive.
# Railway manages application routing and health testing automatically via its platform layer.
