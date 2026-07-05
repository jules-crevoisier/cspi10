<?php
declare(strict_types=1);

/**
 * Restaure une sauvegarde CSPI10 (base SQLite + uploads) en écrasant les données actuelles.
 *
 * Usage (dans le conteneur) :
 *   php scripts/restore-backup.php /chemin/vers/dossier-backup
 *   php scripts/restore-backup.php /chemin/vers/backup.tar.gz
 *
 * Structure attendue du backup :
 *   cspi.db                 (ou database/data/cspi.db)
 *   uploads/                (optionnel — remplace public/uploads)
 */

$root = dirname(__DIR__);

require_once $root . '/vendor/autoload.php';

if (class_exists(\Dotenv\Dotenv::class) && is_file($root . '/.env')) {
    \Dotenv\Dotenv::createImmutable($root)->safeLoad();
}

$backupInput = $argv[1] ?? '';
if ($backupInput === '') {
    fwrite(STDERR, "Usage: php scripts/restore-backup.php <dossier-backup|archive.tar.gz>\n");
    fwrite(STDERR, "\nExemple Docker :\n");
    fwrite(STDERR, "  docker cp ./backup/cspi.db <container>:/tmp/restore/cspi.db\n");
    fwrite(STDERR, "  docker cp ./backup/uploads/. <container>:/tmp/restore/uploads/\n");
    fwrite(STDERR, "  docker exec <container> php scripts/restore-backup.php /tmp/restore\n");
    exit(1);
}

$dbRelative = getenv('DATABASE_PATH') ?: 'database/data/cspi.db';
$dbTarget = str_starts_with($dbRelative, '/') ? $dbRelative : $root . '/' . ltrim($dbRelative, '/');
$uploadsTarget = $root . '/public/uploads';
$backupRoot = $root . '/storage/backups';

$extractDir = null;

try {
    $sourceDir = resolveBackupSource($backupInput, $root, $extractDir);

    $dbSource = findDatabaseFile($sourceDir);
    if ($dbSource === null) {
        throw new RuntimeException('Fichier cspi.db introuvable dans la sauvegarde.');
    }

    if (!isValidSqliteFile($dbSource)) {
        throw new RuntimeException('Le fichier de base n\'est pas un SQLite valide : ' . $dbSource);
    }

    $timestamp = date('Y-m-d_His');
    ensureDirectory($backupRoot);

    echo "=== Restauration CSPI10 ===\n";
    echo "Source : {$sourceDir}\n";
    echo "Base cible : {$dbTarget}\n";

    backupExistingFile($dbTarget, $backupRoot, "cspi_{$timestamp}.db.bak");
    restoreDatabase($dbSource, $dbTarget);
    echo "[OK] Base de données restaurée.\n";

    $uploadsSource = findUploadsDirectory($sourceDir);
    if ($uploadsSource !== null) {
        echo "Uploads source : {$uploadsSource}\n";
        backupExistingDirectory($uploadsTarget, $backupRoot, "uploads_{$timestamp}");
        restoreUploadsDirectory($uploadsSource, $uploadsTarget);
        echo "[OK] Uploads restaurés.\n";
    } else {
        echo "[--] Pas de dossier uploads/ dans la sauvegarde — ignoré.\n";
    }

    fixPermissions($dbTarget, $uploadsTarget);
    echo "\nRestauration terminée.\n";
    echo "Sauvegarde de l'ancien état : {$backupRoot}\n";
} catch (Throwable $e) {
    fwrite(STDERR, 'Erreur : ' . $e->getMessage() . "\n");
    exit(1);
} finally {
    if ($extractDir !== null && is_dir($extractDir)) {
        removeDirectory($extractDir);
    }
}

/**
 * @return non-empty-string
 */
function resolveBackupSource(string $input, string $root, ?string &$extractDir): string
{
    if (!is_file($input) && !is_dir($input)) {
        throw new RuntimeException("Chemin introuvable : {$input}");
    }

    if (is_dir($input)) {
        return realpath($input) ?: $input;
    }

    if (!str_ends_with($input, '.tar.gz') && !str_ends_with($input, '.tgz')) {
        throw new RuntimeException('Fichier backup non supporté (attendu : dossier ou .tar.gz).');
    }

    $extractDir = $root . '/storage/restore_' . bin2hex(random_bytes(4));
    ensureDirectory($extractDir);

    $command = sprintf(
        'tar -xzf %s -C %s',
        escapeshellarg($input),
        escapeshellarg($extractDir)
    );

    exec($command, $output, $exitCode);
    if ($exitCode !== 0) {
        throw new RuntimeException('Impossible d\'extraire l\'archive tar.gz.');
    }

    $entries = array_values(array_filter(scandir($extractDir) ?: [], static fn (string $e): bool => !in_array($e, ['.', '..'], true)));
    if (count($entries) === 1 && is_dir($extractDir . '/' . $entries[0])) {
        return $extractDir . '/' . $entries[0];
    }

    return $extractDir;
}

