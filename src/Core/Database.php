<?php

namespace Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;

    private function __construct() {}
    private function __clone() {}

    // __wakeup() DIHAPUS agar tidak warn

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {

            $db = require __DIR__ . '/../../config/database.php';

            $dsn = "mysql:host={$db['host']};"
                 . "port={$db['port']};"
                 . "dbname={$db['database']};"
                 . "charset={$db['charset']}";

            try {
                self::$instance = new PDO(
                    $dsn,
                    $db['username'],
                    $db['password'],
                    [
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    ]
                );
            } catch (PDOException $e) {
                throw new PDOException("Database connection failed: " . $e->getMessage());
            }
        }

        return self::$instance;
    }
}