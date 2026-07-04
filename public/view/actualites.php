<?php
use App\Models\ActualiteImage;
include __DIR__ . '/../include/header.php';

/* --------------------------------------------------------------------------
   1.  Pagination côté PHP
-------------------------------------------------------------------------- */
$actualites_par_page = 6;
$total_actualites    = count($actualites);
$total_pages         = ceil($total_actualites / $actualites_par_page);

$page_courante = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page_courante = max(1, min($page_courante, $total_pages));

$debut           = ($page_courante - 1) * $actualites_par_page;
$actualites_page = array_slice($actualites, $debut, $actualites_par_page);

$slug = fn(string $s) => strtolower(trim($s));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Actualités – FDPCI</title>
</head>
<body>
<main>
    <section class="hero">
        <div class="hero-content">
            <h1>Actualités</h1>
            <p>Restez informé des dernières nouvelles et événements de la FDPCI</p>
        </div>
    </section>

    <section class="properties-section">
        <div class="container">

            <!-- Filtres (style identique à la page Bien) -->
            <div class="filters">
                <button class="filter-btn active" data-cat="all">Toutes les actualités</button>
                <?php foreach ($categories as $cat): ?>
                    <button class="filter-btn" data-cat="<?= $slug($cat) ?>">
                        <?= ucfirst(htmlspecialchars($cat)) ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <div class="news-grid">
                <?php foreach ($actualites_page as $actu): ?>
                    <?php $catSlug = $slug($actu['categorie']); ?>
                    <article class="news-card" data-cat="<?= $catSlug ?>">
                        <div class="news-image">
                            <?php
                            $img = ActualiteImage::getPrimaryImage($actu['id']);
                            $url = $img ? mediaUrl($img['url']) : 'https://picsum.photos/800/600?random='.$actu['id'];
                            ?>
                            <img src="<?= $url ?>" alt="<?= htmlspecialchars($actu['titre']) ?>" loading="lazy">
                        </div>
                        <div class="news-content">
                            <div class="news-meta">
                                <span class="news-category"><?= ucfirst(htmlspecialchars($actu['categorie'])) ?></span>
                                <span class="news-date"><?= formatDateFrench($actu['publie_le']) ?></span>
                            </div>
                            <h3 class="news-title"><?= htmlspecialchars($actu['titre']) ?></h3>
                            <p class="news-excerpt"><?= htmlspecialchars($actu['extrait']) ?></p>
                            <a href="/index.php/actualite/<?= $actu['id'] ?>" class="read-more">
                                Lire la suite <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <div id="no-results" class="no-results" style="display:none;">
                <p>Aucune actualité ne correspond à votre filtre.</p>
            </div>

            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page_courante > 1): ?>
                        <a href="?page=<?= $page_courante - 1 ?>" class="page-btn"><i class="fas fa-chevron-left"></i> Précédent</a>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?= $i ?>" class="page-btn <?= $i === $page_courante ? 'active' : '' ?>"><?= $i ?></a>
                    <?php endfor; ?>
                    <?php if ($page_courante < $total_pages): ?>
                        <a href="?page=<?= $page_courante + 1 ?>" class="page-btn">Suivant <i class="fas fa-chevron-right"></i></a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php include __DIR__ . '/../include/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const filterBtns  = document.querySelectorAll('.filter-btn');
    const newsCards   = document.querySelectorAll('.news-card');
    const noResults   = document.getElementById('no-results');
    const pagination  = document.querySelector('.pagination');

    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            filterBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            const cat = btn.dataset.cat;
            let shown = 0;

            newsCards.forEach(card => {
                if (cat === 'all' || card.dataset.cat === cat) {
                    card.style.display = 'block';
                    setTimeout(() => {
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                    }, 50);
                    shown++;
                } else {
                    card.style.opacity = '0';
                    card.style.transform = 'translateY(20px)';
                    setTimeout(() => card.style.display = 'none', 300);
                }
            });

            if (shown === 0) {
                noResults.style.display = 'block';
                if (pagination) pagination.style.display = 'none';
            } else {
                noResults.style.display = 'none';
                if (pagination) pagination.style.display = 'flex';
            }
        });
    });
});
</script>

<style>
/* ----------- Grille et cartes ----------- */
.news-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(300px,1fr));
    gap:20px;
}
.news-card{transition:opacity .3s ease,transform .3s ease;}
.no-results{
    text-align:center;
    padding:2rem;
    background:var(--light-gray);
    border-radius:var(--border-radius);
    margin:2rem 0;
}

/* ----------- Pagination (identique bien.php) ----------- */
.pagination{display:flex;justify-content:center;gap:10px;margin-top:2rem;}
.pagination .page-btn{
    padding:8px 16px;border:1px solid #ddd;border-radius:4px;text-decoration:none;transition:.3s;
}
.pagination .page-btn:hover{background:#f5f5f5;}
.pagination .page-btn.active{background:#007cba;color:#fff;border-color:#007cba;}

</style>
</body>
</html>
