<?php
declare(strict_types=1);

namespace App\Models;

class Bien extends BaseModel
{
    private const TABLE = 'biens';
    private const IMAGES_TABLE = 'bien_images';

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
        foreach (BienImage::listByBien($id) as $image) {
            $imagePath = PUBLIC_PATH . ltrim((string) $image['url'], '/');
            if (is_file($imagePath)) {
                unlink($imagePath);
            }
            BienImage::delete((int) $image['id']);
        }
        return self::deleteRow(self::TABLE, $id);
    }

    /** @return array<int, array<string, mixed>> */
    public static function getAll(): array
    {
        return self::fetchAll('SELECT * FROM ' . self::TABLE . ' ORDER BY created_at DESC');
    }

    /** @return array<string, mixed>|null */
    public static function getById(int $id): ?array
    {
        return self::fetchOne('SELECT * FROM ' . self::TABLE . ' WHERE id = ?', [$id]);
    }

    /** @return array<int, array<string, mixed>> */
    public static function getImages(int $bienId): array
    {
        return self::fetchAll(
            'SELECT * FROM ' . self::IMAGES_TABLE . ' WHERE bien_id = ? ORDER BY is_primary DESC, position ASC',
            [$bienId]
        );
    }

    public static function addImage(int $bienId, string $imageUrl, bool $isPrimary = false): bool
    {
        if ($isPrimary) {
            self::run('UPDATE ' . self::IMAGES_TABLE . ' SET is_primary = 0 WHERE bien_id = ?', [$bienId]);
        }
        $row = self::fetchOne('SELECT MAX(position) AS max_pos FROM ' . self::IMAGES_TABLE . ' WHERE bien_id = ?', [$bienId]);
        $position = ((int) ($row['max_pos'] ?? 0)) + 1;
        self::insert(self::IMAGES_TABLE, [
            'bien_id' => $bienId,
            'url' => $imageUrl,
            'is_primary' => $isPrimary ? 1 : 0,
            'position' => $position,
        ]);
        return true;
    }

    public static function deleteImage(int $imageId): bool
    {
        return self::deleteRow(self::IMAGES_TABLE, $imageId);
    }

    public static function setPrimaryImage(int $bienId, int $imageId): bool
    {
        self::run('UPDATE ' . self::IMAGES_TABLE . ' SET is_primary = 0 WHERE bien_id = ?', [$bienId]);
        return self::updateRow(self::IMAGES_TABLE, $imageId, ['is_primary' => 1]);
    }
}
