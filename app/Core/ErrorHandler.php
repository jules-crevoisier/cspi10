<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Gestion centralisée des erreurs avec messages compréhensibles.
 */
final class ErrorHandler
{
    public static function register(): void
    {
        set_exception_handler([self::class, 'handleException']);
        set_error_handler([self::class, 'handleError']);
    }

    public static function handleException(\Throwable $e): void
    {
        $isJson = self::wantsJson();
        $isAdmin = str_starts_with(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '', '/admin');

        if ($e instanceof AppException) {
            self::respond($e->getUserMessage(), $e->getHttpStatus(), $isJson, $isAdmin, 'warning');
            return;
        }

        if ($e instanceof \PDOException) {
            error_log('[DB] ' . $e->getMessage());
            self::respond(
                'Impossible d\'accéder aux données pour le moment. Réessayez dans quelques instants.',
                503,
                $isJson,
                $isAdmin,
                'danger'
            );
            return;
        }

        error_log('[ERROR] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());

        $message = Env::isDebug()
            ? $e->getMessage()
            : 'Une erreur inattendue s\'est produite. Si le problème persiste, contactez l\'administrateur.';

        self::respond($message, 500, $isJson, $isAdmin, 'danger');
    }

    public static function handleError(int $severity, string $message, string $file, int $line): bool
    {
        if (!(error_reporting() & $severity)) {
            return false;
        }

        if ($severity === E_DEPRECATED || $severity === E_USER_DEPRECATED) {
            error_log("[DEPRECATED] {$message} in {$file}:{$line}");
            return true;
        }

        throw new \ErrorException($message, 0, $severity, $file, $line);
    }

    private static function wantsJson(): bool
    {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        return str_contains($accept, 'application/json')
            || str_contains($contentType, 'application/json')
            || str_contains($_SERVER['REQUEST_URI'] ?? '', '/image/');
    }

    private static function respond(
        string $message,
        int $status,
        bool $isJson,
        bool $isAdmin,
        string $alertType
    ): void {
        if (!headers_sent()) {
            http_response_code($status);
        }

        if ($isJson) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'message' => $message,
                'error' => true,
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if ($isAdmin && !headers_sent()) {
            Flash::error($message);
            $referer = $_SERVER['HTTP_REFERER'] ?? url('/admin/login');
            header('Location: ' . $referer);
            exit;
        }

        if ($status === 404) {
            require dirname(__DIR__, 2) . '/public/view/404.php';
            exit;
        }

        echo '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><title>Erreur</title>';
        echo '<style>body{font-family:system-ui,sans-serif;max-width:600px;margin:4rem auto;padding:0 1rem;color:#333}';
        echo '.alert{padding:1rem 1.25rem;border-radius:8px;border-left:4px solid #dc3545;background:#f8d7da;color:#721c24}</style></head><body>';
        echo '<div class="alert" role="alert">' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</div>';
        echo '<p><a href="' . htmlspecialchars(url('/'), ENT_QUOTES, 'UTF-8') . '">Retour à l\'accueil</a></p>';
        echo '</body></html>';
        exit;
    }
}
