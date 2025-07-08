<?php
namespace App\Controller;

use App\Models\Actualite;
use App\Models\ActualiteImage;

class ActualiteController
{
    /** Liste des actus */
    public function index(): void
    {
        // Récupération BDD
        $actualites = Actualite::getAll(); // ← méthode correcte
        $categories = Actualite::getCategories(); // Récupération dynamique des catégories

        include_once __DIR__ . '/../../public/view/actualites.php';
    }

    /** Détail d'une actu */
    public function show(int $id): void
    {
        $actualite = Actualite::getById($id);  // ← méthode correcte
        if (!$actualite) {
            header('Location: /index.php/actualites');
            exit;
        }

        $images = ActualiteImage::listByActualite($id);

        include_once __DIR__ . '/../../public/view/actualite-detail.php';
    }
}
