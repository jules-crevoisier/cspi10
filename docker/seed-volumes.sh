#!/bin/bash
# Initialise les volumes Docker depuis le snapshot versionné dans l'image (.image-data/).

set -e

count_upload_files() {
    find public/uploads -type f 2>/dev/null | wc -l | tr -d ' '
}

count_actualites() {
    php -r '
        try {
            $pdo = new PDO("sqlite:database/data/cspi.db");
            echo (int) $pdo->query("SELECT COUNT(*) FROM actualites")->fetchColumn();
        } catch (Throwable) {
            echo 0;
        }
    '
}

seed_database() {
    [ -f .image-data/cspi.db ] || return 0

    if [ ! -f database/data/cspi.db ] || [ ! -s database/data/cspi.db ]; then
        cp .image-data/cspi.db database/data/cspi.db
        echo "[entrypoint] Base SQLite initialisée depuis l'image (.image-data/cspi.db)."
        return 0
    fi

    local actualites
    actualites=$(count_actualites)
    if [ "$actualites" -eq 0 ]; then
        cp .image-data/cspi.db database/data/cspi.db
        echo "[entrypoint] Base réinitialisée depuis l'image (volume sans actualités)."
    fi
}

seed_uploads() {
    [ -d .image-data/uploads ] || return 0

    local files
    files=$(count_upload_files)
    if [ "$files" -eq 0 ]; then
        cp -a .image-data/uploads/. public/uploads/
        echo "[entrypoint] Uploads initialisés depuis l'image (.image-data/uploads)."
    fi
}

seed_database
seed_uploads
