<?php

declare(strict_types=1);

/** Validasi konsistensi kabupaten → kecamatan → desa */
class WilayahValidate
{
    public function __construct(private \PDO $pdo)
    {
    }

    public function isValidChain(int $kabupatenId, int $kecamatanId, int $desaId): bool
    {
        $sql = 'SELECT 1 FROM desa d
            INNER JOIN kecamatan kc ON d.kecamatan_id = kc.id
            WHERE d.id = ? AND kc.id = ? AND kc.kabupaten_id = ?
            LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$desaId, $kecamatanId, $kabupatenId]);

        return (bool) $stmt->fetchColumn();
    }
}
