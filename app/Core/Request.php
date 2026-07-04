<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Informations sur la requête HTTP (compatible reverse proxy Traefik / Dockploy).
 */
final class Request
{
    public static function isSecure(): bool
    {
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            return true;
        }

        $forwardedProto = strtolower($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '');
        if ($forwardedProto === 'https') {
            return true;
        }

        return ($_SERVER['HTTP_X_FORWARDED_SSL'] ?? '') === 'on';
    }

    public static function host(): string
    {
        return (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');
    }

    public static function uri(): string
    {
        return (string) ($_SERVER['REQUEST_URI'] ?? '/');
    }

    public static function forceHttpsRedirectIfNeeded(): void
    {
        if (!Env::isProduction() || self::isSecure()) {
            return;
        }

        $appUrl = Env::get('APP_URL', '') ?? '';
        if ($appUrl !== '' && str_starts_with($appUrl, 'https://')) {
            header('Location: https://' . self::host() . self::uri(), true, 301);
            exit;
        }
    }

    /**
     * Normalise une URL applicative en https si la requête courante est sécurisée.
     */
    public static function normalizeAppUrl(string $url): string
    {
        if (self::isSecure() && str_starts_with($url, 'http://')) {
            return 'https://' . substr($url, 7);
        }

        return $url;
    }
}
