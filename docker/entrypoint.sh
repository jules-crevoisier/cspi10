#!/bin/bash
set -e

cd /var/www/html

if [ ! -f .env ] && [ -f .env.example ]; then
    cp .env.example .env
    echo "[entrypoint] Fichier .env créé depuis .env.example — configurez vos variables."
fi

# Restauration auto si /backup/deploy-restore.tar.gz est monté (une seule fois)
if [ -f docker/restore-on-start.sh ]; then
    bash docker/restore-on-start.sh || {
        echo "[entrypoint] Échec de la restauration — démarrage annulé."
        exit 1
    }
fi

php scripts/migrate.php

chown -R www-data:www-data database/data public/uploads storage/backups 2>/dev/null || true

exec "$@"
