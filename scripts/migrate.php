<?php
declare(strict_types=1);

/**
 * Script de migration — exécuter au déploiement ou en local :
 * php scripts/migrate.php
 */

require_once dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Database;

try {
    Database::migrate(dirname(__DIR__));
    echo "Migration terminée avec succès.\n";
    echo "Driver : " . Database::driver() . "\n";
} catch (Throwable $e) {
    fwrite(STDERR, "Erreur de migration : " . $e->getMessage() . "\n");
    exit(1);
}
