<?php
declare(strict_types=1);

namespace App\Models;

class BienImage extends BaseModel
{
    private const TABLE = 'bien_images';

    public static function add(int $bienId, string $url, bool $isPrimary = false, int $position = 0): int
    {
        return self::insert(self::TABLE, [
            'bien_id' => $bienId,
            'url' => $url,
            'is_primary' => $isPrimary ? 1 : 0,
            'position' => $position,
        ]);
    }

    public static function setPrimary(int $bienId, int $imageId): void
    {
        self::run('UPDATE ' . self::TABLE . ' SET is_primary = 0 WHERE bien_id = ?', [$bienId]);
        self::updateRow(self::TABLE, $imageId, ['is_primary' => 1]);
    }

    public static function delete(int $id): bool
    {
        return self::deleteRow(self::TABLE, $id);
    }

    /** @return array<int, array<string, mixed>> */
    public static function listByBien(int $bienId): array
    {
        return self::fetchAll(
            'SELECT * FROM ' . self::TABLE . ' WHERE bien_id = ? ORDER BY position',
            [$bienId]
        );
    }

    /** @return array<string, mixed>|null */
    public static function getPrimaryImage(int $bienId): ?array
    {
        return self::fetchOne(
            'SELECT * FROM ' . self::TABLE . ' WHERE bien_id = ? AND is_primary = 1 LIMIT 1',
            [$bienId]
        );
    }

    /** @return array<string, mixed>|null */
    public static function getById(int $id): ?array
    {
        return self::fetchOne('SELECT * FROM ' . self::TABLE . ' WHERE id = ? LIMIT 1', [$id]);
    }
}
