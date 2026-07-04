<?php
declare(strict_types=1);

/**
 * Routeur pour les tests E2E — force l'environnement de test avant bootstrap.
 */
$port = getenv('E2E_PORT') ?: '8765';

putenv('APP_ENV=test');
putenv('APP_DEBUG=false');
putenv('DATABASE_PATH=database/data/cspi-test.db');
putenv('APP_URL=http://localhost:' . $port);
putenv('SITE_URL=http://localhost:' . $port);

require __DIR__ . '/../public/index.php';
