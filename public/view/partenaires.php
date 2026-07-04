<?php
use App\Models\Partenaire;

$partenaires = Partenaire::getAll();
?>
<?php include __DIR__ . '/../include/header.php'; ?>

<main>
    <section class="hero">
        <div class="hero-content">
            <h1>Nos Partenaires</h1>
            <p>Découvrez nos partenaires</p>
        </div>
    </section>

    <section class="partenaires-section">
        <div class="container">
            <div class="partenaires-header">
                <h1>Nos Partenaires</h1>
                <p>Nous travaillons avec des professionnels reconnus pour vous offrir les meilleurs services</p>
            </div>

            <div class="partenaires-grid">
                <?php foreach ($partenaires as $partenaire): ?>
                    <div class="partenaire-card">
                        <?php if (!empty($partenaire['logo_url'])): ?>
                            <div class="partenaire-logo">
                                <img src="/<?php echo htmlspecialchars($partenaire['logo_url']); ?>" 
                                     alt="Logo <?php echo htmlspecialchars($partenaire['nom']); ?>">
                            </div>
                        <?php endif; ?>
                        <div class="partenaire-content">
                            <h3><?php echo htmlspecialchars($partenaire['nom']); ?></h3>
                            <?php if (!empty($partenaire['description'])): ?>
                                <p><?php echo nl2br(htmlspecialchars($partenaire['description'])); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($partenaire['site_url'])): ?>
                                <a href="<?php echo htmlspecialchars($partenaire['site_url']); ?>" 
                                   class="partenaire-website" 
                                   target="_blank" 
                                   rel="noopener noreferrer">
                                    <i class="fas fa-external-link-alt"></i> Visiter le site
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if (empty($partenaires)): ?>
                <div class="text-center">
                    <p>Aucun partenaire n'est actuellement disponible.</p>
                </div>
            <?php endif; ?>

            <div class="partenaire-contact">
                <h2>Vous souhaitez devenir partenaire ?</h2>
                <p>Rejoignez notre réseau de partenaires de confiance et développez votre activité avec nous.</p>
                <a href="/contact" class="contact-btn">
                    <i class="fas fa-handshake"></i> Nous contacter
                </a>
            </div>
        </div>
    </section>
</main>

<?php include __DIR__ . '/../include/footer.php'; ?> 