<?php
require_once __DIR__ . '/../../app/bootstrap.php';

use App\Models\Bien;
use App\Models\BienImage;

// Récupérer l'ID du bien depuis l'URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Récupérer les détails du bien
$bien = Bien::getById($id);

// Rediriger si le bien n'existe pas
if (!$bien) {
    header('Location: /biens');
    exit;
}

// Récupérer les images du bien
$images = BienImage::listByBien($id);

// Récupérer l'image principale
$primaryImage = BienImage::getPrimaryImage($id);
?>
<?php include __DIR__ . '/../include/header.php'; ?>

<main>
    <section class="hero">
        <div class="hero-content">
            <h1><?php echo htmlspecialchars($bien['titre']); ?></h1>
            <p>Découvrez les détails de ce bien immobilier</p>
        </div>
    </section>

    <section class="bien-detail">
        <div class="container">
            <!-- Bouton retour -->
            <a href="/biens" class="retour-btn">
                <i class="fas fa-arrow-left"></i> Retour aux biens
            </a>

            <!-- Carrousel d'images -->
            <?php if (!empty($images)): ?>
            <div class="swiper">
                <div class="swiper-wrapper">
                    <?php foreach ($images as $image): ?>
                    <div class="swiper-slide">
                        <img src="<?php echo htmlspecialchars($image['url']); ?>" alt="Image du bien" loading="lazy">
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php if (count($images) > 1): ?>
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
                <div class="swiper-pagination"></div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <div class="bien-content">
                <!-- Prix -->
                <?php if ($bien['prix']): ?>
                <div class="bien-price">
                    <?php echo number_format($bien['prix'], 0, ',', ' '); ?> €
                </div>
                <?php endif; ?>

                <!-- Métadonnées -->
                <div class="bien-meta">
                    <span class="property-type">
                        <?php
                        switch($bien['type']) {
                            case 'vente':
                                echo 'À vendre';
                                break;
                            case 'location':
                                echo 'À louer';
                                break;
                            case 'location_etudiante':
                                echo 'Location étudiante';
                                break;
                            default:
                                echo ucfirst($bien['type']);
                        }
                        ?>
                    </span>
                </div>

                <!-- Caractéristiques -->
                <div class="bien-features">
                    <?php if ($bien['surface_m2']): ?>
                    <div class="feature-item">
                        <i class="fas fa-vector-square"></i>
                        <span><?php echo $bien['surface_m2']; ?> m²</span>
                    </div>
                    <?php endif; ?>
                    <?php if ($bien['chambres']): ?>
                    <div class="feature-item">
                        <i class="fas fa-bed"></i>
                        <span><?php echo $bien['chambres']; ?> chambre(s)</span>
                    </div>
                    <?php endif; ?>
                    <?php if ($bien['salles_eau']): ?>
                    <div class="feature-item">
                        <i class="fas fa-bath"></i>
                        <span><?php echo $bien['salles_eau']; ?> salle(s) d'eau</span>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Description -->
                <?php if ($bien['description']): ?>
                <div class="bien-description">
                    <?php echo nl2br(htmlspecialchars($bien['description'])); ?>
                </div>
                <?php endif; ?>

                <!-- Localisation -->
                <div class="bien-location">
                    <h2>Localisation</h2>
                    <div class="map" id="map"></div>
                </div>

                <!-- Bouton de contact -->
                <div class="text-center">
                    <a href="/contact" class="contact-btn">
                        <i class="fas fa-envelope"></i> Nous contacter pour ce bien
                    </a>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include __DIR__ . '/../include/footer.php'; ?>

<script src="https://unpkg.com/swiper@8/swiper-bundle.min.js"></script>
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
<script>
// Initialisation du Swiper
if (document.querySelector('.swiper')) {
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
        autoplay: {
            delay: 5000,
            disableOnInteraction: false,
        },
    });
}

// Initialisation de la carte
const map = L.map('map').setView([48.2982, 4.0834], 13); // Coordonnées par défaut pour Troyes
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors'
}).addTo(map);

// Fonction pour géocoder l'adresse
async function geocodeAddress(address) {
    try {
        const response = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address + ', Aube, France')}`);
        const data = await response.json();
        
        if (data && data.length > 0) {
            const { lat, lon } = data[0];
            const position = [parseFloat(lat), parseFloat(lon)];
            
            // Centrer la carte sur la position
            map.setView(position, 14);
            
            // Ajouter un cercle pour la zone approximative
            L.circle(position, {
                radius: 500,
                color: '#3498db',
                fillColor: '#3498db',
                fillOpacity: 0.2,
                weight: 2
            }).addTo(map);
        }
    } catch (error) {
        console.error('Erreur lors du géocodage:', error);
        // En cas d'erreur, garder la vue par défaut sur Troyes
    }
}

// Géocoder l'adresse du bien
<?php if (!empty($bien['adresse'])): ?>
geocodeAddress('<?php echo addslashes($bien['adresse']); ?>');
<?php endif; ?>
</script>

<link rel="stylesheet" href="https://unpkg.com/swiper@8/swiper-bundle.min.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css"> 