<?php
declare(strict_types=1);

/**
 * Helpers espace adhérent — utilise la session centralisée.
 */

use App\Core\Security;

function isAdherentLoggedIn(): bool
{
    Security::startSession();

    if (!isset($_SESSION['adherent_logged_in']) || $_SESSION['adherent_logged_in'] !== true) {
        return false;
    }

    $duration = (int) (getenv('ESPACE_ADHERENT_SESSION_DURATION') ?: 86400);
    if (isset($_SESSION['adherent_login_time']) && (time() - (int) $_SESSION['adherent_login_time']) > $duration) {
        unset($_SESSION['adherent_logged_in'], $_SESSION['adherent_login_time']);
        return false;
    }

    return true;
}

function requireAdherentLogin(): void
{
    if (!isAdherentLoggedIn()) {
        header('Location: espace-adherent-login.php');
        exit;
    }
}

function logoutAdherent(): void
{
    Security::startSession();
    unset($_SESSION['adherent_logged_in'], $_SESSION['adherent_login_time']);
}
