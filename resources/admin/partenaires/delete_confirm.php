<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supprimer le partenaire - Administration</title>
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
        .partenaire-info {
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
            <a class="navbar-brand" href="/admin/dashboard">
                <i class="bi bi-shield-lock"></i> Administration
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="/admin/partenaires">
                    <i class="bi bi-arrow-left"></i> Retour à la liste
                </a>
                <a class="nav-link" href="/admin/logout">
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
                <p class="mb-0">Cette action est <strong>irréversible</strong>. Le partenaire et son logo seront définitivement supprimés.</p>
            </div>

            <div class="partenaire-info">
                <h5>Partenaire à supprimer :</h5>
                <p><strong>Nom :</strong> <?php echo htmlspecialchars($partenaire['nom']); ?></p>
                <?php if ($partenaire['site_url']): ?>
                <p><strong>Site web :</strong> <a href="<?php echo htmlspecialchars($partenaire['site_url']); ?>" target="_blank"><?php echo htmlspecialchars($partenaire['site_url']); ?></a></p>
                <?php endif; ?>
                <?php if ($partenaire['logo_url']): ?>
                <p><strong>Logo :</strong> <img src="/<?php echo htmlspecialchars($partenaire['logo_url']); ?>" alt="Logo" style="max-height: 50px;"></p>
                <?php endif; ?>
            </div>

            <form method="POST" class="text-center">
                <div class="mb-3">
                    <p class="fw-bold">Êtes-vous sûr de vouloir supprimer ce partenaire ?</p>
                </div>
                
                <div class="d-flex gap-3 justify-content-center">
                    <button type="submit" name="confirm_delete" value="yes" class="btn btn-danger">
                        <i class="bi bi-trash"></i> Oui, supprimer définitivement
                    </button>
                    <a href="/admin/partenaires" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> Non, annuler
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 