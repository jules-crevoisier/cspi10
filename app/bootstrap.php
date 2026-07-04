<?php
declare(strict_types=1);

/**
 * Bootstrap application — chargé par index.php et scripts CLI.
 */
define('ROOT_PATH', dirname(__DIR__));

require_once ROOT_PATH . '/vendor/autoload.php';
require_once ROOT_PATH . '/app/config/autoload.php';

use App\Core\Database;
use App\Core\Env;
use App\Core\ErrorHandler;
use App\Core\Security;

Env::load(ROOT_PATH);
ErrorHandler::register();
Security::sendSecurityHeaders();
Security::startSession();

Database::boot(ROOT_PATH);

// Constantes applicatives
define('PUBLIC_PATH', ROOT_PATH . '/public/');
define('UPLOADS_PATH', PUBLIC_PATH . 'uploads/');
define('APP_URL', rtrim(Env::get('APP_URL', 'http://localhost:8000') ?? 'http://localhost:8000', '/'));
define('APP_SECRET', Env::get('APP_SECRET', '') ?? '');

define('RESEND_API_KEY', Env::get('RESEND_API_KEY', '') ?? '');
define('CONTACT_FROM_EMAIL', Env::get('CONTACT_FROM_EMAIL', 'no-reply@localhost') ?? 'no-reply@localhost');
define('CONTACT_TO_EMAIL', trim(Env::get('CONTACT_TO_EMAIL', 'contact@localhost') ?? 'contact@localhost'));
define('CONTACT_FROM_NAME', Env::get('CONTACT_FROM_NAME', 'Site CSPI10') ?? 'Site CSPI10');
define('SITE_URL', Env::get('SITE_URL', Env::get('APP_URL', 'http://localhost:8000')) ?? 'http://localhost:8000');
define('ESPACE_ADHERENT_PASSWORD', Env::get('ESPACE_ADHERENT_PASSWORD', '') ?? '');

\App\Models\BaseModel::init();

// Compatibilité legacy
global $pdo;
if (!Database::isTurso()) {
    $pdo = Database::pdo();
}
