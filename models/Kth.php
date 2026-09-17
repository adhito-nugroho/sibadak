<?php

declare(strict_types=1);

class Kth extends BaseModel
{
    protected string $table = 'kth';

    /**
     * @param array{kabupaten_id?: int, kelas?: string, q?: string, operator_kab_id?: int|null} $filters
     * @return array{data: list<array<string, mixed>>, total: int, pages: int, current: int}
     */
    public function paginateIndex(int $page, int $perPage, array $filters): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset = ($page - 1) * $perPage;

        $where = ['kth.is_active = 1'];
        $params = [];

        if (!empty($filters['operator_kab_id'])) {
            $where[] = 'kth.kabupaten_id = ?';
            $params[] = (int) $filters['operator_kab_id'];
        } elseif (!empty($filters['kabupaten_id'])) {
            $where[] = 'kth.kabupaten_id = ?';
            $params[] = (int) $filters['kabupaten_id'];
        }

        if (!empty($filters['kelas']) && in_array($filters['kelas'], ['Pemula', 'Madya', 'Utama'], true)) {
            $where[] = 'kth.kelas = ?';
            $params[] = $filters['kelas'];
        }

        if (!empty($filters['q'])) {
            $where[] = '(kth.nama LIKE ? OR kth.kode_register LIKE ?)';
            $like = '%' . $filters['q'] . '%';
            $params[] = $like;
            $params[] = $like;
        }

        $whereSql = implode(' AND ', $where);

        $sqlCount = 'SELECT COUNT(*) FROM kth
            INNER JOIN kabupaten kb ON kth.kabupaten_id = kb.id
            WHERE ' . $whereSql;
        $stmt = $this->pdo->prepare($sqlCount);
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();
        $pages = $total > 0 ? (int) ceil($total / $perPage) : 1;

