<?php
declare(strict_types=1);

/**
 * Supprime les fichiers uploadés non référencés en base de données.
 *
 * Usage :
 *   php scripts/cleanup-uploads.php          # aperçu (dry-run)
 *   php scripts/cleanup-uploads.php --delete # suppression réelle
 */

require_once dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Database;

$delete = in_array('--delete', $argv, true);
$uploadDirs = ['biens', 'actualites', 'partenaires'];

/**
 * @return list<string> chemins relatifs normalisés (uploads/…)
 */
function collectReferencedUploads(): array
{
    $referenced = [];

    $bienImages = Database::query('SELECT url FROM bien_images', []);
    foreach ($bienImages as $row) {
        $path = normalizeUploadPath((string) ($row['url'] ?? ''));
        if ($path !== '') {
            $referenced[$path] = true;
        }
    }

    $actualiteImages = Database::query('SELECT url FROM actualite_images', []);
    foreach ($actualiteImages as $row) {
        $path = normalizeUploadPath((string) ($row['url'] ?? ''));
        if ($path !== '') {
            $referenced[$path] = true;
        }
    }

    $partenaires = Database::query('SELECT logo_url FROM partenaires WHERE logo_url IS NOT NULL AND logo_url != \'\'', []);
    foreach ($partenaires as $row) {
        $path = normalizeUploadPath((string) ($row['logo_url'] ?? ''));
        if ($path !== '') {
            $referenced[$path] = true;
        }
    }

    return array_keys($referenced);
}

function normalizeUploadPath(string $url): string
{
    $url = str_replace('\\', '/', trim($url));
    if ($url === '') {
        return '';
    }

    $url = preg_replace('#^/+#', '', $url) ?? $url;
    $url = preg_replace('#^public/#', '', $url) ?? $url;

    if (!str_starts_with($url, 'uploads/')) {
        return '';
    }

    return $url;
}

/**
 * @return list<string>
 */
function listUploadFiles(array $uploadDirs): array
{
    $files = [];

    foreach ($uploadDirs as $dir) {
        $base = UPLOADS_PATH . $dir;
        if (!is_dir($base)) {
            continue;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if (!$file->isFile()) {
                continue;
            }

            $relative = 'uploads/' . $dir . '/' . $file->getFilename();
            $files[] = str_replace('\\', '/', $relative);
        }
    }

    sort($files);

    return $files;
}

$referenced = collectReferencedUploads();
$onDisk = listUploadFiles($uploadDirs);

$orphans = array_values(array_filter(
    $onDisk,
    static fn (string $path): bool => !isset(array_flip($referenced)[$path])
));

$referencedCount = count($referenced);
$diskCount = count($onDisk);
$orphanCount = count($orphans);

echo "Référencés en BDD : {$referencedCount}\n";
echo "Fichiers sur disque : {$diskCount}\n";
echo "Orphelins          : {$orphanCount}\n\n";

if ($orphanCount === 0) {
    echo "Rien à supprimer.\n";
    exit(0);
}

foreach ($orphans as $path) {
    $fullPath = PUBLIC_PATH . $path;
    if ($delete) {
        if (is_file($fullPath) && unlink($fullPath)) {
            echo "SUPPRIMÉ : {$path}\n";
        } else {
            echo "ERREUR   : {$path}\n";
        }
    } else {
        echo "ORPHELIN : {$path}\n";
    }
}

if (!$delete) {
    echo "\nDry-run — relancez avec --delete pour supprimer.\n";
}

exit(0);
