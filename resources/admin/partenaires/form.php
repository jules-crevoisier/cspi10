<?php
declare(strict_types=1);

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
    $id = (int) $_GET['id'];
    $partenaire = Partenaire::getById($id);

    if (!$partenaire) {
        header('Location: ' . url('/admin/partenaires'));
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if ($isEdit) {
            $controller->edit($id);
        } else {
            $controller->create();
        }
    } catch (Exception $e) {
        $error = 'Une erreur est survenue : ' . $e->getMessage();
    }
}

$pageTitle = ($isEdit ? 'Modifier' : 'Ajouter') . ' un partenaire';
$activeNav = 'partenaires';

require __DIR__ . '/../include/layout_start.php';
?>

<div class="admin-toolbar">
    <a href="<?= url('/admin/partenaires') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Retour à la liste
    </a>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data">
            <?= \App\Core\Security::csrfField() ?>
            <div class="form-section">
                <div class="mb-3">
                    <label for="nom" class="form-label required-label">Nom</label>
                    <input type="text" class="form-control" id="nom" name="nom" required
                           value="<?= e($partenaire['nom'] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="4"><?= e($partenaire['description'] ?? '') ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="logo" class="form-label">Logo</label>
                    <input type="file" class="form-control" id="logo" name="logo" accept="image/*">
                    <?php if ($isEdit && !empty($partenaire['logo_url'])): ?>
                        <div class="mt-2">
                            <p class="small text-muted mb-1">Logo actuel</p>
                            <img src="<?= mediaUrl($partenaire['logo_url']) ?>" alt="Logo actuel" style="max-height: 100px;">
                            <input type="hidden" name="existing_logo" value="<?= e($partenaire['logo_url']) ?>">
                        </div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="site_url" class="form-label">Site web</label>
                    <input type="url" class="form-control" id="site_url" name="site_url"
                           value="<?= e($partenaire['site_url'] ?? '') ?>">
                </div>
            </div>

            <div class="text-end">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> <?= $isEdit ? 'Mettre à jour' : 'Enregistrer' ?>
                </button>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../include/layout_end.php'; ?>
