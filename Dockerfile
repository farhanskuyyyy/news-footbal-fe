# Production image: PHP-FPM + Nginx in one container (serversideup base).
# No Node stage needed — the app is plain Blade with Tailwind via CDN.
FROM serversideup/php:8.3-fpm-nginx

# Stateless container: no database — file-based session/cache, logs to stderr.
ENV SESSION_DRIVER=file \
    CACHE_STORE=file \
    QUEUE_CONNECTION=sync \
    LOG_CHANNEL=stderr

USER root
WORKDIR /var/www/html

# Install required PHP extensions:
# - sockets: php-amqplib (RabbitMQ)
# - pdo_pgsql: connect to the shared Postgres DB (users/auth), instead of sqlite
RUN install-php-extensions sockets pdo_pgsql

# Install dependencies first for better layer caching.
COPY --chown=www-data:www-data composer.json composer.lock ./
USER www-data
RUN composer install --no-dev --no-scripts --no-interaction --prefer-dist

COPY --chown=www-data:www-data . .
RUN composer dump-autoload --optimize \
    && php artisan package:discover --ansi

# serversideup/php serves on 8080 and ships a healthcheck out of the box.
EXPOSE 8080
