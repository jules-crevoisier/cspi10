<?php
declare(strict_types=1);

use App\Controller\AdminController;
use App\Models\Bien;

$adminController = new AdminController();
$adminController->requireLogin();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$bien = Bien::getById($id);

if (!$bien) {
    header('Location: ' . url('/admin/biens'));
    exit;
}

$pageTitle = 'Détails du bien';
$activeNav = 'biens';

require __DIR__ . '/../include/layout_start.php';
?>

<div class="admin-toolbar">
    <div class="d-flex gap-2">
        <a href="<?= url('/admin/biens/edit/' . $bien['id']) ?>" class="btn btn-primary">
            <i class="bi bi-pencil"></i> Modifier
        </a>
        <a href="<?= url('/admin/biens') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Retour à la liste
        </a>
    </div>
</div>

<div class="info-section">
    <h4>Informations publiques</h4>
    <div class="row">
        <div class="col-md-6">
            <p><strong>Titre :</strong> <?= e($bien['titre']) ?></p>
            <p><strong>Type :</strong> <?= e($bien['type']) ?></p>
            <p><strong>Adresse publique :</strong> <?= e($bien['adresse_publique'] ?? '') ?></p>
            <p><strong>Surface :</strong> <?= $bien['surface_m2'] ? (int) $bien['surface_m2'] . ' m²' : '—' ?></p>
        </div>
        <div class="col-md-6">
            <p><strong>Chambres :</strong> <?= (int) ($bien['chambres'] ?? 0) ?></p>
            <p><strong>Salles d'eau :</strong> <?= (int) ($bien['salles_eau'] ?? 0) ?></p>
            <p><strong>Prix :</strong> <?= $bien['prix'] ? number_format((float) $bien['prix'], 2, ',', ' ') . ' €' : '—' ?></p>
        </div>
    </div>
    <?php if (!empty($bien['description'])): ?>
        <div class="mt-3">
            <h5>Description</h5>
            <p><?= nl2br(e($bien['description'])) ?></p>
        </div>
    <?php endif; ?>
</div>

<div class="info-section">
    <h4>Informations privées</h4>
    <div class="row">
        <div class="col-md-6">
            <p><strong>Adresse complète :</strong> <?= e($bien['adresse'] ?? '') ?></p>
            <p><strong>Propriétaire :</strong> <?= e(trim(($bien['proprietaire_prenom'] ?? '') . ' ' . ($bien['proprietaire_nom'] ?? ''))) ?></p>
            <p><strong>Adresse du propriétaire :</strong> <?= e($bien['proprietaire_adresse'] ?? '') ?></p>
        </div>
        <div class="col-md-6">
            <p><strong>Email :</strong> <?= e($bien['proprietaire_email'] ?? '') ?></p>
            <p><strong>Téléphone :</strong> <?= e($bien['proprietaire_telephone'] ?? '') ?></p>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../include/layout_end.php'; ?>
