<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Utilitaires de sécurité : sessions, CSRF, validation.
 */
final class Security
{
    private const CSRF_KEY = '_csrf_token';
    private const LOGIN_ATTEMPTS_KEY = '_login_attempts';
    private const MAX_LOGIN_ATTEMPTS = 5;
    private const LOCKOUT_SECONDS = 900;

    public static function startSession(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }

    public static function regenerateSession(): void
    {
        self::startSession();
        session_regenerate_id(true);
    }

    public static function csrfToken(): string
    {
        self::startSession();
        if (empty($_SESSION[self::CSRF_KEY])) {
            $_SESSION[self::CSRF_KEY] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::CSRF_KEY];
    }

    public static function csrfField(): string
    {
        $token = htmlspecialchars(self::csrfToken(), ENT_QUOTES, 'UTF-8');
        return '<input type="hidden" name="_csrf" value="' . $token . '">';
    }

    public static function verifyCsrf(?string $token): void
    {
        self::startSession();
        $expected = $_SESSION[self::CSRF_KEY] ?? '';
        if ($token === null || $expected === '' || !hash_equals($expected, $token)) {
            throw new AppException(
                'Votre session a expiré. Veuillez réessayer.',
                'CSRF_INVALID',
                403
            );
        }
    }

    public static function verifyCsrfFromRequest(): void
    {
        $token = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        self::verifyCsrf(is_string($token) ? $token : null);
    }

    public static function recordFailedLogin(): void
    {
        self::startSession();
        $attempts = ($_SESSION[self::LOGIN_ATTEMPTS_KEY]['count'] ?? 0) + 1;
        $_SESSION[self::LOGIN_ATTEMPTS_KEY] = [
            'count' => $attempts,
            'locked_until' => $attempts >= self::MAX_LOGIN_ATTEMPTS
                ? time() + self::LOCKOUT_SECONDS
                : 0,
        ];
    }

    public static function clearLoginAttempts(): void
    {
        self::startSession();
        unset($_SESSION[self::LOGIN_ATTEMPTS_KEY]);
    }

    public static function isLoginLocked(): bool
    {
        self::startSession();
        $lockedUntil = $_SESSION[self::LOGIN_ATTEMPTS_KEY]['locked_until'] ?? 0;
        if ($lockedUntil > time()) {
            return true;
        }
        if ($lockedUntil > 0 && $lockedUntil <= time()) {
            self::clearLoginAttempts();
        }
        return false;
    }

    public static function loginLockMessage(): string
    {
        self::startSession();
        $lockedUntil = $_SESSION[self::LOGIN_ATTEMPTS_KEY]['locked_until'] ?? 0;
        $minutes = max(1, (int) ceil(($lockedUntil - time()) / 60));
        return "Trop de tentatives de connexion. Réessayez dans {$minutes} minute(s).";
    }

    public static function sendSecurityHeaders(): void
    {
        header('X-Frame-Options: SAMEORIGIN');
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
        if (Env::isProduction()) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
    }

    /**
     * @param list<string> $allowedMimeTypes
     */
    public static function validateUploadedFile(
        array $file,
        array $allowedMimeTypes,
        int $maxBytes = 10_485_760
    ): void {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new AppException(
                self::uploadErrorMessage((int) ($file['error'] ?? UPLOAD_ERR_NO_FILE)),
                'UPLOAD_ERROR'
            );
        }

        if (($file['size'] ?? 0) > $maxBytes) {
            $maxMo = round($maxBytes / 1_048_576, 1);
            throw new AppException(
                "Le fichier est trop volumineux (maximum {$maxMo} Mo).",
                'UPLOAD_TOO_LARGE'
            );
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        if ($mime === false || !in_array($mime, $allowedMimeTypes, true)) {
            throw new AppException(
                'Format de fichier non autorisé. Utilisez JPG, PNG, GIF ou WebP.',
                'UPLOAD_INVALID_TYPE'
            );
        }
    }

    private static function uploadErrorMessage(int $code): string
    {
        return match ($code) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Le fichier dépasse la taille maximale autorisée.',
            UPLOAD_ERR_PARTIAL => 'Le fichier n\'a été que partiellement téléversé. Réessayez.',
            UPLOAD_ERR_NO_FILE => 'Aucun fichier n\'a été sélectionné.',
            default => 'Erreur lors du téléversement du fichier. Réessayez.',
        };
    }
}
