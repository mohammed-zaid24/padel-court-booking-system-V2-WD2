<?php

namespace App\Repositories;

use App\Models\CourtModel;
use PDO;

class CourtRepository extends Repository implements ICourtRepository
{
    /**
     * Get all courts with optional filtering and pagination
     */
    public function getAll(array $filters = []): array
    {
        $pdo = $this->getConnection();

        $sql = "SELECT id, name, location FROM courts";
        $params = [];
        $conditions = [];

        if (!empty($filters['name'])) {
            $conditions[] = "name LIKE :name";
            $params['name'] = '%' . $filters['name'] . '%';
        }

        if (!empty($filters['location'])) {
            $conditions[] = "location LIKE :location";
            $params['location'] = '%' . $filters['location'] . '%';
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        $sql .= " ORDER BY id ASC";

        if (isset($filters['limit'])) {
            $limit = (int)$filters['limit'];
            $offset = (int)($filters['offset'] ?? 0);
            $sql .= " LIMIT $limit OFFSET $offset";
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $courts = [];
        foreach ($rows as $row) {
            $courts[] = new CourtModel(
                (int)$row['id'],
                $row['name'],
                $row['location']
            );
        }

        return $courts;
    }

    public function countAll(array $filters = []): int
    {
        $pdo = $this->getConnection();

        $sql = "SELECT COUNT(*) FROM courts";
        $params = [];
        $conditions = [];

        if (!empty($filters['name'])) {
            $conditions[] = "name LIKE :name";
            $params['name'] = '%' . $filters['name'] . '%';
        }
        if (!empty($filters['location'])) {
            $conditions[] = "location LIKE :location";
            $params['location'] = '%' . $filters['location'] . '%';
        }
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function getById(int $id): ?CourtModel
    {
        $pdo = $this->getConnection();
        $sql = "SELECT id, name, location FROM courts WHERE id = :id LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return null;
        return new CourtModel((int)$row['id'], $row['name'], $row['location']);
    }

    public function create(string $name, string $location): int
    {
        $pdo = $this->getConnection();
        $sql = "INSERT INTO courts (name, location) VALUES (:name, :location)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['name' => $name, 'location' => $location]);
        return (int)$pdo->lastInsertId();
    }

    public function update(int $id, string $name, string $location): void
    {
        $pdo = $this->getConnection();
        $sql = "UPDATE courts SET name = :name, location = :location WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id, 'name' => $name, 'location' => $location]);
    }

    public function delete(int $id): void
    {
        $pdo = $this->getConnection();
        $sql = "DELETE FROM courts WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
    }
}
