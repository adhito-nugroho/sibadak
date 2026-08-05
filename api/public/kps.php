<?php

declare(strict_types=1);

require_once __DIR__ . '/_helper.php';
setPublicHeaders();

try {
    $pdo = Database::connect();

    $total = (int) $pdo->query("SELECT COUNT(*) FROM kps")->fetchColumn();

    $sql = "SELECT skema AS nama, COUNT(*) AS jumlah
            FROM kps
            GROUP BY skema
            ORDER BY jumlah DESC";

    $rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    $skema = [];
    foreach ($rows as $r) {
        $skema[] = [
            'nama' => $r['nama'],
            'jumlah' => (int) $r['jumlah'],
        ];
    }

    sendJson([
        'status' => 'ok',
        'data' => [
            'total' => $total,
            'skema' => $skema,
        ],
    ]);
} catch (\Throwable $e) {
    sendError('Gagal memuat data KPS');
}
