#!/bin/bash
# Restaure une sauvegarde CSPI10 dans le conteneur (base + uploads).
# Usage : docker exec <container> bash docker/restore-backup.sh /tmp/restore
set -euo pipefail

cd /var/www/html

if [ $# -lt 1 ]; then
    echo "Usage: docker exec <container> bash docker/restore-backup.sh <dossier-backup|backup.tar.gz>"
    echo ""
    echo "Exemple depuis votre machine :"
    echo "  docker cp ./cspi.db <container>:/tmp/restore/cspi.db"
    echo "  docker cp ./uploads/. <container>:/tmp/restore/uploads/"
    echo "  docker exec <container> bash docker/restore-backup.sh /tmp/restore"
    exit 1
fi

php scripts/restore-backup.php "$1"
