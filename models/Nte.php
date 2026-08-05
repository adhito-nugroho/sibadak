<?php

declare(strict_types=1);

class Nte extends BaseModel
{
    protected string $table = 'nte';

    // Bulan mapping
    public static function bulanList(): array
    {
        return [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',
                7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
    }

    /**
     * @param array{kabupaten_id?:int,kth_id?:int,tahun?:int,bulan?:int,q?:string,operator_kab_id?:int} $filters
     * @return array{data:list<array<string,mixed>>,total:int,pages:int,current:int}
     */
    public function paginateIndex(int $page, int $perPage, array $filters): array
    {
        $page   = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset  = ($page - 1) * $perPage;

        $where  = ['1=1'];
        $params = [];

        if (!empty($filters['operator_kab_id'])) {
            $where[] = 'n.kabupaten_id = ?';
            $params[] = (int) $filters['operator_kab_id'];
        } elseif (!empty($filters['kabupaten_id'])) {
            $where[] = 'n.kabupaten_id = ?';
            $params[] = (int) $filters['kabupaten_id'];
        }
        if (!empty($filters['kth_id'])) {
            $where[] = 'n.kth_id = ?';
            $params[] = (int) $filters['kth_id'];
        }
        if (!empty($filters['tahun'])) {
            $where[] = 'n.tahun = ?';
            $params[] = (int) $filters['tahun'];
        }
        if (!empty($filters['bulan'])) {
            $where[] = 'n.bulan = ?';
            $params[] = (int) $filters['bulan'];
        }
        if (!empty($filters['q'])) {
            $where[] = '(n.nama_kth LIKE ? OR n.jenis_barang LIKE ? OR n.produk LIKE ?)';
            $like = '%' . $filters['q'] . '%';
            $params[] = $like; $params[] = $like; $params[] = $like;
        }

        $whereSql = implode(' AND ', $where);
        $total = (int) $this->pdo->prepare("SELECT COUNT(*) FROM nte n WHERE $whereSql")->execute($params) ?
            (function() use ($whereSql, $params) {
                $s = $this->pdo->prepare("SELECT COUNT(*) FROM nte n WHERE $whereSql");
                $s->execute($params);
                return (int) $s->fetchColumn();
            })() : 0;

        // Separate clean count query
        $stmtCount = $this->pdo->prepare("SELECT COUNT(*) FROM nte n WHERE $whereSql");
        $stmtCount->execute($params);
        $total = (int) $stmtCount->fetchColumn();
        $pages = $total > 0 ? (int) ceil($total / $perPage) : 1;

        $sql = "SELECT n.*, kb.nama AS kabupaten_nama
            FROM nte n
            LEFT JOIN kabupaten kb ON n.kabupaten_id = kb.id
            WHERE $whereSql
            ORDER BY n.tahun DESC, n.bulan DESC, n.id DESC
            LIMIT $perPage OFFSET $offset";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return ['data' => $stmt->fetchAll(\PDO::FETCH_ASSOC), 'total' => $total, 'pages' => $pages, 'current' => $page];
    }

    /** @return list<array{tahun:int,total_nilai:float}> */
    public function rekap(array $filters = []): array
    {
        $where = ['1=1']; $params = [];
        if (!empty($filters['kabupaten_id'])) { $where[] = 'kabupaten_id = ?'; $params[] = $filters['kabupaten_id']; }
        if (!empty($filters['kth_id'])) { $where[] = 'kth_id = ?'; $params[] = $filters['kth_id']; }
        $sql = "SELECT tahun, SUM(nilai_rp) AS total_nilai, COUNT(*) AS total_transaksi
                FROM nte WHERE " . implode(' AND ', $where) . "
                GROUP BY tahun ORDER BY tahun DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /** @return list<array{bulan:int,total_nilai:float}> */
    public function rekapBulanan(int $tahun, array $filters = []): array
    {
        $where = ['tahun = ?']; $params = [$tahun];
        if (!empty($filters['kabupaten_id'])) { $where[] = 'kabupaten_id = ?'; $params[] = $filters['kabupaten_id']; }
        if (!empty($filters['kth_id'])) { $where[] = 'kth_id = ?'; $params[] = $filters['kth_id']; }
        $sql = "SELECT bulan, SUM(nilai_rp) AS total_nilai, COUNT(*) AS total_transaksi
                FROM nte WHERE " . implode(' AND ', $where) . "
                GROUP BY bulan ORDER BY bulan ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Bulk insert dengan chunking untuk performa maksimal.
     * @param list<array> $rows
     */
    public function bulkInsert(array $rows, int $chunkSize = 500): int
    {
        if (empty($rows)) return 0;
        $inserted = 0;
        $chunks = array_chunk($rows, $chunkSize);
        $this->pdo->exec('SET FOREIGN_KEY_CHECKS=0');

        foreach ($chunks as $chunk) {
            $placeholders = implode(',', array_fill(0, count($chunk), '(?,?,?,?,?,?,?,?,?)'));
            $sql = "INSERT INTO nte (kth_id, nama_kth, kabupaten_id, tahun, bulan, jenis_barang, produk, jumlah, satuan, nilai_rp, penyuluh)
                    VALUES " . implode(',', array_fill(0, count($chunk), '(?,?,?,?,?,?,?,?,?,?,?)'));
            $params = [];
            foreach ($chunk as $r) {
                $params[] = $r['kth_id'];
                $params[] = $r['nama_kth'];
                $params[] = $r['kabupaten_id'];
                $params[] = $r['tahun'];
                $params[] = $r['bulan'];
                $params[] = $r['jenis_barang'];
                $params[] = $r['produk'];
                $params[] = $r['jumlah'];
                $params[] = $r['satuan'];
                $params[] = $r['nilai_rp'];
                $params[] = $r['penyuluh'];
            }
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $inserted += count($chunk);
        }

        $this->pdo->exec('SET FOREIGN_KEY_CHECKS=1');
        return $inserted;
    }

    /** @return list<int> */
    public static function availableYears(): array
    {
        return range(2020, (int) date('Y'));
    }
}
