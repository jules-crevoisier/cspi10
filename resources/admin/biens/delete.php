<?php
use App\Controller\AdminController;
use App\Controller\AdminBienController;

$adminController = new AdminController();
$adminController->requireLogin();

// Récupération de l'ID du bien à supprimer
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    $controller = new AdminBienController();
    $controller->delete($id);
} else {
    // Redirection vers la liste des biens si l'ID n'est pas valide
    header('Location: /admin/biens');
    exit;
} 