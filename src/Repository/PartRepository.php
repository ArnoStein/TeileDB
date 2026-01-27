<?php
declare(strict_types=1);

namespace App\Repository;

use PDO;
use PDOException;
use RuntimeException;

class PartRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * @param array{status_id?: int, search?: string} $filters
     * @return array<int, array<string, mixed>>
     */
    public function listParts(array $filters = []): array
    {
        $sql = 'SELECT p.id, p.serial_number, pt.short_name AS part_type_short_name, '
            . 'pt.name AS part_type_name, s.name AS status_name, p.created_at '
            . 'FROM parts p '
            . 'JOIN part_types pt ON pt.id = p.part_type_id '
            . 'JOIN statuses s ON s.id = p.status_id';

        $conditions = [];
        $params = [];

        if (isset($filters['status_id'])) {
            $conditions[] = 'p.status_id = :status_id';
            $params['status_id'] = (int) $filters['status_id'];
        }

        if (isset($filters['search']) && trim((string) $filters['search']) !== '') {
            $conditions[] = '(p.serial_number LIKE :search OR pt.short_name LIKE :search OR pt.name LIKE :search)';
            $params['search'] = '%' . trim((string) $filters['search']) . '%';
        }

        if ($conditions !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' ORDER BY p.created_at DESC';

        try {
            $stmt = $this->pdo->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new RuntimeException('Failed to fetch parts list', 0, $e);
        }
    }

    public function getPartById(int $id): ?array
    {
        $sql = 'SELECT p.id, p.serial_number, p.part_type_id, pt.short_name AS part_type_short_name, '
            . 'pt.name AS part_type_name, p.status_id, s.name AS status_name, p.created_at '
            . 'FROM parts p '
            . 'JOIN part_types pt ON pt.id = p.part_type_id '
            . 'JOIN statuses s ON s.id = p.status_id '
            . 'WHERE p.id = :id '
            . 'LIMIT 1';
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch();
            return $row === false ? null : $row;
        } catch (PDOException $e) {
            throw new RuntimeException('Failed to fetch part', 0, $e);
        }
    }

    /**
     * @param array{serial_number:string, part_type_id:int, status_id:int} $data
     */
    public function createPart(array $data): int
    {
        $sql = 'INSERT INTO parts (serial_number, part_type_id, status_id) VALUES (:serial_number, :part_type_id, :status_id)';
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':serial_number', $data['serial_number']);
            $stmt->bindValue(':part_type_id', $data['part_type_id'], PDO::PARAM_INT);
            $stmt->bindValue(':status_id', $data['status_id'], PDO::PARAM_INT);
            $stmt->execute();
            return (int) $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                // UNIQUE constraint (serial_number) verletzt
                throw new RuntimeException('duplicate_serial_number', 0, $e);
            }
            throw new RuntimeException('Failed to create part', 0, $e);
        }
    }

    public function updateStatus(int $partId, int $statusId): void
    {
        $sql = 'UPDATE parts SET status_id = :status_id WHERE id = :id';
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':status_id', $statusId, PDO::PARAM_INT);
            $stmt->bindValue(':id', $partId, PDO::PARAM_INT);
            $stmt->execute();
        } catch (PDOException $e) {
            throw new RuntimeException('Failed to update part status', 0, $e);
        }
    }
}
