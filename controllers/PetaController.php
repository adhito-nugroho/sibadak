<?php

declare(strict_types=1);

class PetaController
{
    private function pdo(): \PDO
    {
        return Database::connect();
    }

    public function index(): void
    {
        requireLogin();

        $kabupatenList = (new Kth($this->pdo()))->listKabupatenForFilter();

        $pageTitle = 'Peta Wilayah';
        $activeNav = 'peta';

        ob_start();
        require view_path('peta/index.php');
        $content = ob_get_clean();

        require view_path('layouts/main.php');
    }

    /**
     * GET /api/peta?layer=kth|dpn|gully_plug|upsa&kabupaten_id=X
     * Returns GeoJSON FeatureCollection.
     */
    public function apiData(): void
    {
        requireLogin();
        header('Content-Type: application/json; charset=utf-8');

        $layer = trim((string) ($_GET['layer'] ?? ''));
        $kabId = isset($_GET['kabupaten_id']) ? (int) $_GET['kabupaten_id'] : 0;

        $pdo = $this->pdo();
        $features = [];

        switch ($layer) {
            case 'kth':
                $where = ['kth.is_active = 1', 'kth.koordinat_ls IS NOT NULL', 'kth.koordinat_bt IS NOT NULL'];
                $params = [];
                if ($kabId > 0) {
                    $where[] = 'kth.kabupaten_id = ?';
                    $params[] = $kabId;
                }
                if (user_role() === 'operator' && user_kabupaten_id() !== null) {
                    $where[] = 'kth.kabupaten_id = ?';
                    $params[] = user_kabupaten_id();
                }
                $sql = 'SELECT kth.id, kth.nama, kth.kelas, kth.koordinat_ls, kth.koordinat_bt, kth.jumlah_anggota,
                               kb.nama AS kabupaten, kc.nama AS kecamatan, ds.nama AS desa
                        FROM kth
                        INNER JOIN kabupaten kb ON kth.kabupaten_id = kb.id
                        INNER JOIN kecamatan kc ON kth.kecamatan_id = kc.id
                        INNER JOIN desa ds ON kth.desa_id = ds.id
                        WHERE ' . implode(' AND ', $where) . '
                        ORDER BY kb.nama, kth.nama';
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $r) {
                    $features[] = [
                        'type' => 'Feature',
                        'geometry' => ['type' => 'Point', 'coordinates' => [(float) $r['koordinat_bt'], (float) $r['koordinat_ls']]],
                        'properties' => [
                            'id' => (int) $r['id'],
                            'nama' => $r['nama'],
                            'kelas' => $r['kelas'],
                            'kabupaten' => $r['kabupaten'],
                            'kecamatan' => $r['kecamatan'],
                            'desa' => $r['desa'],
                            'anggota' => (int) $r['jumlah_anggota'],
                            'layer' => 'kth',
                        ],
                    ];
                }
                break;

            case 'dpn':
                $where = ['dpn.koordinat_ls IS NOT NULL', 'dpn.koordinat_bt IS NOT NULL'];
                $params = [];
                $sql = 'SELECT dpn.id, dpn.sasaran, dpn.lokasi, dpn.koordinat_ls, dpn.koordinat_bt,
                               dpn.jumlah_unit, dpn.tahun, ds.nama AS desa
                        FROM dpn
                        LEFT JOIN desa ds ON dpn.desa_id = ds.id
                        WHERE ' . implode(' AND ', $where);
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $r) {
                    $features[] = [
                        'type' => 'Feature',
                        'geometry' => ['type' => 'Point', 'coordinates' => [(float) $r['koordinat_bt'], (float) $r['koordinat_ls']]],
                        'properties' => [
                            'id' => (int) $r['id'],
                            'nama' => $r['sasaran'] ?? 'DPN',
                            'lokasi' => $r['lokasi'],
                            'desa' => $r['desa'],
                            'jumlah_unit' => (int) $r['jumlah_unit'],
                            'tahun' => (int) $r['tahun'],
                            'layer' => 'dpn',
                        ],
                    ];
                }
                break;

