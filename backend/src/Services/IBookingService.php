<?php

namespace App\Services;

interface IBookingService
{
    public function getBookedTimeslotIds(int $courtId, string $date): array;
    public function createBooking(int $userId, int $courtId, string $date, int $timeslotId): int;
    public function getByUserId(int $userId): array;
    public function getBookingById(int $bookingId, int $userId): ?array;
    public function updateBooking(int $bookingId, int $userId, string $date, int $timeslotId): bool;
    public function getBookingByIdForAdmin(int $bookingId): ?array;
    public function updateBookingForAdmin(int $bookingId, string $date, int $timeslotId): bool;
    public function cancelBooking(int $bookingId, int $userId): bool;
    public function getAll(array $filters = []): array;
    public function countAll(array $filters = []): int;
    public function deleteById(int $bookingId): void;
}
