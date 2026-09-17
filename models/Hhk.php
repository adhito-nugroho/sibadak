<?php

declare(strict_types=1);

class Hhk extends BaseModel
{
    protected string $table = 'hhk';

    public static function availableYears(): array { return range(2020, (int) date('Y')); }

    public static function bulanList(): array
    {
        return [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'Mei',6=>'Jun',
                7=>'Jul',8=>'Agt',9=>'Sep',10=>'Okt',11=>'Nov',12=>'Des'];
    }

    /**
     * @param array{kabupaten_id?:int,tahun?:int,q?:string,operator_kab_id?:int} $filters
     */
    public function paginateIndex(int $page, int $perPage, array $filters): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset = ($page - 1) * $perPage;
        $where = ['1=1']; $params = [];

        if (!empty($filters['operator_kab_id'])) { $where[] = 'h.kabupaten_id = ?'; $params[] = (int) $filters['operator_kab_id']; }
        elseif (!empty($filters['kabupaten_id'])) { $where[] = 'h.kabupaten_id = ?'; $params[] = (int) $filters['kabupaten_id']; }
        if (!empty($filters['tahun'])) { $where[] = 'h.tahun = ?'; $params[] = (int) $filters['tahun']; }
        if (!empty($filters['q'])) {
            $where[] = '(h.nama_kth LIKE ? OR h.nama_penyuluh LIKE ?)';
            $like = '%' . $filters['q'] . '%';
            $params[] = $like; $params[] = $like;
        }

        $whereSql = implode(' AND ', $where);
        $stmtC = $this->pdo->prepare("SELECT COUNT(*) FROM hhk h WHERE $whereSql");
        $stmtC->execute($params);
        $total = (int) $stmtC->fetchColumn();
        $pages = $total > 0 ? (int) ceil($total / $perPage) : 1;

        $sql = "SELECT h.*, kb.nama AS kabupaten_nama, kc.nama AS kecamatan_nama, ds.nama AS desa_nama
            FROM hhk h
            LEFT JOIN kabupaten kb ON h.kabupaten_id = kb.id
            LEFT JOIN kecamatan kc ON h.kecamatan_id = kc.id
            LEFT JOIN desa ds ON h.desa_id = ds.id
            WHERE $whereSql ORDER BY h.tahun DESC, h.bulan DESC, h.id DESC
            LIMIT $perPage OFFSET $offset";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return ['data' => $stmt->fetchAll(\PDO::FETCH_ASSOC), 'total' => $total, 'pages' => $pages, 'current' => $page];
    }

    public function findWithRelations(int $id): array|false
    {
        $sql = "SELECT h.*, kb.nama AS kabupaten_nama, kc.nama AS kecamatan_nama, ds.nama AS desa_nama
            FROM hhk h
            LEFT JOIN kabupaten kb ON h.kabupaten_id = kb.id
            LEFT JOIN kecamatan kc ON h.kecamatan_id = kc.id
            LEFT JOIN desa ds ON h.desa_id = ds.id
            WHERE h.id = ? LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row === false ? false : $row;
    }

    public function getDetail(int $hhkId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM hhk_detail WHERE hhk_id = ? ORDER BY id');
        $stmt->execute([$hhkId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Auto-calculate s.d. bulan lalu dari database.
     * Sum semua volume untuk jenis_kayu yang sama di hhk_id yang punya bulan < bulan saat ini dan tahun sama.
     */
    public function getSdBulanLalu(int $hhkId, string $jenisKayu): float
    {
        // Ambil bulan & tahun dari hhk header
        $header = $this->findById($hhkId);
        if ($header === false) return 0.0;

        $bulan = (int) $header['bulan'];
        $tahun = (int) $header['tahun'];
        $kabupatenId = (int) $header['kabupaten_id'];

        // Sum semua bulan sebelumnya di tahun yang sama, kabupaten yang sama, jenis_kayu yang sama
        $sql = 'SELECT COALESCE(SUM(d.volume_bulan_ini_m3), 0)
                FROM hhk_detail d
                JOIN hhk h ON d.hhk_id = h.id
                WHERE h.tahun = ? AND h.bulan < ? AND h.kabupaten_id = ?
                AND d.jenis_kayu = ?';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$tahun, $bulan, $kabupatenId, $jenisKayu]);
        return (float) $stmt->fetchColumn();
    }

    /** @return list<string> Daftar jenis kayu dari master komoditas */
    public function getMasterKomoditas(): array
    {
        try {
            $stmt = $this->pdo->query('SELECT nama FROM hhk_komoditas WHERE is_active = 1 ORDER BY urutan');
            return $stmt->fetchAll(\PDO::FETCH_COLUMN);
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function addDetail(int $hhkId, string $jenisKayu, float $bulanIni, float $sdBulanLalu, float $sdBulanIni): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO hhk_detail (hhk_id, jenis_kayu, volume_bulan_ini_m3, volume_sd_bulan_lalu_m3, volume_sd_bulan_ini_m3) VALUES (?,?,?,?,?)');
        $stmt->execute([$hhkId, $jenisKayu, $bulanIni, $sdBulanLalu, $sdBulanIni]);
        return (int) $this->pdo->lastInsertId();
    }public function deleteDetail(int $detailId): void
    {
        $this->pdo->prepare('DELETE FROM hhk_detail WHERE id = ?')->execute([$detailId]);
    }

    public function getDetailById(int $detailId): array|false
    {
        $stmt = $this->pdo->prepare('SELECT * FROM hhk_detail WHERE id = ?');
        $stmt->execute([$detailId]);
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
        $where = 'h.id IN (' . $in['sql'] . ')';
        if ($operatorKabId !== null) {
            $where .= ' AND h.kabupaten_id = ?';
            $params[] = $operatorKabId;
        }
        $sql = 'SELECT h.*, kb.nama AS kabupaten_nama, kc.nama AS kecamatan_nama, ds.nama AS desa_nama
            FROM hhk h
            LEFT JOIN kabupaten kb ON h.kabupaten_id = kb.id
            LEFT JOIN kecamatan kc ON h.kecamatan_id = kc.id
            LEFT JOIN desa ds ON h.desa_id = ds.id
            WHERE ' . $where . '
            ORDER BY h.tahun DESC, h.bulan DESC, h.id DESC';

        return $this->query($sql, $params);
    }
}
