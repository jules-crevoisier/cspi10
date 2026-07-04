<?php
// Bootstrap puis SEO (SITE_URL, APP_URL…)
require_once __DIR__ . '/../../app/bootstrap.php';
require_once __DIR__ . '/../../app/config/seo.php';

// Détermination de la page actuelle pour le SEO
$current_page = 'home';
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = preg_replace('#^/public#', '', $uri);
$uri = preg_replace('#^/index\.php#', '', $uri);

if (preg_match('#^/(actualites?)#', $uri)) {
    $current_page = 'actualites';
} elseif (preg_match('#^/biens?#', $uri)) {
    $current_page = 'biens';
} elseif (preg_match('#^/adhesion#', $uri)) {
    $current_page = 'adhesion';
} elseif (preg_match('#^/contact#', $uri)) {
    $current_page = 'contact';
} elseif (preg_match('#^/partenaires#', $uri)) {
    $current_page = 'partenaires';
} elseif (preg_match('#^/mentions-legales#', $uri)) {
    $current_page = 'mentions-legales';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- SEO Meta Tags -->
    <?= generateSeoMeta($current_page) ?>
    
    <!-- Favicons -->
    <link rel="apple-touch-icon" sizes="180x180" href="/asset/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/asset/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/asset/favicon/favicon-16x16.png">
    <link rel="manifest" href="/asset/favicon/site.webmanifest">
    <link rel="shortcut icon" href="/asset/favicon/favicon.ico">
    
    <!-- Preconnect for performance -->
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="<?= url('/asset/css/style.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    
    <!-- Structured Data -->
    <?= generateJsonLd($current_page) ?>
</head>
<body>
    <header>
        <nav class="navbar" role="navigation" aria-label="Navigation principale">
            <div class="container nav-container">
                <div class="logo">
                    <a href="<?= url('/') ?>" aria-label="Accueil CSPI10">
                        <img src="<?= url('/asset/img/logo.png') ?>" alt="Logo CSPI10 - Chambre Syndicale des Propriétaires Immobiliers de l'Aube" width="60" height="60">
                    </a>
                </div>
                <ul class="nav-links" role="menubar">
                    <li role="none"><a href="<?= url('/') ?>" role="menuitem" <?= ($current_page === 'home') ? 'aria-current="page"' : '' ?>>Accueil</a></li>
                    <li role="none"><a href="<?= url('/actualites') ?>" role="menuitem" <?= ($current_page === 'actualites') ? 'aria-current="page"' : '' ?>>Actualités</a></li>
                    <li role="none"><a href="<?= url('/adhesion') ?>" role="menuitem" <?= ($current_page === 'adhesion') ? 'aria-current="page"' : '' ?>>Adhésion</a></li>
                    <li role="none"><a href="<?= url('/biens') ?>" role="menuitem" <?= ($current_page === 'biens') ? 'aria-current="page"' : '' ?>>Nos biens</a></li>
                    <li role="none"><a href="<?= url('/partenaires') ?>" role="menuitem" <?= ($current_page === 'partenaires') ? 'aria-current="page"' : '' ?>>Partenaires</a></li>
                    <li role="none"><a href="<?= url('/contact') ?>" role="menuitem" <?= ($current_page === 'contact') ? 'aria-current="page"' : '' ?>>Contact</a></li>
                </ul>
                <button class="burger" aria-label="Menu mobile" aria-expanded="false" aria-controls="nav-links">
                    <div class="line1"></div>
                    <div class="line2"></div>
                    <div class="line3"></div>
                </button>
            </div>
        </nav>
    </header>
