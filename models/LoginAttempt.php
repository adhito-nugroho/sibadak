<?php

declare(strict_types=1);

class LoginAttempt
{
    public function __construct(private \PDO $pdo)
    {
    }

    public function countRecent(string $ipAddress, string $username, int $windowSeconds = 60): int
    {
        $window = max(1, $windowSeconds);
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM login_attempts
             WHERE ip_address = ? AND username = ?
               AND attempted_at >= DATE_SUB(NOW(), INTERVAL ' . $window . ' SECOND)'
        );
        $stmt->execute([$ipAddress, $username]);

        return (int) $stmt->fetchColumn();
    }

    public function record(string $ipAddress, string $username): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO login_attempts (ip_address, username) VALUES (?, ?)'
        );
        $stmt->execute([$ipAddress, $username]);
    }

    public function clear(string $ipAddress, string $username): void
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM login_attempts WHERE ip_address = ? AND username = ?'
        );
        $stmt->execute([$ipAddress, $username]);
    }
}
