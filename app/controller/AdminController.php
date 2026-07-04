<?php
declare(strict_types=1);

namespace App\Controller;

use App\Core\AppException;
use App\Core\Database;
use App\Core\Flash;
use App\Core\Security;

class AdminController
{
    public function __construct()
    {
        Security::startSession();
    }

    /** @return array{error?: string}|null */
    public function login(): ?array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return null;
        }

        if (Security::isLoginLocked()) {
            return ['error' => Security::loginLockMessage()];
        }

        try {
            Security::verifyCsrfFromRequest();
        } catch (AppException $e) {
            return ['error' => $e->getUserMessage()];
        }

        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'] ?? '';

        if (!$email || $password === '') {
            return ['error' => 'Veuillez saisir votre email et votre mot de passe.'];
        }

        try {
            $admin = Database::queryOne('SELECT * FROM administrateurs WHERE email = ?', [$email]);

            if ($admin && password_verify($password, (string) $admin['password'])) {
                Security::regenerateSession();
                Security::clearLoginAttempts();
                $_SESSION['admin_id'] = (int) $admin['id'];
                $_SESSION['admin_email'] = (string) $admin['email'];
                Flash::success('Connexion réussie. Bienvenue !');
                header('Location: /admin/dashboard');
                exit;
            }

            Security::recordFailedLogin();
            return ['error' => 'Email ou mot de passe incorrect. Vérifiez vos identifiants.'];
        } catch (\Throwable) {
            return ['error' => 'Impossible de se connecter pour le moment. Réessayez plus tard.'];
        }
    }

    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
        header('Location: /admin/login');
        exit;
    }

    public function isLoggedIn(): bool
    {
        return isset($_SESSION['admin_id']);
    }

    public function requireLogin(): void
    {
        if (!$this->isLoggedIn()) {
            Flash::warning('Veuillez vous connecter pour accéder à l\'administration.');
            header('Location: /admin/login');
            exit;
        }
    }

    public function requireLoginJson(): void
    {
        if (!$this->isLoggedIn()) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'message' => 'Session expirée. Reconnectez-vous.',
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
}
