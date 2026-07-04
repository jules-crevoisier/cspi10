<?php
declare(strict_types=1);

/** Point d'entrée santé pour Dockploy / monitoring */
require_once dirname(__DIR__) . '/app/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

try {
    \App\Core\Database::queryOne('SELECT 1 AS ok', []);
    echo json_encode([
        'status' => 'ok',
        'driver' => \App\Core\Database::driver(),
        'timestamp' => date('c'),
    ], JSON_THROW_ON_ERROR);
} catch (Throwable $e) {
    http_response_code(503);
    echo json_encode([
        'status' => 'error',
        'message' => 'Base de données indisponible',
    ], JSON_THROW_ON_ERROR);
}
