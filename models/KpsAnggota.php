<?php

declare(strict_types=1);

class KpsAnggota extends BaseModel
{
    protected string $table = 'kps_anggota';

    /** @return list<string> */
    public static function kategoriList(): array
    {
        return ['ruang_perlindungan_komunal', 'andil_garapan'];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listByKpsId(int $kpsId, ?string $kategori = null): array
    {
        $where = ['a.kps_id = ?'];
        $params = [$kpsId];

        if ($kategori !== null && in_array($kategori, self::kategoriList(), true)) {
            $where[] = 'a.kategori = ?';
            $params[] = $kategori;
        }

        $sql = 'SELECT a.*, ds.nama AS desa_nama, kc.nama AS kecamatan_nama
            FROM kps_anggota a
            LEFT JOIN desa ds ON a.desa_id = ds.id
            LEFT JOIN kecamatan kc ON a.kecamatan_id = kc.id
            WHERE ' . implode(' AND ', $where) . '
            ORDER BY a.kategori ASC, a.no_andil ASC, a.nama_penggarap ASC, a.id ASC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /** @return array<string, mixed>|false */
    public function findWithKps(int $id): array|false
    {
        $sql = 'SELECT a.*, k.kabupaten_id AS kps_kabupaten_id, k.nama_lembaga AS kps_nama
            FROM kps_anggota a
            INNER JOIN kps k ON a.kps_id = k.id
            WHERE a.id = ?
            LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $row === false ? false : $row;
    }
}

