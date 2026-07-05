#!/bin/bash
set -e

cd /var/www/html

if [ ! -f .env ] && [ -f .env.example ]; then
    cp .env.example .env
    echo "[entrypoint] Fichier .env créé depuis .env.example — définissez APP_SECRET, RESEND_API_KEY, etc. dans Dockploy."
fi

mkdir -p database/data public/uploads/biens public/uploads/actualites public/uploads/partenaires

# Données Git → volumes (1er démarrage ou volume vide / sans contenu)
docker/seed-volumes.sh

php scripts/migrate.php

chown -R www-data:www-data database/data public/uploads 2>/dev/null || true

exec "$@"
