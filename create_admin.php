<?php

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

try {
    $pdo = Database::connect();

    // Mencari ID role admin
    $stmt = $pdo->prepare('SELECT id FROM roles WHERE nama = :nama LIMIT 1');
    $stmt->execute(['nama' => 'admin']);
    $roleId = $stmt->fetchColumn();

    if (!$roleId) {
        // Jika role admin belum ada, buat baru
        $stmt = $pdo->prepare('INSERT INTO roles (nama) VALUES (:nama)');
        $stmt->execute(['nama' => 'admin']);
        $roleId = $pdo->lastInsertId();
        echo "Role 'admin' berhasil dibuat.<br>";
    }

    $username = 'admin';
    $password = 'admin123';
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Mengecek apakah user admin sudah ada
    $stmt = $pdo->prepare('SELECT id FROM users WHERE username = :username LIMIT 1');
    $stmt->execute(['username' => $username]);
    $userId = $stmt->fetchColumn();

    if ($userId) {
        // Jika sudah ada, update password dan pastikan aktif
        $stmt = $pdo->prepare('UPDATE users SET password = :password, is_active = 1, role_id = :role_id WHERE id = :id');
        $stmt->execute([
            'password' => $hashedPassword, 
            'role_id' => $roleId,
            'id' => $userId
        ]);
        echo "User 'admin' sudah ada di database. Password berhasil diperbarui menjadi 'admin123'.<br>";
    } else {
        // Jika belum ada, buat user baru
        $stmt = $pdo->prepare('
            INSERT INTO users (nama, username, email, password, role_id, is_active) 
            VALUES (:nama, :username, :email, :password, :role_id, 1)
        ');
        $stmt->execute([
            'nama' => 'Administrator',
            'username' => $username,
            'email' => 'admin@localhost',
            'password' => $hashedPassword,
            'role_id' => $roleId
        ]);
        echo "User 'admin' berhasil ditambahkan dengan password 'admin123'.<br>";
    }

    echo "<br><b>PENTING:</b> Harap segera HAPUS file <code>create_admin.php</code> ini dari server Anda demi keamanan sistem!";

} catch (Exception $e) {
    echo "Terjadi kesalahan: " . $e->getMessage();
}
