FROM php:8.3-apache-bookworm

LABEL org.opencontainers.image.title="CSPI10 Website"
LABEL org.opencontainers.image.description="Site web CSPI10 — PHP + SQLite/Turso"

RUN apt-get update && apt-get install -y --no-install-recommends \
    libsqlite3-dev \
    unzip \
    git \
    curl \
    && docker-php-ext-install pdo_sqlite \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction

COPY . .

RUN mkdir -p database/data public/uploads/biens public/uploads/actualites public/uploads/partenaires \
    && chown -R www-data:www-data database/data public/uploads \
    && chmod +x docker/entrypoint.sh

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=20s --retries=3 \
    CMD curl -f http://localhost/health.php || exit 1

ENTRYPOINT ["docker/entrypoint.sh"]
CMD ["apache2-foreground"]
