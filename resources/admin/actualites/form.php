<?php
use App\Controller\AdminController;
use App\Controller\AdminActualiteController;
use App\Models\Actualite;

$adminController = new AdminController();
$adminController->requireLogin();

$controller = new AdminActualiteController();
$actualite = null;
$isEdit = false;

if (isset($_GET['id'])) {
    $isEdit = true;
    $id = (int)$_GET['id'];
    $actualite = $controller->edit($id);
    
    if (!$actualite) {
        header('Location: ' . url('/admin/actualites'));
        exit;
    }
}

// Form Processing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($isEdit) {
        $controller->edit($id);
    } else {
        $controller->create();
    }
}
?>
<?php
$pageTitle = ($isEdit ? 'Modifier' : 'Ajouter') . ' une actualité';
$activeNav = 'actualites';
$extraHead = '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">';
ob_start();
?>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
function deleteImage(actualiteId, imageId) {
    if (!confirm('Supprimer cette image ?')) return;
    fetch(`/admin/actualites/${actualiteId}/image/${imageId}/delete`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': window.CSPI10.csrfToken }
    })
    .then(r => r.json())
    .then(data => { if (data.success) location.reload(); else alert(data.message || 'Erreur'); })
    .catch(() => alert('Erreur réseau'));
}

function setPrimaryImage(actualiteId, imageId) {
    fetch(`/admin/actualites/${actualiteId}/image/${imageId}/primary`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': window.CSPI10.csrfToken }
    })
    .then(r => r.json())
    .then(data => { if (!data.success) { alert(data.message || 'Erreur'); location.reload(); } })
    .catch(() => alert('Erreur réseau'));
}

document.addEventListener('DOMContentLoaded', function () {
    const adminSwiper = document.querySelector('.admin-swiper');
    if (adminSwiper) {
        new Swiper('.admin-swiper', {
            loop: true,
            spaceBetween: 10,
            centeredSlides: true,
            pagination: { el: '.swiper-pagination', clickable: true, dynamicBullets: true },
            navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
        });
    }
});
</script>
<?php
$extraScripts = ob_get_clean();
require __DIR__ . '/../include/layout_start.php';
?>

<div class="admin-toolbar">
    <a href="<?= url('/admin/actualites') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Retour à la liste
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data">
            <?= \App\Core\Security::csrfField() ?>
                            <div class="form-section">
                                <div class="mb-3">
                                    <label for="titre" class="form-label">Titre *</label>
                                    <input type="text" class="form-control" id="titre" name="titre" required
                                           value="<?php echo htmlspecialchars($actualite['titre'] ?? ''); ?>">
                                </div>

                                <div class="mb-3">
                                    <label for="categorie" class="form-label">Catégorie</label>
                                    <select class="form-select" id="categorie" name="categorie" required>
                                        <option value="">Sélectionnez une catégorie</option>
                                        <option value="juridique" <?php echo (isset($actualite) && $actualite['categorie'] === 'juridique') ? 'selected' : ''; ?>>Juridique</option>
                                        <option value="formation" <?php echo (isset($actualite) && $actualite['categorie'] === 'formation') ? 'selected' : ''; ?>>Formation</option>
                                        <option value="evenement" <?php echo (isset($actualite) && $actualite['categorie'] === 'evenement') ? 'selected' : ''; ?>>Événement</option>
                                        <option value="autre" <?php echo (isset($actualite) && $actualite['categorie'] === 'autre') ? 'selected' : ''; ?>>Autre</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="contenu" class="form-label">Contenu *</label>
                                    <textarea class="form-control" id="contenu" name="contenu" rows="10" required><?php echo htmlspecialchars($actualite['contenu'] ?? ''); ?></textarea>
                                </div>

                                <div class="mb-3">
                                    <label for="publie_le" class="form-label">Date de publication</label>
                                    <input type="date" class="form-control" id="publie_le" name="publie_le"
                                           value="<?php echo htmlspecialchars($actualite['publie_le'] ?? date('Y-m-d')); ?>">
                                </div>

                                <div class="mb-3">
                                    <label for="images" class="form-label">Images</label>
                                    <input type="file" class="form-control" id="images" name="images[]" multiple accept="image/jpeg,image/png,image/gif">
                                    <div class="form-text">Formats acceptés : JPG, PNG, GIF. Taille maximum : 5MB par image.</div>
                                </div>

                                <?php if (isset($actualite) && !empty($actualite['images'])): 
                                    // Analyser les images pour adapter le carousel
                                    $carouselConfig = analyzeImagesForCarousel($actualite['images']);
                                ?>
                                <div class="mb-3">
                                    <label class="form-label">Images actuelles</label>
                                    
                                    <!-- Carousel adaptatif pour l'aperçu -->
                                    <div class="admin-image-preview mb-3">
                                        <div class="swiper admin-swiper <?= $carouselConfig['class'] ?>" data-type="<?= $carouselConfig['type'] ?>">
                                            <div class="swiper-wrapper">
                                                <?php foreach ($actualite['images'] as $image): ?>
                                                <div class="swiper-slide">
                                                    <div class="admin-slide-content">
                                                        <img src="<?= mediaUrl($image['url']) ?>" alt="Image de l'actualité" loading="lazy">
                                                        <?php if ($image['is_primary']): ?>
                                                            <div class="primary-badge">
                                                                <i class="bi bi-star-fill"></i> Image principale
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <?php endforeach; ?>
                                            </div>
                                            <div class="swiper-pagination"></div>
                                            <div class="swiper-button-next"></div>
                                            <div class="swiper-button-prev"></div>
                                        </div>
                                    </div>
                                    
                                    <!-- Contrôles des images en liste -->
                                    <div class="image-controls">
                                        <div class="row">
                                            <?php foreach ($actualite['images'] as $index => $image): ?>
                                            <div class="col-md-6 col-lg-4 mb-3">
                                                <div class="card h-100">
                                                    <div class="card-body d-flex flex-column">
                                                        <div class="d-flex align-items-center mb-2">
                                                            <img src="<?= mediaUrl($image['url']) ?>" class="rounded me-2" style="width: 50px; height: 50px; object-fit: cover;" alt="Miniature">
                                                            <div class="flex-grow-1">
                                                                <small class="text-muted">Image <?= $index + 1 ?></small>
                                                                <?php if ($image['is_primary']): ?>
                                                                    <br><small class="text-primary"><i class="bi bi-star-fill"></i> Principale</small>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                        <div class="mt-auto">
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input" type="radio" name="is_primary" value="<?php echo $image['id']; ?>" 
                                                                    <?php echo $image['is_primary'] ? 'checked' : ''; ?> 
                                                                    onchange="setPrimaryImage(<?php echo $actualite['id']; ?>, <?php echo $image['id']; ?>)">
                                                                <label class="form-check-label">
                                                                    Définir comme principale
                                                                </label>
                                                            </div>
                                                            <button type="button" class="btn btn-danger btn-sm w-100" 
                                                                    onclick="deleteImage(<?php echo $actualite['id']; ?>, <?php echo $image['id']; ?>)">
                                                                <i class="bi bi-trash"></i> Supprimer
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>

                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> <?php echo $isEdit ? 'Mettre à jour' : 'Enregistrer'; ?>
                                </button>
                            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../include/layout_end.php'; ?>