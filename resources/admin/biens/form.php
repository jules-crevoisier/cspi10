<?php
use App\Controller\AdminController;
use App\Models\Bien;
use App\Models\BienImage;

$adminController = new AdminController();
$adminController->requireLogin();

$bien = null;
$isEdit = false;
$error = null;
$images = [];

// Determine if we're in edit mode and get the bien
if (isset($_GET['id'])) {
    $isEdit = true;
    $id = (int)$_GET['id'];
    $bien = Bien::getById($id);
    
    if (!$bien) {
        header('Location: /admin/biens');
        exit;
    }

    // Récupérer les images du bien
    $images = BienImage::listByBien($id);
}

// Form Processing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'titre' => $_POST['titre'] ?? '',
        'type' => $_POST['type'] ?? '',
        'adresse' => $_POST['adresse'] ?? '',
        'surface_m2' => (int)($_POST['surface_m2'] ?? 0),
        'chambres' => (int)($_POST['chambres'] ?? 0),
        'salles_eau' => (int)($_POST['salles_eau'] ?? 0),
        'prix' => (float)($_POST['prix'] ?? 0),
        'description' => $_POST['description'] ?? '',
        'proprietaire_nom' => $_POST['proprietaire_nom'] ?? '',
        'proprietaire_prenom' => $_POST['proprietaire_prenom'] ?? '',
        'proprietaire_adresse' => $_POST['proprietaire_adresse'] ?? '',
        'proprietaire_email' => $_POST['proprietaire_email'] ?? '',
        'proprietaire_telephone' => $_POST['proprietaire_telephone'] ?? ''
    ];

    try {
        if ($isEdit) {
            Bien::update($id, $data);
            $bienId = $id;
        } else {
            $bienId = Bien::create($data);
        }

        // Gestion des images
        if (isset($_FILES['images'])) {
            $uploadDir = __DIR__ . '/../../../public/uploads/biens/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                    $fileName = uniqid() . '_' . $_FILES['images']['name'][$key];
                    $filePath = $uploadDir . $fileName;
                    
                    if (move_uploaded_file($tmp_name, $filePath)) {
                        $imageUrl = '/uploads/biens/' . $fileName;
                        BienImage::add($bienId, $imageUrl, $key === 0);
                    }
                }
            }
        }

        header('Location: ' . url('/admin/biens/detail/' . $bienId));
        exit;
    } catch (Exception $e) {
        $error = "Une erreur est survenue : " . $e->getMessage();
    }
}
?>
<?php
$pageTitle = ($isEdit ? 'Modifier' : 'Ajouter') . ' un bien';
$activeNav = 'biens';
ob_start();
?>
<script>
(function () {
    'use strict';
    document.querySelectorAll('.needs-validation').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
})();

function deleteImage(bienId, imageId) {
    if (!confirm('Supprimer cette image ?')) return;
    fetch(`/admin/biens/${bienId}/image/${imageId}/delete`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': window.CSPI10.csrfToken }
    })
    .then(r => r.json())
    .then(data => { if (data.success) location.reload(); else alert(data.message || 'Erreur'); })
    .catch(() => alert('Erreur réseau'));
}

function setPrimaryImage(bienId, imageId) {
    fetch(`/admin/biens/${bienId}/image/${imageId}/primary`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': window.CSPI10.csrfToken }
    })
    .then(r => r.json())
    .then(data => { if (data.success) location.reload(); else alert(data.message || 'Erreur'); })
    .catch(() => alert('Erreur réseau'));
}
</script>
<?php
$extraScripts = ob_get_clean();
require __DIR__ . '/../include/layout_start.php';
?>

