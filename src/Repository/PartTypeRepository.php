<?php
declare(strict_types=1);

namespace App\Repository;

use PDO;
use PDOException;
use RuntimeException;

class PartTypeRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listPartTypes(): array
    {
        $sql = 'SELECT id, name, short_name, description FROM part_types ORDER BY name ASC';
        try {
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new RuntimeException('Failed to fetch part types', 0, $e);
        }
    }

    /**
     * @param array{name: string, short_name: string, description?: ?string} $data
     */
    public function createPartType(array $data): int
    {
        $sql = 'INSERT INTO part_types (name, short_name, description) VALUES (:name, :short_name, :description)';
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':name', $data['name']);
            $stmt->bindValue(':short_name', $data['short_name']);
            $stmt->bindValue(':description', $data['description'] ?? null);
            $stmt->execute();
            return (int) $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            // 23000 = integrity constraint violation (incl. UNIQUE)
            if ($e->getCode() === '23000') {
                throw new RuntimeException('duplicate_part_type', 0, $e);
            }
            throw new RuntimeException('Failed to create part type', 0, $e);
        }
    }

    public function getByShortName(string $short): ?array
    {
        $sql = 'SELECT id, name, short_name, description FROM part_types WHERE short_name = :short LIMIT 1';
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':short', $short);
            $stmt->execute();
            $row = $stmt->fetch();
            return $row === false ? null : $row;
        } catch (PDOException $e) {
            throw new RuntimeException('Failed to fetch part type', 0, $e);
        }
    }
}
