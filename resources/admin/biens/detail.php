<?php
use App\Controller\AdminController;
use App\Models\Bien;

$adminController = new AdminController();
$adminController->requireLogin();

// Récupération du bien
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$bien = Bien::getById($id);

if (!$bien) {
    header('Location: /admin/biens');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails du bien - Administration</title>
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
        .info-section {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .info-section h4 {
            color: #343a40;
            margin-bottom: 15px;
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
                    <a href="/public/" class="btn btn-sm btn-light mb-3">
                        <i class="bi bi-house-door"></i> Retour au site
                    </a>
                    <hr>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/dashboard">
                                <i class="bi bi-speedometer2"></i> Tableau de bord
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="/admin/biens">
                                <i class="bi bi-house"></i> Biens
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/actualites/liste_actualites">
                                <i class="bi bi-newspaper"></i> Actualités
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/partenaires">
                                <i class="bi bi-people"></i> Partenaires
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/logout">
                                <i class="bi bi-box-arrow-right"></i> Déconnexion
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main content -->
            <div class="col-md-9 col-lg-10 main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Détails du bien</h2>
                    <div>
                        <a href="/admin/biens/edit/<?php echo $bien['id']; ?>" class="btn btn-primary">
                            <i class="bi bi-pencil"></i> Modifier
                        </a>
                        <a href="/admin/biens" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Retour à la liste
                        </a>
                    </div>
                </div>

                <!-- Informations publiques -->
                <div class="info-section">
                    <h4>Informations publiques</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Titre :</strong> <?php echo htmlspecialchars($bien['titre']); ?></p>
                            <p><strong>Type :</strong> <?php echo htmlspecialchars($bien['type']); ?></p>
                            <p><strong>Adresse publique :</strong> <?php echo htmlspecialchars($bien['adresse_publique']); ?></p>
                            <p><strong>Surface :</strong> <?php echo $bien['surface_m2']; ?> m²</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Chambres :</strong> <?php echo $bien['chambres']; ?></p>
                            <p><strong>Salles d'eau :</strong> <?php echo $bien['salles_eau']; ?></p>
                            <p><strong>Prix :</strong> <?php echo number_format($bien['prix'], 2, ',', ' '); ?> €</p>
                        </div>
                    </div>
                    <div class="mt-3">
                        <h5>Description</h5>
                        <p><?php echo nl2br(htmlspecialchars($bien['description'])); ?></p>
                    </div>
                </div>

                <!-- Informations privées -->
                <div class="info-section">
                    <h4>Informations privées</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Adresse complète :</strong> <?php echo htmlspecialchars($bien['adresse']); ?></p>
                            <p><strong>Propriétaire :</strong> <?php echo htmlspecialchars($bien['proprietaire_prenom'] . ' ' . $bien['proprietaire_nom']); ?></p>
                            <p><strong>Adresse du propriétaire :</strong> <?php echo htmlspecialchars($bien['proprietaire_adresse']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Email :</strong> <?php echo htmlspecialchars($bien['proprietaire_email']); ?></p>
                            <p><strong>Téléphone :</strong> <?php echo htmlspecialchars($bien['proprietaire_telephone']); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 