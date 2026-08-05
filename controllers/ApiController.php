<?php

declare(strict_types=1);

class ApiController
{
    public function kecamatan(): void
    {
        requireLogin();
        header('Content-Type: application/json; charset=utf-8');

        $kabId = isset($_GET['kabupaten_id']) ? (int) $_GET['kabupaten_id'] : 0;
        if ($kabId <= 0) {
            echo json_encode([], JSON_UNESCAPED_UNICODE);

            return;
        }

        if (user_role() === 'operator') {
            $own = user_kabupaten_id();
            if ($own !== null && $kabId !== $own) {
                http_response_code(403);
                echo json_encode(['error' => 'forbidden'], JSON_UNESCAPED_UNICODE);

                return;
            }
        }

        $pdo = Database::connect();
        $m = new Kth($pdo);
        echo json_encode($m->listKecamatanByKabupaten($kabId), JSON_UNESCAPED_UNICODE);
    }

    public function desa(): void
    {
        requireLogin();
        header('Content-Type: application/json; charset=utf-8');

        $kecId = isset($_GET['kecamatan_id']) ? (int) $_GET['kecamatan_id'] : 0;
        if ($kecId <= 0) {
            echo json_encode([], JSON_UNESCAPED_UNICODE);

            return;
        }

        $pdo = Database::connect();
        $stmt = $pdo->prepare('SELECT kabupaten_id FROM kecamatan WHERE id = ? LIMIT 1');
        $stmt->execute([$kecId]);
        $kabOfKec = $stmt->fetchColumn();
        if ($kabOfKec === false) {
            echo json_encode([], JSON_UNESCAPED_UNICODE);

            return;
        }
        $kabOfKec = (int) $kabOfKec;

        if (user_role() === 'operator') {
            $own = user_kabupaten_id();
            if ($own !== null && $kabOfKec !== $own) {
                http_response_code(403);
                echo json_encode(['error' => 'forbidden'], JSON_UNESCAPED_UNICODE);

                return;
            }
        }

        $m = new Kth($pdo);
        echo json_encode($m->listDesaByKecamatan($kecId), JSON_UNESCAPED_UNICODE);
    }

    public function kthSearch(): void
    {
        requireLogin();
        header('Content-Type: application/json; charset=utf-8');

        $q = isset($_GET['q']) ? trim((string) $_GET['q']) : '';
        $kabId = isset($_GET['kabupaten_id']) ? (int) $_GET['kabupaten_id'] : 0;

        if (strlen($q) < 2) {
            echo json_encode([], JSON_UNESCAPED_UNICODE);
            return;
        }

        if (user_role() === 'operator') {
            $own = user_kabupaten_id();
            if ($own !== null) {
                $kabId = (int) $own;
            }
        }

        $pdo = Database::connect();

        $where = ['kth.is_active = 1'];
        $params = [];
        if ($kabId > 0) {
            $where[] = 'kth.kabupaten_id = ?';
            $params[] = $kabId;
        }
        $where[] = '(kth.nama LIKE ? OR kth.kode_register LIKE ?)';
        $like = '%' . $q . '%';
        $params[] = $like;
        $params[] = $like;

        $sql = 'SELECT kth.id, kth.nama, kth.kode_register, kb.nama AS kabupaten_nama
            FROM kth
            INNER JOIN kabupaten kb ON kth.kabupaten_id = kb.id
            WHERE ' . implode(' AND ', $where) . '
            ORDER BY kb.nama ASC, kth.nama ASC
            LIMIT 20';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        echo json_encode($stmt->fetchAll(\PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE);
    }

    /**
     * GET /api/pelaksana?kabupaten_id=X
     * Returns list of KTH + KPS names for a given kabupaten (for datalist autocomplete).
     */
    public function pelaksana(): void
    {
        requireLogin();
        header('Content-Type: application/json; charset=utf-8');

        $kabId = isset($_GET['kabupaten_id']) ? (int) $_GET['kabupaten_id'] : 0;

        if (user_role() === 'operator') {
            $own = user_kabupaten_id();
            if ($own !== null) {
                $kabId = (int) $own;
            }
        }

        $pdo = Database::connect();
        $results = [];

        if ($kabId > 0) {
            $stmt = $pdo->prepare('SELECT nama FROM kth WHERE is_active = 1 AND kabupaten_id = ? ORDER BY nama');
            $stmt->execute([$kabId]);
            $results = $stmt->fetchAll(\PDO::FETCH_COLUMN);

            $stmt2 = $pdo->prepare('SELECT nama_lembaga FROM kps WHERE kabupaten_id = ? ORDER BY nama_lembaga');
            $stmt2->execute([$kabId]);
            $kpsNames = $stmt2->fetchAll(\PDO::FETCH_COLUMN);

            $results = array_values(array_unique(array_merge($results, $kpsNames)));
            sort($results);
        } else {
            $stmt = $pdo->query('SELECT nama FROM kth WHERE is_active = 1 ORDER BY nama');
            $results = $stmt->fetchAll(\PDO::FETCH_COLUMN);

            $stmt2 = $pdo->query('SELECT nama_lembaga FROM kps ORDER BY nama_lembaga');
            $kpsNames = $stmt2->fetchAll(\PDO::FETCH_COLUMN);

            $results = array_values(array_unique(array_merge($results, $kpsNames)));
            sort($results);
        }

        echo json_encode($results, JSON_UNESCAPED_UNICODE);
    }

    /**
     * GET /api/kps-search?q=keyword&kabupaten_id=X
     * Returns KPS list for searchable combobox.
     */
    public function kpsSearch(): void
    {
        requireLogin();
        header('Content-Type: application/json; charset=utf-8');

        $q = isset($_GET['q']) ? trim((string) $_GET['q']) : '';
        $kabId = isset($_GET['kabupaten_id']) ? (int) $_GET['kabupaten_id'] : 0;

        if (strlen($q) < 2) {
            echo json_encode([], JSON_UNESCAPED_UNICODE);
            return;
        }

        if (user_role() === 'operator') {
            $own = user_kabupaten_id();
            if ($own !== null) {
                $kabId = (int) $own;
            }
        }

        $pdo = Database::connect();

        $where = ['1=1'];
        $params = [];
        if ($kabId > 0) {
            $where[] = 'kps.kabupaten_id = ?';
            $params[] = $kabId;
        }
        $where[] = '(kps.nama_lembaga LIKE ? OR kps.no_sk LIKE ?)';
        $like = '%' . $q . '%';
        $params[] = $like;
        $params[] = $like;

        $sql = 'SELECT kps.id, kps.nama_lembaga, kps.skema, kb.nama AS kabupaten_nama
            FROM kps
            INNER JOIN kabupaten kb ON kps.kabupaten_id = kb.id
            WHERE ' . implode(' AND ', $where) . '
            ORDER BY kb.nama ASC, kps.nama_lembaga ASC
            LIMIT 20';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        echo json_encode($stmt->fetchAll(\PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE);
    }
}
