<?php
use App\Controller\AdminController;
use App\Controller\AdminPartenaireController;

$adminController = new AdminController();
$adminController->requireLogin();

// Récupération de l'ID du partenaire à supprimer
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    $controller = new AdminPartenaireController();
    $controller->delete($id);
} else {
    // Redirection vers la liste des partenaires si l'ID n'est pas valide
    header('Location: /index.php/admin/partenaires');
    exit;
} 