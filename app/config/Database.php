<?php
declare(strict_types=1);

namespace App\Config;

use App\Core\Database as Db;
use PDO;

/** @deprecated Utiliser App\Core\Database */
class Database
{
    protected PDO $conn;

    public function __construct()
    {
        global $pdo;
        $this->conn = ($pdo instanceof PDO) ? $pdo : Db::pdo();
    }
}
