<?php

declare(strict_types=1);

class Dashboard
{
    /** @var array<string, string> */
    private const WARNA_SKEMA = [
        'HKm' => '#3a8f3a',
        'HD' => '#a87638',
        'HTR' => '#1d4ed8',
        'Kulin KK' => '#7c3aed',
        'IPHPS' => '#0d9488',
    ];

    public function __construct(private \PDO $pdo)
    {
    }

    /**
     * @param array{kabupaten_id?:int,tahun?:int} $filters
     * @return array{
     *   stats: array{kth:int,kps:int,anggota:int,rhl_ha:float},
     *   delta: array{kth:int,kps:int,anggota:int,rhl_ha:float},
     *   kth_per_kab: list<array{nama:string,kode:string,utama:int,madya:int,pemula:int}>,
     *   kps_per_skema: list<array{skema:string,jumlah:int,luas_ha:float,warna:string,pct:float}>,
     *   kth_per_kelas: list<array{kelas:string,jumlah_kth:int,total_anggota:int,pct_kth:float}>,
     *   rhl_per_kegiatan: list<array{kegiatan:string,total_luas_ha:float,pct:float}>,
     *   kth_terbaru: list<array{nama:string,kabupaten:string,kecamatan:string,kelas:string,anggota:int}>,
     *   kps_terbaru: list<array{nama:string,kecamatan:string,skema:string,luas:float|null}>,
     *   error: ?string
     * }
     */
    public function fetchAll(array $filters = []): array
    {
        try {
            $kabupatenId = $filters['kabupaten_id'] ?? null;
            $tahun = $filters['tahun'] ?? null;

            $stats = $this->fetchStats($kabupatenId);
            $delta = $this->fetchDeltas($kabupatenId, $tahun);
            $kthPerKab = $this->fetchKthPerKabupaten($kabupatenId);
            $kpsSkema = $this->fetchKpsPerSkema($kabupatenId, $tahun);
            $kthKelas = $this->fetchKthPerKelas($kabupatenId);
            $rhlKeg = $this->fetchRhlPerKegiatan($kabupatenId, $tahun);
            $kthBaru = $this->fetchKthTerbaru($kabupatenId);
            $kpsBaru = $this->fetchKpsTerbaru($kabupatenId);

            return [
                'stats' => $stats,
                'delta' => $delta,
                'kth_per_kab' => $kthPerKab,
                'kps_per_skema' => $kpsSkema,
                'kth_per_kelas' => $kthKelas,
                'rhl_per_kegiatan' => $rhlKeg,
                'kth_terbaru' => $kthBaru,
                'kps_terbaru' => $kpsBaru,
                'error' => null,
            ];
        } catch (\Throwable $e) {
            return [
                'stats' => ['kth' => 0, 'kps' => 0, 'anggota' => 0, 'rhl_ha' => 0.0],
                'delta' => ['kth' => 0, 'kps' => 0, 'anggota' => 0, 'rhl_ha' => 0.0],
                'kth_per_kab' => [],
                'kps_per_skema' => [],
                'kth_per_kelas' => [],
                'rhl_per_kegiatan' => [],
                'kth_terbaru' => [],
                'kps_terbaru' => [],
                'error' => APP_DEBUG ? $e->getMessage() : 'Data dashboard tidak dapat dimuat.',
            ];
        }
    }

    /**
     * @param int|null $kabupatenId
     * @return array{kth:int,kps:int,anggota:int,rhl_ha:float}
     */
    private function fetchStats(?int $kabupatenId = null): array
    {
        $whereKth = $kabupatenId ? "WHERE kth.is_active = 1 AND kth.kabupaten_id = $kabupatenId" : "WHERE kth.is_active = 1";
        $whereKps = $kabupatenId ? "WHERE kps.kabupaten_id = $kabupatenId" : "";
        $whereRhl = $kabupatenId ? "WHERE rhl.kabupaten_id = $kabupatenId" : "";
        
        $kth = (int) $this->pdo->query("SELECT COUNT(*) FROM kth $whereKth")->fetchColumn();
        $kps = (int) $this->pdo->query("SELECT COUNT(*) FROM kps $whereKps")->fetchColumn();
        $anggota = (int) $this->pdo->query("SELECT COALESCE(SUM(jumlah_anggota), 0) FROM kth $whereKth")->fetchColumn();
        $rhl = (float) $this->pdo->query("SELECT COALESCE(SUM(luas_ha), 0) FROM rhl $whereRhl")->fetchColumn();

        return ['kth' => $kth, 'kps' => $kps, 'anggota' => $anggota, 'rhl_ha' => $rhl];
    }

