<?php
declare(strict_types=1);

use App\Controller\AdminController;

$adminController = new AdminController();
if ($adminController->isLoggedIn()) {
    header('Location: /admin/dashboard');
    exit;
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $adminController->login();
    if ($result !== null && isset($result['error'])) {
        $error = $result['error'];
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — Administration CSPI10</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= url('/asset/css/admin.css') ?>" rel="stylesheet">
</head>
<body class="admin-login-body">
    <div class="container">
        <div class="login-container">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <h1 class="h4">Administration CSPI10</h1>
                        <p class="text-muted small mb-0">Connectez-vous pour gérer le site</p>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger" role="alert">
                            <i class="bi bi-exclamation-triangle"></i>
                            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" novalidate>
                        <?= \App\Core\Security::csrfField() ?>
                        <div class="mb-3">
                            <label for="email" class="form-label">Adresse email</label>
                            <input type="email" class="form-control" id="email" name="email" required autocomplete="email" placeholder="votre@email.fr">
                        </div>
                        <div class="mb-4">
                            <label for="password" class="form-label">Mot de passe</label>
                            <input type="password" class="form-control" id="password" name="password" required autocomplete="current-password">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Se connecter</button>
                    </form>

                    <p class="text-center mt-4 mb-0">
                        <a href="<?= url('/') ?>" class="text-muted small">← Retour au site public</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
