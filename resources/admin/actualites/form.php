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
        header('Location: /admin/actualites');
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
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - <?php echo $isEdit ? 'Modifier' : 'Ajouter'; ?> une actualité</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
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
        .form-section {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .form-section h4 {
            color: #343a40;
            margin-bottom: 15px;
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
        
        /* Styles pour le carousel d'administration */
        .admin-image-preview {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            overflow: hidden;
            background: #f8f9fa;
        }
        
        .admin-swiper {
            width: 100%;
            height: 400px;
            position: relative;
        }
        
        .admin-swiper.swiper-document-portrait {
            height: 500px;
            max-width: 450px;
            margin: 0 auto;
        }
        
        .admin-swiper.swiper-document-landscape {
            height: 450px;
            max-width: 700px;
            margin: 0 auto;
        }
        
        .admin-swiper.swiper-photo {
            height: 400px;
        }
        
        .admin-slide-content {
            position: relative;
            width: 100%;
            height: 100%;
        }
        
        .admin-slide-content img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            background: #fff;
        }
        
        .admin-swiper.swiper-photo .admin-slide-content img {
            object-fit: cover;
        }
        
        .primary-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(13, 110, 253, 0.9);
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            backdrop-filter: blur(5px);
        }
        
        .swiper-button-next,
        .swiper-button-prev {
            color: #495057;
            background: rgba(255, 255, 255, 0.9);
            width: 35px;
            height: 35px;
            border-radius: 50%;
            margin-top: -17.5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .swiper-button-next:hover,
        .swiper-button-prev:hover {
            background: rgba(255, 255, 255, 1);
            color: #212529;
        }
        
        .swiper-button-next::after,
        .swiper-button-prev::after {
            font-size: 16px;
            font-weight: bold;
        }
        
        .swiper-pagination-bullet {
            background: rgba(73, 80, 87, 0.5);
        }
        
        .swiper-pagination-bullet-active {
            background: #495057;
        }
        
        .image-controls {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
        }
        
        @media (max-width: 768px) {
            .admin-swiper,
            .admin-swiper.swiper-document-portrait,
            .admin-swiper.swiper-document-landscape,
            .admin-swiper.swiper-photo {
                height: 300px;
                max-width: 100%;
            }
            
            .admin-swiper.swiper-document-portrait {
                height: 350px;
            }
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
                            <a class="nav-link" href="/admin/dashboard">
                                <i class="bi bi-speedometer2"></i> Tableau de bord
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/biens">
                                <i class="bi bi-house"></i> Biens
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="/admin/actualites">
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
                    <h2><?php echo $isEdit ? 'Modifier' : 'Ajouter'; ?> une actualité</h2>
                    <a href="/admin/actualites" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Retour à la liste
                    </a>
                </div>

                <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?php 
                    echo htmlspecialchars($_SESSION['error']);
                    unset($_SESSION['error']);
                    ?>
                </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data" action="">
                            <!-- Champs du formulaire -->
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
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script>
    function deleteImage(actualiteId, imageId) {
        if (confirm('Êtes-vous sûr de vouloir supprimer cette image ?')) {
            fetch(`/admin/actualites/${actualiteId}/image/${imageId}/delete`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.error || 'Une erreur est survenue');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Une erreur est survenue');
            });
        }
    }

    function setPrimaryImage(actualiteId, imageId) {
        fetch(`/admin/actualites/${actualiteId}/image/${imageId}/primary`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                alert(data.error || 'Une erreur est survenue');
                location.reload();
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Une erreur est survenue');
            location.reload();
        });
    }

    // Initialiser le carousel Swiper pour l'administration
    document.addEventListener('DOMContentLoaded', function() {
        const adminSwiper = document.querySelector('.admin-swiper');
        if (adminSwiper) {
            new Swiper('.admin-swiper', {
                loop: true,
                spaceBetween: 10,
                centeredSlides: true,
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true,
                    dynamicBullets: true,
                },
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
                },
                keyboard: {
                    enabled: true,
                },
                mousewheel: {
                    forceToAxis: true,
                },
                breakpoints: {
                    320: {
                        slidesPerView: 1,
                        spaceBetween: 10
                    },
                    768: {
                        slidesPerView: 1,
                        spaceBetween: 15
                    },
                    1024: {
                        slidesPerView: 1,
                        spaceBetween: 20
                    }
                }
            });
        }
    });
    </script>
</body>
</html> 