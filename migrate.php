<?php

declare(strict_types=1);

// Pastikan script ini dijalankan dari CLI
if (php_sapi_name() !== 'cli') {
    die('Script ini hanya bisa dijalankan melalui command line (CLI).');
}

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

try {
    $pdo = Database::connect();
    
    echo "====================================\n";
    echo "  Sistem Migrasi Database SIBADAK   \n";
    echo "====================================\n\n";

    // 1. Buat tabel migrations jika belum ada
    $createTableQuery = "
        CREATE TABLE IF NOT EXISTS migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL UNIQUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    $pdo->exec($createTableQuery);
    echo "✓ Tabel 'migrations' siap.\n";

    // 2. Ambil daftar migrasi yang sudah pernah dijalankan
    $stmt = $pdo->query("SELECT migration FROM migrations");
    $ranMigrations = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // 3. Baca semua file .sql dari folder database/migrations/
    $migrationsDir = __DIR__ . '/database/migrations';
    if (!is_dir($migrationsDir)) {
        mkdir($migrationsDir, 0755, true);
    }

    $files = scandir($migrationsDir);
    $sqlFiles = array_filter($files, function ($file) {
        return pathinfo($file, PATHINFO_EXTENSION) === 'sql';
    });
    
    // Urutkan file agar eksekusi sesuai urutan nama/tanggal
    sort($sqlFiles);

    // 4. Bandingkan dan eksekusi yang belum
    $newMigrations = array_diff($sqlFiles, $ranMigrations);

    if (empty($newMigrations)) {
        echo "✓ Tidak ada migrasi baru untuk dijalankan. Database sudah up-to-date.\n";
        exit(0);
    }

    echo "Menjalankan " . count($newMigrations) . " migrasi baru:\n";

    foreach ($newMigrations as $migrationFile) {
        $filePath = $migrationsDir . '/' . $migrationFile;
        echo "  -> Menjalankan: " . $migrationFile . "... ";

        $sql = file_get_contents($filePath);
        
        if (empty(trim($sql))) {
            echo "LEWATI (File kosong)\n";
            continue;
        }

        try {
            // Gunakan transaction jika memungkinkan
            $pdo->beginTransaction();
            
            // Eksekusi SQL (bisa berisi beberapa query)
            $pdo->exec($sql);
            
            // Catat ke tabel migrations
            $stmtInsert = $pdo->prepare("INSERT INTO migrations (migration) VALUES (:migration)");
            $stmtInsert->execute(['migration' => $migrationFile]);
            
            $pdo->commit();
            echo "BERHASIL\n";
            
        } catch (Exception $e) {
            $pdo->rollBack();
            echo "GAGAL!\n";
            echo "Error: " . $e->getMessage() . "\n";
            echo "Migrasi dihentikan.\n";
            exit(1);
        }
    }

    echo "\n✓ Semua migrasi berhasil dijalankan!\n";

} catch (PDOException $e) {
    echo "Koneksi Database Gagal: " . $e->getMessage() . "\n";
    exit(1);
}
