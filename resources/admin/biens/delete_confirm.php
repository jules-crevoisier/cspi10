<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supprimer le bien - Administration</title>
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
        .bien-info {
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
                <a class="nav-link" href="/admin/biens">
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
                <p class="mb-0">Cette action est <strong>irréversible</strong>. Le bien et toutes ses images seront définitivement supprimés.</p>
            </div>

            <div class="bien-info">
                <h5>Bien à supprimer :</h5>
                <p><strong>Titre :</strong> <?php echo htmlspecialchars($bien['titre']); ?></p>
                <p><strong>Type :</strong> <?php echo htmlspecialchars($bien['type']); ?></p>
                <?php if ($bien['prix']): ?>
                <p><strong>Prix :</strong> <?php echo number_format($bien['prix'], 0, ',', ' '); ?> €</p>
                <?php endif; ?>
                <p><strong>Adresse :</strong> <?php echo htmlspecialchars($bien['adresse']); ?></p>
            </div>

            <form method="POST" class="text-center">
                <div class="mb-3">
                    <p class="fw-bold">Êtes-vous sûr de vouloir supprimer ce bien ?</p>
                </div>
                
                <div class="d-flex gap-3 justify-content-center">
                    <button type="submit" name="confirm_delete" value="yes" class="btn btn-danger">
                        <i class="bi bi-trash"></i> Oui, supprimer définitivement
                    </button>
                    <a href="/admin/biens" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> Non, annuler
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 