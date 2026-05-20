<?php

namespace App\Controllers;

use App\Services\CourtService;
use App\Services\TimeslotService;
use App\Services\BookingService;
use App\Middleware\JwtHelper;

class CourtController
{
    private CourtService $courtService;
    private TimeslotService $timeslotService;
    private BookingService $bookingService;

    public function __construct()
    {
        $this->courtService = new CourtService();
        $this->timeslotService = new TimeslotService();
        $this->bookingService = new BookingService();
    }

    /**
     * GET /api/courts
     * List all courts with optional filtering and pagination
     */
    public function index(): void
    {
        try {
            $filters = [];

            if (!empty($_GET['name'])) {
                $filters['name'] = $_GET['name'];
            }
            if (!empty($_GET['location'])) {
                $filters['location'] = $_GET['location'];
            }
            if (isset($_GET['limit'])) {
                $filters['limit'] = (int)$_GET['limit'];
                $filters['offset'] = (int)($_GET['offset'] ?? 0);
            }

            $courts = $this->courtService->getAll($filters);
            $total = $this->courtService->countAll($filters);

            $result = [];
            foreach ($courts as $court) {
                $result[] = [
                    'id' => $court->id,
                    'name' => $court->name,
                    'location' => $court->location,
                ];
            }

            echo json_encode([
                'data' => $result,
                'total' => $total,
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to load courts.']);
        }
    }

    /**
     * GET /api/courts/:id
     * Get a single court by ID
     */
    public function getById(int $id): void
    {
        try {
            $court = $this->courtService->getById($id);

            if ($court === null) {
                http_response_code(404);
                echo json_encode(['error' => 'Court not found.']);
                return;
            }

            echo json_encode([
                'id' => $court->id,
                'name' => $court->name,
                'location' => $court->location,
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to load court.']);
        }
    }

    /**
     * POST /api/courts (admin only)
     * Create a new court
     */
    public function create(array $body): void
    {
        $user = JwtHelper::requireAdmin();

        $name = trim($body['name'] ?? '');
        $location = trim($body['location'] ?? '');

        if ($name === '' || $location === '') {
            http_response_code(422);
            echo json_encode(['error' => 'Name and location are required.']);
            return;
        }

        try {
            $id = $this->courtService->create($name, $location);
            $court = $this->courtService->getById($id);

            http_response_code(201);
            echo json_encode([
                'id' => $court->id,
                'name' => $court->name,
                'location' => $court->location,
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create court.']);
        }
    }

    /**
     * PUT /api/courts/:id (admin only)
     * Update an existing court
     */
    public function update(int $id, array $body): void
    {
        $user = JwtHelper::requireAdmin();

        $name = trim($body['name'] ?? '');
        $location = trim($body['location'] ?? '');

        if ($name === '' || $location === '') {
            http_response_code(422);
            echo json_encode(['error' => 'Name and location are required.']);
            return;
        }

        try {
            $court = $this->courtService->getById($id);
            if ($court === null) {
                http_response_code(404);
                echo json_encode(['error' => 'Court not found.']);
                return;
            }

            $this->courtService->update($id, $name, $location);
            $updated = $this->courtService->getById($id);

            echo json_encode([
                'id' => $updated->id,
                'name' => $updated->name,
                'location' => $updated->location,
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update court.']);
        }
    }

    /**
     * DELETE /api/courts/:id (admin only)
     * Delete a court
     */
    public function delete(int $id): void
    {
        $user = JwtHelper::requireAdmin();

        try {
            $court = $this->courtService->getById($id);
            if ($court === null) {
                http_response_code(404);
                echo json_encode(['error' => 'Court not found.']);
                return;
            }

            $this->courtService->delete($id);
            echo json_encode(['message' => 'Court deleted.']);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to delete court.']);
        }
    }

    /**
     * GET /api/courts/:id/availability?date=YYYY-MM-DD
     * Get available timeslots for a court on a specific date
     */
    public function availability(int $courtId): void
    {
        $date = $_GET['date'] ?? '';

        if ($date === '') {
            http_response_code(400);
            echo json_encode(['error' => 'Date parameter is required (format: YYYY-MM-DD).']);
            return;
        }

        try {
            $court = $this->courtService->getById($courtId);
            if ($court === null) {
                http_response_code(404);
                echo json_encode(['error' => 'Court not found.']);
                return;
            }

            $timeslots = $this->timeslotService->getByCourtIdAndDate($courtId, $date);
            $bookedIds = $this->bookingService->getBookedTimeslotIds($courtId, $date);

            $result = [];
            foreach ($timeslots as $t) {
                $result[] = [
                    'id' => $t->id,
                    'start_time' => $t->startTime,
                    'end_time' => $t->endTime,
                    'is_booked' => in_array($t->id, $bookedIds, true),
                ];
            }

            echo json_encode([
                'court_id' => $courtId,
                'date' => $date,
                'slots' => $result,
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to load availability.']);
        }
    }
}
