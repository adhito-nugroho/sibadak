<?php

declare(strict_types=1);

class Kps extends BaseModel
{
    protected string $table = 'kps';

    /** @return list<string> */
    public static function skemaList(): array
    {
        return ['HKm', 'HD', 'HTR', 'Kulin KK', 'IPHPS'];
    }

    /**
     * @param array{
     *   kabupaten_id?: int,
     *   skema?: string,
     *   q?: string,
     *   operator_kab_id?: int
     * } $filters
     * @return array{data: list<array<string, mixed>>, total: int, pages: int, current: int}
     */
    public function paginateIndex(int $page, int $perPage, array $filters): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset = ($page - 1) * $perPage;

        $where = ['1=1'];
        $params = [];

        if (!empty($filters['operator_kab_id'])) {
            $where[] = 'kps.kabupaten_id = ?';
            $params[] = (int) $filters['operator_kab_id'];
        } elseif (!empty($filters['kabupaten_id'])) {
            $where[] = 'kps.kabupaten_id = ?';
            $params[] = (int) $filters['kabupaten_id'];
        }

        if (!empty($filters['skema']) && in_array($filters['skema'], self::skemaList(), true)) {
            $where[] = 'kps.skema = ?';
            $params[] = $filters['skema'];
        }

        if (!empty($filters['q'])) {
            $where[] = '(kps.nama_lembaga LIKE ? OR kps.no_sk LIKE ?)';
            $like = '%' . $filters['q'] . '%';
            $params[] = $like;
            $params[] = $like;
        }

        $whereSql = implode(' AND ', $where);

        $sqlCount = 'SELECT COUNT(*) FROM kps WHERE ' . $whereSql;
        $stmt = $this->pdo->prepare($sqlCount);
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();
        $pages = $total > 0 ? (int) ceil($total / $perPage) : 1;

        $sqlList = 'SELECT kps.*, kb.nama AS kabupaten_nama, kc.nama AS kecamatan_nama, ds.nama AS desa_nama
            FROM kps
            INNER JOIN kabupaten kb ON kps.kabupaten_id = kb.id
            INNER JOIN kecamatan kc ON kps.kecamatan_id = kc.id
            INNER JOIN desa ds ON kps.desa_id = ds.id
            WHERE ' . $whereSql . '
            ORDER BY kps.nama_lembaga ASC
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
        $sql = 'SELECT kps.*, kb.nama AS kabupaten_nama, kc.nama AS kecamatan_nama, ds.nama AS desa_nama
            FROM kps
            INNER JOIN kabupaten kb ON kps.kabupaten_id = kb.id
            INNER JOIN kecamatan kc ON kps.kecamatan_id = kc.id
            INNER JOIN desa ds ON kps.desa_id = ds.id
            WHERE kps.id = ?
            LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $row === false ? false : $row;
    }

    /**
     * @return list<array{id:int,nama_lembaga:string,kabupaten_id:int,kabupaten_nama:string}>
     */
    public function listForSelect(?int $kabupatenFilter, ?int $operatorKabId): array
    {
        $where = ['1=1'];
        $params = [];
        if ($operatorKabId !== null) {
            $where[] = 'kps.kabupaten_id = ?';
            $params[] = $operatorKabId;
        } elseif ($kabupatenFilter !== null && $kabupatenFilter > 0) {
            $where[] = 'kps.kabupaten_id = ?';
            $params[] = $kabupatenFilter;
        }

        $sql = 'SELECT kps.id, kps.nama_lembaga, kps.kabupaten_id, kb.nama AS kabupaten_nama
            FROM kps
            INNER JOIN kabupaten kb ON kps.kabupaten_id = kb.id
            WHERE ' . implode(' AND ', $where) . '
            ORDER BY kb.nama ASC, kps.nama_lembaga ASC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
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
        $where = 'kps.id IN (' . $in['sql'] . ')';
        if ($operatorKabId !== null) {
            $where .= ' AND kps.kabupaten_id = ?';
            $params[] = $operatorKabId;
        }
        $sql = 'SELECT kps.*, kb.nama AS kabupaten_nama, kc.nama AS kecamatan_nama, ds.nama AS desa_nama
            FROM kps
            INNER JOIN kabupaten kb ON kps.kabupaten_id = kb.id
            INNER JOIN kecamatan kc ON kps.kecamatan_id = kc.id
            INNER JOIN desa ds ON kps.desa_id = ds.id
            WHERE ' . $where . '
            ORDER BY kps.nama_lembaga ASC';

        return $this->query($sql, $params);
    }
}
