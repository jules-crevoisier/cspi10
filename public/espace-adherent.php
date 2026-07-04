<?php
require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Security;

Security::startSession();

if (!isset($_SESSION['adherent_logged_in']) || $_SESSION['adherent_logged_in'] !== true) {
    header('Location: espace-adherent-login.php');
    exit;
}

if (isset($_SESSION['adherent_login_time']) && (time() - $_SESSION['adherent_login_time']) > 86400) {
    session_destroy();
    header('Location: espace-adherent-login.php');
    exit;
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: espace-adherent-login.php');
    exit;
}

$tools = [
    [
        'icon' => 'fa-thermometer-half',
        'color' => '#27ae60',
        'title' => 'Diagnostic DPE',
        'subtitle' => 'Information officielle',
        'desc' => 'Tout savoir sur le diagnostic de performance énergétique',
        'url' => 'https://www.ecologie.gouv.fr/diagnostic-performance-energetique-dpe',
        'label' => 'Informations DPE',
    ],
    [
        'icon' => 'fa-euro-sign',
        'color' => '#f39c12',
        'title' => 'Fiscalité Immobilière',
        'subtitle' => 'Simulateurs officiels',
        'desc' => 'Calculez vos impôts fonciers',
        'url' => 'https://www.impots.gouv.fr/portail/simulateurs',
        'label' => 'Simuler',
    ],
    [
        'icon' => 'fa-balance-scale',
        'color' => '#8e44ad',
        'title' => 'Chambre des Notaires',
        'subtitle' => 'Conseils & barèmes',
        'desc' => 'Barèmes et conseils des notaires',
        'url' => 'https://www.notaires.fr',
        'label' => 'Notaires.fr',
    ],
    [
        'icon' => 'fa-university',
        'color' => '#2c3e50',
        'title' => 'Banque de France',
        'subtitle' => 'Taux & usure',
        'desc' => 'Taux d\'usure et statistiques officielles',
        'url' => 'https://webstat.banque-france.fr/fr/themes/taux-et-cours/taux-de-usure',
        'label' => 'Consulter les taux',
    ],
    [
        'icon' => 'fa-gavel',
        'color' => '#34495e',
        'title' => 'Aide Juridique',
        'subtitle' => 'Service-public.fr',
        'desc' => 'Questions juridiques immobilières',
        'url' => 'https://www.service-public.fr/particuliers/vosdroits/N19808',
        'label' => 'Poser une question',
    ],
    [
        'icon' => 'fa-home',
        'color' => '#16a085',
        'title' => 'ADIL',
        'subtitle' => 'Agence Départementale',
        'desc' => 'Conseils gratuits en logement',
        'url' => 'https://www.anil.org/lanil-et-les-adil/votre-adil/',
        'label' => 'Trouver mon ADIL',
    ],
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Adhérent - FDPCI</title>
    <link rel="stylesheet" href="asset/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Open+Sans:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="hero adherent-hero">
        <div class="container">
            <div class="hero-content">
                <h1><i class="fas fa-users"></i> Espace Adhérent FDPCI</h1>
                <p>Ressources exclusives pour les propriétaires français</p>
                <div class="adherent-hero-actions">
                    <a href="?logout=1" class="btn btn-secondary btn-logout">
                        <i class="fas fa-sign-out-alt"></i> Se déconnecter
                    </a>
                    <a href="/" class="btn btn-primary">
                        <i class="fas fa-home"></i> Retour au site
                    </a>
                </div>
            </div>
        </div>
    </div>

    <section class="section">
        <div class="container">
            <div class="card adherent-panel">
                <div class="section-title">
                    <h2><i class="fas fa-tools"></i> Outils Pratiques</h2>
                </div>

                <div class="tools-grid">
                    <?php foreach ($tools as $tool): ?>
                        <article class="tool-card">
                            <header class="tool-card-header">
                                <i class="fas <?= htmlspecialchars($tool['icon']) ?> tool-card-icon" style="color: <?= htmlspecialchars($tool['color']) ?>"></i>
                                <div>
                                    <h3 class="tool-card-title"><?= htmlspecialchars($tool['title']) ?></h3>
                                    <small class="tool-card-subtitle"><?= htmlspecialchars($tool['subtitle']) ?></small>
                                </div>
                            </header>
                            <p class="tool-card-desc"><?= htmlspecialchars($tool['desc']) ?></p>
                            <a href="<?= htmlspecialchars($tool['url']) ?>" class="tool-card-link" target="_blank" rel="noopener noreferrer">
                                <i class="fas fa-external-link-alt"></i>
                                <?= htmlspecialchars($tool['label']) ?>
                            </a>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>
</body>
</html>
