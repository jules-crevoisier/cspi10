<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Messages flash pour feedback utilisateur dans l'administration.
 */
final class Flash
{
    private const SESSION_KEY = '_flash_messages';

    public static function success(string $message): void
    {
        self::add('success', $message);
    }

    public static function error(string $message): void
    {
        self::add('error', $message);
    }

    public static function warning(string $message): void
    {
        self::add('warning', $message);
    }

    public static function info(string $message): void
    {
        self::add('info', $message);
    }

    private static function add(string $type, string $message): void
    {
        Security::startSession();
        $_SESSION[self::SESSION_KEY][] = ['type' => $type, 'message' => $message];
    }

    /** @return list<array{type: string, message: string}> */
    public static function pull(): array
    {
        Security::startSession();
        $messages = $_SESSION[self::SESSION_KEY] ?? [];
        unset($_SESSION[self::SESSION_KEY]);
        return $messages;
    }

    public static function hasMessages(): bool
    {
        Security::startSession();
        return !empty($_SESSION[self::SESSION_KEY]);
    }
}
