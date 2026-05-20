<?php

namespace App\Controllers;

use App\Services\TimeslotService;
use App\Middleware\JwtHelper;

class TimeslotController
{
    private TimeslotService $timeslotService;

    public function __construct()
    {
        $this->timeslotService = new TimeslotService();
    }

    /**
     * GET /api/timeslots?court_id=X&date=YYYY-MM-DD
     * List timeslots, filtered by court and optionally by date
     */
    public function index(): void
    {
        $courtId = (int)($_GET['court_id'] ?? 0);

        if ($courtId <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'court_id parameter is required.']);
            return;
        }

        try {
            $date = $_GET['date'] ?? '';

            if ($date !== '') {
                $timeslots = $this->timeslotService->getByCourtIdAndDate($courtId, $date);
            } else {
                $timeslots = $this->timeslotService->getByCourtId($courtId);
            }

            $result = [];
            foreach ($timeslots as $t) {
                $result[] = [
                    'id' => $t->id,
                    'court_id' => $t->courtId,
                    'slot_date' => $t->slotDate,
                    'start_time' => $t->startTime,
                    'end_time' => $t->endTime,
                ];
            }

            echo json_encode(['data' => $result]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to load timeslots.']);
        }
    }

    /**
     * GET /api/timeslots/:id
     * Get a single timeslot
     */
    public function getById(int $id): void
    {
        try {
            $timeslot = $this->timeslotService->getById($id);

            if ($timeslot === null) {
                http_response_code(404);
                echo json_encode(['error' => 'Timeslot not found.']);
                return;
            }

            echo json_encode([
                'id' => $timeslot->id,
                'court_id' => $timeslot->courtId,
                'slot_date' => $timeslot->slotDate,
                'start_time' => $timeslot->startTime,
                'end_time' => $timeslot->endTime,
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to load timeslot.']);
        }
    }

    /**
     * POST /api/timeslots (admin only)
     * Create a new timeslot
     */
    public function create(array $body): void
    {
        JwtHelper::requireAdmin();

        $courtId = (int)($body['court_id'] ?? 0);
        $slotDate = trim($body['slot_date'] ?? '');
        $startTime = trim($body['start_time'] ?? '');
        $endTime = trim($body['end_time'] ?? '');

        $errors = [];
        if ($courtId <= 0) $errors[] = 'court_id is required.';
        if ($slotDate === '') $errors[] = 'slot_date is required.';
        if ($startTime === '') $errors[] = 'start_time is required.';
        if ($endTime === '') $errors[] = 'end_time is required.';
        if ($startTime !== '' && $endTime !== '' && strtotime($endTime) <= strtotime($startTime)) {
            $errors[] = 'end_time must be later than start_time.';
        }

        if (!empty($errors)) {
            http_response_code(422);
            echo json_encode(['errors' => $errors]);
            return;
        }

        try {
            $this->timeslotService->create($courtId, $slotDate, $startTime, $endTime);

            // Get the newly created timeslot to return with ID
            $timeslots = $this->timeslotService->getByCourtIdAndDate($courtId, $slotDate);
            $newTimeslot = null;
            foreach ($timeslots as $t) {
                if ($t->startTime === $startTime && $t->endTime === $endTime) {
                    $newTimeslot = $t;
                }
            }

            http_response_code(201);
            echo json_encode([
                'id' => $newTimeslot ? $newTimeslot->id : null,
                'court_id' => $courtId,
                'slot_date' => $slotDate,
                'start_time' => $startTime,
                'end_time' => $endTime,
            ]);
        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => 'Failed to create timeslot: ' . $e->getMessage()]);
        }
    }

    /**
     * PUT /api/timeslots/:id (admin only)
     * Update an existing timeslot
     */
    public function update(int $id, array $body): void
    {
        JwtHelper::requireAdmin();

        $slotDate = trim($body['slot_date'] ?? '');
        $startTime = trim($body['start_time'] ?? '');
        $endTime = trim($body['end_time'] ?? '');

        if ($slotDate === '' || $startTime === '' || $endTime === '') {
            http_response_code(422);
            echo json_encode(['error' => 'slot_date, start_time, and end_time are required.']);
            return;
        }

        if (strtotime($endTime) <= strtotime($startTime)) {
            http_response_code(422);
            echo json_encode(['error' => 'end_time must be later than start_time.']);
            return;
        }

        try {
            $existing = $this->timeslotService->getById($id);
            if ($existing === null) {
                http_response_code(404);
                echo json_encode(['error' => 'Timeslot not found.']);
                return;
            }

            $this->timeslotService->update($id, $slotDate, $startTime, $endTime);
            $updated = $this->timeslotService->getById($id);

            echo json_encode([
                'id' => $updated->id,
                'court_id' => $updated->courtId,
                'slot_date' => $updated->slotDate,
                'start_time' => $updated->startTime,
                'end_time' => $updated->endTime,
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update timeslot.']);
        }
    }

    /**
     * DELETE /api/timeslots/:id (admin only)
     * Delete a timeslot
     */
    public function delete(int $id): void
    {
        JwtHelper::requireAdmin();

        try {
            $existing = $this->timeslotService->getById($id);
            if ($existing === null) {
                http_response_code(404);
                echo json_encode(['error' => 'Timeslot not found.']);
                return;
            }

            $this->timeslotService->delete($id);
            echo json_encode(['message' => 'Timeslot deleted.']);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to delete timeslot.']);
        }
    }
}
