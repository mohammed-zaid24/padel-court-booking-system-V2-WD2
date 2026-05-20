<?php

namespace App\Services;

use App\Repositories\BookingRepository;

class BookingService implements IBookingService
{
    private BookingRepository $bookingRepository;

    public function __construct()
    {
        $this->bookingRepository = new BookingRepository();
    }

    public function getBookedTimeslotIds(int $courtId, string $date): array
    {
        return $this->bookingRepository->getBookedTimeslotIds($courtId, $date);
    }

    /**
     * Create a booking and return the new booking ID
     */
    public function createBooking(int $userId, int $courtId, string $date, int $timeslotId): int
    {
        // Validate timeslot belongs to court+date
        if (!$this->bookingRepository->isTimeslotForCourtAndDate($courtId, $date, $timeslotId)) {
            throw new \RuntimeException('Selected timeslot is not available for the chosen date.');
        }

        // Check double booking
        if ($this->bookingRepository->isSlotTaken($courtId, $date, $timeslotId)) {
            throw new \RuntimeException('This time slot is already booked.');
        }

        // Prevent booking in the past
        if (strtotime($date) < strtotime(date('Y-m-d'))) {
            throw new \RuntimeException('Cannot book a date in the past.');
        }

        return $this->bookingRepository->createBooking($userId, $courtId, $date, $timeslotId);
    }

    public function getByUserId(int $userId): array
    {
        return $this->bookingRepository->getByUserId($userId);
    }

    public function getBookingById(int $bookingId, int $userId): ?array
    {
        return $this->bookingRepository->getByIdAndUserId($bookingId, $userId);
    }

    public function updateBooking(int $bookingId, int $userId, string $date, int $timeslotId): bool
    {
        $booking = $this->bookingRepository->getByIdAndUserId($bookingId, $userId);
        if ($booking === null) {
            return false;
        }
        $courtId = (int)$booking['court_id'];
        if (!$this->bookingRepository->isTimeslotForCourtAndDate($courtId, $date, $timeslotId)) {
            return false;
        }
        if ($this->bookingRepository->isSlotTaken($courtId, $date, $timeslotId, $bookingId)) {
            return false;
        }
        return $this->bookingRepository->updateBooking($bookingId, $userId, $date, $timeslotId);
    }

    public function getBookingByIdForAdmin(int $bookingId): ?array
    {
        return $this->bookingRepository->getById($bookingId);
    }

    public function updateBookingForAdmin(int $bookingId, string $date, int $timeslotId): bool
    {
        $booking = $this->bookingRepository->getById($bookingId);
        if ($booking === null) {
            return false;
        }
        $courtId = (int)$booking['court_id'];
        if (!$this->bookingRepository->isTimeslotForCourtAndDate($courtId, $date, $timeslotId)) {
            return false;
        }
        if ($this->bookingRepository->isSlotTaken($courtId, $date, $timeslotId, $bookingId)) {
            return false;
        }
        return $this->bookingRepository->updateBookingById($bookingId, $date, $timeslotId);
    }

    public function cancelBooking(int $bookingId, int $userId): bool
    {
        return $this->bookingRepository->deleteByIdAndUserId($bookingId, $userId);
    }

    public function getAll(array $filters = []): array
    {
        return $this->bookingRepository->getAll($filters);
    }

    public function countAll(array $filters = []): int
    {
        return $this->bookingRepository->countAll($filters);
    }

    public function deleteById(int $bookingId): void
    {
        $this->bookingRepository->deleteById($bookingId);
    }
}
