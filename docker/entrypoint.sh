#!/bin/bash
set -e

cd /var/www/html

if [ ! -f .env ] && [ -f .env.example ]; then
    cp .env.example .env
    echo "[entrypoint] Fichier .env créé depuis .env.example — définissez APP_SECRET, RESEND_API_KEY, etc. dans Dockploy."
fi

mkdir -p database/data public/uploads/biens public/uploads/actualites public/uploads/partenaires

# Données versionnées dans Git → copiées dans le volume au 1er démarrage (volume Docker vide)
if [ -f .image-data/cspi.db ] && [ ! -s database/data/cspi.db ]; then
    cp .image-data/cspi.db database/data/cspi.db
    echo "[entrypoint] Base SQLite initialisée depuis l'image (.image-data/cspi.db)."
fi

if [ -d .image-data/uploads ] && [ -z "$(ls -A public/uploads 2>/dev/null)" ]; then
    cp -a .image-data/uploads/. public/uploads/
    echo "[entrypoint] Uploads initialisés depuis l'image (.image-data/uploads)."
fi

php scripts/migrate.php

chown -R www-data:www-data database/data public/uploads 2>/dev/null || true

exec "$@"
