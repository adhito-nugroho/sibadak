<?php

declare(strict_types=1);

class LaporanHhbk
{
    public function __construct(private \PDO $pdo) {}

    /** @return list<array{id:int,nama:string,satuan:string,urutan:int}> */
    public function getKomoditas(bool $activeOnly = true): array
    {
        $where = $activeOnly ? 'WHERE is_active = 1' : '';
        return $this->pdo->query("SELECT id, nama, satuan, urutan, is_active FROM hhbk_komoditas $where ORDER BY urutan")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function saveKomoditas(string $nama, string $satuan, int $urutan): int
    {
        $stmt = $this->pdo->prepare("INSERT INTO hhbk_komoditas (nama, satuan, urutan) VALUES (?,?,?) ON DUPLICATE KEY UPDATE satuan=VALUES(satuan), urutan=VALUES(urutan), is_active=1");
        $stmt->execute([$nama, $satuan, $urutan]);
        return (int) $this->pdo->lastInsertId();
    }

    public function toggleKomoditas(int $id): void
    {
        $this->pdo->prepare("UPDATE hhbk_komoditas SET is_active = IF(is_active=1,0,1) WHERE id = ?")->execute([$id]);
    }

    /** @return array<string,float> nama => target */
    public function getTargetDpa(int $tahun): array
    {
        $sql = "SELECT k.nama, COALESCE(t.target_nilai, 0) AS target_nilai
                FROM hhbk_komoditas k
                LEFT JOIN hhbk_target_dpa t ON t.komoditas_id = k.id AND t.tahun = ?
                WHERE k.is_active = 1 ORDER BY k.urutan";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$tahun]);
        $result = [];
        foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $r) {
            $result[$r['nama']] = (float) $r['target_nilai'];
        }
        return $result;
    }

    public function saveTargetDpa(int $tahun, int $komoditasId, float $target): void
    {
        $stmt = $this->pdo->prepare("INSERT INTO hhbk_target_dpa (tahun, komoditas_id, target_nilai) VALUES (?,?,?) ON DUPLICATE KEY UPDATE target_nilai = VALUES(target_nilai)");
        $stmt->execute([$tahun, $komoditasId, $target]);
    }

    /** @return array<string,array{bulan_ini:float,sd_bulan_ini:float,satuan:string}> */
    public function getRekap(int $bulan, int $tahun, ?int $kabupatenId = null): array
    {
        $kabWhere = $kabupatenId ? "AND h.kabupaten_id = $kabupatenId" : '';
        $sql = "SELECT d.komoditas, d.satuan, SUM(d.jumlah_bulan_ini) AS bulan_ini, SUM(d.jumlah_sd_bulan_ini) AS sd_bulan_ini
                FROM hhbk_detail d
                JOIN hhbk h ON d.hhbk_id = h.id
                WHERE h.bulan = ? AND h.tahun = ? $kabWhere
                GROUP BY d.komoditas, d.satuan";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$bulan, $tahun]);
        $result = [];
        foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $r) {
            $result[$r['komoditas']] = ['bulan_ini' => (float) $r['bulan_ini'], 'sd_bulan_ini' => (float) $r['sd_bulan_ini'], 'satuan' => $r['satuan']];
        }
        return $result;
    }

    public function getLampiran(int $bulan, int $tahun, ?int $kabupatenId = null): array
    {
        $kabWhere = $kabupatenId ? "AND h.kabupaten_id = $kabupatenId" : '';
        $sql = "SELECT h.id, h.nama_kth, h.nama_penyuluh,
                       kb.nama AS kabupaten, kc.nama AS kecamatan, ds.nama AS desa,
                       h.total_btg_bulan_ini, h.total_kg_bulan_ini, h.total_btg_sd_bulan_lalu,
                       h.total_kg_sd_bulan_lalu, h.total_btg_sd_bulan_ini, h.total_kg_sd_bulan_ini
                FROM hhbk h
                LEFT JOIN kabupaten kb ON h.kabupaten_id = kb.id
                LEFT JOIN kecamatan kc ON h.kecamatan_id = kc.id
                LEFT JOIN desa ds ON h.desa_id = ds.id
                WHERE h.bulan = ? AND h.tahun = ? $kabWhere
                ORDER BY kb.nama, kc.nama, ds.nama, h.nama_kth";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$bulan, $tahun]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $result = [];
        foreach ($rows as $row) {
            $stmt2 = $this->pdo->prepare("SELECT komoditas, satuan, jumlah_bulan_ini, jumlah_sd_bulan_ini FROM hhbk_detail WHERE hhbk_id = ?");
            $stmt2->execute([$row['id']]);
            $details = [];
            foreach ($stmt2->fetchAll(\PDO::FETCH_ASSOC) as $d) {
                $details[$d['komoditas']] = ['bulan_ini' => (float) $d['jumlah_bulan_ini'], 'sd_bulan_ini' => (float) $d['jumlah_sd_bulan_ini'], 'satuan' => $d['satuan']];
            }
            $row['detail'] = $details;
            $result[] = $row;
        }
        return $result;
    }
}
