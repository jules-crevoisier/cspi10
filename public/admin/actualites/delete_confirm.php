<?php
use App\Controller\AdminController;
use App\Controller\AdminActualiteController;

$adminController = new AdminController();
$adminController->requireLogin();

// Récupération de l'ID de l'actualité
if (!isset($_GET['id'])) {
    $_SESSION['error'] = 'ID de l\'actualité non spécifié.';
    header('Location: /index.php/admin/actualites');
    exit;
}

$id = (int)$_GET['id'];
$controller = new AdminActualiteController();

// Récupération de l'actualité pour affichage
$actualite = $controller->getActualite($id);

if (!$actualite) {
    $_SESSION['error'] = 'Actualité non trouvée.';
    header('Location: /index.php/admin/actualites');
    exit;
}

// Traitement de la suppression si confirmée
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete']) && $_POST['confirm_delete'] === 'yes') {
    $controller->delete($id);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supprimer l'actualité - Administration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .delete-confirmation {
            max-width: 600px;
            margin: 2rem auto;
            padding: 2rem;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .alert-danger {
            border-left: 4px solid #dc3545;
        }
        .actualite-info {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 4px;
            margin: 1rem 0;
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="/index.php/admin/dashboard">
                <i class="bi bi-shield-lock"></i> Administration
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="/index.php/admin/actualites">
                    <i class="bi bi-arrow-left"></i> Retour à la liste
                </a>
                <a class="nav-link" href="/index.php/admin/logout">
                    <i class="bi bi-box-arrow-right"></i> Déconnexion
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="delete-confirmation">
            <div class="text-center mb-4">
                <i class="bi bi-exclamation-triangle text-danger" style="font-size: 3rem;"></i>
                <h2 class="text-danger mt-2">Confirmer la suppression</h2>
            </div>

            <div class="alert alert-danger">
                <h5><i class="bi bi-warning"></i> Attention !</h5>
                <p class="mb-0">Cette action est <strong>irréversible</strong>. L'actualité et toutes ses images seront définitivement supprimées.</p>
            </div>

            <div class="actualite-info">
                <h5>Actualité à supprimer :</h5>
                <p><strong>Titre :</strong> <?php echo htmlspecialchars($actualite['titre']); ?></p>
                <p><strong>Catégorie :</strong> <?php echo htmlspecialchars($actualite['categorie']); ?></p>
                <?php if ($actualite['publie_le']): ?>
                <p><strong>Date de publication :</strong> <?php echo date('d/m/Y', strtotime($actualite['publie_le'])); ?></p>
                <?php endif; ?>
                <?php if (isset($actualite['extrait']) && $actualite['extrait']): ?>
                <p><strong>Extrait :</strong> <?php echo htmlspecialchars($actualite['extrait']); ?></p>
                <?php endif; ?>
            </div>

            <form method="POST" action="/index.php/admin/actualites/delete/<?php echo $id; ?>" class="text-center">
                <div class="mb-3">
                    <p class="fw-bold">Êtes-vous sûr de vouloir supprimer cette actualité ?</p>
                </div>
                
                <div class="d-flex gap-3 justify-content-center">
                    <button type="submit" name="confirm_delete" value="yes" class="btn btn-danger">
                        <i class="bi bi-trash"></i> Oui, supprimer définitivement
                    </button>
                    <a href="/index.php/admin/actualites" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> Non, annuler
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 