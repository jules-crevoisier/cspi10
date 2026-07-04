<?php
declare(strict_types=1);

namespace App\Core;

use Darkterminal\TursoHttp\LibSQL;
use PDO;
use PDOException;
use PDOStatement;
use RuntimeException;

/**
 * Couche d'accès base de données unifiée (SQLite locale ou Turso).
 */
final class Database
{
    private const DRIVER_SQLITE = 'sqlite';
    private const DRIVER_TURSO = 'turso';

    private static ?PDO $pdo = null;
    private static ?LibSQL $turso = null;
    private static string $driver = self::DRIVER_SQLITE;
    private static int $lastInsertId = 0;

    public static function boot(string $rootPath): void
    {
        if (self::$pdo !== null || self::$turso !== null) {
            return;
        }

        $tursoUrl = Env::get('TURSO_DATABASE_URL');
        $tursoToken = Env::get('TURSO_AUTH_TOKEN');

        if ($tursoUrl && $tursoToken) {
            self::$driver = self::DRIVER_TURSO;
            $dsn = str_contains($tursoUrl, 'authToken=')
                ? $tursoUrl
                : 'libsql://' . ltrim(str_replace(['libsql://', 'https://', 'http://'], '', $tursoUrl), '/') . '?authToken=' . $tursoToken;
            self::$turso = new LibSQL($dsn);
            return;
        }

        self::$driver = self::DRIVER_SQLITE;
        $dbPath = Env::get('DATABASE_PATH', 'database/data/cspi.db');
        $absolutePath = str_starts_with($dbPath, DIRECTORY_SEPARATOR) || preg_match('#^[A-Za-z]:#', $dbPath)
            ? $dbPath
            : $rootPath . '/' . ltrim($dbPath, '/');

        $directory = dirname($absolutePath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        self::$pdo = new PDO('sqlite:' . $absolutePath, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        self::$pdo->exec('PRAGMA foreign_keys = ON');
    }

    public static function driver(): string
    {
        return self::$driver;
    }

    public static function isTurso(): bool
    {
        return self::$driver === self::DRIVER_TURSO;
    }

    /** @return PDO */
    public static function pdo(): PDO
    {
        if (self::$pdo === null) {
            throw new RuntimeException('PDO non disponible — utilisez Turso ou SQLite.');
        }
        return self::$pdo;
    }

    public static function turso(): LibSQL
    {
        if (self::$turso === null) {
            throw new RuntimeException('Connexion Turso non initialisée.');
        }
        return self::$turso;
    }

    /**
     * @param array<string, mixed> $params
     */
    public static function query(string $sql, array $params = []): array
    {
        if (self::isTurso()) {
            return self::tursoQuery($sql, $params);
        }

        $stmt = self::pdo()->prepare($sql);
        self::bindParams($stmt, $params);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * @param array<string, mixed> $params
     */
    public static function queryOne(string $sql, array $params = []): ?array
    {
        $rows = self::query($sql, $params);
        return $rows[0] ?? null;
    }

    /**
     * @param array<string, mixed> $params
     */
    public static function execute(string $sql, array $params = []): int
    {
        if (self::isTurso()) {
            return self::tursoExecute($sql, $params);
        }

        $stmt = self::pdo()->prepare($sql);
        self::bindParams($stmt, $params);
        $stmt->execute();
        return $stmt->rowCount();
    }

    public static function lastInsertId(): int
    {
        if (self::isTurso()) {
            return self::$lastInsertId;
        }
        return (int) self::pdo()->lastInsertId();
    }

    public static function setLastInsertId(int $id): void
    {
        self::$lastInsertId = $id;
    }

    /**
     * @param array<string, mixed> $params
     */
    private static function bindParams(PDOStatement $stmt, array $params): void
    {
        foreach ($params as $key => $value) {
            $param = is_int($key) ? $key + 1 : (str_starts_with((string) $key, ':') ? $key : ':' . $key);
            $stmt->bindValue($param, $value);
        }
    }

    /**
     * @param array<string, mixed> $params
     * @return array<int, array<string, mixed>>
     */
    private static function tursoQuery(string $sql, array $params): array
    {
        [$sql, $values] = self::normalizeSql($sql, $params);
        $result = self::turso()->prepare($sql)->query($values);
        $rows = $result->fetchAll(LibSQL::LIBSQL_ASSOC);
        return is_array($rows) ? $rows : [];
    }

    /**
     * @param array<string, mixed> $params
     */
    private static function tursoExecute(string $sql, array $params): int
    {
        [$sql, $values] = self::normalizeSql($sql, $params);
        $affected = self::turso()->prepare($sql)->execute($values);

        if (stripos($sql, 'INSERT') === 0) {
            $idRow = self::tursoQuery('SELECT last_insert_rowid() AS id', []);
            self::$lastInsertId = (int) ($idRow[0]['id'] ?? 0);
        }

        return $affected;
    }

    /**
     * Convertit les paramètres nommés en paramètres positionnels pour Turso.
     *
     * @param array<string, mixed> $params
     * @return array{0: string, 1: array<int, mixed>}
     */
    private static function normalizeSql(string $sql, array $params): array
    {
        if ($params === []) {
            return [$sql, []];
        }

        $values = [];
        $normalized = preg_replace_callback(
            '/:(\w+)/',
            static function (array $matches) use ($params, &$values): string {
                $key = $matches[1];
                if (!array_key_exists($key, $params)) {
                    throw new RuntimeException("Paramètre SQL manquant : :{$key}");
                }
                $values[] = $params[$key];
                return '?';
            },
            $sql
        );

        return [$normalized ?? $sql, $values];
    }

    public static function migrate(string $rootPath): void
    {
        self::boot($rootPath);

        $schemaFile = $rootPath . '/database/schema.sql';
        if (!file_exists($schemaFile)) {
            throw new RuntimeException('Fichier schema.sql introuvable.');
        }

        $schema = file_get_contents($schemaFile);
        if ($schema === false) {
            throw new RuntimeException('Impossible de lire schema.sql.');
        }

        if (self::isTurso()) {
            foreach (self::splitSqlStatements($schema) as $statement) {
                if (trim($statement) !== '') {
                    self::tursoExecute($statement, []);
                }
            }
        } else {
            self::pdo()->exec($schema);
        }

        $seedFile = $rootPath . '/database/seed.sql';
        if (file_exists($seedFile)) {
            $seed = file_get_contents($seedFile);
            if ($seed !== false) {
                if (self::isTurso()) {
                    foreach (self::splitSqlStatements($seed) as $statement) {
                        if (trim($statement) !== '') {
                            self::tursoExecute($statement, []);
                        }
                    }
                } else {
                    self::pdo()->exec($seed);
                }
            }
        }
    }

    /**
     * @return list<string>
     */
    private static function splitSqlStatements(string $sql): array
    {
        $parts = preg_split('/;\s*\n/', $sql) ?: [];
        return array_values(array_filter(array_map('trim', $parts)));
    }
}
