<?php

declare(strict_types=1);

class Aep extends BaseModel
{
    protected string $table = 'aep';

    /** @return list<int> */
    public static function availableYears(): array
    {
        return range(2015, 2035);
    }

    /**
     * @param array{kabupaten_id?:int,tahun?:int,q?:string,operator_kab_id?:int} $filters
     * @return array{data:list<array<string,mixed>>,total:int,pages:int,current:int}
     */
    public function paginateIndex(int $page, int $perPage, array $filters): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset = ($page - 1) * $perPage;

        $where = ['1=1'];
        $params = [];

        if (!empty($filters['operator_kab_id'])) {
            $where[] = 'aep.kabupaten_id = ?';
            $params[] = (int) $filters['operator_kab_id'];
        } elseif (!empty($filters['kabupaten_id'])) {
            $where[] = 'aep.kabupaten_id = ?';
            $params[] = (int) $filters['kabupaten_id'];
        }
        if (!empty($filters['tahun'])) {
            $where[] = 'aep.tahun = ?';
            $params[] = (int) $filters['tahun'];
        }
        if (!empty($filters['q'])) {
            $where[] = '(aep.nama_kth LIKE ? OR aep.jenis_bantuan LIKE ?)';
            $like = '%' . $filters['q'] . '%';
            $params[] = $like;
            $params[] = $like;
        }

        $whereSql = implode(' AND ', $where);
        $countStmt = $this->pdo->prepare('SELECT COUNT(*) FROM aep WHERE ' . $whereSql);
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();
        $pages = $total > 0 ? (int) ceil($total / $perPage) : 1;

        $sql = 'SELECT aep.*, kb.nama AS kabupaten_nama, ds.nama AS desa_nama
            FROM aep
            INNER JOIN kabupaten kb ON aep.kabupaten_id = kb.id
            LEFT JOIN desa ds ON aep.desa_id = ds.id
            WHERE ' . $whereSql . '
            ORDER BY aep.tahun DESC, aep.id DESC
            LIMIT ' . $perPage . ' OFFSET ' . $offset;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return ['data' => $rows, 'total' => $total, 'pages' => $pages, 'current' => $page];
    }

    /** @return array<string,mixed>|false */
    public function findWithRelations(int $id): array|false
    {
        $sql = 'SELECT aep.*, kb.nama AS kabupaten_nama, ds.nama AS desa_nama
            FROM aep
            INNER JOIN kabupaten kb ON aep.kabupaten_id = kb.id
            LEFT JOIN desa ds ON aep.desa_id = ds.id
            WHERE aep.id = ?
            LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $row === false ? false : $row;
    }
}
