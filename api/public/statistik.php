<?php

declare(strict_types=1);

require_once __DIR__ . '/_helper.php';
setPublicHeaders();

try {
    $pdo = Database::connect();

    $totalKth = (int) $pdo->query("SELECT COUNT(*) FROM kth WHERE is_active = 1")->fetchColumn();
    $totalAnggota = (int) $pdo->query("SELECT COALESCE(SUM(jumlah_anggota), 0) FROM kth WHERE is_active = 1")->fetchColumn();
    $totalLuasRhl = (float) $pdo->query("SELECT COALESCE(SUM(luas_ha), 0) FROM rhl")->fetchColumn();
    $totalKabupaten = (int) $pdo->query("SELECT COUNT(*) FROM kabupaten")->fetchColumn();
    $totalKps = (int) $pdo->query("SELECT COUNT(*) FROM kps")->fetchColumn();

    sendJson([
        'status' => 'ok',
        'updated_at' => date('c'),
        'data' => [
            'total_kth' => $totalKth,
            'total_anggota' => $totalAnggota,
            'total_luas_rhl' => round($totalLuasRhl, 2),
            'total_kabupaten' => $totalKabupaten,
            'total_kps' => $totalKps,
        ],
    ]);
} catch (\Throwable $e) {
    sendError('Gagal memuat data statistik');
}
