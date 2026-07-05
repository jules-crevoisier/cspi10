<?php
declare(strict_types=1);

use App\Controller\AdminBienController;
use App\Controller\AdminController;

$adminController = new AdminController();
$adminController->requireLogin();

(new AdminBienController())->index();
$biens = $GLOBALS['biens'] ?? [];

$pageTitle = 'Biens immobiliers';
$activeNav = 'biens';

require __DIR__ . '/../include/layout_start.php';
?>

<div class="admin-toolbar">
    <a href="<?= url('/admin/biens/create') ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Nouveau bien
    </a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Type</th>
                        <th>Surface</th>
                        <th>Prix</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($biens === []): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Aucun bien pour le moment.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($biens as $bien): ?>
                            <tr>
                                <td><?= e($bien['titre']) ?></td>
                                <td><?= ucfirst(e($bien['type'])) ?></td>
                                <td><?= $bien['surface_m2'] ? (int) $bien['surface_m2'] . ' m²' : '—' ?></td>
                                <td><?= $bien['prix'] ? number_format((float) $bien['prix'], 0, ',', ' ') . ' €' : '—' ?></td>
                                <td class="text-end text-nowrap">
                                    <a href="<?= url('/admin/biens/view/' . $bien['id']) ?>" class="btn btn-sm btn-outline-info" title="Voir">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="<?= url('/admin/biens/edit/' . $bien['id']) ?>" class="btn btn-sm btn-outline-primary" title="Modifier">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="<?= url('/admin/biens/delete/' . $bien['id']) ?>" class="btn btn-sm btn-outline-danger" title="Supprimer">
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
