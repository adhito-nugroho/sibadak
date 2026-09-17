<?php

declare(strict_types=1);

class RktKps extends BaseModel
{
    protected string $table = 'kps_rkt';

    private static ?bool $hasDokumenLink = null;

    private function hasDokumenLinkColumn(): bool
    {
        if (self::$hasDokumenLink !== null) {
            return self::$hasDokumenLink;
        }
        try {
            $stmt = $this->pdo->prepare("SHOW COLUMNS FROM kps_rkt LIKE 'dokumen_link'");
            $stmt->execute();
            self::$hasDokumenLink = (bool) $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            self::$hasDokumenLink = false;
        }

        return self::$hasDokumenLink;
    }

    /** @return list<string> */
    public static function statusList(): array
    {
        return ['sudah', 'proses', 'belum'];
    }

    /** @return list<int> */
    public static function availableYears(): array
    {
        return range(2023, 2035);
    }

    /**
     * @param array{kabupaten_id?: int, kps_id?: int, tahun?: int, status?: string, q?: string, operator_kab_id?: int} $filters
     * @return array{data: list<array<string,mixed>>, total:int, pages:int, current:int}
     */
    public function paginateIndex(int $page, int $perPage, array $filters): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset = ($page - 1) * $perPage;

        $where = ['1=1'];
        $params = [];

        if (!empty($filters['operator_kab_id'])) {
            $where[] = 'k.kabupaten_id = ?';
            $params[] = (int) $filters['operator_kab_id'];
        } elseif (!empty($filters['kabupaten_id'])) {
            $where[] = 'k.kabupaten_id = ?';
            $params[] = (int) $filters['kabupaten_id'];
        }

        if (!empty($filters['kps_id'])) {
            $where[] = 'r.kps_id = ?';
            $params[] = (int) $filters['kps_id'];
        }

        if (!empty($filters['tahun'])) {
            $where[] = 'r.tahun = ?';
            $params[] = (int) $filters['tahun'];
        }

        if (!empty($filters['status']) && in_array($filters['status'], self::statusList(), true)) {
            $where[] = 'r.status = ?';
            $params[] = $filters['status'];
        }

        if (!empty($filters['q'])) {
            $where[] = '(k.nama_lembaga LIKE ? OR k.no_sk LIKE ? OR r.catatan LIKE ?)';
            $like = '%' . $filters['q'] . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $whereSql = implode(' AND ', $where);

        $sqlCount = 'SELECT COUNT(*)
            FROM kps_rkt r
            INNER JOIN kps k ON r.kps_id = k.id
            WHERE ' . $whereSql;
        $stmt = $this->pdo->prepare($sqlCount);
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();
        $pages = $total > 0 ? (int) ceil($total / $perPage) : 1;

        $sqlList = 'SELECT r.*, k.nama_lembaga AS kps_nama, k.no_sk AS kps_no_sk,
            k.kabupaten_id, kb.nama AS kabupaten_nama
            FROM kps_rkt r
            INNER JOIN kps k ON r.kps_id = k.id
            INNER JOIN kabupaten kb ON k.kabupaten_id = kb.id
            WHERE ' . $whereSql . '
            ORDER BY r.tahun DESC, k.nama_lembaga ASC
            LIMIT ' . $perPage . ' OFFSET ' . $offset;
        $stmt = $this->pdo->prepare($sqlList);
        $stmt->execute($params);
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return ['data' => $data, 'total' => $total, 'pages' => $pages, 'current' => $page];
    }

    /** @return array<string,mixed>|false */
    public function findWithKps(int $id): array|false
    {
        $sql = 'SELECT r.*, k.nama_lembaga AS kps_nama, k.no_sk AS kps_no_sk,
            k.kabupaten_id, kb.nama AS kabupaten_nama
            FROM kps_rkt r
            INNER JOIN kps k ON r.kps_id = k.id
            INNER JOIN kabupaten kb ON k.kabupaten_id = kb.id
            WHERE r.id = ?
            LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $row === false ? false : $row;
    }

    public function uniqueYearExists(int $kpsId, int $year, ?int $excludeId = null): bool
    {
        $sql = 'SELECT 1 FROM kps_rkt WHERE kps_id = ? AND tahun = ?';
        $params = [$kpsId, $year];
        if ($excludeId !== null) {
            $sql .= ' AND id <> ?';
            $params[] = $excludeId;
        }
        $sql .= ' LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return (bool) $stmt->fetchColumn();
    }

    /** @return list<array<string,mixed>> */
    public function listByKpsId(int $kpsId, int $limit = 10): array
    {
        $limit = max(1, min(50, $limit));
        $cols = $this->hasDokumenLinkColumn()
            ? 'id, tahun, status, catatan, dokumen_link'
            : 'id, tahun, status, catatan, NULL AS dokumen_link';
        $sql = 'SELECT ' . $cols . '
            FROM kps_rkt
            WHERE kps_id = ?
            ORDER BY tahun DESC
            LIMIT ' . $limit;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$kpsId]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /** @return array{total:int,sudah:int,proses:int,belum:int,latest_tahun:?int,latest_status:?string} */
    public function summaryByKpsId(int $kpsId): array
    {
        $sql = 'SELECT
            COUNT(*) AS total,
            SUM(CASE WHEN status = \'sudah\' THEN 1 ELSE 0 END) AS sudah,
            SUM(CASE WHEN status = \'proses\' THEN 1 ELSE 0 END) AS proses,
            SUM(CASE WHEN status = \'belum\' THEN 1 ELSE 0 END) AS belum
            FROM kps_rkt
            WHERE kps_id = ?';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$kpsId]);
        $agg = $stmt->fetch(\PDO::FETCH_ASSOC) ?: [];

        $latestStmt = $this->pdo->prepare(
            'SELECT tahun, status FROM kps_rkt WHERE kps_id = ? ORDER BY tahun DESC LIMIT 1'
        );
        $latestStmt->execute([$kpsId]);
        $latest = $latestStmt->fetch(\PDO::FETCH_ASSOC) ?: null;

        return [
            'total' => (int) ($agg['total'] ?? 0),
            'sudah' => (int) ($agg['sudah'] ?? 0),
            'proses' => (int) ($agg['proses'] ?? 0),
            'belum' => (int) ($agg['belum'] ?? 0),
            'latest_tahun' => $latest !== null ? (int) $latest['tahun'] : null,
            'latest_status' => $latest['status'] ?? null,
        ];
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
        $where = 'r.id IN (' . $in['sql'] . ')';
        if ($operatorKabId !== null) {
            $where .= ' AND k.kabupaten_id = ?';
            $params[] = $operatorKabId;
        }
        $sql = 'SELECT r.*, k.nama_lembaga AS kps_nama, k.no_sk AS kps_no_sk,
            k.kabupaten_id, kb.nama AS kabupaten_nama
            FROM kps_rkt r
            INNER JOIN kps k ON r.kps_id = k.id
            INNER JOIN kabupaten kb ON k.kabupaten_id = kb.id
            WHERE ' . $where . '
            ORDER BY r.tahun DESC, k.nama_lembaga ASC';

        return $this->query($sql, $params);
    }
}