        $sqlList = 'SELECT kth.*, kb.nama AS kabupaten_nama, kc.nama AS kecamatan_nama, ds.nama AS desa_nama
            FROM kth
            INNER JOIN kabupaten kb ON kth.kabupaten_id = kb.id
            INNER JOIN kecamatan kc ON kth.kecamatan_id = kc.id
            INNER JOIN desa ds ON kth.desa_id = ds.id
            WHERE ' . $whereSql . '
            ORDER BY kth.nama ASC
            LIMIT ' . $perPage . ' OFFSET ' . $offset;
        $stmt = $this->pdo->prepare($sqlList);
        $stmt->execute($params);
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return [
            'data' => $data,
            'total' => $total,
            'pages' => $pages,
            'current' => $page,
        ];
    }

    /** @return array<string, mixed>|false */
    public function findWithWilayah(int $id): array|false
    {
        $sql = 'SELECT kth.*, kb.nama AS kabupaten_nama, kc.nama AS kecamatan_nama, ds.nama AS desa_nama
            FROM kth
            INNER JOIN kabupaten kb ON kth.kabupaten_id = kb.id
            INNER JOIN kecamatan kc ON kth.kecamatan_id = kc.id
            INNER JOIN desa ds ON kth.desa_id = ds.id
            WHERE kth.id = ?
            LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $row === false ? false : $row;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listAnggotaByKthId(int $kthId): array
    {
        $sql = 'SELECT * FROM kth_anggota WHERE kth_id = ? ORDER BY FIELD(posisi,\'Kantor KTH\',\'Ketua\',\'Sekretaris\',\'Bendahara\',\'Seksi\',\'Anggota\'), nama ASC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$kthId]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function softDelete(int $id): bool
    {
        $stmt = $this->pdo->prepare('UPDATE kth SET is_active = 0 WHERE id = ?');

        return $stmt->execute([$id]);
    }

    /**
     * @param list<int> $ids
     * @return list<array<string, mixed>>
     */
    public function findByIds(array $ids, ?int $operatorKabId = null): array
    {
        $ids = array_values(array_unique(array_filter(
            array_map(static fn ($v): int => (int) $v, $ids),
            static fn (int $id): bool => $id > 0
        )));
        if ($ids === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $params = $ids;
        $where = 'kth.id IN (' . $placeholders . ') AND kth.is_active = 1';
        if ($operatorKabId !== null) {
            $where .= ' AND kth.kabupaten_id = ?';
            $params[] = $operatorKabId;
        }

        $sql = 'SELECT kth.*, kb.nama AS kabupaten_nama, kc.nama AS kecamatan_nama, ds.nama AS desa_nama
            FROM kth
            INNER JOIN kabupaten kb ON kth.kabupaten_id = kb.id
            INNER JOIN kecamatan kc ON kth.kecamatan_id = kc.id
            INNER JOIN desa ds ON kth.desa_id = ds.id
            WHERE ' . $where . '
            ORDER BY kth.nama ASC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Soft-delete banyak KTH sekaligus.
     *
     * @param list<int> $ids
     */
    public function softDeleteMany(array $ids, ?int $operatorKabId = null): int
    {
        $ids = array_values(array_unique(array_filter(
            array_map(static fn ($v): int => (int) $v, $ids),
            static fn (int $id): bool => $id > 0
        )));
        if ($ids === []) {
            return 0;
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $params = $ids;
        $sql = 'UPDATE kth SET is_active = 0 WHERE id IN (' . $placeholders . ') AND is_active = 1';
        if ($operatorKabId !== null) {
            $sql .= ' AND kabupaten_id = ?';
            $params[] = $operatorKabId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->rowCount();
    }

    /** @return list<array{id:int,kode:string,nama:string}> */
    public function listKabupatenForFilter(): array
    {
        return $this->query('SELECT id, kode, nama FROM kabupaten ORDER BY nama ASC');
    }

    /** @return list<array{id:int,nama:string}> */
    public function listKecamatanByKabupaten(int $kabupatenId): array
    {
        return $this->query(
            'SELECT id, nama FROM kecamatan WHERE kabupaten_id = ? ORDER BY nama ASC',
            [$kabupatenId]
        );
    }

    /** @return list<array{id:int,nama:string}> */
    public function listDesaByKecamatan(int $kecamatanId): array
    {
        return $this->query(
            'SELECT id, nama FROM desa WHERE kecamatan_id = ? ORDER BY nama ASC',
            [$kecamatanId]
        );
    }

    public function kodeRegisterExists(string $kode, ?int $exceptId = null): bool
    {
        $sql = 'SELECT 1 FROM kth WHERE kode_register = ?';
        $params = [$kode];
        if ($exceptId !== null) {
            $sql .= ' AND id <> ?';
            $params[] = $exceptId;
        }
        $sql .= ' LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return (bool) $stmt->fetchColumn();
    }

    /**
     * Dropdown KTH aktif — opsional filter kabupaten atau pembatasan operator.
     *
     * @return list<array{id:int,kode_register:string,nama:string,kabupaten_nama:string}>
     */
    public function listKthForSelect(?int $kabupatenFilter, ?int $operatorKabId): array
    {
        $where = ['kth.is_active = 1'];
        $params = [];
        if ($operatorKabId !== null) {
            $where[] = 'kth.kabupaten_id = ?';
            $params[] = $operatorKabId;
        } elseif ($kabupatenFilter !== null && $kabupatenFilter > 0) {
            $where[] = 'kth.kabupaten_id = ?';
            $params[] = $kabupatenFilter;
        }
        $sql = 'SELECT kth.id, kth.kode_register, kth.nama, kb.nama AS kabupaten_nama
            FROM kth
            INNER JOIN kabupaten kb ON kth.kabupaten_id = kb.id
            WHERE ' . implode(' AND ', $where) . '
            ORDER BY kb.nama ASC, kth.nama ASC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /** Samakan field jumlah_anggota di master KTH dengan jumlah baris di kth_anggota. */
    public function syncJumlahAnggotaFromAnggotaTable(int $kthId): void
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM kth_anggota WHERE kth_id = ?');
        $stmt->execute([$kthId]);
        $n = (int) $stmt->fetchColumn();
        $upd = $this->pdo->prepare('UPDATE kth SET jumlah_anggota = ? WHERE id = ?');
        $upd->execute([$n, $kthId]);
    }

    /**
     * Ambil semua kegiatan terkait KTH berdasarkan kth_id (relasi langsung).
     *
     * @return array{rhl:list<array<string,mixed>>,kbr:list<array<string,mixed>>,aep:list<array<string,mixed>>,kps:list<array<string,mixed>>}
     */
    public function getRelatedActivities(int $kthId, string $namaKth): array
    {
        // RHL — only by kth_id
        $sql = 'SELECT id, nama_kth, kegiatan, tahun, luas_ha FROM rhl WHERE kth_id = ? ORDER BY tahun DESC, id DESC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$kthId]);
        $rhl = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // KBR — only by kth_id
        $sql = 'SELECT id, nama_kth, lokasi, tahun_tanam, subdas FROM kbr WHERE kth_id = ? ORDER BY tahun_tanam DESC, id DESC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$kthId]);
        $kbr = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // AEP — only by kth_id
        $sql = 'SELECT id, nama_kth, jenis_bantuan, jumlah, tahun FROM aep WHERE kth_id = ? ORDER BY tahun DESC, id DESC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$kthId]);
        $aep = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // KPS — by kth_id
        $sql = 'SELECT id, nama_lembaga, skema, luas_wilayah_ha, no_sk FROM kps WHERE kth_id = ? ORDER BY id DESC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$kthId]);
        $kps = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return ['rhl' => $rhl, 'kbr' => $kbr, 'aep' => $aep, 'kps' => $kps];
    }
}
