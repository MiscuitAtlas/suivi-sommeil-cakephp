# Démonstration en ligne : PHP 8.3, Apache et une base SQLite recréée à chaque démarrage.
FROM php:8.3-apache

# intl est exigée par CakePHP ; pdo_sqlite et mbstring sont déjà incluses dans l'image.
RUN apt-get update \
    && apt-get install -y --no-install-recommends libicu-dev unzip \
    && docker-php-ext-install intl opcache \
    && rm -rf /var/lib/apt/lists/* \
    && a2enmod rewrite headers \
    && cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Dépendances d'abord : cette étape reste en cache tant que composer.lock ne change pas.
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction --no-progress

COPY . .
RUN composer dump-autoload --no-dev --optimize \
    && cp config/app_local.example.php config/app_local.php \
    && mkdir -p tmp/cache/models tmp/cache/persistent tmp/cache/views tmp/sessions logs \
    && chown -R www-data:www-data tmp logs \
    && chmod +x docker/entrypoint.sh

COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf
COPY docker/php.ini "$PHP_INI_DIR/conf.d/zz-app.ini"
RUN echo 'Listen ${PORT}' > /etc/apache2/ports.conf

ENV PORT=10000 \
    DEBUG=false \
    DEMO_MODE=true \
    DATABASE_URL=sqlite:////var/www/html/tmp/demo.sqlite \
    LOG_DEBUG_URL="console:///?stream=php://stdout&levels[]=notice&levels[]=info&levels[]=debug" \
    LOG_ERROR_URL="console:///?stream=php://stderr&levels[]=warning&levels[]=error&levels[]=critical&levels[]=alert&levels[]=emergency"

EXPOSE 10000
ENTRYPOINT ["docker/entrypoint.sh"]
