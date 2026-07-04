<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;
use PDOException;

abstract class BaseModel
{
    protected static bool $initialized = false;

    public static function init(): void
    {
        self::$initialized = true;
    }

    protected static function db(): PDO
    {
        if (!self::$initialized) {
            throw new \RuntimeException('BaseModel non initialisé.');
        }
        if (Database::isTurso()) {
            throw new \RuntimeException('Utilisez Database::query/execute pour Turso.');
        }
        return Database::pdo();
    }

    /** @param array<string, mixed> $data */
    protected static function insert(string $table, array $data): int
    {
        try {
            $cols = implode(',', array_keys($data));
            $binds = ':' . implode(', :', array_keys($data));
            $sql = "INSERT INTO {$table} ({$cols}) VALUES ({$binds})";

            if (Database::isTurso()) {
                Database::execute($sql, $data);
                return Database::lastInsertId();
            }

            $stmt = self::db()->prepare($sql);
            $stmt->execute($data);
            return (int) self::db()->lastInsertId();
        } catch (PDOException $e) {
            throw new \App\Core\AppException(
                'Impossible d\'enregistrer les données. Vérifiez les champs saisis.',
                'DB_INSERT_ERROR',
                500,
                $e
            );
        }
    }

    /** @param array<string, mixed> $data */
    protected static function updateRow(string $table, int $id, array $data): bool
    {
        try {
            $pairs = [];
            foreach ($data as $col => $val) {
                $pairs[] = "{$col} = :{$col}";
            }
            $sql = "UPDATE {$table} SET " . implode(',', $pairs) . " WHERE id = :id";
            $data['id'] = $id;

            if (Database::isTurso()) {
                return Database::execute($sql, $data) > 0;
            }

            $stmt = self::db()->prepare($sql);
            return $stmt->execute($data);
        } catch (PDOException $e) {
            throw new \App\Core\AppException(
                'Impossible de mettre à jour les données.',
                'DB_UPDATE_ERROR',
                500,
                $e
            );
        }
    }

    protected static function deleteRow(string $table, int $id): bool
    {
        try {
            $sql = "DELETE FROM {$table} WHERE id = ?";

            if (Database::isTurso()) {
                return Database::execute($sql, [$id]) > 0;
            }

            $stmt = self::db()->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            throw new \App\Core\AppException(
                'Impossible de supprimer cet élément.',
                'DB_DELETE_ERROR',
                500,
                $e
            );
        }
    }

    /** @return array<int, array<string, mixed>> */
    protected static function fetchAll(string $sql, array $params = []): array
    {
        if (Database::isTurso()) {
            return Database::query($sql, $params);
        }
        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** @return array<string, mixed>|null */
    protected static function fetchOne(string $sql, array $params = []): ?array
    {
        if (Database::isTurso()) {
            return Database::queryOne($sql, $params);
        }
        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    protected static function run(string $sql, array $params = []): void
    {
        if (Database::isTurso()) {
            Database::execute($sql, $params);
            return;
        }
        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
    }
}
