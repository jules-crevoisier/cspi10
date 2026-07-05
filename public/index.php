<?php
declare(strict_types=1);

// Fichiers statiques (uploads, assets…)
$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
$staticFile = __DIR__ . $requestPath;
if ($requestPath !== '/' && is_file($staticFile)) {
    return false;
}

// Fallback : chemin décodé (espaces %20, etc.)
if ($requestPath !== '/') {
    $decodedPath = rawurldecode($requestPath);
    if ($decodedPath !== $requestPath && is_file(__DIR__ . $decodedPath)) {
        $_SERVER['SCRIPT_FILENAME'] = __DIR__ . $decodedPath;
        return false;
    }
}

require_once __DIR__ . '/../app/bootstrap.php';

use App\Controller\AdminController;

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
$uri = preg_replace('#^/public#', '', $uri);
$uri = preg_replace('#^/index\.php#', '', $uri) ?: '/';

/** Routes admin protégées (sauf login) */
$adminPublicRoutes = ['/admin/login'];
$isAdminRoute = str_starts_with($uri, '/admin');
if ($isAdminRoute && !in_array($uri, $adminPublicRoutes, true)) {
    (new AdminController())->requireLogin();
}

/** Routes admin JSON (images) — auth + CSRF */
$adminJsonGuard = static function () use ($uri): void {
    (new AdminController())->requireLoginJson();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        \App\Core\Security::verifyCsrfFromRequest();
    }
};

