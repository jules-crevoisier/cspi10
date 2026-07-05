#!/bin/bash
# Restauration automatique au démarrage si une sauvegarde est présente sur /backup.
set -euo pipefail

cd /var/www/html

RESTORE_PATH="${RESTORE_BACKUP_PATH:-/backup/deploy-restore.tar.gz}"
RESTORE_DIR="/backup/restore"
STATE_FILE="storage/backups/.restore-state"

resolve_source() {
    if [ -f "$RESTORE_PATH" ]; then
        echo "$RESTORE_PATH"
        return 0
    fi

    if [ -f "/backup/deploy-restore.tar.gz" ] && [ "$RESTORE_PATH" != "/backup/deploy-restore.tar.gz" ]; then
        echo "/backup/deploy-restore.tar.gz"
        return 0
    fi

    if [ -d "$RESTORE_DIR" ] && [ -f "$RESTORE_DIR/cspi.db" ]; then
        echo "$RESTORE_DIR"
        return 0
    fi

    return 1
}

if ! SOURCE="$(resolve_source)"; then
    exit 0
fi

mkdir -p storage/backups

if [ -f "$SOURCE" ]; then
    CURRENT_HASH="file:$(stat -c '%s-%Y' "$SOURCE" 2>/dev/null || stat -f '%z-%m' "$SOURCE")"
else
    CURRENT_HASH="dir:$(find "$SOURCE" -type f -print0 | sort -z | xargs -0 md5sum 2>/dev/null | md5sum | awk '{print $1}')"
fi

if [ -f "$STATE_FILE" ] && [ "$(cat "$STATE_FILE")" = "$CURRENT_HASH" ]; then
    echo "[entrypoint] Sauvegarde déjà restaurée — ignoré."
    exit 0
fi

echo "[entrypoint] Restauration depuis : $SOURCE"
php scripts/restore-backup.php "$SOURCE"
echo "$CURRENT_HASH" > "$STATE_FILE"

if [ -f "$SOURCE" ] && [ "${RESTORE_KEEP_ARCHIVE:-0}" != "1" ]; then
    mv "$SOURCE" "${SOURCE}.applied"
    echo "[entrypoint] Archive renommée en ${SOURCE}.applied"
fi

echo "[entrypoint] Restauration terminée."