<div class="admin-toolbar">
    <a href="<?= url('/admin/biens') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Retour à la liste
    </a>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" class="needs-validation card card-body" novalidate>
    <?= \App\Core\Security::csrfField() ?>
                    <!-- Informations publiques -->
                    <div class="form-section">
                        <h4>Informations publiques</h4>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="titre" class="form-label">Titre *</label>
                                <input type="text" class="form-control" id="titre" name="titre" required
                                    value="<?php echo htmlspecialchars($bien['titre'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="type" class="form-label">Type *</label>
                                <select class="form-select" id="type" name="type" required>
                                    <option value="">Sélectionnez un type</option>
                                    <option value="vente" <?php echo ($bien['type'] ?? '') === 'vente' ? 'selected' : ''; ?>>Vente</option>
                                    <option value="location" <?php echo ($bien['type'] ?? '') === 'location' ? 'selected' : ''; ?>>Location</option>
                                    <option value="location_etudiante" <?php echo ($bien['type'] ?? '') === 'location_etudiante' ? 'selected' : ''; ?>>Location étudiante</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="adresse" class="form-label">Adresse complète *</label>
                            <input type="text" class="form-control" id="adresse" name="adresse" required
                                value="<?php echo htmlspecialchars($bien['adresse'] ?? ''); ?>">
                            <div class="form-text">L'adresse exacte sera utilisée pour afficher une zone vague sur la carte dans le frontend.</div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="surface_m2" class="form-label">Surface (m²)</label>
                                <input type="number" class="form-control" id="surface_m2" name="surface_m2"
                                    value="<?php echo $bien['surface_m2'] ?? ''; ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="chambres" class="form-label">Nombre de chambres</label>
                                <input type="number" class="form-control" id="chambres" name="chambres"
                                    value="<?php echo $bien['chambres'] ?? ''; ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="salles_eau" class="form-label">Nombre de salles d'eau</label>
                                <input type="number" class="form-control" id="salles_eau" name="salles_eau"
                                    value="<?php echo $bien['salles_eau'] ?? ''; ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="prix" class="form-label">Prix *</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" class="form-control" id="prix" name="prix" required
                                        value="<?php echo $bien['prix'] ?? ''; ?>">
                                    <span class="input-group-text">€</span>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4"><?php echo htmlspecialchars($bien['description'] ?? ''); ?></textarea>
                        </div>

                        <!-- Gestion des images -->
                        <div class="mb-3">
                            <label class="form-label">Images</label>
                            <?php if ($isEdit && !empty($images)): ?>
                                <div class="mb-3">
                                    <div class="row">
                                        <?php foreach ($images as $image): ?>
                                            <div class="col-md-4 mb-3">
                                                                                        <div class="image-container">
                                            <img src="<?= mediaUrl(ltrim($image['url'], '/')) ?>"
                                                 class="img-thumbnail" 
                                                 alt="Image du bien">
                                            <div class="image-actions">
                                                <button type="button" 
                                                        class="btn btn-sm <?php echo $image['is_primary'] ? 'btn-success' : 'btn-outline-success'; ?>" 
                                                        onclick="setPrimaryImage(<?php echo $bien['id']; ?>, <?php echo $image['id']; ?>)"
                                                        <?php echo $image['is_primary'] ? 'disabled' : ''; ?>>
                                                    <i class="bi bi-star<?php echo $image['is_primary'] ? '-fill' : ''; ?>"></i>
                                                </button>
                                                <button type="button" 
                                                        class="btn btn-sm btn-danger" 
                                                        onclick="deleteImage(<?php echo $bien['id']; ?>, <?php echo $image['id']; ?>)">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <input type="file" class="form-control" name="images[]" multiple accept="image/jpeg,image/png,image/gif,image/webp">
                            <div class="form-text">Formats acceptés : JPG, PNG, GIF, WEBP. Taille maximum : 10MB par image. La première image sera définie comme image principale.</div>
                        </div>
                    </div>

                    <!-- Informations privées -->
                    <div class="form-section">
                        <h4>Informations privées</h4>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="proprietaire_nom" class="form-label">Nom du propriétaire *</label>
                                <input type="text" class="form-control" id="proprietaire_nom" name="proprietaire_nom" required
                                    value="<?php echo htmlspecialchars($bien['proprietaire_nom'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="proprietaire_prenom" class="form-label">Prénom du propriétaire *</label>
                                <input type="text" class="form-control" id="proprietaire_prenom" name="proprietaire_prenom" required
                                    value="<?php echo htmlspecialchars($bien['proprietaire_prenom'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="proprietaire_adresse" class="form-label">Adresse du propriétaire *</label>
                            <textarea class="form-control" id="proprietaire_adresse" name="proprietaire_adresse" required rows="2"><?php echo htmlspecialchars($bien['proprietaire_adresse'] ?? ''); ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="proprietaire_email" class="form-label">Email du propriétaire *</label>
                                <input type="email" class="form-control" id="proprietaire_email" name="proprietaire_email" required
                                    value="<?php echo htmlspecialchars($bien['proprietaire_email'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="proprietaire_telephone" class="form-label">Téléphone du propriétaire *</label>
                                <input type="tel" class="form-control" id="proprietaire_telephone" name="proprietaire_telephone" required
                                    value="<?php echo htmlspecialchars($bien['proprietaire_telephone'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>

    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-3">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-save"></i> <?= $isEdit ? 'Mettre à jour' : 'Ajouter' ?> le bien
        </button>
    </div>
</form>

<?php require __DIR__ . '/../include/layout_end.php'; ?>