<?php

declare(strict_types=1);

class Database
{
    private static ?\PDO $instance = null;

    public static function connect(): \PDO
    {
        if (self::$instance === null) {
            $host = $_ENV['DB_HOST'] ?? 'localhost';
            $port = $_ENV['DB_PORT'] ?? '3306';
            $name = $_ENV['DB_NAME'] ?? 'db_kth_cdk_bjn';
            $user = $_ENV['DB_USER'] ?? 'root';
            $pass = $_ENV['DB_PASS'] ?? '';

            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                $host,
                $port,
                $name
            );

            self::$instance = new \PDO($dsn, $user, $pass, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        }

        return self::$instance;
    }
}
