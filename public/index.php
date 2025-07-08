<?php

// Autoloading
require_once __DIR__ . '/../app/config/autoload.php';

// Connexion à la BDD + injection dans les modèles
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/config/helpers.php';

// Récupération de l'URL
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Retirer le chemin de base /public si présent
$basePath = '/public';
if (str_starts_with($uri, $basePath)) {
    $uri = substr($uri, strlen($basePath));
}

// Supprimer /index.php si présent
$uri = preg_replace('#^/index\.php#', '', $uri);

// Gestion des routes simples
switch ($uri) {
    case '':
    case '/':
        require __DIR__ . '/view/home.php';
        break;

    case '/actualites':
        // Initialiser les variables pour la page actualités
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

    case '/adherents':
        require __DIR__ . '/view/adherents.php';
        break;

    case '/biens':
        // Initialiser les variables pour la page biens
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
            // Traitement du formulaire de contact via AJAX
            $controller = new \App\Controller\ContactController();
            $controller->sendMessage();
        } else {
            // Affichage de la page de contact
            require __DIR__ . '/view/contact.php';
        }
        break;

    case '/mentions-legales':
        require __DIR__ . '/view/mentions-legales.php';
        break;



    // Routes d'administration
    case '/admin/login':
        require __DIR__ . '/admin/login.php';
        break;

    case '/admin/dashboard':
        require __DIR__ . '/admin/admin.php';
        break;

    case '/admin/logout':
        require __DIR__ . '/admin/logout.php';
        break;

    case '/admin/generate_password':
        require __DIR__ . '/admin/generate_password.php';
        break;

    case '/admin/actualites/liste_actualites':
        require __DIR__ . '/admin/actualites/liste_actualites.php';
        break;

    case '/admin/actualites':
        require __DIR__ . '/admin/actualites/liste_actualites.php';
        break;

    case '/admin/actualites/create':
        require __DIR__ . '/admin/actualites/form.php';
        break;

    case '/admin/actualites/edit':
        require __DIR__ . '/admin/actualites/form.php';
        break;

    case (preg_match('/^\/admin\/actualites\/edit\/(\d+)$/', $uri, $matches) ? true : false):
        $_GET['id'] = $matches[1];
        require __DIR__ . '/admin/actualites/form.php';
        break;

    case (preg_match('/^\/admin\/actualites\/delete\/(\d+)$/', $uri, $matches) ? true : false):
        $_GET['id'] = $matches[1];
        require __DIR__ . '/admin/actualites/delete_confirm.php';
        break;

    case '/admin/biens/liste_biens':
        require __DIR__ . '/admin/biens/liste_biens.php';
        break;

    case '/admin/biens':
        require __DIR__ . '/admin/biens/liste_biens.php';
        break;

    case '/admin/biens/create':
        require __DIR__ . '/admin/biens/form.php';
        break;

    case '/admin/biens/edit':
        require __DIR__ . '/admin/biens/form.php';
        break;

    case (preg_match('/^\/admin\/biens\/edit\/(\d+)$/', $uri, $matches) ? true : false):
        $_GET['id'] = $matches[1];
        require __DIR__ . '/admin/biens/form.php';
        break;

    case (preg_match('/^\/admin\/biens\/delete\/(\d+)$/', $uri, $matches) ? true : false):
        $_GET['id'] = $matches[1];
        require __DIR__ . '/admin/biens/delete.php';
        break;

    case '/admin/biens/detail':
        require __DIR__ . '/admin/biens/detail.php';
        break;

    case (preg_match('/^\/admin\/biens\/detail\/(\d+)$/', $uri, $matches) ? true : false):
        $_GET['id'] = $matches[1];
        require __DIR__ . '/admin/biens/detail.php';
        break;

    case '/admin/biens/view':
        require __DIR__ . '/admin/biens/view.php';
        break;

    case (preg_match('/^\/admin\/biens\/view\/(\d+)$/', $uri, $matches) ? true : false):
        $_GET['id'] = $matches[1];
        $controller = new \App\Controller\AdminBienController();
        $controller->view($matches[1]);
        break;

    case '/admin/partenaires/liste_partenaires':
        require __DIR__ . '/admin/partenaires/liste_partenaires.php';
        break;

    case '/admin/partenaires':
        require __DIR__ . '/admin/partenaires/liste_partenaires.php';
        break;

    case '/admin/partenaires/create':
        require __DIR__ . '/admin/partenaires/form.php';
        break;

    case '/admin/partenaires/edit':
        require __DIR__ . '/admin/partenaires/form.php';
        break;

    case (preg_match('/^\/admin\/partenaires\/edit\/(\d+)$/', $uri, $matches) ? true : false):
        $_GET['id'] = $matches[1];
        require __DIR__ . '/admin/partenaires/form.php';
        break;

    case (preg_match('/^\/admin\/partenaires\/delete\/(\d+)$/', $uri, $matches) ? true : false):
        $_GET['id'] = $matches[1];
        require __DIR__ . '/admin/partenaires/delete.php';
        break;

    case (preg_match('/^\/admin\/actualites\/(\d+)\/image\/(\d+)\/delete$/', $uri, $matches) ? true : false):
        $controller = new \App\Controller\AdminActualiteController();
        $controller->deleteImage($matches[1], $matches[2]);
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        break;

    case (preg_match('/^\/admin\/actualites\/(\d+)\/image\/(\d+)\/primary$/', $uri, $matches) ? true : false):
        $controller = new \App\Controller\AdminActualiteController();
        $controller->setPrimaryImage($matches[1], $matches[2]);
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        break;

    // Routes pour la gestion des images des biens
    case (preg_match('/^\/admin\/biens\/(\d+)\/image\/(\d+)\/delete$/', $uri, $matches) ? true : false):
        try {
            $controller = new \App\Controller\AdminBienController();
            $controller->deleteImage($matches[1], $matches[2]);
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            error_log("Erreur lors de la suppression d'image: " . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;

    case (preg_match('/^\/admin\/biens\/(\d+)\/image\/(\d+)\/primary$/', $uri, $matches) ? true : false):
        try {
            $controller = new \App\Controller\AdminBienController();
            $controller->setPrimaryImage($matches[1], $matches[2]);
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            error_log("Erreur lors de la définition de l'image principale: " . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;

    default:
        http_response_code(404);
        echo "Page non trouvée : $uri";
        break;
}
