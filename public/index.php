<?php

require __DIR__ . '/../src/Core/Database.php';

use Core\Database;

try {
    $pdo = Database::getConnection();
    echo "✓ Koneksi BERHASIL ke database elearning_db<br>";

    $stmt = $pdo->query("SELECT COUNT(*) FROM courses");
    echo "Jumlah data course: " . $stmt->fetchColumn();
} 
catch (Exception $e) {
    echo "✗ GAGAL: " . $e->getMessage();
}