switch ($uri) {
    case '':
    case '/':
        require __DIR__ . '/view/home.php';
        break;

    case '/actualites':
        $actualites = \App\Models\Actualite::getAll();
        $categories = \App\Models\Actualite::getCategories();
        require __DIR__ . '/view/actualites.php';
        break;

    case '/actualite-detail':
        require __DIR__ . '/view/actualite-detail.php';
        break;

    case (preg_match('/^\/actualite\/(\d+)$/', $uri, $matches) ? true : false):
        $_GET['id'] = $matches[1];
        require __DIR__ . '/view/actualite-detail.php';
        break;

    case '/adhesion':
        require __DIR__ . '/view/adhesion.php';
        break;

    case '/biens':
        $biens = \App\Models\Bien::getAll();
        $types = ['vente', 'location', 'location_etudiante'];
        require __DIR__ . '/view/biens.php';
        break;

    case '/bien-detail':
        require __DIR__ . '/view/bien-detail.php';
        break;

    case (preg_match('/^\/bien\/(\d+)$/', $uri, $matches) ? true : false):
        $_GET['id'] = $matches[1];
        require __DIR__ . '/view/bien-detail.php';
        break;

    case '/partenaires':
        require __DIR__ . '/view/partenaires.php';
        break;

    case '/contact':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new \App\Controller\ContactController())->sendMessage();
        } else {
            require __DIR__ . '/view/contact.php';
        }
        break;

    case '/mentions-legales':
        require __DIR__ . '/view/mentions-legales.php';
        break;

    case '/health':
        require __DIR__ . '/health.php';
        break;

    // Administration
    case '/admin/login':
        require ADMIN_PATH . 'login.php';
        break;

    case '/admin/dashboard':
        require ADMIN_PATH . 'admin.php';
        break;

    case '/admin/logout':
        require ADMIN_PATH . 'logout.php';
        break;

    case '/admin/actualites/liste_actualites':
    case '/admin/actualites':
        require ADMIN_PATH . 'actualites/liste_actualites.php';
        break;

    case '/admin/actualites/create':
    case '/admin/actualites/edit':
        require ADMIN_PATH . 'actualites/form.php';
        break;

    case (preg_match('/^\/admin\/actualites\/edit\/(\d+)$/', $uri, $matches) ? true : false):
        $_GET['id'] = $matches[1];
        require ADMIN_PATH . 'actualites/form.php';
        break;

    case (preg_match('/^\/admin\/actualites\/delete\/(\d+)$/', $uri, $matches) ? true : false):
        $_GET['id'] = $matches[1];
        require ADMIN_PATH . 'actualites/delete_confirm.php';
        break;

    case '/admin/biens/liste_biens':
    case '/admin/biens':
        require ADMIN_PATH . 'biens/liste_biens.php';
        break;

    case '/admin/biens/create':
    case '/admin/biens/edit':
        require ADMIN_PATH . 'biens/form.php';
        break;

    case (preg_match('/^\/admin\/biens\/edit\/(\d+)$/', $uri, $matches) ? true : false):
        $_GET['id'] = $matches[1];
        require ADMIN_PATH . 'biens/form.php';
        break;

    case (preg_match('/^\/admin\/biens\/delete\/(\d+)$/', $uri, $matches) ? true : false):
        $_GET['id'] = $matches[1];
        require ADMIN_PATH . 'biens/delete.php';
        break;

    case '/admin/biens/detail':
        require ADMIN_PATH . 'biens/detail.php';
        break;

    case (preg_match('/^\/admin\/biens\/detail\/(\d+)$/', $uri, $matches) ? true : false):
        $_GET['id'] = $matches[1];
        require ADMIN_PATH . 'biens/detail.php';
        break;

    case (preg_match('/^\/admin\/biens\/view\/(\d+)$/', $uri, $matches) ? true : false):
        $_GET['id'] = $matches[1];
        (new \App\Controller\AdminBienController())->view($matches[1]);
        break;

    case '/admin/partenaires/liste_partenaires':
    case '/admin/partenaires':
        require ADMIN_PATH . 'partenaires/liste_partenaires.php';
        break;

    case '/admin/partenaires/create':
    case '/admin/partenaires/edit':
        require ADMIN_PATH . 'partenaires/form.php';
        break;

    case (preg_match('/^\/admin\/partenaires\/edit\/(\d+)$/', $uri, $matches) ? true : false):
        $_GET['id'] = $matches[1];
        require ADMIN_PATH . 'partenaires/form.php';
        break;

    case (preg_match('/^\/admin\/partenaires\/delete\/(\d+)$/', $uri, $matches) ? true : false):
        $_GET['id'] = $matches[1];
        require ADMIN_PATH . 'partenaires/delete.php';
        break;

    case (preg_match('/^\/admin\/actualites\/(\d+)\/image\/(\d+)\/delete$/', $uri, $matches) ? true : false):
        $adminJsonGuard();
        (new \App\Controller\AdminActualiteController())->deleteImage($matches[1], $matches[2]);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => true, 'message' => 'Image supprimée.'], JSON_UNESCAPED_UNICODE);
        break;

    case (preg_match('/^\/admin\/actualites\/(\d+)\/image\/(\d+)\/primary$/', $uri, $matches) ? true : false):
        $adminJsonGuard();
        (new \App\Controller\AdminActualiteController())->setPrimaryImage($matches[1], $matches[2]);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => true, 'message' => 'Image principale définie.'], JSON_UNESCAPED_UNICODE);
        break;

    case (preg_match('/^\/admin\/biens\/(\d+)\/image\/(\d+)\/delete$/', $uri, $matches) ? true : false):
        $adminJsonGuard();
        (new \App\Controller\AdminBienController())->deleteImage($matches[1], $matches[2]);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => true, 'message' => 'Image supprimée.'], JSON_UNESCAPED_UNICODE);
        break;

    case (preg_match('/^\/admin\/biens\/(\d+)\/image\/(\d+)\/primary$/', $uri, $matches) ? true : false):
        $adminJsonGuard();
        (new \App\Controller\AdminBienController())->setPrimaryImage($matches[1], $matches[2]);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => true, 'message' => 'Image principale définie.'], JSON_UNESCAPED_UNICODE);
        break;

    default:
        http_response_code(404);
        require __DIR__ . '/view/404.php';
        break;
}
