<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\AppException;

class Actualite extends BaseModel
{
    private const TABLE = 'actualites';

    /** @param array<string, mixed> $data */
    public static function create(array $data): int
    {
        foreach (['titre', 'categorie'] as $field) {
            if (empty($data[$field])) {
                throw new AppException("Le champ « {$field} » est obligatoire.", 'VALIDATION_ERROR');
            }
        }
        if (!empty($data['publie_le'])) {
            $date = \DateTime::createFromFormat('Y-m-d', (string) $data['publie_le']);
            if (!$date || $date->format('Y-m-d') !== $data['publie_le']) {
                throw new AppException('La date de publication est invalide (format attendu : AAAA-MM-JJ).', 'VALIDATION_ERROR');
            }
        }
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
        return self::fetchAll('SELECT * FROM ' . self::TABLE . ' ORDER BY publie_le DESC');
    }

    /** @return array<string, mixed>|null */
    public static function getById(int $id): ?array
    {
        return self::fetchOne('SELECT * FROM ' . self::TABLE . ' WHERE id = ?', [$id]);
    }

    /** @return list<string> */
    public static function getCategories(): array
    {
        $rows = self::fetchAll(
            "SELECT DISTINCT categorie FROM " . self::TABLE . " WHERE categorie IS NOT NULL AND categorie != '' ORDER BY categorie"
        );
        return array_column($rows, 'categorie');
    }
}
