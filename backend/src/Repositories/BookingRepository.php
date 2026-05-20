<?php

namespace App\Repositories;

use PDO;

class BookingRepository extends Repository implements IBookingRepository
{
    public function getBookedTimeslotIds(int $courtId, string $date): array
    {
        $pdo = $this->getConnection();
        $sql = "SELECT timeslot_id FROM bookings WHERE court_id = :court_id AND date = :date";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['court_id' => $courtId, 'date' => $date]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $ids = [];
        foreach ($rows as $row) {
            $ids[] = (int)$row['timeslot_id'];
        }
        return $ids;
    }

    /**
     * Create a booking and return the new booking ID
     */
    public function createBooking(int $userId, int $courtId, string $date, int $timeslotId): int
    {
        $pdo = $this->getConnection();
        $sql = "INSERT INTO bookings (user_id, court_id, date, timeslot_id)
                VALUES (:user_id, :court_id, :date, :timeslot_id)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'user_id' => $userId,
            'court_id' => $courtId,
            'date' => $date,
            'timeslot_id' => $timeslotId
        ]);
        return (int)$pdo->lastInsertId();
    }

    public function getByUserId(int $userId): array
    {
        $pdo = $this->getConnection();
        $sql = "
            SELECT
                b.id AS booking_id,
                b.user_id,
                b.court_id,
                b.date,
                b.timeslot_id,
                c.name AS court_name,
                c.location AS court_location,
                t.start_time,
                t.end_time
            FROM bookings b
            INNER JOIN courts c ON c.id = b.court_id
            INNER JOIN timeslots t ON t.id = b.timeslot_id
            WHERE b.user_id = :user_id
            ORDER BY b.date ASC, t.start_time ASC
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByIdAndUserId(int $bookingId, int $userId): ?array
    {
        $pdo = $this->getConnection();
        $sql = "
            SELECT b.id AS booking_id, b.user_id, b.court_id, b.date, b.timeslot_id,
                   c.name AS court_name, t.start_time, t.end_time
            FROM bookings b
            INNER JOIN courts c ON c.id = b.court_id
            INNER JOIN timeslots t ON t.id = b.timeslot_id
            WHERE b.id = :id AND b.user_id = :user_id
            LIMIT 1
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $bookingId, 'user_id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function isSlotTaken(int $courtId, string $date, int $timeslotId, ?int $excludeBookingId = null): bool
    {
        $pdo = $this->getConnection();
        $sql = "SELECT 1 FROM bookings WHERE court_id = :court_id AND date = :date AND timeslot_id = :timeslot_id";
        $params = ['court_id' => $courtId, 'date' => $date, 'timeslot_id' => $timeslotId];
        if ($excludeBookingId !== null) {
            $sql .= " AND id != :exclude_id";
            $params['exclude_id'] = $excludeBookingId;
        }
        $sql .= " LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (bool)$stmt->fetch();
    }

    public function isTimeslotForCourtAndDate(int $courtId, string $date, int $timeslotId): bool
    {
        $pdo = $this->getConnection();
        $sql = "SELECT 1 FROM timeslots WHERE id = :id AND court_id = :court_id AND slot_date = :slot_date LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $timeslotId, 'court_id' => $courtId, 'slot_date' => $date]);
        return (bool)$stmt->fetch();
    }

    public function updateBooking(int $bookingId, int $userId, string $date, int $timeslotId): bool
    {
        $pdo = $this->getConnection();
        $sql = "UPDATE bookings SET date = :date, timeslot_id = :timeslot_id WHERE id = :id AND user_id = :user_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $bookingId, 'user_id' => $userId, 'date' => $date, 'timeslot_id' => $timeslotId]);
        return $stmt->rowCount() > 0;
    }

    public function getById(int $bookingId): ?array
    {
        $pdo = $this->getConnection();
        $sql = "
            SELECT b.id AS booking_id, b.user_id, b.court_id, b.date, b.timeslot_id,
                   c.name AS court_name, c.location AS court_location,
                   t.start_time, t.end_time,
                   u.name AS user_name, u.email AS user_email
            FROM bookings b
            INNER JOIN courts c ON c.id = b.court_id
            INNER JOIN timeslots t ON t.id = b.timeslot_id
            INNER JOIN users u ON u.id = b.user_id
            WHERE b.id = :id
            LIMIT 1
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $bookingId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function updateBookingById(int $bookingId, string $date, int $timeslotId): bool
    {
        $pdo = $this->getConnection();
        $sql = "UPDATE bookings SET date = :date, timeslot_id = :timeslot_id WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $bookingId, 'date' => $date, 'timeslot_id' => $timeslotId]);
        return $stmt->rowCount() > 0;
    }

    public function deleteByIdAndUserId(int $bookingId, int $userId): bool
    {
        $pdo = $this->getConnection();
        $sql = "DELETE FROM bookings WHERE id = :id AND user_id = :user_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $bookingId, 'user_id' => $userId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Get all bookings with filtering and pagination (admin)
     */
    public function getAll(array $filters = []): array
    {
        $pdo = $this->getConnection();

        $sql = "
            SELECT
                b.id AS booking_id,
                b.user_id,
                b.court_id,
                b.date,
                b.timeslot_id,
                b.created_at,
                u.name AS user_name,
                u.email AS user_email,
                c.name AS court_name,
                c.location AS court_location,
                t.start_time,
                t.end_time
            FROM bookings b
            INNER JOIN users u ON u.id = b.user_id
            INNER JOIN courts c ON c.id = b.court_id
            INNER JOIN timeslots t ON t.id = b.timeslot_id
        ";
        $params = [];
        $conditions = [];

        if (!empty($filters['date'])) {
            $conditions[] = "b.date = :date";
            $params['date'] = $filters['date'];
        }
        if (!empty($filters['court_id'])) {
            $conditions[] = "b.court_id = :court_id";
            $params['court_id'] = (int)$filters['court_id'];
        }
        if (!empty($filters['user_id'])) {
            $conditions[] = "b.user_id = :user_id";
            $params['user_id'] = (int)$filters['user_id'];
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        // Sorting
        $sortField = $filters['sort'] ?? 'date';
        $sortDir = (isset($filters['order']) && strtolower($filters['order']) === 'desc') ? 'DESC' : 'ASC';
        $allowedSorts = ['date', 'court_name', 'user_name', 'created_at'];
        if (!in_array($sortField, $allowedSorts)) {
            $sortField = 'date';
        }
        if ($sortField === 'date') {
            $sql .= " ORDER BY b.date $sortDir, t.start_time $sortDir";
        } elseif ($sortField === 'created_at') {
            $sql .= " ORDER BY b.created_at $sortDir";
        } else {
            $sql .= " ORDER BY $sortField $sortDir";
        }

        // Pagination
        if (isset($filters['limit'])) {
            $limit = (int)$filters['limit'];
            $offset = (int)($filters['offset'] ?? 0);
            $sql .= " LIMIT $limit OFFSET $offset";
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Count total bookings for pagination
     */
    public function countAll(array $filters = []): int
    {
        $pdo = $this->getConnection();

        $sql = "SELECT COUNT(*) FROM bookings b";
        $params = [];
        $conditions = [];

        if (!empty($filters['date'])) {
            $conditions[] = "b.date = :date";
            $params['date'] = $filters['date'];
        }
        if (!empty($filters['court_id'])) {
            $conditions[] = "b.court_id = :court_id";
            $params['court_id'] = (int)$filters['court_id'];
        }
        if (!empty($filters['user_id'])) {
            $conditions[] = "b.user_id = :user_id";
            $params['user_id'] = (int)$filters['user_id'];
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function deleteById(int $bookingId): void
    {
        $pdo = $this->getConnection();
        $sql = "DELETE FROM bookings WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $bookingId]);
    }
}
