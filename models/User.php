<?php

declare(strict_types=1);

class User
{
    public function __construct(private \PDO $pdo)
    {
    }

    /** @return array<string, mixed>|false */
    public function findActiveByUsername(string $username): array|false
    {
        $sql = 'SELECT u.id, u.nama, u.username, u.email, u.password, u.role_id, u.kabupaten_id, u.is_active,
                       r.nama AS role_nama
                FROM users u
                INNER JOIN roles r ON r.id = u.role_id
                WHERE u.username = :u AND u.is_active = 1
                LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['u' => $username]);

        $row = $stmt->fetch();
        return $row === false ? false : $row;
    }

    public function touchLastLogin(int $userId): void
    {
        $stmt = $this->pdo->prepare('UPDATE users SET last_login = NOW() WHERE id = :id');
        $stmt->execute(['id' => $userId]);
    }

    /**
     * @param array{q?:string} $filters
     * @return array{data:list<array<string,mixed>>,total:int,pages:int,current:int}
     */
    public function paginateIndex(int $page, int $perPage, array $filters = []): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset = ($page - 1) * $perPage;

        $where = ['1=1'];
        $params = [];

        if (!empty($filters['q'])) {
            $where[] = '(u.nama LIKE ? OR u.username LIKE ? OR u.email LIKE ?)';
            $like = '%' . $filters['q'] . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $whereSql = implode(' AND ', $where);

        $countStmt = $this->pdo->prepare('SELECT COUNT(*) FROM users u WHERE ' . $whereSql);
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();
        $pages = $total > 0 ? (int) ceil($total / $perPage) : 1;

        $sql = 'SELECT u.id, u.nama, u.username, u.email, u.role_id, u.kabupaten_id, u.is_active, u.last_login, u.created_at,
                       r.nama AS role_nama, kb.nama AS kabupaten_nama
                FROM users u
                INNER JOIN roles r ON r.id = u.role_id
                LEFT JOIN kabupaten kb ON u.kabupaten_id = kb.id
                WHERE ' . $whereSql . '
                ORDER BY u.id ASC
                LIMIT ' . $perPage . ' OFFSET ' . $offset;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return ['data' => $rows, 'total' => $total, 'pages' => $pages, 'current' => $page];
    }

    /** @return array<string,mixed>|false */
    public function findById(int $id): array|false
    {
        $sql = 'SELECT u.*, r.nama AS role_nama, kb.nama AS kabupaten_nama
                FROM users u
                INNER JOIN roles r ON r.id = u.role_id
                LEFT JOIN kabupaten kb ON u.kabupaten_id = kb.id
                WHERE u.id = ? LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row === false ? false : $row;
    }

    public function usernameExists(string $username, ?int $exceptId = null): bool
    {
        $sql = 'SELECT 1 FROM users WHERE username = ?';
        $params = [$username];
        if ($exceptId !== null) {
            $sql .= ' AND id <> ?';
            $params[] = $exceptId;
        }
        $stmt = $this->pdo->prepare($sql . ' LIMIT 1');
        $stmt->execute($params);
        return (bool) $stmt->fetchColumn();
    }

    public function emailExists(string $email, ?int $exceptId = null): bool
    {
        $sql = 'SELECT 1 FROM users WHERE email = ?';
        $params = [$email];
        if ($exceptId !== null) {
            $sql .= ' AND id <> ?';
            $params[] = $exceptId;
        }
        $stmt = $this->pdo->prepare($sql . ' LIMIT 1');
        $stmt->execute($params);
        return (bool) $stmt->fetchColumn();
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO users (nama, username, email, password, role_id, kabupaten_id, is_active)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['nama'],
            $data['username'],
            $data['email'],
            $data['password'],
            $data['role_id'],
            $data['kabupaten_id'],
            $data['is_active'] ?? 1,
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $sets = [];
        $params = [];
        foreach ($data as $col => $val) {
            $sets[] = $col . ' = ?';
            $params[] = $val;
        }
        $params[] = $id;
        $this->pdo->prepare('UPDATE users SET ' . implode(', ', $sets) . ' WHERE id = ?')->execute($params);
    }

    public function toggleActive(int $id): bool
    {
        $stmt = $this->pdo->prepare('UPDATE users SET is_active = IF(is_active = 1, 0, 1) WHERE id = ?');
        $stmt->execute([$id]);
        // Return new state
        $s = $this->pdo->prepare('SELECT is_active FROM users WHERE id = ?');
        $s->execute([$id]);
        return (bool) $s->fetchColumn();
    }

    /** @return list<array{id:int,nama:string}> */
    public function listRoles(): array
    {
        return $this->pdo->query('SELECT id, nama FROM roles ORDER BY id')->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function generatePassword(int $length = 10): string
    {
        $chars = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789';
        $pw = '';
        for ($i = 0; $i < $length; $i++) {
            $pw .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $pw;
    }
}
