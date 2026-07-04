<?php include __DIR__ . '/../include/header.php'; ?>

<main class="container" style="padding: 4rem 1rem; text-align: center;">
    <h1>Page introuvable</h1>
    <p>Désolé, la page que vous recherchez n'existe pas ou a été déplacée.</p>
    <div style="margin-top: 2rem; display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
        <a href="<?= url('/') ?>" class="btn btn-primary">Retour à l'accueil</a>
        <a href="<?= url('/contact') ?>" class="btn btn-secondary">Nous contacter</a>
    </div>
</main>

<?php include __DIR__ . '/../include/footer.php'; ?>
