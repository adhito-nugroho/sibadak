<?php

declare(strict_types=1);

class PenyuluhKehutanan extends BaseModel
{
    protected string $table = 'penyuluh_kehutanan';

    /**
     * @param array{q?:string,status?:string} $filters
     * @return array{data:list<array<string,mixed>>,total:int,pages:int,current:int}
     */
    public function paginateIndex(int $page, int $perPage, array $filters): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset = ($page - 1) * $perPage;

        $where = ['1=1'];
        $params = [];
        if (($filters['q'] ?? '') !== '') {
            $where[] = '(nip LIKE ? OR nama LIKE ? OR pangkat LIKE ? OR jabatan LIKE ?)';
            $like = '%' . $filters['q'] . '%';
            array_push($params, $like, $like, $like, $like);
        }
        if (($filters['status'] ?? '') === 'aktif') {
            $where[] = 'is_active = 1';
        } elseif (($filters['status'] ?? '') === 'nonaktif') {
            $where[] = 'is_active = 0';
        }

        $whereSql = implode(' AND ', $where);
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM penyuluh_kehutanan WHERE ' . $whereSql);
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();
        $pages = $total > 0 ? (int) ceil($total / $perPage) : 1;

        $sql = 'SELECT * FROM penyuluh_kehutanan WHERE ' . $whereSql . ' ORDER BY nama ASC LIMIT ' . $perPage . ' OFFSET ' . $offset;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return [
            'data' => $stmt->fetchAll(\PDO::FETCH_ASSOC),
            'total' => $total,
            'pages' => $pages,
            'current' => $page,
        ];
    }

    /** @return list<array{id:int,nip:string,nama:string,pangkat:string,jabatan:string}> */
    public function options(bool $activeOnly = true): array
    {
        $sql = 'SELECT id, nip, nama, pangkat, jabatan FROM penyuluh_kehutanan';
        if ($activeOnly) {
            $sql .= ' WHERE is_active = 1';
        }
        $sql .= ' ORDER BY nama ASC';

        return $this->query($sql);
    }
}
