FROM php:8.2-apache

RUN apt-get update && apt-get install -y --no-install-recommends curl \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install pdo pdo_mysql mysqli \
    && a2enmod rewrite

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

ENV DB_HOST=db
ENV DB_NAME=neresstore
ENV DB_USER=neresuser
ENV DB_PASS=nerespass
ENV APP_URL=http://localhost:8080

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
    CMD curl -f http://localhost/health.php || exit 1
