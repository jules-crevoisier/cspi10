<?php
declare(strict_types=1);

namespace App\Models;

class Partenaire extends BaseModel
{
    private const TABLE = 'partenaires';

    /** @param array<string, mixed> $data */
    public static function create(array $data): int
    {
        return self::insert(self::TABLE, $data);
    }

    /** @param array<string, mixed> $data */
    public static function update(int $id, array $data): bool
    {
        return self::updateRow(self::TABLE, $id, $data);
    }

    public static function delete(int $id): bool
    {
        return self::deleteRow(self::TABLE, $id);
    }

    /** @return array<int, array<string, mixed>> */
    public static function getAll(): array
    {
        return self::fetchAll('SELECT * FROM ' . self::TABLE . ' ORDER BY nom ASC');
    }

    /** @return array<string, mixed>|null */
    public static function getById(int $id): ?array
    {
        return self::fetchOne('SELECT * FROM ' . self::TABLE . ' WHERE id = ?', [$id]);
    }
}
