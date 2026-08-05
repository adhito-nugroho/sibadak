<?php

declare(strict_types=1);

class Kbr extends BaseModel
{
    protected string $table = 'kbr';

    /** @return list<int> */
    public static function availableYears(): array
    {
        return range(2015, 2035);
    }

    /**
     * @param array{kabupaten_id?:int,tahun?:int,q?:string,operator_kab_id?:int} $filters
     * @return array{data:list<array<string,mixed>>,total:int,pages:int,current:int}
     */
    public function paginateIndex(int $page, int $perPage, array $filters): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset = ($page - 1) * $perPage;

        $where = ['1=1'];
        $params = [];

        if (!empty($filters['operator_kab_id'])) {
            $where[] = 'kbr.kth_id IN (SELECT id FROM kth WHERE kabupaten_id = ?)';
            $params[] = (int) $filters['operator_kab_id'];
        } elseif (!empty($filters['kabupaten_id'])) {
            $where[] = 'kbr.kth_id IN (SELECT id FROM kth WHERE kabupaten_id = ?)';
            $params[] = (int) $filters['kabupaten_id'];
        }
        if (!empty($filters['tahun'])) {
            $where[] = 'kbr.tahun_tanam = ?';
            $params[] = (int) $filters['tahun'];
        }
        if (!empty($filters['q'])) {
            $where[] = '(kbr.nama_kth LIKE ? OR kbr.lokasi LIKE ? OR kbr.subdas LIKE ?)';
            $like = '%' . $filters['q'] . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $whereSql = implode(' AND ', $where);
        $countStmt = $this->pdo->prepare('SELECT COUNT(*) FROM kbr WHERE ' . $whereSql);
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();
        $pages = $total > 0 ? (int) ceil($total / $perPage) : 1;

        $sql = 'SELECT kbr.*, ds.nama AS desa_nama,
                kec.nama AS kecamatan_nama, kb.nama AS kabupaten_nama
            FROM kbr
            LEFT JOIN desa ds ON kbr.desa_id = ds.id
            LEFT JOIN kecamatan kec ON ds.kecamatan_id = kec.id
            LEFT JOIN kabupaten kb ON kec.kabupaten_id = kb.id
            WHERE ' . $whereSql . '
            ORDER BY kbr.tahun_tanam DESC, kbr.id DESC
            LIMIT ' . $perPage . ' OFFSET ' . $offset;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return ['data' => $rows, 'total' => $total, 'pages' => $pages, 'current' => $page];
    }

    /** @return array<string,mixed>|false */
    public function findWithRelations(int $id): array|false
    {
        $sql = 'SELECT kbr.*, ds.nama AS desa_nama,
                kec.nama AS kecamatan_nama, kb.nama AS kabupaten_nama
            FROM kbr
            LEFT JOIN desa ds ON kbr.desa_id = ds.id
            LEFT JOIN kecamatan kec ON ds.kecamatan_id = kec.id
            LEFT JOIN kabupaten kb ON kec.kabupaten_id = kb.id
            WHERE kbr.id = ?
            LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $row === false ? false : $row;
    }

    /** @return list<array<string,mixed>> */
    public function getTanaman(int $kbrId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM kbr_tanaman WHERE kbr_id = ? ORDER BY id ASC');
        $stmt->execute([$kbrId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function addTanaman(int $kbrId, string $jenis, int $jumlah, ?float $luas): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO kbr_tanaman (kbr_id, jenis, jumlah_btg, luas_ha) VALUES (?, ?, ?, ?)');
        $stmt->execute([$kbrId, $jenis, $jumlah, $luas]);
        return (int) $this->pdo->lastInsertId();
    }

    public function deleteTanaman(int $tanamanId): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM kbr_tanaman WHERE id = ?');
        $stmt->execute([$tanamanId]);
    }

    /** @return array<string,mixed>|false */
    public function getTanamanById(int $tanamanId): array|false
    {
        $stmt = $this->pdo->prepare('SELECT * FROM kbr_tanaman WHERE id = ?');
        $stmt->execute([$tanamanId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row === false ? false : $row;
    }

    /**
     * Determine kabupaten_id for a KBR row (via desa → kecamatan → kabupaten).
     */
    public function getKabupatenId(int $kbrId): ?int
    {
        $sql = 'SELECT kb.id FROM kbr
            LEFT JOIN desa ds ON kbr.desa_id = ds.id
            LEFT JOIN kecamatan kec ON ds.kecamatan_id = kec.id
            LEFT JOIN kabupaten kb ON kec.kabupaten_id = kb.id
            WHERE kbr.id = ? LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$kbrId]);
        $val = $stmt->fetchColumn();
        return $val !== false && $val !== null ? (int) $val : null;
    }
}
