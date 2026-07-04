<?php
declare(strict_types=1);

namespace App\Models;

class ActualiteImage extends BaseModel
{
    private const TABLE = 'actualite_images';

    public static function add(int $actualiteId, string $url, bool $isPrimary = false, int $position = 0): int
    {
        return self::insert(self::TABLE, [
            'actualite_id' => $actualiteId,
            'url' => $url,
            'is_primary' => $isPrimary ? 1 : 0,
            'position' => $position,
        ]);
    }

    public static function setPrimary(int $actualiteId, int $imageId): void
    {
        self::run('UPDATE ' . self::TABLE . ' SET is_primary = 0 WHERE actualite_id = ?', [$actualiteId]);
        self::updateRow(self::TABLE, $imageId, ['is_primary' => 1]);
    }

    public static function delete(int $id): bool
    {
        return self::deleteRow(self::TABLE, $id);
    }

    /** @return array<int, array<string, mixed>> */
    public static function listByActualite(int $actualiteId): array
    {
        return self::fetchAll(
            'SELECT * FROM ' . self::TABLE . ' WHERE actualite_id = ? ORDER BY position',
            [$actualiteId]
        );
    }

    /** @return array<string, mixed>|null */
    public static function getPrimaryImage(int $actualiteId): ?array
    {
        return self::fetchOne(
            'SELECT * FROM ' . self::TABLE . ' WHERE actualite_id = ? AND is_primary = 1 LIMIT 1',
            [$actualiteId]
        );
    }

    /** @return array<string, mixed>|null */
    public static function getById(int $id): ?array
    {
        return self::fetchOne('SELECT * FROM ' . self::TABLE . ' WHERE id = ? LIMIT 1', [$id]);
    }
}
