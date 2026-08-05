<?php

declare(strict_types=1);

abstract class BaseModel
{
    protected \PDO $pdo;

    /** @var non-empty-string */
    protected string $table;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /** @return array<string, mixed>|false */
    public function findById(int $id): array|false
    {
        $sql = 'SELECT * FROM `' . $this->table . '` WHERE id = ? LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $row === false ? false : $row;
    }

    /**
     * @param array<string, scalar|null> $conditions kolom => nilai (AND, =)
     * @return list<array<string, mixed>>
     */
    public function findAll(array $conditions = [], string $orderBy = 'id DESC', int $limit = 0): array
    {
        $sql = 'SELECT * FROM `' . $this->table . '`';
        $params = [];
        if ($conditions !== []) {
            $parts = [];
            foreach ($conditions as $col => $val) {
                $parts[] = '`' . str_replace('`', '', (string) $col) . '` = ?';
                $params[] = $val;
            }
            $sql .= ' WHERE ' . implode(' AND ', $parts);
        }
        $sql .= ' ORDER BY ' . $orderBy;
        if ($limit > 0) {
            $sql .= ' LIMIT ' . (int) $limit;
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): int
    {
        $cols = array_keys($data);
        $placeholders = implode(', ', array_fill(0, count($cols), '?'));
        $colList = implode(', ', array_map(static fn (string $c): string => '`' . str_replace('`', '', $c) . '`', $cols));
        $sql = 'INSERT INTO `' . $this->table . '` (' . $colList . ') VALUES (' . $placeholders . ')';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array_values($data));

        return (int) $this->pdo->lastInsertId();
    }

    /** @param array<string, mixed> $data */
    public function update(int $id, array $data): bool
    {
        if ($data === []) {
            return false;
        }
        $sets = [];
        $params = [];
        foreach ($data as $col => $val) {
            $sets[] = '`' . str_replace('`', '', (string) $col) . '` = ?';
            $params[] = $val;
        }
        $params[] = $id;
        $sql = 'UPDATE `' . $this->table . '` SET ' . implode(', ', $sets) . ' WHERE id = ?';
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute($params);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM `' . $this->table . '` WHERE id = ?');

        return $stmt->execute([$id]);
    }

    /**
     * @param array<string, scalar|null> $conditions
     * @return array{data: list<array<string, mixed>>, total: int, pages: int, current: int}
     */
    public function paginate(int $page, int $perPage, array $conditions = [], string $orderBy = 'id DESC'): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset = ($page - 1) * $perPage;

        $where = '';
        $params = [];
        if ($conditions !== []) {
            $parts = [];
            foreach ($conditions as $col => $val) {
                $parts[] = '`' . str_replace('`', '', (string) $col) . '` = ?';
                $params[] = $val;
            }
            $where = ' WHERE ' . implode(' AND ', $parts);
        }

        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM `' . $this->table . '`' . $where);
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();
        $pages = $total > 0 ? (int) ceil($total / $perPage) : 1;

        $sql = 'SELECT * FROM `' . $this->table . '`' . $where . ' ORDER BY ' . $orderBy . ' LIMIT ' . $perPage . ' OFFSET ' . $offset;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return [
            'data' => $data,
            'total' => $total,
            'pages' => $pages,
            'current' => $page,
        ];
    }

    /**
     * @param list<mixed> $params
     * @return list<array<string, mixed>>
     */
    public function query(string $sql, array $params = []): array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
