<?php

declare(strict_types=1);

class Hhbk extends BaseModel
{
    protected string $table = 'hhbk';

    public static function availableYears(): array { return range(2020, (int) date('Y')); }

    public static function bulanList(): array
    {
        return [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'Mei',6=>'Jun',
                7=>'Jul',8=>'Agt',9=>'Sep',10=>'Okt',11=>'Nov',12=>'Des'];
    }

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
        $stmtC = $this->pdo->prepare("SELECT COUNT(*) FROM hhbk h WHERE $whereSql");
        $stmtC->execute($params);
        $total = (int) $stmtC->fetchColumn();
        $pages = $total > 0 ? (int) ceil($total / $perPage) : 1;

        $sql = "SELECT h.*, kb.nama AS kabupaten_nama, kc.nama AS kecamatan_nama, ds.nama AS desa_nama
            FROM hhbk h
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
            FROM hhbk h
            LEFT JOIN kabupaten kb ON h.kabupaten_id = kb.id
            LEFT JOIN kecamatan kc ON h.kecamatan_id = kc.id
            LEFT JOIN desa ds ON h.desa_id = ds.id
            WHERE h.id = ? LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row === false ? false : $row;
    }

    public function getDetail(int $hhbkId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM hhbk_detail WHERE hhbk_id = ? ORDER BY id');
        $stmt->execute([$hhbkId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Auto-calculate s.d. bulan lalu dari database.
     */
    public function getSdBulanLalu(int $hhbkId, string $komoditas, string $satuan): float
    {
        $header = $this->findById($hhbkId);
        if ($header === false) return 0.0;

        $bulan = (int) $header['bulan'];
        $tahun = (int) $header['tahun'];
        $kabupatenId = (int) $header['kabupaten_id'];

        $sql = 'SELECT COALESCE(SUM(d.jumlah_bulan_ini), 0)
                FROM hhbk_detail d
                JOIN hhbk h ON d.hhbk_id = h.id
                WHERE h.tahun = ? AND h.bulan < ? AND h.kabupaten_id = ?
                AND d.komoditas = ? AND d.satuan = ?';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$tahun, $bulan, $kabupatenId, $komoditas, $satuan]);
        return (float) $stmt->fetchColumn();
    }

    /** @return list<array{nama:string,satuan:string}> */
    public function getMasterKomoditas(): array
    {
        try {
            $stmt = $this->pdo->query('SELECT nama, satuan FROM hhbk_komoditas WHERE is_active = 1 ORDER BY urutan');
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function addDetail(int $hhbkId, string $komoditas, string $satuan, float $bulanIni, float $sdBulanLalu, float $sdBulanIni): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO hhbk_detail (hhbk_id, komoditas, satuan, jumlah_bulan_ini, jumlah_sd_bulan_lalu, jumlah_sd_bulan_ini) VALUES (?,?,?,?,?,?)');
        $stmt->execute([$hhbkId, $komoditas, $satuan, $bulanIni, $sdBulanLalu, $sdBulanIni]);
        return (int) $this->pdo->lastInsertId();
    }

    public function deleteDetail(int $detailId): void
    {
        $this->pdo->prepare('DELETE FROM hhbk_detail WHERE id = ?')->execute([$detailId]);
    }

    public function getDetailById(int $detailId): array|false
    {
        $stmt = $this->pdo->prepare('SELECT * FROM hhbk_detail WHERE id = ?');
        $stmt->execute([$detailId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row === false ? false : $row;
    }
}
