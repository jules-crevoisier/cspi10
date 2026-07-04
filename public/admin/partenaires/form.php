<?php
use App\Controller\AdminController;
use App\Controller\AdminPartenaireController;
use App\Models\Partenaire;

$adminController = new AdminController();
$adminController->requireLogin();

$controller = new AdminPartenaireController();
$partenaire = null;
$isEdit = false;
$error = null;

if (isset($_GET['id'])) {
    $isEdit = true;
    $id = (int)$_GET['id'];
    // Just get the partenaire information, don't process form
    $partenaire = Partenaire::getById($id);
    
    if (!$partenaire) {
        header('Location: /index.php/admin/partenaires');
        exit;
    }
}

// Form Processing - Use the controller methods
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if ($isEdit) {
            $controller->edit($id);
        } else {
            $controller->create();
        }
        // Controller will handle redirect
    } catch (Exception $e) {
        $error = "Une erreur est survenue : " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - <?php echo $isEdit ? 'Modifier' : 'Ajouter'; ?> un partenaire</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #343a40;
            color: white;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,.75);
        }
        .sidebar .nav-link:hover {
            color: rgba(255,255,255,1);
        }
        .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255,255,255,.1);
        }
        .main-content {
            padding: 20px;
        }
        .form-section {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .form-section h4 {
            color: #343a40;
            margin-bottom: 15px;
        }
        
        /* Correction pour l'affichage des erreurs - éviter le rouge sur rouge */
        .alert-danger {
            background-color: #f8d7da !important;
            border-color: #f5c2c7 !important;
            color: #721c24 !important;
            border-left: 4px solid #dc3545;
        }
        
        .alert-success {
            background-color: #d1e7dd !important;
            border-color: #badbcc !important;
            color: #0f5132 !important;
            border-left: 4px solid #198754;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0 sidebar">
                <div class="p-3">
                    <h4>Administration</h4>
                    <a href="/index.php" class="btn btn-sm btn-light mb-3">
                        <i class="bi bi-house-door"></i> Retour au site
                    </a>
                    <hr>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="/index.php/admin/dashboard">
                                <i class="bi bi-speedometer2"></i> Tableau de bord
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/index.php/admin/biens">
                                <i class="bi bi-house"></i> Biens
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/index.php/admin/actualites/liste_actualites">
                                <i class="bi bi-newspaper"></i> Actualités
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="/index.php/admin/partenaires">
                                <i class="bi bi-people"></i> Partenaires
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/index.php/admin/logout">
                                <i class="bi bi-box-arrow-right"></i> Déconnexion
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main content -->
            <div class="col-md-9 col-lg-10 main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><?php echo $isEdit ? 'Modifier' : 'Ajouter'; ?> un partenaire</h2>
                    <a href="/index.php/admin/partenaires" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Retour à la liste
                    </a>
                </div>

                <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($error); ?>
                </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data" action="">
                            <!-- Champs du formulaire -->
                            <div class="form-section">
                                <div class="mb-3">
                                    <label for="nom" class="form-label">Nom *</label>
                                    <input type="text" class="form-control" id="nom" name="nom" required
                                           value="<?php echo htmlspecialchars($partenaire['nom'] ?? ''); ?>">
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="4"><?php echo htmlspecialchars($partenaire['description'] ?? ''); ?></textarea>
                                    <div class="form-text">Description du partenaire et de ses services.</div>
                                </div>

                                <div class="mb-3">
                                    <label for="logo" class="form-label">Logo du partenaire</label>
                                    <input type="file" class="form-control" id="logo" name="logo" accept="image/*">
                                    <div class="form-text">Formats acceptés: JPG, PNG, GIF, WEBP. Taille max: 2MB.</div>
                                    <?php if ($isEdit && !empty($partenaire['logo_url'])): ?>
                                        <div class="mt-2">
                                            <p>Logo actuel :</p>
                                            <img src="/<?php echo htmlspecialchars($partenaire['logo_url']); ?>" alt="Logo actuel" style="max-height: 100px;">
                                            <input type="hidden" name="existing_logo" value="<?php echo htmlspecialchars($partenaire['logo_url']); ?>">
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="mb-3">
                                    <label for="site_url" class="form-label">URL du site web</label>
                                    <input type="url" class="form-control" id="site_url" name="site_url"
                                           value="<?php echo htmlspecialchars($partenaire['site_url'] ?? ''); ?>">
                                </div>
                            </div>

                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> <?php echo $isEdit ? 'Mettre à jour' : 'Enregistrer'; ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 