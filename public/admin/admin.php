<?php
declare(strict_types=1);

use App\Core\Database;

$pageTitle = 'Tableau de bord';
$activeNav = 'dashboard';

try {
    $totalBiens = (int) (Database::queryOne('SELECT COUNT(*) AS c FROM biens', [])['c'] ?? 0);
    $totalActualites = (int) (Database::queryOne('SELECT COUNT(*) AS c FROM actualites', [])['c'] ?? 0);
    $totalPartenaires = (int) (Database::queryOne('SELECT COUNT(*) AS c FROM partenaires', [])['c'] ?? 0);
    $derniersBiens = Database::query('SELECT * FROM biens ORDER BY created_at DESC LIMIT 5');
    $dernieresActualites = Database::query('SELECT * FROM actualites ORDER BY created_at DESC LIMIT 5');
} catch (Throwable) {
    $totalBiens = $totalActualites = $totalPartenaires = 0;
    $derniersBiens = $dernieresActualites = [];
    \App\Core\Flash::error('Impossible de charger les statistiques. Vérifiez la connexion à la base de données.');
}

require __DIR__ . '/include/layout_start.php';
?>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card bg-primary text-white">
            <div class="stat-value"><?= $totalBiens ?></div>
            <div class="stat-label">Biens immobiliers</div>
            <a href="<?= url('/admin/biens/create') ?>" class="stat-link">+ Ajouter un bien</a>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card bg-success text-white">
            <div class="stat-value"><?= $totalActualites ?></div>
            <div class="stat-label">Actualités</div>
            <a href="<?= url('/admin/actualites/create') ?>" class="stat-link">+ Publier une actualité</a>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card bg-info text-white">
            <div class="stat-value"><?= $totalPartenaires ?></div>
            <div class="stat-label">Partenaires</div>
            <a href="<?= url('/admin/partenaires/create') ?>" class="stat-link">+ Ajouter un partenaire</a>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Derniers biens ajoutés</span>
        <a href="<?= url('/admin/biens') ?>" class="btn btn-sm btn-outline-primary">Voir tout</a>
    </div>
    <div class="card-body p-0">
        <?php if ($derniersBiens === []): ?>
            <p class="p-4 text-muted mb-0">Aucun bien pour le moment. <a href="<?= url('/admin/biens/create') ?>">Ajoutez le premier</a>.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Titre</th><th>Type</th><th>Prix</th><th>Date</th></tr></thead>
                    <tbody>
                    <?php foreach ($derniersBiens as $bien): ?>
                        <tr>
                            <td><a href="<?= url('/admin/biens/edit/' . $bien['id']) ?>"><?= htmlspecialchars((string) $bien['titre'], ENT_QUOTES, 'UTF-8') ?></a></td>
                            <td><?= htmlspecialchars((string) $bien['type'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= $bien['prix'] !== null ? number_format((float) $bien['prix'], 0, ',', ' ') . ' €' : '—' ?></td>
                            <td><?= date('d/m/Y', strtotime((string) $bien['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Dernières actualités</span>
        <a href="<?= url('/admin/actualites') ?>" class="btn btn-sm btn-outline-primary">Voir tout</a>
    </div>
    <div class="card-body p-0">
        <?php if ($dernieresActualites === []): ?>
            <p class="p-4 text-muted mb-0">Aucune actualité. <a href="<?= url('/admin/actualites/create') ?>">Publiez la première</a>.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Titre</th><th>Catégorie</th><th>Publication</th></tr></thead>
                    <tbody>
                    <?php foreach ($dernieresActualites as $actu): ?>
                        <tr>
                            <td><a href="<?= url('/admin/actualites/edit/' . $actu['id']) ?>"><?= htmlspecialchars((string) $actu['titre'], ENT_QUOTES, 'UTF-8') ?></a></td>
                            <td><?= htmlspecialchars((string) $actu['categorie'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= $actu['publie_le'] ? date('d/m/Y', strtotime((string) $actu['publie_le'])) : '—' ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/include/layout_end.php'; ?>
