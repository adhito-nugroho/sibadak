<?php

declare(strict_types=1);

class Rhl extends BaseModel
{
    protected string $table = 'rhl';

    /** @return list<string> */
    public static function kegiatanList(): array
    {
        return [
            'Penanaman Hutan Rakyat',
            'Agroforestry Luar Kawasan Hutan',
            'Penghijauan Lingkungan',
            'RHL Mangrove',
            'RHL Mangrove Folu Net Sink',
            'Agroforestry Pada Areal Perhutanan Sosial',
            'Fape',
            'Penanaman Bibit Produktif',
            'Penanaman Pesisir Pantai',
            'Penanaman Eksternal',
            'Rehabilitasi Dalam Kawasan PS',
        ];
    }

    /** @return list<int> */
    public static function availableYears(): array
    {
        return range(2020, 2035);
    }

    /** @return list<string> */
    public static function sumberDanaList(): array
    {
        return ['APBD', 'APBN', 'Swadaya', 'CSR'];
    }

    /**
     * @param array{kabupaten_id?:int,tahun?:int,kegiatan?:string,q?:string,operator_kab_id?:int,sumber_dana?:string} $filters
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
            $where[] = 'rhl.kabupaten_id = ?';
            $params[] = (int) $filters['operator_kab_id'];
        } elseif (!empty($filters['kabupaten_id'])) {
            $where[] = 'rhl.kabupaten_id = ?';
            $params[] = (int) $filters['kabupaten_id'];
        }
        if (!empty($filters['tahun'])) {
            $where[] = 'rhl.tahun = ?';
            $params[] = (int) $filters['tahun'];
        }
        if (!empty($filters['kegiatan']) && in_array($filters['kegiatan'], self::kegiatanList(), true)) {
            $where[] = 'rhl.kegiatan = ?';
            $params[] = $filters['kegiatan'];
        }
        if (!empty($filters['sumber_dana']) && in_array($filters['sumber_dana'], self::sumberDanaList(), true)) {
            $where[] = 'rhl.sumber_dana = ?';
            $params[] = $filters['sumber_dana'];
        }
        if (!empty($filters['q'])) {
            $where[] = '(rhl.nama_kth LIKE ? OR rhl.kegiatan LIKE ?)';
            $like = '%' . $filters['q'] . '%';
            $params[] = $like;
            $params[] = $like;
        }

        $whereSql = implode(' AND ', $where);
        $countStmt = $this->pdo->prepare('SELECT COUNT(*) FROM rhl WHERE ' . $whereSql);
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();
        $pages = $total > 0 ? (int) ceil($total / $perPage) : 1;

        $sql = 'SELECT rhl.*, kb.nama AS kabupaten_nama, ds.nama AS desa_nama
            FROM rhl
            INNER JOIN kabupaten kb ON rhl.kabupaten_id = kb.id
            LEFT JOIN desa ds ON rhl.desa_id = ds.id
            WHERE ' . $whereSql . '
            ORDER BY rhl.tahun DESC, rhl.id DESC
            LIMIT ' . $perPage . ' OFFSET ' . $offset;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return ['data' => $rows, 'total' => $total, 'pages' => $pages, 'current' => $page];
    }

    /** @return array<string,mixed>|false */
    public function findWithRelations(int $id): array|false
    {
        $sql = 'SELECT rhl.*, kb.nama AS kabupaten_nama, ds.nama AS desa_nama
            FROM rhl
            INNER JOIN kabupaten kb ON rhl.kabupaten_id = kb.id
            LEFT JOIN desa ds ON rhl.desa_id = ds.id
            WHERE rhl.id = ?
            LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $row === false ? false : $row;
    }

    /** @return list<array<string,mixed>> */
    public function getBibit(int $rhlId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM rhl_bibit WHERE rhl_id = ? ORDER BY id ASC');
        $stmt->execute([$rhlId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function addBibit(int $rhlId, string $jenis, int $jumlah): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO rhl_bibit (rhl_id, jenis_bibit, jumlah_btg) VALUES (?, ?, ?)');
        $stmt->execute([$rhlId, $jenis, $jumlah]);
        return (int) $this->pdo->lastInsertId();
    }

    public function deleteBibit(int $bibitId): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM rhl_bibit WHERE id = ?');
        $stmt->execute([$bibitId]);
    }

    /** @return array<string,mixed>|false */
    public function getBibitById(int $bibitId): array|false
    {
        $stmt = $this->pdo->prepare('SELECT * FROM rhl_bibit WHERE id = ?');
        $stmt->execute([$bibitId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row === false ? false : $row;
    }

    /**
     * @param list<int> $ids
     * @return list<array<string,mixed>>
     */
    public function findByIds(array $ids, ?int $operatorKabId = null): array
    {
        $ids = bulk_parse_ids($ids);
        if ($ids === []) {
            return [];
        }
        $in = bulk_in_clause($ids);
        $params = $in['params'];
        $where = 'rhl.id IN (' . $in['sql'] . ')';
        if ($operatorKabId !== null) {
            $where .= ' AND rhl.kabupaten_id = ?';
            $params[] = $operatorKabId;
        }
        $sql = 'SELECT rhl.*, kb.nama AS kabupaten_nama, ds.nama AS desa_nama
            FROM rhl
            INNER JOIN kabupaten kb ON rhl.kabupaten_id = kb.id
            LEFT JOIN desa ds ON rhl.desa_id = ds.id
            WHERE ' . $where . '
            ORDER BY rhl.tahun DESC, rhl.id DESC';

        return $this->query($sql, $params);
    }
}