            case 'gully_plug':
                $where = ['gully_plug.koordinat_ls IS NOT NULL', 'gully_plug.koordinat_bt IS NOT NULL'];
                $params = [];
                $sql = 'SELECT gully_plug.id, gully_plug.sasaran, gully_plug.lokasi, gully_plug.koordinat_ls, gully_plug.koordinat_bt,
                               gully_plug.jumlah_unit, gully_plug.tahun, ds.nama AS desa
                        FROM gully_plug
                        LEFT JOIN desa ds ON gully_plug.desa_id = ds.id
                        WHERE ' . implode(' AND ', $where);
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $r) {
                    $features[] = [
                        'type' => 'Feature',
                        'geometry' => ['type' => 'Point', 'coordinates' => [(float) $r['koordinat_bt'], (float) $r['koordinat_ls']]],
                        'properties' => [
                            'id' => (int) $r['id'],
                            'nama' => $r['sasaran'] ?? 'Gully Plug',
                            'lokasi' => $r['lokasi'],
                            'desa' => $r['desa'],
                            'jumlah_unit' => (int) $r['jumlah_unit'],
                            'tahun' => (int) $r['tahun'],
                            'layer' => 'gully_plug',
                        ],
                    ];
                }
                break;

            case 'upsa':
                $where = ['upsa.koordinat_ls IS NOT NULL', 'upsa.koordinat_bt IS NOT NULL'];
                $params = [];
                $sql = 'SELECT upsa.id, upsa.sasaran, upsa.lokasi, upsa.koordinat_ls, upsa.koordinat_bt,
                               upsa.jenis_tanaman, upsa.jumlah, ds.nama AS desa
                        FROM upsa
                        LEFT JOIN desa ds ON upsa.desa_id = ds.id
                        WHERE ' . implode(' AND ', $where);
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $r) {
                    $features[] = [
                        'type' => 'Feature',
                        'geometry' => ['type' => 'Point', 'coordinates' => [(float) $r['koordinat_bt'], (float) $r['koordinat_ls']]],
                        'properties' => [
                            'id' => (int) $r['id'],
                            'nama' => $r['sasaran'] ?? 'UPSA',
                            'lokasi' => $r['lokasi'],
                            'desa' => $r['desa'],
                            'jenis_tanaman' => $r['jenis_tanaman'],
                            'jumlah' => (int) ($r['jumlah'] ?? 0),
                            'layer' => 'upsa',
                        ],
                    ];
                }
                break;

            case 'rhl':
                $where = ['rhl.koordinat_ls IS NOT NULL', 'rhl.koordinat_bt IS NOT NULL'];
                $params = [];
                if ($kabId > 0) {
                    $where[] = 'rhl.kabupaten_id = ?';
                    $params[] = $kabId;
                }
                if (user_role() === 'operator' && user_kabupaten_id() !== null) {
                    $where[] = 'rhl.kabupaten_id = ?';
                    $params[] = user_kabupaten_id();
                }
                $sql = 'SELECT rhl.id, rhl.nama_kth, rhl.kegiatan, rhl.tahun, rhl.luas_ha,
                               rhl.koordinat_ls, rhl.koordinat_bt, kb.nama AS kabupaten
                        FROM rhl
                        INNER JOIN kabupaten kb ON rhl.kabupaten_id = kb.id
                        WHERE ' . implode(' AND ', $where);
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $r) {
                    $features[] = [
                        'type' => 'Feature',
                        'geometry' => ['type' => 'Point', 'coordinates' => [(float) $r['koordinat_bt'], (float) $r['koordinat_ls']]],
                        'properties' => [
                            'id' => (int) $r['id'],
                            'nama' => $r['nama_kth'] ?? 'RHL',
                            'kegiatan' => $r['kegiatan'],
                            'kabupaten' => $r['kabupaten'],
                            'tahun' => (int) $r['tahun'],
                            'luas_ha' => $r['luas_ha'] !== null ? (float) $r['luas_ha'] : null,
                            'layer' => 'rhl',
                        ],
                    ];
                }
                break;

            case 'kbr':
                $where = ['kbr.koordinat_ls IS NOT NULL', 'kbr.koordinat_bt IS NOT NULL'];
                $params = [];
                $sql = 'SELECT kbr.id, kbr.nama_kth, kbr.tahun_tanam, kbr.subdas,
                               kbr.koordinat_ls, kbr.koordinat_bt, ds.nama AS desa
                        FROM kbr
                        LEFT JOIN desa ds ON kbr.desa_id = ds.id
                        WHERE ' . implode(' AND ', $where);
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $r) {
                    $features[] = [
                        'type' => 'Feature',
                        'geometry' => ['type' => 'Point', 'coordinates' => [(float) $r['koordinat_bt'], (float) $r['koordinat_ls']]],
                        'properties' => [
                            'id' => (int) $r['id'],
                            'nama' => $r['nama_kth'] ?? 'KBR',
                            'desa' => $r['desa'],
                            'subdas' => $r['subdas'],
                            'tahun' => $r['tahun_tanam'] !== null ? (int) $r['tahun_tanam'] : null,
                            'layer' => 'kbr',
                        ],
                    ];
                }
                break;

            case 'aep':
                // AEP tidak punya koordinat sendiri, tapi bisa ambil dari KTH terkait
                $where = ['kth.koordinat_ls IS NOT NULL', 'kth.koordinat_bt IS NOT NULL', 'aep.kth_id IS NOT NULL'];
                $params = [];
                if ($kabId > 0) {
                    $where[] = 'aep.kabupaten_id = ?';
                    $params[] = $kabId;
                }
                if (user_role() === 'operator' && user_kabupaten_id() !== null) {
                    $where[] = 'aep.kabupaten_id = ?';
                    $params[] = user_kabupaten_id();
                }
                $sql = 'SELECT aep.id, aep.nama_kth, aep.jenis_bantuan, aep.jumlah, aep.tahun,
                               kth.koordinat_ls, kth.koordinat_bt, kb.nama AS kabupaten
                        FROM aep
                        INNER JOIN kth ON aep.kth_id = kth.id
                        INNER JOIN kabupaten kb ON aep.kabupaten_id = kb.id
                        WHERE ' . implode(' AND ', $where);
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $r) {
                    $features[] = [
                        'type' => 'Feature',
                        'geometry' => ['type' => 'Point', 'coordinates' => [(float) $r['koordinat_bt'], (float) $r['koordinat_ls']]],
                        'properties' => [
                            'id' => (int) $r['id'],
                            'nama' => $r['nama_kth'] ?? 'AEP',
                            'jenis_bantuan' => $r['jenis_bantuan'],
                            'jumlah' => (int) $r['jumlah'],
                            'kabupaten' => $r['kabupaten'],
                            'tahun' => (int) $r['tahun'],
                            'layer' => 'aep',
                        ],
                    ];
                }
                break;
        }

        echo json_encode(['type' => 'FeatureCollection', 'features' => $features], JSON_UNESCAPED_UNICODE);
    }
}
