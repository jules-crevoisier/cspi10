# syntax=docker/dockerfile:1

# --- Dépendances PHP (build) ---------------------------------------------------
FROM composer:2.7 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./
COPY app ./app

RUN composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --optimize-autoloader \
    --prefer-dist

# --- Runtime -------------------------------------------------------------------
FROM php:8.3-apache-bookworm AS runtime

LABEL org.opencontainers.image.title="CSPI10 Website"
LABEL org.opencontainers.image.description="Site web CSPI10 — PHP + SQLite"

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public \
    APP_PORT=8080

RUN apt-get update \
    && apt-get install -y --no-install-recommends libsqlite3-dev \
    && docker-php-ext-install pdo_sqlite \
    && apt-get purge -y --auto-remove libsqlite3-dev \
    && a2enmod rewrite headers \
    && sed -i "s/Listen 80/Listen ${APP_PORT}/" /etc/apache2/ports.conf \
    && sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY --from=vendor /app/vendor ./vendor
COPY composer.json composer.lock ./
COPY app ./app
COPY public ./public
COPY database ./database
COPY scripts ./scripts
COPY docker ./docker
COPY .env.example ./

RUN mkdir -p database/data public/uploads/biens public/uploads/actualites public/uploads/partenaires \
    && chown -R www-data:www-data database/data public/uploads \
    && chmod +x docker/entrypoint.sh docker/healthcheck.sh

COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf
COPY docker/apache-security.conf /etc/apache2/conf-available/security-custom.conf
RUN a2enconf security-custom

# Port interne uniquement — Traefik / Dockploy route le trafic, pas de bind sur l'hôte
EXPOSE 8080

HEALTHCHECK --interval=30s --timeout=5s --start-period=20s --retries=3 \
    CMD ["docker/healthcheck.sh"]

ENTRYPOINT ["docker/entrypoint.sh"]
CMD ["apache2-foreground"]
