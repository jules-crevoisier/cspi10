<?php
require_once __DIR__ . '/../../app/bootstrap.php';
use App\Models\Actualite;
use App\Models\ActualiteImage;

// Récupérer l'ID de l'actualité depuis l'URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Récupérer les détails de l'actualité
$actualite = Actualite::getById($id);

// Rediriger si l'actualité n'existe pas
if (!$actualite) {
    header('Location: /actualites');
    exit;
}

// Récupérer les images de l'actualité
$images = ActualiteImage::listByActualite($id);

// Analyser les images pour adapter le carousel
$carouselConfig = analyzeImagesForCarousel($images);
?>
<?php include __DIR__ . '/../include/header.php'; ?>

    <main>
        <section class="hero">
            <div class="hero-content">
                <h1><?= e($actualite['titre']) ?></h1>
                <div class="news-meta">
                    <span class="news-category"><?= ucfirst(e($actualite['categorie'])) ?></span>
                    <?php if (!empty($actualite['publie_le'])): ?>
                        <span class="news-date white-date"><?= formatDateFrench($actualite['publie_le']) ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section class="news-detail">
            <div class="container">
                <?php if (!empty($images)): ?>
                    <div class="swiper <?= $carouselConfig['class'] ?>" data-type="<?= $carouselConfig['type'] ?>">
                        <div class="swiper-wrapper">
                            <?php foreach ($images as $image): ?>
                                <div class="swiper-slide">
                                    <img src="<?= mediaUrl($image['url']) ?>" alt="<?= e($actualite['titre']) ?>" loading="lazy">
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="swiper-pagination"></div>
                        <div class="swiper-button-next"></div>
                        <div class="swiper-button-prev"></div>
                    </div>
                <?php endif; ?>

                <div class="news-content">
                    <?php if (!empty($actualite['extrait'])): ?>
                        <div class="news-excerpt">
                            <?= e($actualite['extrait']) ?>
                        </div>
                    <?php endif; ?>

                    <div class="news-body">
                        <?= \App\Core\HtmlSanitizer::clean($actualite['contenu'] ?? '') ?>
                    </div>
                </div>

                <div class="news-actions">
                    <a href="/index.php/actualites" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Retour aux actualités
                    </a>
                </div>
            </div>
        </section>
    </main>

<?php include __DIR__ . '/../include/footer.php'; ?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
    const swiper = new Swiper('.swiper', {
        loop: true,
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
    });
</script>

<style>
.news-detail {
    padding: 40px 0;
}

/* Carousel par défaut - Images photos classiques */
.swiper {
    width: 100%;
    height: 500px;
    margin-bottom: 30px;
    border-radius: var(--border-radius);
    overflow: hidden;
    box-shadow: var(--shadow-md);
}

/* Carousel pour images de documents en portrait (PDF A4, etc.) */
.swiper.swiper-document-portrait {
    height: 700px;
    max-width: 600px;
    margin: 0 auto 30px auto;
}

/* Carousel pour images de documents en paysage (PowerPoint, etc.) */
.swiper.swiper-document-landscape {
    height: 600px;
    max-width: 900px;
    margin: 0 auto 30px auto;
}

/* Carousel pour photos classiques */
.swiper.swiper-photo {
    height: 500px;
}

/* Images dans le carousel */
.swiper-slide img {
    width: 100%;
    height: 100%;
    object-fit: contain; /* Changé de 'cover' à 'contain' pour préserver les proportions des documents */
    background: #f8f9fa; /* Fond clair pour les documents */
}

/* Pour les photos, utiliser object-fit: cover */
.swiper.swiper-photo .swiper-slide img {
    object-fit: cover;
    background: transparent;
}

.news-content {
    max-width: 800px;
    margin: 0 auto;
}

.news-excerpt {
    font-size: 1.2em;
    color: #666;
    margin-bottom: 30px;
    font-style: italic;
}

.news-body {
    line-height: 1.8;
}

.news-actions {
    margin-top: 40px;
    text-align: center;
}

.news-meta {
    margin-top: 10px;
}

.news-category {
    background-color: #007bff;
    color: white;
    padding: 5px 10px;
    border-radius: 4px;
    margin-right: 10px;
}

.news-date {
    color: #666;
}

/* Style pour la date en blanc dans le hero */
.hero .news-date.white-date {
    color: white !important;
}

.swiper-button-next,
.swiper-button-prev {
    color: #fff;
    background: rgba(0, 0, 0, 0.5);
    width: 40px;
    height: 40px;
    border-radius: 50%;
    transition: all 0.3s ease;
}

.swiper-button-next:hover,
.swiper-button-prev:hover {
    background: rgba(0, 0, 0, 0.7);
    transform: scale(1.1);
}

.swiper-button-next::after,
.swiper-button-prev::after {
    font-size: 20px;
}

.swiper-pagination-bullet {
    background: rgba(255, 255, 255, 0.7);
    opacity: 1;
    transition: all 0.3s ease;
}

.swiper-pagination-bullet-active {
    background: #007bff;
    transform: scale(1.2);
}

/* Responsive pour les différents types de carousel */
@media (max-width: 992px) {
    .swiper.swiper-document-portrait {
        height: 600px;
        max-width: 500px;
    }
    
    .swiper.swiper-document-landscape {
        height: 500px;
        max-width: 800px;
    }
}

@media (max-width: 768px) {
    .swiper,
    .swiper.swiper-document-portrait,
    .swiper.swiper-document-landscape,
    .swiper.swiper-photo {
        height: 400px;
        max-width: 100%;
    }
    
    .swiper.swiper-document-portrait {
        height: 500px;
    }
}

@media (max-width: 576px) {
    .swiper,
    .swiper.swiper-document-portrait,
    .swiper.swiper-document-landscape,
    .swiper.swiper-photo {
        height: 300px;
    }
    
    .swiper.swiper-document-portrait {
        height: 400px;
    }
    
    .swiper-button-next,
    .swiper-button-prev {
        width: 35px;
        height: 35px;
    }
    
    .swiper-button-next::after,
    .swiper-button-prev::after {
        font-size: 16px;
    }
}
</style> 
</html> 