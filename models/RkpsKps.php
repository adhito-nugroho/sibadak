<?php

declare(strict_types=1);

class RkpsKps extends BaseModel
{
    protected string $table = 'kps_rkps';

    /** @return list<string> */
    public static function statusList(): array
    {
        return ['sudah', 'proses', 'belum'];
    }

    /**
     * @param array{kps_id?:int,kabupaten_id?:int,status?:string,q?:string,operator_kab_id?:int} $filters
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
            $where[] = 'kps.kabupaten_id = ?';
            $params[] = (int) $filters['operator_kab_id'];
        } elseif (!empty($filters['kabupaten_id'])) {
            $where[] = 'kps.kabupaten_id = ?';
            $params[] = (int) $filters['kabupaten_id'];
        }
        if (!empty($filters['kps_id'])) {
            $where[] = 'r.kps_id = ?';
            $params[] = (int) $filters['kps_id'];
        }
        if (!empty($filters['status']) && in_array($filters['status'], self::statusList(), true)) {
            $where[] = 'r.status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['q'])) {
            $where[] = '(kps.nama_lembaga LIKE ? OR r.catatan LIKE ?)';
            $like = '%' . $filters['q'] . '%';
            $params[] = $like;
            $params[] = $like;
        }

        $whereSql = implode(' AND ', $where);
        $countStmt = $this->pdo->prepare("SELECT COUNT(*) FROM kps_rkps r JOIN kps ON r.kps_id = kps.id WHERE $whereSql");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();
        $pages = $total > 0 ? (int) ceil($total / $perPage) : 1;

        $sql = "SELECT r.*, kps.nama_lembaga, kps.skema, kb.nama AS kabupaten_nama
            FROM kps_rkps r
            JOIN kps ON r.kps_id = kps.id
            JOIN kabupaten kb ON kps.kabupaten_id = kb.id
            WHERE $whereSql
            ORDER BY r.periode_awal DESC, kps.nama_lembaga ASC
            LIMIT $perPage OFFSET $offset";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return ['data' => $stmt->fetchAll(\PDO::FETCH_ASSOC), 'total' => $total, 'pages' => $pages, 'current' => $page];
    }

    /** @return array<string,mixed>|false */
    public function findWithKps(int $id): array|false
    {
        $sql = 'SELECT r.*, kps.nama_lembaga, kps.skema, kb.nama AS kabupaten_nama
            FROM kps_rkps r
            JOIN kps ON r.kps_id = kps.id
            JOIN kabupaten kb ON kps.kabupaten_id = kb.id
            WHERE r.id = ? LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row === false ? false : $row;
    }

    public function periodeExists(int $kpsId, int $periodeAwal, ?int $exceptId = null): bool
    {
        $sql = 'SELECT 1 FROM kps_rkps WHERE kps_id = ? AND periode_awal = ?';
        $params = [$kpsId, $periodeAwal];
        if ($exceptId !== null) {
            $sql .= ' AND id <> ?';
            $params[] = $exceptId;
        }
        $stmt = $this->pdo->prepare($sql . ' LIMIT 1');
        $stmt->execute($params);
        return (bool) $stmt->fetchColumn();
    }

    /** @return list<array<string,mixed>> */
    public function listByKpsId(int $kpsId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM kps_rkps WHERE kps_id = ? ORDER BY periode_awal DESC');
        $stmt->execute([$kpsId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
