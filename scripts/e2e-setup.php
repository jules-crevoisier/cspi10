<?php
declare(strict_types=1);

/**
 * Prépare la base de données isolée pour les tests E2E.
 */

$root = dirname(__DIR__);
$dbPath = $root . '/database/data/cspi-test.db';

if (file_exists($dbPath)) {
    unlink($dbPath);
}

putenv('DATABASE_PATH=database/data/cspi-test.db');
putenv('APP_ENV=test');

require $root . '/app/bootstrap.php';

use App\Core\Database;

Database::migrate($root);

$hash = password_hash('admin', PASSWORD_BCRYPT);
Database::execute(
    'INSERT INTO administrateurs (email, password, created_at) VALUES (?, ?, datetime(\'now\'))',
    ['e2e-admin@cspi10.test', $hash]
);

echo "Base de test E2E prête (e2e-admin@cspi10.test / admin)\n";
