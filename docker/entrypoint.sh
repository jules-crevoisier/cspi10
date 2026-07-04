#!/bin/bash
set -e

cd /var/www/html

if [ ! -f .env ] && [ -f .env.example ]; then
    cp .env.example .env
    echo "[entrypoint] Fichier .env créé depuis .env.example — configurez vos variables."
fi

php scripts/migrate.php

chown -R www-data:www-data database/data public/uploads 2>/dev/null || true

exec "$@"
