<?php

namespace App\PDO;

use InvalidArgumentException;
use PDO;

class PdoFactory
{
    public static function create(string $databaseUrl): PDO
    {
        $url = parse_url($databaseUrl);

        if (!$url) {
            throw new InvalidArgumentException('Invalid DATABASE_URL');
        }

        $host = $url['host'] ?? '127.0.0.1';
        $port = $url['port'] ?? 5432;
        $user = rawurldecode($url['user'] ?? '');
        $pass = rawurldecode($url['pass'] ?? '');
        $dbname = ltrim($url['path'] ?? '', '/');

        $dsn = sprintf('pgsql:host=%s;port=%d;dbname=%s', $host, $port, $dbname);

        if (isset($url['query'])) {
            parse_str($url['query'], $query);
            if (isset($query['sslmode'])) {
                $dsn .= sprintf(';sslmode=%s', $query['sslmode']);
            }
        }

        return new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
    }
}
