<?php
declare(strict_types=1);

/**
 * Importe un dump MySQL/MariaDB (mysqldump) vers la base SQLite du projet.
 *
 * Usage :
 *   php scripts/import-from-mysql-dump.php [chemin/backup.sql]
 */

require_once dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Database;

const IMPORT_TABLES = [
    'administrateurs',
    'biens',
    'bien_images',
    'actualites',
    'actualite_images',
    'partenaires',
];

$dumpPath = $argv[1] ?? dirname(__DIR__) . '/backup-cspi.sql';

if (!is_file($dumpPath)) {
    fwrite(STDERR, "Fichier introuvable : {$dumpPath}\n");
    exit(1);
}

$root = dirname(__DIR__);
$dump = file_get_contents($dumpPath);
if ($dump === false) {
    fwrite(STDERR, "Impossible de lire {$dumpPath}\n");
    exit(1);
}

$dbFile = $root . '/database/data/cspi.db';

if (!is_file($dbFile)) {
    Database::migrate($root);
} else {
    Database::boot($root);
}

$pdo = Database::pdo();
$pdo->exec('PRAGMA foreign_keys = OFF');

foreach (array_reverse(IMPORT_TABLES) as $table) {
    $pdo->exec("DELETE FROM {$table}");
}

$imported = 0;

foreach (IMPORT_TABLES as $table) {
    $rows = extractInsertRows($dump, $table);
    if ($rows === []) {
        echo "  {$table} : aucune donnée\n";
        continue;
    }

    $pdo->exec("DELETE FROM {$table}");

    $columns = detectColumns($pdo, $table);
    $placeholders = implode(', ', array_fill(0, count($columns), '?'));
    $columnList = implode(', ', $columns);
    $stmt = $pdo->prepare("INSERT INTO {$table} ({$columnList}) VALUES ({$placeholders})");

    foreach ($rows as $values) {
        if (count($values) !== count($columns)) {
            throw new RuntimeException(
                "Ligne invalide pour {$table} : " . count($values) . ' valeurs, ' . count($columns) . ' colonnes attendues'
            );
        }
        $stmt->execute($values);
        $imported++;
    }

    echo "  {$table} : " . count($rows) . " ligne(s)\n";
}

$pdo->exec('PRAGMA foreign_keys = ON');

echo "\nImport terminé — {$imported} ligne(s) au total.\n";
echo "Base : {$dbFile}\n";

/**
 * @return list<string>
 */
function detectColumns(PDO $pdo, string $table): array
{
    $stmt = $pdo->query("PRAGMA table_info({$table})");
    $columns = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $columns[] = $row['name'];
    }
    if ($columns === []) {
        throw new RuntimeException("Table inconnue : {$table}");
    }
    return $columns;
}

/**
 * @return list<list<mixed>>
 */
function extractInsertRows(string $dump, string $table): array
{
    $pattern = '/INSERT INTO `' . preg_quote($table, '/') . '` VALUES\s*(.*?);\s*(?:UNLOCK TABLES|\/\*!40000)/s';
    if (!preg_match($pattern, $dump, $match)) {
        return [];
    }

    return parseValueTuples(trim($match[1]));
}

/**
 * @return list<list<mixed>>
 */
function parseValueTuples(string $payload): array
{
    $rows = [];
    $length = strlen($payload);
    $index = 0;

    while ($index < $length) {
        while ($index < $length && ($payload[$index] === ',' || $payload[$index] === ' ' || $payload[$index] === "\n" || $payload[$index] === "\r")) {
            $index++;
        }

        if ($index >= $length) {
            break;
        }

        if ($payload[$index] !== '(') {
            break;
        }

        [$values, $index] = parseTuple($payload, $index + 1);
        $rows[] = $values;
    }

    return $rows;
}

/**
 * @return array{0: list<mixed>, 1: int}
 */
function parseTuple(string $payload, int $index): array
{
    $values = [];
    $length = strlen($payload);

    while ($index < $length) {
        while ($index < $length && ($payload[$index] === ' ' || $payload[$index] === "\n" || $payload[$index] === "\r")) {
            $index++;
        }

        if ($index >= $length) {
            break;
        }

        if ($payload[$index] === ')') {
            return [$values, $index + 1];
        }

        if ($payload[$index] === ',') {
            $index++;
            continue;
        }

        [$value, $index] = parseValue($payload, $index);
        $values[] = $value;
    }

    throw new RuntimeException('Tuple SQL mal formé.');
}

/**
 * @return array{0: mixed, 1: int}
 */
function parseValue(string $payload, int $index): array
{
    $char = $payload[$index];

    if ($char === "'") {
        return parseQuotedString($payload, $index + 1);
    }

    if (str_starts_with(substr($payload, $index), 'NULL')) {
        return [null, $index + 4];
    }

    $end = $index;
    while ($end < strlen($payload) && !in_array($payload[$end], [',', ')'], true)) {
        $end++;
    }

    $raw = trim(substr($payload, $index, $end - $index));
    if ($raw === '') {
        throw new RuntimeException('Valeur SQL vide inattendue.');
    }

    if (is_numeric($raw) && !str_contains($raw, '.')) {
        return [(int) $raw, $end];
    }

    if (is_numeric($raw)) {
        return [(float) $raw, $end];
    }

    return [$raw, $end];
}

/**
 * @return array{0: string, 1: int}
 */
function parseQuotedString(string $payload, int $index): array
{
    $value = '';
    $length = strlen($payload);

    while ($index < $length) {
        $char = $payload[$index];

        if ($char === '\\' && $index + 1 < $length) {
            $next = $payload[$index + 1];
            $value .= match ($next) {
                'n' => "\n",
                'r' => "\r",
                't' => "\t",
                '\\' => '\\',
                "'" => "'",
                '"' => '"',
                default => $next,
            };
            $index += 2;
            continue;
        }

        if ($char === "'") {
            if ($index + 1 < $length && $payload[$index + 1] === "'") {
                $value .= "'";
                $index += 2;
                continue;
            }
            return [$value, $index + 1];
        }

        $value .= $char;
        $index++;
    }

    throw new RuntimeException('Chaîne SQL non terminée.');
}
