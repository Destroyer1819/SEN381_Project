# Builds CivicConnect for Render (D-018). Three stages: PHP packages, frontend, then the final server.

# ---- Stage 1: install the PHP packages (the vendor folder) ----
FROM php:8.4-cli AS vendor
RUN apt-get update && apt-get install -y --no-install-recommends git unzip libzip-dev \
    && docker-php-ext-install zip
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /app
COPY . .
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# ---- Stage 2: build the React/Inertia frontend (public/build) ----
FROM node:24-slim AS assets
RUN npm install -g pnpm@12
WORKDIR /app
COPY --from=vendor /app /app
RUN pnpm install --frozen-lockfile && pnpm run build

# ---- Stage 3: the server Render runs ----
FROM php:8.4-apache
RUN apt-get update && apt-get install -y --no-install-recommends libpq-dev \
    && docker-php-ext-install pdo_pgsql opcache \
    && rm -rf /var/lib/apt/lists/*
# Serve the public folder, allow Laravel's .htaccess, and listen on the port Render gives us.
RUN a2enmod rewrite \
    && sed -ri 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf \
    && sed -ri 's!AllowOverride None!AllowOverride All!g' /etc/apache2/apache2.conf \
    && sed -ri 's!Listen 80!Listen ${PORT}!' /etc/apache2/ports.conf \
    && sed -ri 's!<VirtualHost \*:80>!<VirtualHost *:${PORT}>!' /etc/apache2/sites-available/000-default.conf
ENV PORT=10000
WORKDIR /var/www/html
COPY --from=vendor /app /var/www/html
COPY --from=assets /app/public/build /var/www/html/public/build
RUN chown -R www-data:www-data storage bootstrap/cache
# On every start: cache the settings, apply new migrations (never seeds), then start the web server.
CMD ["sh", "-c", "php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan migrate --force && chown -R www-data:www-data storage bootstrap/cache && apache2-foreground"]