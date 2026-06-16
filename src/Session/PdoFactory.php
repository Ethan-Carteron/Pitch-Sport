<?php

namespace App\Session;

use PDO;
use Doctrine\DBAL\Connection;

class PdoFactory
{
    public static function create(Connection $connection): PDO
    {
        $native = $connection->getNativeConnection();
        if (!$native instanceof PDO) {
            throw new \RuntimeException('Native connection is not PDO. Ensure pdo_pgsql is used.');
        }
        return $native;
    }
}
