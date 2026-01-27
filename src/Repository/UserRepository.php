<?php
declare(strict_types=1);

namespace App\Repository;

use PDO;
use PDOException;
use RuntimeException;

class UserRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findByUsername(string $username): ?array
    {
        $sql = 'SELECT id, username, password_hash, is_active, last_login_at FROM users WHERE username = :username LIMIT 1';
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':username', $username);
            $stmt->execute();
            $row = $stmt->fetch();
            return $row === false ? null : $row;
        } catch (PDOException $e) {
            throw new RuntimeException('Failed to fetch user by username', 0, $e);
        }
    }

    public function findById(int $id): ?array
    {
        $sql = 'SELECT id, username, password_hash, is_active, last_login_at FROM users WHERE id = :id LIMIT 1';
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch();
            return $row === false ? null : $row;
        } catch (PDOException $e) {
            throw new RuntimeException('Failed to fetch user by id', 0, $e);
        }
    }

    public function setPasswordHash(int $userId, string $hash): void
    {
        $sql = 'UPDATE users SET password_hash = :hash WHERE id = :id';
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':hash', $hash);
            $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
            $stmt->execute();
        } catch (PDOException $e) {
            throw new RuntimeException('Failed to update password hash', 0, $e);
        }
    }

    public function updateLastLogin(int $userId): void
    {
        $sql = 'UPDATE users SET last_login_at = CURRENT_TIMESTAMP WHERE id = :id';
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
            $stmt->execute();
        } catch (PDOException $e) {
            throw new RuntimeException('Failed to update last login', 0, $e);
        }
    }
}