    /**
     * Compute deltas by comparing current totals against previous year.
     * @param int|null $kabupatenId
     * @param int|null $tahun current filter year (if any)
     * @return array{kth:int,kps:int,anggota:int,rhl_ha:float}
     */
    private function fetchDeltas(?int $kabupatenId = null, ?int $tahun = null): array
    {
        $prevYear = $tahun ? $tahun - 1 : (int) date('Y') - 1;

        // KTH delta: compare kth created_at year
        $kthWhere = $kabupatenId ? "kth.kabupaten_id = $kabupatenId AND " : "";
        $kthCurrent = (int) $this->pdo->query(
            "SELECT COUNT(*) FROM kth WHERE $kthWhere YEAR(created_at) = " . ($tahun ?? (int) date('Y'))
        )->fetchColumn();
        $kthPrev = (int) $this->pdo->query(
            "SELECT COUNT(*) FROM kth WHERE $kthWhere YEAR(created_at) = $prevYear"
        )->fetchColumn();

        // KPS delta
        $kpsWhere = $kabupatenId ? "kabupaten_id = $kabupatenId AND " : "";
        $kpsCurrent = (int) $this->pdo->query(
            "SELECT COUNT(*) FROM kps WHERE $kpsWhere YEAR(created_at) = " . ($tahun ?? (int) date('Y'))
        )->fetchColumn();
        $kpsPrev = (int) $this->pdo->query(
            "SELECT COUNT(*) FROM kps WHERE $kpsWhere YEAR(created_at) = $prevYear"
        )->fetchColumn();

        // Anggota delta
        $anggotaCurrent = (int) $this->pdo->query(
            "SELECT COALESCE(SUM(jumlah_anggota), 0) FROM kth WHERE $kthWhere YEAR(created_at) = " . ($tahun ?? (int) date('Y'))
        )->fetchColumn();
        $anggotaPrev = (int) $this->pdo->query(
            "SELECT COALESCE(SUM(jumlah_anggota), 0) FROM kth WHERE $kthWhere YEAR(created_at) = $prevYear"
        )->fetchColumn();

        // RHL delta
        $rhlWhere = $kabupatenId ? "kabupaten_id = $kabupatenId AND " : "";
        $rhlCurrent = (float) $this->pdo->query(
            "SELECT COALESCE(SUM(luas_ha), 0) FROM rhl WHERE $rhlWhere tahun = " . ($tahun ?? (int) date('Y'))
        )->fetchColumn();
        $rhlPrev = (float) $this->pdo->query(
            "SELECT COALESCE(SUM(luas_ha), 0) FROM rhl WHERE $rhlWhere tahun = $prevYear"
        )->fetchColumn();

        return [
            'kth'     => $kthCurrent - $kthPrev,
            'kps'     => $kpsCurrent - $kpsPrev,
            'anggota' => $anggotaCurrent - $anggotaPrev,
            'rhl_ha'  => $rhlCurrent - $rhlPrev,
        ];
    }

    /**
     * @param int|null $kabupatenId
     * @return list<array{nama:string,kode:string,utama:int,madya:int,pemula:int}>
     */
    private function fetchKthPerKabupaten(?int $kabupatenId = null): array
    {
        $where = $kabupatenId ? "WHERE k.id = $kabupatenId" : "";
        $stmt = $this->pdo->query(
            "SELECT k.nama AS kabupaten, kth.kelas, COUNT(kth.id) AS jumlah_kth 
             FROM kth
             JOIN kabupaten k ON kth.kabupaten_id = k.id
             $where
             GROUP BY k.id, kth.kelas"
        );
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        /** @var array<string, array{Utama:int,Madya:int,Pemula:int}> $map */
        $map = [];
        foreach ($rows as $r) {
            $kab = (string) $r['kabupaten'];
            if (!isset($map[$kab])) {
                $map[$kab] = ['Utama' => 0, 'Madya' => 0, 'Pemula' => 0];
            }
            $kelas = (string) $r['kelas'];
            $map[$kab][$kelas] = (int) $r['jumlah_kth'];
        }

        $codes = [];
        $q = $this->pdo->query('SELECT kode, nama FROM kabupaten ORDER BY id');
        while ($row = $q->fetch(\PDO::FETCH_ASSOC)) {
            $codes[(string) $row['nama']] = (string) $row['kode'];
        }

        $out = [];
        foreach ($map as $nama => $v) {
            $out[] = [
                'nama' => $nama,
                'kode' => $codes[$nama] ?? strtoupper(substr($nama, 0, 3)),
                'utama' => $v['Utama'],
                'madya' => $v['Madya'],
                'pemula' => $v['Pemula'],
            ];
        }
        usort($out, static fn (array $a, array $b): int => strcmp($a['nama'], $b['nama']));

        return $out;
    }

    /**
     * @param int|null $kabupatenId
     * @param int|null $tahun
     * @return list<array{skema:string,jumlah:int,luas_ha:float,warna:string,pct:float}>
     */
    private function fetchKpsPerSkema(?int $kabupatenId = null, ?int $tahun = null): array
    {
        $whereKab = $kabupatenId ? "WHERE kps.kabupaten_id = $kabupatenId" : "";
        $whereTahun = $tahun ? ($whereKab ? " AND " : "WHERE ") . "YEAR(kps.created_at) = $tahun" : "";
        
        $sql = "SELECT kps.skema, COUNT(kps.id) AS jumlah, COALESCE(SUM(kps.luas_wilayah_ha), 0) AS luas
                FROM kps
                $whereKab $whereTahun
                GROUP BY kps.skema ORDER BY kps.skema";
        $stmt = $this->pdo->query($sql);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $totalJumlah = 0;
        foreach ($rows as $r) {
            $totalJumlah += (int) $r['jumlah'];
        }
        $out = [];
        foreach ($rows as $r) {
            $skema = (string) $r['skema'];
            $jumlah = (int) $r['jumlah'];
            $pct = $totalJumlah > 0 ? ($jumlah / $totalJumlah) * 100 : 0.0;
            $out[] = [
                'skema' => $skema,
                'jumlah' => $jumlah,
                'luas_ha' => (float) $r['luas'],
                'warna' => self::WARNA_SKEMA[$skema] ?? '#6b7280',
                'pct' => $pct,
            ];
        }

        return $out;
    }

