<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Charge et expose les variables d'environnement de l'application.
 */
final class Env
{
    private static bool $loaded = false;

    public static function load(string $rootPath): void
    {
        if (self::$loaded) {
            return;
        }

        if (file_exists($rootPath . '/vendor/autoload.php')) {
            require_once $rootPath . '/vendor/autoload.php';
        }

        if (class_exists(\Dotenv\Dotenv::class) && file_exists($rootPath . '/.env')) {
            \Dotenv\Dotenv::createImmutable($rootPath)->safeLoad();
        }

        self::$loaded = true;
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
        if ($value === false || $value === null || $value === '') {
            return $default;
        }
        return (string) $value;
    }

    public static function bool(string $key, bool $default = false): bool
    {
        $value = self::get($key);
        if ($value === null) {
            return $default;
        }
        return in_array(strtolower($value), ['1', 'true', 'yes', 'on'], true);
    }

    public static function isProduction(): bool
    {
        return self::get('APP_ENV', 'production') === 'production';
    }

    public static function isDebug(): bool
    {
        return self::bool('APP_DEBUG', !self::isProduction());
    }
}
