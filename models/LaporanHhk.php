<?php

declare(strict_types=1);

class LaporanHhk
{
    public function __construct(private \PDO $pdo) {}

    /** @return list<array{id:int,nama:string,urutan:int}> */
    public function getKomoditas(bool $activeOnly = true): array
    {
        $where = $activeOnly ? 'WHERE is_active = 1' : '';
        return $this->pdo->query("SELECT id, nama, urutan, is_active FROM hhk_komoditas $where ORDER BY urutan")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function saveKomoditas(string $nama, int $urutan): int
    {
        $stmt = $this->pdo->prepare("INSERT INTO hhk_komoditas (nama, urutan) VALUES (?,?) ON DUPLICATE KEY UPDATE urutan=VALUES(urutan), is_active=1");
        $stmt->execute([$nama, $urutan]);
        return (int) $this->pdo->lastInsertId();
    }

    public function toggleKomoditas(int $id): void
    {
        $this->pdo->prepare("UPDATE hhk_komoditas SET is_active = IF(is_active=1,0,1) WHERE id = ?")->execute([$id]);
    }

    public function updateUrutan(int $id, int $urutan): void
    {
        $this->pdo->prepare("UPDATE hhk_komoditas SET urutan = ? WHERE id = ?")->execute([$urutan, $id]);
    }

    /** @return array<string,float> nama_komoditas => target_m3 */
    public function getTargetDpa(int $tahun): array
    {
        $sql = "SELECT k.nama, COALESCE(t.target_m3, 0) AS target_m3
                FROM hhk_komoditas k
                LEFT JOIN hhk_target_dpa t ON t.komoditas_id = k.id AND t.tahun = ?
                WHERE k.is_active = 1 ORDER BY k.urutan";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$tahun]);
        $result = [];
        foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $r) {
            $result[$r['nama']] = (float) $r['target_m3'];
        }
        return $result;
    }

    public function saveTargetDpa(int $tahun, int $komoditasId, float $target): void
    {
        $stmt = $this->pdo->prepare("INSERT INTO hhk_target_dpa (tahun, komoditas_id, target_m3) VALUES (?,?,?) ON DUPLICATE KEY UPDATE target_m3 = VALUES(target_m3)");
        $stmt->execute([$tahun, $komoditasId, $target]);
    }

    /**
     * Data rekap untuk Berita Acara — ringkasan per jenis kayu untuk bulan/tahun tertentu.
     * @return array<string, array{bulan_ini:float, sd_bulan_ini:float}>
     */
    public function getRekap(int $bulan, int $tahun, ?int $kabupatenId = null): array
    {
        $kabWhere = $kabupatenId ? "AND h.kabupaten_id = $kabupatenId" : '';
        $sql = "SELECT d.jenis_kayu, SUM(d.volume_bulan_ini_m3) AS bulan_ini, SUM(d.volume_sd_bulan_ini_m3) AS sd_bulan_ini
                FROM hhk_detail d
                JOIN hhk h ON d.hhk_id = h.id
                WHERE h.bulan = ? AND h.tahun = ? $kabWhere
                GROUP BY d.jenis_kayu";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$bulan, $tahun]);
        $result = [];
        foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $r) {
            $result[$r['jenis_kayu']] = ['bulan_ini' => (float) $r['bulan_ini'], 'sd_bulan_ini' => (float) $r['sd_bulan_ini']];
        }
        return $result;
    }

    /**
     * Data lampiran — detail per KTH/desa/kecamatan/kabupaten.
     */
    public function getLampiran(int $bulan, int $tahun, ?int $kabupatenId = null): array
    {
        $kabWhere = $kabupatenId ? "AND h.kabupaten_id = $kabupatenId" : '';
        $sql = "SELECT h.id, h.nama_kth, h.nama_penyuluh,
                       kb.nama AS kabupaten, kc.nama AS kecamatan, ds.nama AS desa,
                       h.total_bulan_ini_m3, h.total_sd_bulan_lalu_m3, h.total_sd_bulan_ini_m3
                FROM hhk h
                LEFT JOIN kabupaten kb ON h.kabupaten_id = kb.id
                LEFT JOIN kecamatan kc ON h.kecamatan_id = kc.id
                LEFT JOIN desa ds ON h.desa_id = ds.id
                WHERE h.bulan = ? AND h.tahun = ? $kabWhere
                ORDER BY kb.nama, kc.nama, ds.nama, h.nama_kth";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$bulan, $tahun]);
        $headers = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Ambil detail per HHK
        $result = [];
        foreach ($headers as $row) {
            $stmt2 = $this->pdo->prepare("SELECT jenis_kayu, volume_bulan_ini_m3, volume_sd_bulan_ini_m3 FROM hhk_detail WHERE hhk_id = ?");
            $stmt2->execute([$row['id']]);
            $details = [];
            foreach ($stmt2->fetchAll(\PDO::FETCH_ASSOC) as $d) {
                $details[$d['jenis_kayu']] = ['bulan_ini' => (float) $d['volume_bulan_ini_m3'], 'sd_bulan_ini' => (float) $d['volume_sd_bulan_ini_m3']];
            }
            $row['detail'] = $details;
            $result[] = $row;
        }
        return $result;
    }
}
