<?php
declare(strict_types=1);

namespace App\Repository;

use PDO;
use PDOException;
use RuntimeException;

class PartCommentRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listForPart(int $partId): array
    {
        $sql = 'SELECT id, comment, created_at FROM part_comments '
            . 'WHERE part_id = :part_id '
            . 'ORDER BY created_at DESC, id DESC';
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':part_id', $partId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new RuntimeException('Failed to fetch comments', 0, $e);
        }
    }

    public function createForPart(int $partId, string $comment): int
    {
        $sql = 'INSERT INTO part_comments (part_id, comment) VALUES (:part_id, :comment)';
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':part_id', $partId, PDO::PARAM_INT);
            $stmt->bindValue(':comment', $comment);
            $stmt->execute();
            return (int) $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            throw new RuntimeException('Failed to create comment', 0, $e);
        }
    }
}