function findDatabaseFile(string $sourceDir): ?string
{
    $candidates = [
        $sourceDir . '/cspi.db',
        $sourceDir . '/database/data/cspi.db',
    ];

    foreach ($candidates as $path) {
        if (is_file($path)) {
            return $path;
        }
    }

    return null;
}

function findUploadsDirectory(string $sourceDir): ?string
{
    $candidates = [
        $sourceDir . '/uploads',
        $sourceDir . '/public/uploads',
    ];

    foreach ($candidates as $path) {
        if (is_dir($path)) {
            return $path;
        }
    }

    return null;
}

function isValidSqliteFile(string $path): bool
{
    $handle = fopen($path, 'rb');
    if ($handle === false) {
        return false;
    }

    $header = fread($handle, 16);
    fclose($handle);

    return $header === "SQLite format 3\u0000";
}

function ensureDirectory(string $path): void
{
    if (!is_dir($path) && !mkdir($path, 0755, true) && !is_dir($path)) {
        throw new RuntimeException("Impossible de créer le dossier : {$path}");
    }
}

function backupExistingFile(string $file, string $backupRoot, string $name): void
{
    if (!is_file($file)) {
        return;
    }

    $destination = $backupRoot . '/' . $name;
    if (!copy($file, $destination)) {
        throw new RuntimeException("Impossible de sauvegarder {$file} vers {$destination}");
    }

    echo "[bak] Base actuelle → {$destination}\n";
}

function backupExistingDirectory(string $directory, string $backupRoot, string $name): void
{
    if (!is_dir($directory)) {
        return;
    }

    $destination = $backupRoot . '/' . $name;
    copyDirectory($directory, $destination);
    echo "[bak] Uploads actuels → {$destination}\n";
}

function restoreDatabase(string $source, string $target): void
{
    ensureDirectory(dirname($target));

    $temp = $target . '.restore.' . bin2hex(random_bytes(4));
    if (!copy($source, $temp)) {
        throw new RuntimeException("Impossible de copier {$source} vers {$temp}");
    }

    if (is_file($target) && !unlink($target)) {
        @unlink($temp);
        throw new RuntimeException("Impossible de remplacer {$target}");
    }

    if (!rename($temp, $target)) {
        @unlink($temp);
        throw new RuntimeException("Impossible de finaliser la restauration de {$target}");
    }
}

function restoreUploadsDirectory(string $source, string $target): void
{
    ensureDirectory($target);
    clearDirectory($target);
    copyDirectory($source, $target);
}

function clearDirectory(string $directory): void
{
    if (!is_dir($directory)) {
        return;
    }

    $items = scandir($directory);
    if ($items === false) {
        throw new RuntimeException("Impossible de lire {$directory}");
    }

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }

        $path = $directory . '/' . $item;
        if (is_dir($path)) {
            removeDirectory($path);
            continue;
        }

        if (!unlink($path)) {
            throw new RuntimeException("Impossible de supprimer {$path}");
        }
    }
}

function copyDirectory(string $source, string $destination): void
{
    ensureDirectory($destination);

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $item) {
        /** @var SplFileInfo $item */
        $relative = substr($item->getPathname(), strlen($source) + 1);
        $targetPath = $destination . '/' . $relative;

        if ($item->isDir()) {
            ensureDirectory($targetPath);
            continue;
        }

        ensureDirectory(dirname($targetPath));
        if (!copy($item->getPathname(), $targetPath)) {
            throw new RuntimeException("Impossible de copier {$item->getPathname()}");
        }
    }
}

function removeDirectory(string $directory): void
{
    if (!is_dir($directory)) {
        return;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($iterator as $item) {
        /** @var SplFileInfo $item */
        if ($item->isDir()) {
            rmdir($item->getPathname());
            continue;
        }

        unlink($item->getPathname());
    }

    rmdir($directory);
}

function fixPermissions(string $dbTarget, string $uploadsTarget): void
{
    if (function_exists('posix_getpwnam')) {
        $wwwData = posix_getpwnam('www-data');
        if ($wwwData !== false) {
            @chown($dbTarget, $wwwData['uid']);
            @chgrp($dbTarget, $wwwData['gid']);
            chownRecursive($uploadsTarget, (int) $wwwData['uid'], (int) $wwwData['gid']);
        }
    }

    @chmod($dbTarget, 0644);
}

function chownRecursive(string $directory, int $uid, int $gid): void
{
    if (!is_dir($directory)) {
        return;
    }

    @chown($directory, $uid);
    @chgrp($directory, $gid);

    $items = scandir($directory);
    if ($items === false) {
        return;
    }

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }

        $path = $directory . '/' . $item;
        if (is_dir($path)) {
            chownRecursive($path, $uid, $gid);
            continue;
        }

        @chown($path, $uid);
        @chgrp($path, $gid);
    }
}
