<?php
declare(strict_types=1);

namespace App\Repository;

use PDO;
use PDOException;
use RuntimeException;

class StatusRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listStatusesOrdered(): array
    {
        $sql = 'SELECT id, name FROM statuses ORDER BY sort_order ASC, id ASC';
        try {
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new RuntimeException('Failed to fetch statuses', 0, $e);
        }
    }
}
