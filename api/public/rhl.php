<?php

declare(strict_types=1);

require_once __DIR__ . '/_helper.php';
setPublicHeaders();

try {
    $pdo = Database::connect();

    $sql = "SELECT
                tahun,
                COALESCE(SUM(luas_ha), 0) AS luas_ha,
                COUNT(*) AS jumlah_kegiatan
            FROM rhl
            GROUP BY tahun
            ORDER BY tahun DESC";

    $rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    $data = [];
    foreach ($rows as $r) {
        $luasHa = round((float) $r['luas_ha'], 2);
        $data[] = [
            'tahun' => (int) $r['tahun'],
            'luas_ha' => $luasHa,
            'jumlah_kegiatan' => (int) $r['jumlah_kegiatan'],
        ];
    }

    sendJson(['status' => 'ok', 'data' => $data]);
} catch (\Throwable $e) {
    sendError('Gagal memuat data RHL');
}
