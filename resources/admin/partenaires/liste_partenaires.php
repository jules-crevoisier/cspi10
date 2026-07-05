<?php
declare(strict_types=1);

use App\Controller\AdminController;
use App\Controller\AdminPartenaireController;

$adminController = new AdminController();
$adminController->requireLogin();

$partenaires = (new AdminPartenaireController())->index();

$pageTitle = 'Partenaires';
$activeNav = 'partenaires';

require __DIR__ . '/../include/layout_start.php';
?>

<div class="admin-toolbar">
    <a href="<?= url('/admin/partenaires/create') ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Nouveau partenaire
    </a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Logo</th>
                        <th>Site web</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($partenaires === []): ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">Aucun partenaire pour le moment.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($partenaires as $partenaire): ?>
                            <tr>
                                <td><?= e($partenaire['nom']) ?></td>
                                <td>
                                    <?php if (!empty($partenaire['logo_url'])): ?>
                                        <img src="<?= mediaUrl($partenaire['logo_url']) ?>" alt="Logo" style="max-height: 50px;">
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($partenaire['site_url'])): ?>
                                        <a href="<?= e($partenaire['site_url']) ?>" target="_blank" rel="noopener noreferrer">
                                            <?= e($partenaire['site_url']) ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end text-nowrap">
                                    <a href="<?= url('/admin/partenaires/edit/' . $partenaire['id']) ?>" class="btn btn-sm btn-outline-primary" title="Modifier">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="<?= url('/admin/partenaires/delete/' . $partenaire['id']) ?>" class="btn btn-sm btn-outline-danger" title="Supprimer"
                                       onclick="return confirm('Supprimer ce partenaire ?')">
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
