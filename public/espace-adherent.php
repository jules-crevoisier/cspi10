<?php
require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Security;

Security::startSession();

// Vérification de la connexion
if (!isset($_SESSION['adherent_logged_in']) || $_SESSION['adherent_logged_in'] !== true) {
    header('Location: espace-adherent-login.php');
    exit;
}

// Vérification de l'expiration (24h)
if (isset($_SESSION['adherent_login_time']) && (time() - $_SESSION['adherent_login_time']) > 86400) {
    session_destroy();
    header('Location: espace-adherent-login.php');
    exit;
}

// Gestion de la déconnexion
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: espace-adherent-login.php');
    exit;
}
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
    <!-- En-tête avec déconnexion -->
    <div class="hero" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%); margin-top: 0px;">
        <div class="container">
            <div class="hero-content">
                <h1><i class="fas fa-users"></i> Espace Adhérent FDPCI</h1>
                <p>Ressources exclusives pour les propriétaires français</p>
                <div style="margin-top: 1rem;">
                    <a href="?logout=1" class="btn btn-secondary" style="background: #dc3545; margin-right: 1rem;">
                        <i class="fas fa-sign-out-alt"></i> Se déconnecter
                    </a>
                    <a href="index.php" class="btn btn-primary">
                        <i class="fas fa-home"></i> Retour au site
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenu principal -->
    <div class="section">
        <div class="container">
            <!-- Outils pratiques -->
            <div class="card" style="margin-bottom: 2rem;">
                <h2 style="color: var(--primary-color); border-bottom: 3px solid var(--secondary-color); text-align: center; margin-bottom: 2rem;">
                    <i class="fas fa-tools"></i> Outils Pratiques
                </h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;">

                    <!-- Diagnostic DPE -->
                    <div style="padding: 1.5rem; border: 2px solid #e9ecef; border-radius: 12px;">
                        <div style="display: flex; align-items: center; margin-bottom: 1rem;">
                            <i class="fas fa-thermometer-half" style="color: #27ae60; font-size: 2rem; margin-right: 1rem;"></i>
                            <div><h4 style="margin: 0; color: var(--primary-color);">Diagnostic DPE</h4><small>Information officielle</small></div>
                        </div>
                        <p>Tout savoir sur le diagnostic de performance énergétique</p>
                        <a href="https://www.ecologie.gouv.fr/diagnostic-performance-energetique-dpe" target="_blank">
                            <i class="fas fa-external-link-alt"></i> Informations DPE
                        </a>
                    </div>

                    <!-- Fiscalité Immobilière -->
                    <div style="padding: 1.5rem; border: 2px solid #e9ecef; border-radius: 12px;">
                        <div style="display: flex; align-items: center; margin-bottom: 1rem;">
                            <i class="fas fa-euro-sign" style="color: #f39c12; font-size: 2rem; margin-right: 1rem;"></i>
                            <div><h4 style="margin: 0; color: var(--primary-color);">Fiscalité Immobilière</h4><small>Simulateurs officiels</small></div>
                        </div>
                        <p>Calculez vos impôts fonciers</p>
                        <a href="https://www.impots.gouv.fr/portail/simulateurs" target="_blank">
                            <i class="fas fa-calculator"></i> Simuler
                        </a>
                    </div>

                    <!-- Chambre des Notaires -->
                    <div style="padding: 1.5rem; border: 2px solid #e9ecef; border-radius: 12px;">
                        <div style="display: flex; align-items: center; margin-bottom: 1rem;">
                            <i class="fas fa-balance-scale" style="color: #8e44ad; font-size: 2rem; margin-right: 1rem;"></i>
                            <div><h4 style="margin: 0; color: var(--primary-color);">Chambre des Notaires</h4><small>Conseils & barèmes</small></div>
                        </div>
                        <p>Barèmes et conseils des notaires</p>
                        <a href="https://www.notaires.fr" target="_blank">
                            <i class="fas fa-external-link-alt"></i> Notaires.fr
                        </a>
                    </div>

                    <!-- Banque de France -->
                    <div style="padding: 1.5rem; border: 2px solid #e9ecef; border-radius: 12px;">
                        <div style="display: flex; align-items: center; margin-bottom: 1rem;">
                            <i class="fas fa-university" style="color: #2c3e50; font-size: 2rem; margin-right: 1rem;"></i>
                            <div><h4 style="margin: 0; color: var(--primary-color);">Banque de France</h4><small>Taux & usure</small></div>
                        </div>
                        <p>Taux d'usure et statistiques officielles</p>
                        <a href="https://webstat.banque-france.fr/fr/themes/taux-et-cours/taux-de-usure" target="_blank">
                            <i class="fas fa-external-link-alt"></i> Consulter les taux
                        </a>
                    </div>

                    <!-- Aide Juridique -->
                    <div style="padding: 1.5rem; border: 2px solid #e9ecef; border-radius: 12px;">
                        <div style="display: flex; align-items: center; margin-bottom: 1rem;">
                            <i class="fas fa-gavel" style="color: #34495e; font-size: 2rem; margin-right: 1rem;"></i>
                            <div><h4 style="margin: 0; color: var(--primary-color);">Aide Juridique</h4><small>Service-public.fr</small></div>
                        </div>
                        <p>Questions juridiques immobilières</p>
                        <a href="https://www.service-public.fr/particuliers/vosdroits/N19808" target="_blank">
                            <i class="fas fa-question-circle"></i> Poser une question
                        </a>
                    </div>

                    <!-- ADIL -->
                    <div style="padding: 1.5rem; border: 2px solid #e9ecef; border-radius: 12px;">
                        <div style="display: flex; align-items: center; margin-bottom: 1rem;">
                            <i class="fas fa-home" style="color: #16a085; font-size: 2rem; margin-right: 1rem;"></i>
                            <div><h4 style="margin: 0; color: var(--primary-color);">ADIL</h4><small>Agence Départementale</small></div>
                        </div>
                        <p>Conseils gratuits en logement</p>
                        <a href="https://www.anil.org/lanil-et-les-adil/votre-adil/" target="_blank">
                            <i class="fas fa-map-marker-alt"></i> Trouver mon ADIL
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</body>
</html>
