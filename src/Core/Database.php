<?php

namespace Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;

    private function __construct() {}     // cegah instansiasi langsung
    private function __clone() {}         // cegah clone
    private function __wakeup() {}        // cegah unserialize

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {

            // ambil config database
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
                        PDO::ATTR_PERSISTENT         => false
                    ]
                );
            } catch (PDOException $e) {
                // PDF instructs: throw exception (biar error cepat terlihat)
                throw new PDOException("Database connection failed: " . $e->getMessage());
            }
        }

        return self::$instance;
    }
}