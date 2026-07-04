<?php
declare(strict_types=1);

/**
 * Réinitialise le mot de passe admin — usage local uniquement.
 * php scripts/reset-admin-password.php [nouveau_mot_de_passe]
 */

require_once dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Database;

$password = $argv[1] ?? 'admin';
$email = 'chambredesproprietaires10@gmail.com';
$hash = password_hash($password, PASSWORD_BCRYPT);

$existing = Database::queryOne('SELECT id FROM administrateurs WHERE email = ?', [$email]);

if ($existing) {
    Database::execute('UPDATE administrateurs SET password = ? WHERE email = ?', [$hash, $email]);
    echo "Mot de passe mis à jour pour {$email}\n";
} else {
    Database::execute(
        'INSERT INTO administrateurs (email, password, created_at) VALUES (?, ?, datetime(\'now\'))',
        [$email, $hash]
    );
    echo "Compte admin créé : {$email}\n";
}

echo "Mot de passe : {$password}\n";
