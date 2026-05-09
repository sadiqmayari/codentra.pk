<?php
declare(strict_types=1);

class Database
{
    private static ?PDO $instance = null;

    private function __construct() {}
    private function __clone() {}

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $driver = strtolower($_ENV['DB_DRIVER'] ?? 'mysql');

            $opts = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            if ($driver === 'sqlite') {
                // Local-dev fallback — DB_NAME is the .sqlite file path.
                $path = $_ENV['DB_NAME'] ?? '';
                if ($path === '') {
                    throw new \RuntimeException('DB_NAME must be set to the sqlite file path when DB_DRIVER=sqlite');
                }
                self::$instance = new PDO('sqlite:' . $path, null, null, $opts);
                self::$instance->exec('PRAGMA foreign_keys = ON');
                return self::$instance;
            }

            // Default: MySQL (production on Hostinger)
            $host = $_ENV['DB_HOST'] ?? 'localhost';
            $port = $_ENV['DB_PORT'] ?? '3306';
            $name = $_ENV['DB_NAME'] ?? '';
            $user = $_ENV['DB_USER'] ?? '';
            $pass = $_ENV['DB_PASS'] ?? '';

            $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
            $opts[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci";

            self::$instance = new PDO($dsn, $user, $pass, $opts);
        }

        return self::$instance;
    }

    /** Test-only — drops the cached instance so a new one picks up env changes. */
    public static function reset(): void
    {
        self::$instance = null;
    }
}
