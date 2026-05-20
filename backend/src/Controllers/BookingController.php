<?php

namespace App\Controllers;

use App\Services\BookingService;
use App\Middleware\JwtHelper;

class BookingController
{
    private BookingService $bookingService;

    public function __construct()
    {
        $this->bookingService = new BookingService();
    }

    /**
     * GET /api/bookings (admin only)
     * List all bookings with filtering and pagination
     */
    public function index(): void
    {
        $user = JwtHelper::requireAdmin();

        try {
            $filters = [];

            if (!empty($_GET['date'])) {
                $filters['date'] = $_GET['date'];
            }
            if (!empty($_GET['court_id'])) {
                $filters['court_id'] = (int)$_GET['court_id'];
            }
            if (!empty($_GET['user_id'])) {
                $filters['user_id'] = (int)$_GET['user_id'];
            }
            if (!empty($_GET['sort'])) {
                $filters['sort'] = $_GET['sort'];
            }
            if (!empty($_GET['order'])) {
                $filters['order'] = $_GET['order'];
            }
            if (isset($_GET['limit'])) {
                $filters['limit'] = (int)$_GET['limit'];
                $filters['offset'] = (int)($_GET['offset'] ?? 0);
            }

            $bookings = $this->bookingService->getAll($filters);
            $total = $this->bookingService->countAll($filters);

            echo json_encode([
                'data' => $bookings,
                'total' => $total,
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to load bookings.']);
        }
    }

    /**
     * GET /api/bookings/my
     * Get current user's bookings
     */
    public function myBookings(): void
    {
        $user = JwtHelper::requireAuth();

        try {
            $bookings = $this->bookingService->getByUserId($user->id);

            echo json_encode([
                'data' => $bookings,
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to load your bookings.']);
        }
    }

    /**
     * GET /api/bookings/:id
     * Get a single booking
     */
    public function getById(int $id): void
    {
        $user = JwtHelper::requireAuth();

        try {
            if ($user->role === 'admin') {
                $booking = $this->bookingService->getBookingByIdForAdmin($id);
            } else {
                $booking = $this->bookingService->getBookingById($id, $user->id);
            }

            if ($booking === null) {
                http_response_code(404);
                echo json_encode(['error' => 'Booking not found.']);
                return;
            }

            echo json_encode($booking);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to load booking.']);
        }
    }

    /**
     * POST /api/bookings
     * Create a new booking (authenticated users)
     */
    public function create(array $body): void
    {
        $user = JwtHelper::requireAuth();

        $courtId = (int)($body['court_id'] ?? 0);
        $date = trim($body['date'] ?? '');
        $timeslotId = (int)($body['timeslot_id'] ?? 0);

        $errors = [];
        if ($courtId <= 0) $errors[] = 'court_id is required.';
        if ($date === '') $errors[] = 'date is required (YYYY-MM-DD).';
        if ($timeslotId <= 0) $errors[] = 'timeslot_id is required.';

        if (!empty($errors)) {
            http_response_code(422);
            echo json_encode(['errors' => $errors]);
            return;
        }

        try {
            $bookingId = $this->bookingService->createBooking($user->id, $courtId, $date, $timeslotId);

            // Return the newly created booking with its ID
            $booking = $this->bookingService->getBookingByIdForAdmin($bookingId);

            http_response_code(201);
            echo json_encode($booking);
        } catch (\RuntimeException $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create booking.']);
        }
    }

    /**
     * PUT /api/bookings/:id
     * Update a booking (user can update own, admin can update any)
     */
    public function update(int $id, array $body): void
    {
        $user = JwtHelper::requireAuth();

        $date = trim($body['date'] ?? '');
        $timeslotId = (int)($body['timeslot_id'] ?? 0);

        if ($date === '' || $timeslotId <= 0) {
            http_response_code(422);
            echo json_encode(['error' => 'date and timeslot_id are required.']);
            return;
        }

        try {
            if ($user->role === 'admin') {
                $updated = $this->bookingService->updateBookingForAdmin($id, $date, $timeslotId);
            } else {
                $updated = $this->bookingService->updateBooking($id, $user->id, $date, $timeslotId);
            }

            if (!$updated) {
                http_response_code(400);
                echo json_encode(['error' => 'Could not update booking. It may not exist, or the slot is already taken.']);
                return;
            }

            // Return updated booking
            $booking = $this->bookingService->getBookingByIdForAdmin($id);
            echo json_encode($booking);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update booking.']);
        }
    }

    /**
     * DELETE /api/bookings/:id
     * Delete a booking (user can delete own, admin can delete any)
     */
    public function delete(int $id): void
    {
        $user = JwtHelper::requireAuth();

        try {
            if ($user->role === 'admin') {
                $booking = $this->bookingService->getBookingByIdForAdmin($id);
                if ($booking === null) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Booking not found.']);
                    return;
                }
                $this->bookingService->deleteById($id);
            } else {
                $deleted = $this->bookingService->cancelBooking($id, $user->id);
                if (!$deleted) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Booking not found or you do not have permission.']);
                    return;
                }
            }

            echo json_encode(['message' => 'Booking deleted.']);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to delete booking.']);
        }
    }
}
