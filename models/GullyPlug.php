<?php

declare(strict_types=1);

class GullyPlug extends BaseModel
{
    protected string $table = 'gully_plug';

    /** @return list<int> */
    public static function availableYears(): array
    {
        return range(2015, 2035);
    }

    /**
     * @param array{tahun?:int,q?:string} $filters
     * @return array{data:list<array<string,mixed>>,total:int,pages:int,current:int}
     */
    public function paginateIndex(int $page, int $perPage, array $filters): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset = ($page - 1) * $perPage;

        $where = ['1=1'];
        $params = [];

        if (!empty($filters['tahun'])) {
            $where[] = 'gully_plug.tahun = ?';
            $params[] = (int) $filters['tahun'];
        }
        if (!empty($filters['q'])) {
            $where[] = '(gully_plug.sasaran LIKE ? OR gully_plug.lokasi LIKE ? OR gully_plug.subdas LIKE ?)';
            $like = '%' . $filters['q'] . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $whereSql = implode(' AND ', $where);
        $countStmt = $this->pdo->prepare('SELECT COUNT(*) FROM gully_plug WHERE ' . $whereSql);
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();
        $pages = $total > 0 ? (int) ceil($total / $perPage) : 1;

        $sql = 'SELECT gully_plug.*, ds.nama AS desa_nama
            FROM gully_plug
            LEFT JOIN desa ds ON gully_plug.desa_id = ds.id
            WHERE ' . $whereSql . '
            ORDER BY gully_plug.tahun DESC, gully_plug.id DESC
            LIMIT ' . $perPage . ' OFFSET ' . $offset;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return ['data' => $stmt->fetchAll(\PDO::FETCH_ASSOC), 'total' => $total, 'pages' => $pages, 'current' => $page];
    }

    /** @return array<string,mixed>|false */
    public function findWithRelations(int $id): array|false
    {
        $sql = 'SELECT gully_plug.*, ds.nama AS desa_nama FROM gully_plug LEFT JOIN desa ds ON gully_plug.desa_id = ds.id WHERE gully_plug.id = ? LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row === false ? false : $row;
    }
}
