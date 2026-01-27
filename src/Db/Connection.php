<?php
declare(strict_types=1);

namespace App\Db;

use PDO;
use PDOException;
use RuntimeException;

class Connection
{
    public static function create(array $config): PDO
    {
        if (!isset($config['dsn'], $config['user'], $config['pass'])) {
            throw new RuntimeException('Database configuration incomplete.');
        }

        try {
            $pdo = new PDO(
                (string) $config['dsn'],
                (string) $config['user'],
                (string) $config['pass'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
        } catch (PDOException $e) {
            throw new RuntimeException('Database connection failed', 0, $e);
        }

        return $pdo;
    }
}