    /**
     * @param int|null $kabupatenId
     * @return list<array{kelas:string,jumlah_kth:int,total_anggota:int,pct_kth:float}>
     */
    private function fetchKthPerKelas(?int $kabupatenId = null): array
    {
        $where = $kabupatenId ? "WHERE kth.kabupaten_id = $kabupatenId" : "";
        $sql = "SELECT kth.kelas, COUNT(kth.id) AS jumlah_kth, SUM(kth.jumlah_anggota) AS total_anggota
                FROM kth $where 
                GROUP BY kth.kelas
                ORDER BY FIELD(kth.kelas, 'Utama', 'Madya', 'Pemula')";
        $stmt = $this->pdo->query($sql);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $sumKth = 0;
        foreach ($rows as $r) {
            $sumKth += (int) $r['jumlah_kth'];
        }
        $out = [];
        foreach ($rows as $r) {
            $jk = (int) $r['jumlah_kth'];
            $out[] = [
                'kelas' => (string) $r['kelas'],
                'jumlah_kth' => $jk,
                'total_anggota' => (int) $r['total_anggota'],
                'pct_kth' => $sumKth > 0 ? ($jk / $sumKth) * 100 : 0.0,
            ];
        }

        return $out;
    }

    /**
     * @param int|null $kabupatenId
     * @param int|null $tahun
     * @return list<array{kegiatan:string,total_luas_ha:float,pct:float}>
     */
    private function fetchRhlPerKegiatan(?int $kabupatenId = null, ?int $tahun = null): array
    {
        $whereKab = $kabupatenId ? "WHERE rhl.kabupaten_id = $kabupatenId" : "";
        $whereTahun = $tahun ? ($whereKab ? " AND " : "WHERE ") . "rhl.tahun = $tahun" : "";
        
        $sql = "SELECT rhl.kegiatan, SUM(rhl.luas_ha) AS total_luas
                FROM rhl $whereKab $whereTahun
                GROUP BY rhl.kegiatan
                ORDER BY total_luas DESC";
        $stmt = $this->pdo->query($sql);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $max = 0.0;
        foreach ($rows as $r) {
            $max = max($max, (float) $r['total_luas']);
        }
        $out = [];
        foreach ($rows as $r) {
            $luas = (float) $r['total_luas'];
            $out[] = [
                'kegiatan' => (string) $r['kegiatan'],
                'total_luas_ha' => $luas,
                'pct' => $max > 0 ? ($luas / $max) * 100 : 0.0,
            ];
        }

        return $out;
    }

    /**
     * @return list<array{nama:string,kabupaten:string,kecamatan:string,kelas:string,anggota:int}>
     */
    private function fetchKthTerbaru(?int $kabupatenId = null): array
    {
        $whereKab = $kabupatenId ? " AND kth.kabupaten_id = $kabupatenId" : "";
        $sql = "SELECT kth.nama, kb.nama AS kabupaten, kc.nama AS kecamatan, kth.kelas, kth.jumlah_anggota
                FROM kth
                JOIN kabupaten kb ON kth.kabupaten_id = kb.id
                JOIN kecamatan kc ON kth.kecamatan_id = kc.id
                WHERE kth.is_active = 1 $whereKab
                ORDER BY kth.created_at DESC, kth.id DESC
                LIMIT 7";
        $stmt = $this->pdo->query($sql);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * @return list<array{nama:string,kecamatan:string,skema:string,luas:float|null}>
     */
    private function fetchKpsTerbaru(?int $kabupatenId = null): array
    {
        $where = $kabupatenId ? "WHERE kps.kabupaten_id = $kabupatenId" : "";
        $sql = "SELECT kps.nama_lembaga AS nama, kb.nama AS kabupaten, kc.nama AS kecamatan, kps.skema, kps.luas_wilayah_ha AS luas
                FROM kps
                JOIN kabupaten kb ON kps.kabupaten_id = kb.id
                JOIN kecamatan kc ON kps.kecamatan_id = kc.id
                $where
                ORDER BY kps.created_at DESC, kps.id DESC
                LIMIT 5";
        $stmt = $this->pdo->query($sql);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $out = [];
        foreach ($rows as $r) {
            $out[] = [
                'nama' => (string) $r['nama'],
                'kecamatan' => (string) $r['kecamatan'],
                'skema' => (string) $r['skema'],
                'luas' => $r['luas'] !== null ? (float) $r['luas'] : null,
            ];
        }

        return $out;
    }
}
