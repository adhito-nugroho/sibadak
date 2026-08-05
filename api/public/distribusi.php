<?php

declare(strict_types=1);

require_once __DIR__ . '/_helper.php';
setPublicHeaders();

try {
    $pdo = Database::connect();

    $sql = "SELECT
                kb.nama AS kabupaten,
                SUM(CASE WHEN kth.kelas = 'Utama' THEN 1 ELSE 0 END) AS utama,
                SUM(CASE WHEN kth.kelas = 'Madya' THEN 1 ELSE 0 END) AS madya,
                SUM(CASE WHEN kth.kelas = 'Pemula' THEN 1 ELSE 0 END) AS pemula,
                COUNT(kth.id) AS total_kth
            FROM kth
            INNER JOIN kabupaten kb ON kth.kabupaten_id = kb.id
            WHERE kth.is_active = 1
            GROUP BY kb.id, kb.nama
            ORDER BY kb.nama";

    $rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    $data = [];
    foreach ($rows as $r) {
        $data[] = [
            'kabupaten' => $r['kabupaten'],
            'total_kth' => (int) $r['total_kth'],
            'kelas' => [
                'utama' => (int) $r['utama'],
                'madya' => (int) $r['madya'],
                'pemula' => (int) $r['pemula'],
            ],
        ];
    }

    sendJson(['status' => 'ok', 'data' => $data]);
} catch (\Throwable $e) {
    sendError('Gagal memuat data distribusi');
}
