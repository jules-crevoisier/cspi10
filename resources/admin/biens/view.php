<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails du bien - Administration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .bien-details {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .section {
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #eee;
        }
        .section:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: bold;
            color: #666;
        }
        .info-value {
            color: #333;
        }
        .gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }
        .gallery img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 4px;
        }
        .gallery .primary {
            border: 3px solid #0d6efd;
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
        <div class="bien-details">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Détails du bien</h2>
                <div>
                    <a href="/admin/biens/edit/<?= $bien['id'] ?>" class="btn btn-primary">
                        <i class="bi bi-pencil"></i> Modifier
                    </a>
                </div>
            </div>

            <!-- Informations publiques -->
            <div class="section">
                <h3 class="mb-3">Informations publiques</h3>
                <div class="row">
                    <div class="col-md-6">
                        <p><span class="info-label">Titre :</span> <span class="info-value"><?= isset($bien['titre']) ? htmlspecialchars($bien['titre']) : '-' ?></span></p>
                        <p><span class="info-label">Type :</span> <span class="info-value"><?= isset($bien['type']) ? htmlspecialchars($bien['type']) : '-' ?></span></p>
                        <p><span class="info-label">Prix :</span> <span class="info-value"><?= isset($bien['prix']) ? number_format($bien['prix'], 0, ',', ' ') . ' €' : '-' ?></span></p>
                    </div>
                    <div class="col-md-6">
                        <p><span class="info-label">Surface :</span> <span class="info-value"><?= isset($bien['surface_m2']) ? $bien['surface_m2'] . ' m²' : '-' ?></span></p>
                        <p><span class="info-label">Chambres :</span> <span class="info-value"><?= isset($bien['chambres']) ? $bien['chambres'] : '-' ?></span></p>
                        <p><span class="info-label">Salles d'eau :</span> <span class="info-value"><?= isset($bien['salles_eau']) ? $bien['salles_eau'] : '-' ?></span></p>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <p><span class="info-label">Adresse publique :</span> <span class="info-value"><?= isset($bien['adresse_publique']) ? htmlspecialchars($bien['adresse_publique']) : '-' ?></span></p>
                        <p><span class="info-label">Description :</span></p>
                        <div class="info-value"><?= isset($bien['description']) ? nl2br(htmlspecialchars($bien['description'])) : '-' ?></div>
                    </div>
                </div>
            </div>

            <!-- Informations privées -->
            <div class="section">
                <h3 class="mb-3">Informations privées</h3>
                <div class="row">
                    <div class="col-md-6">
                        <p><span class="info-label">Adresse complète :</span> <span class="info-value"><?= isset($bien['adresse']) ? htmlspecialchars($bien['adresse']) : '-' ?></span></p>
                        <p><span class="info-label">Propriétaire :</span> <span class="info-value"><?= isset($bien['proprietaire_prenom'], $bien['proprietaire_nom']) ? htmlspecialchars($bien['proprietaire_prenom'] . ' ' . $bien['proprietaire_nom']) : '-' ?></span></p>
                    </div>
                    <div class="col-md-6">
                        <p><span class="info-label">Email :</span> <span class="info-value"><?= isset($bien['proprietaire_email']) ? htmlspecialchars($bien['proprietaire_email']) : '-' ?></span></p>
                        <p><span class="info-label">Téléphone :</span> <span class="info-value"><?= isset($bien['proprietaire_telephone']) ? htmlspecialchars($bien['proprietaire_telephone']) : '-' ?></span></p>
                    </div>
                </div>
            </div>

            <!-- Galerie d'images -->
            <?php if (!empty($images)): ?>
            <div class="section">
                <h3 class="mb-3">Galerie d'images</h3>
                <div class="gallery">
                    <?php foreach ($images as $image): ?>
                    <div>
                        <img src="<?= htmlspecialchars($image['url']) ?>" 
                             alt="Image du bien" 
                             class="<?= $image['is_primary'] ? 'primary' : '' ?>"
                             title="<?= $image['is_primary'] ? 'Image principale' : '' ?>">
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Métadonnées -->
            <div class="section">
                <h3 class="mb-3">Métadonnées</h3>
                <div class="row">
                    <div class="col-md-6">
                        <p><span class="info-label">Créé le :</span> <span class="info-value"><?= isset($bien['created_at']) ? date('d/m/Y H:i', strtotime($bien['created_at'])) : '-' ?></span></p>
                    </div>
                    <div class="col-md-6">
                        <p><span class="info-label">Dernière modification :</span> <span class="info-value"><?= isset($bien['updated_at']) ? date('d/m/Y H:i', strtotime($bien['updated_at'])) : 'Jamais' ?></span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 