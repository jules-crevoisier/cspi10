<?php
declare(strict_types=1);

/**
 * Initialisation commune des pages admin (après bootstrap).
 */
use App\Controller\AdminController;
use App\Core\Security;

Security::startSession();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Security::verifyCsrfFromRequest();
}
