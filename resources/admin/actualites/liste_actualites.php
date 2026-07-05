<?php
declare(strict_types=1);

use App\Controller\AdminActualiteController;
use App\Controller\AdminController;

$adminController = new AdminController();
$adminController->requireLogin();

$actualites = (new AdminActualiteController())->index();

$pageTitle = 'Actualités';
$activeNav = 'actualites';

require __DIR__ . '/../include/layout_start.php';
?>

<div class="admin-toolbar">
    <a href="<?= url('/admin/actualites/create') ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Nouvelle actualité
    </a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Catégorie</th>
                        <th>Publication</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($actualites === []): ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">Aucune actualité pour le moment.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($actualites as $actualite): ?>
                            <tr>
                                <td><?= e($actualite['titre']) ?></td>
                                <td><?= e($actualite['categorie']) ?></td>
                                <td><?= !empty($actualite['publie_le']) ? date('d/m/Y', strtotime((string) $actualite['publie_le'])) : '—' ?></td>
                                <td class="text-end text-nowrap">
                                    <a href="<?= url('/admin/actualites/edit/' . $actualite['id']) ?>" class="btn btn-sm btn-outline-primary" title="Modifier">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="<?= url('/admin/actualites/delete/' . $actualite['id']) ?>" class="btn btn-sm btn-outline-danger" title="Supprimer"
                                       onclick="return confirm('Supprimer cette actualité ?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../include/layout_end.php'; ?>
