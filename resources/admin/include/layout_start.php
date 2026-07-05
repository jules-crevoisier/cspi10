<?php
declare(strict_types=1);

/**
 * Layout partagé de l'administration — navigation et messages flash.
 *
 * @var string $pageTitle
 * @var string $activeNav  biens|actualites|partenaires|dashboard
 */
$pageTitle = $pageTitle ?? 'Administration';
$activeNav = $activeNav ?? 'dashboard';
$flashMessages = \App\Core\Flash::pull();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?> — CSPI10</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= url('/asset/css/admin.css') ?>" rel="stylesheet">
</head>
<body class="admin-body">
<div class="container-fluid">
    <div class="row">
        <aside class="col-md-3 col-lg-2 px-0 admin-sidebar">
            <div class="p-3">
                <h4 class="mb-1">CSPI10</h4>
                <p class="text-white-50 small mb-3">Espace administration</p>
                <a href="<?= url('/') ?>" class="btn btn-sm btn-outline-light w-100 mb-3">
                    <i class="bi bi-house-door"></i> Voir le site
                </a>
                <nav class="nav flex-column admin-nav">
                    <a class="nav-link<?= $activeNav === 'dashboard' ? ' active' : '' ?>" href="<?= url('/admin/dashboard') ?>">
                        <i class="bi bi-speedometer2"></i> Tableau de bord
                    </a>
                    <a class="nav-link<?= $activeNav === 'biens' ? ' active' : '' ?>" href="<?= url('/admin/biens') ?>">
                        <i class="bi bi-building"></i> Biens immobiliers
                    </a>
                    <a class="nav-link<?= $activeNav === 'actualites' ? ' active' : '' ?>" href="<?= url('/admin/actualites') ?>">
                        <i class="bi bi-newspaper"></i> Actualités
                    </a>
                    <a class="nav-link<?= $activeNav === 'partenaires' ? ' active' : '' ?>" href="<?= url('/admin/partenaires') ?>">
                        <i class="bi bi-people"></i> Partenaires
                    </a>
                    <hr class="border-secondary">
                    <a class="nav-link" href="<?= url('/admin/logout') ?>">
                        <i class="bi bi-box-arrow-right"></i> Déconnexion
                    </a>
                </nav>
            </div>
        </aside>
        <main class="col-md-9 col-lg-10 admin-main">
            <header class="admin-header d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0"><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></h1>
                <?php if (!empty($_SESSION['admin_email'])): ?>
                    <span class="text-muted small">
                        <i class="bi bi-person-circle"></i>
                        <?= htmlspecialchars((string) $_SESSION['admin_email'], ENT_QUOTES, 'UTF-8') ?>
                    </span>
                <?php endif; ?>
            </header>

            <?php foreach ($flashMessages as $flash): ?>
                <div class="alert alert-<?= htmlspecialchars($flash['type'] === 'error' ? 'danger' : $flash['type'], ENT_QUOTES, 'UTF-8') ?> alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                </div>
            <?php endforeach; ?>

            <div class="admin-content">
