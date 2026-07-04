<?php
use App\Controller\AdminController;
use App\Controller\AdminPartenaireController;
use App\Models\Partenaire;

$adminController = new AdminController();
$adminController->requireLogin();

$controller = new AdminPartenaireController();

$partenaires = $controller->index();
error_log("Partenaires récupérés");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - Partenaires</title>
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
                    <h2>Partenaires</h2>
                    <a href="/index.php/admin/partenaires/create" class="btn btn-primary">
                        <i class="bi bi-plus"></i> Nouveau partenaire
                    </a>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Nom</th>
                                        <th>Logo</th>
                                        <th>Site web</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($partenaires)): ?>
                                        <?php foreach ($partenaires as $partenaire): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($partenaire['nom']); ?></td>
                                            <td>
                                                <?php if (!empty($partenaire['logo_url'])): ?>
                                                    <img src="/<?php echo htmlspecialchars($partenaire['logo_url']); ?>" alt="Logo" style="max-height: 50px;">
                                                <?php else: ?>
                                                    <span class="text-muted">Aucun logo</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($partenaire['site_url'])): ?>
                                                    <a href="<?php echo htmlspecialchars($partenaire['site_url']); ?>" target="_blank">
                                                        <?php echo htmlspecialchars($partenaire['site_url']); ?>
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="/index.php/admin/partenaires/edit/<?php echo $partenaire['id']; ?>" class="btn btn-sm btn-primary">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="/index.php/admin/partenaires/delete/<?php echo $partenaire['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce partenaire ?')">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center">Aucun partenaire trouvé</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 