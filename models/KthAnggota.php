<?php

declare(strict_types=1);

class KthAnggota extends BaseModel
{
    protected string $table = 'kth_anggota';

    /** @var list<string> */
    private const POSISI_ORDER = [
        'Kantor KTH', 'Ketua', 'Sekretaris', 'Bendahara', 'Seksi', 'Anggota',
    ];

    /**
     * @param array{
     *   q?: string,
     *   kabupaten_id?: int,
     *   kth_id?: int,
     *   posisi?: string,
     *   operator_kab_id?: int
     * } $filters
     * @return array{data: list<array<string, mixed>>, total: int, pages: int, current: int}
     */
    public function paginateIndex(int $page, int $perPage, array $filters): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset = ($page - 1) * $perPage;

        $where = ['k.is_active = 1'];
        $params = [];

        if (!empty($filters['operator_kab_id'])) {
            $where[] = 'k.kabupaten_id = ?';
            $params[] = (int) $filters['operator_kab_id'];
        } elseif (!empty($filters['kabupaten_id'])) {
            $where[] = 'k.kabupaten_id = ?';
            $params[] = (int) $filters['kabupaten_id'];
        }

        if (!empty($filters['kth_id'])) {
            $where[] = 'a.kth_id = ?';
            $params[] = (int) $filters['kth_id'];
        }

        $posisiList = self::POSISI_ORDER;
        if (!empty($filters['posisi']) && in_array($filters['posisi'], $posisiList, true)) {
            $where[] = 'a.posisi = ?';
            $params[] = $filters['posisi'];
        }

        if (!empty($filters['q'])) {
            $where[] = '(a.nama LIKE ? OR a.nik LIKE ? OR k.nama LIKE ? OR k.kode_register LIKE ?)';
            $like = '%' . $filters['q'] . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $whereSql = implode(' AND ', $where);
        $orderField = "FIELD(a.posisi,'" . implode("','", array_map(
            static fn (string $p): string => str_replace("'", "''", $p),
            $posisiList
        )) . "')";

        $sqlCount = 'SELECT COUNT(*)
            FROM kth_anggota a
            INNER JOIN kth k ON a.kth_id = k.id
            WHERE ' . $whereSql;
        $stmt = $this->pdo->prepare($sqlCount);
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();
        $pages = $total > 0 ? (int) ceil($total / $perPage) : 1;

        $sqlList = 'SELECT a.*, k.nama AS kth_nama, k.kode_register AS kth_kode, kb.nama AS kabupaten_nama
            FROM kth_anggota a
            INNER JOIN kth k ON a.kth_id = k.id
            INNER JOIN kabupaten kb ON k.kabupaten_id = kb.id
            WHERE ' . $whereSql . '
            ORDER BY ' . $orderField . ', a.nama ASC
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
    public function findWithKth(int $id): array|false
    {
        $sql = 'SELECT a.*, k.kabupaten_id, k.nama AS kth_nama, k.kode_register AS kth_kode,
            k.is_active AS kth_is_active, kb.nama AS kabupaten_nama
            FROM kth_anggota a
            INNER JOIN kth k ON a.kth_id = k.id
            INNER JOIN kabupaten kb ON k.kabupaten_id = kb.id
            WHERE a.id = ?
            LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $row === false ? false : $row;
    }

    public function nikExists(string $nik, ?int $excludeId = null): bool
    {
        if ($nik === '') {
            return false;
        }
        $sql = 'SELECT 1 FROM kth_anggota WHERE nik = ?';
        $params = [$nik];
        if ($excludeId !== null) {
            $sql .= ' AND id <> ?';
            $params[] = $excludeId;
        }
        $sql .= ' LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return (bool) $stmt->fetchColumn();
    }

    /** @return list<string> */
    public static function posisiList(): array
    {
        return self::POSISI_ORDER;
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
        $where = 'a.id IN (' . $in['sql'] . ') AND k.is_active = 1';
        if ($operatorKabId !== null) {
            $where .= ' AND k.kabupaten_id = ?';
            $params[] = $operatorKabId;
        }
        $sql = 'SELECT a.*, k.nama AS kth_nama, k.kode_register AS kth_kode, kb.nama AS kabupaten_nama
            FROM kth_anggota a
            INNER JOIN kth k ON a.kth_id = k.id
            INNER JOIN kabupaten kb ON k.kabupaten_id = kb.id
            WHERE ' . $where . '
            ORDER BY a.nama ASC';

        return $this->query($sql, $params);
    }
}
