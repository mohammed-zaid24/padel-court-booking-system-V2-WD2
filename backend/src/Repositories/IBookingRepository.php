<?php

namespace App\Repositories;

interface IBookingRepository
{
    public function getBookedTimeslotIds(int $courtId, string $date): array;
    public function createBooking(int $userId, int $courtId, string $date, int $timeslotId): int;
    public function getByUserId(int $userId): array;
    public function getByIdAndUserId(int $bookingId, int $userId): ?array;
    public function isSlotTaken(int $courtId, string $date, int $timeslotId, ?int $excludeBookingId = null): bool;
    public function isTimeslotForCourtAndDate(int $courtId, string $date, int $timeslotId): bool;
    public function updateBooking(int $bookingId, int $userId, string $date, int $timeslotId): bool;
    public function getById(int $bookingId): ?array;
    public function updateBookingById(int $bookingId, string $date, int $timeslotId): bool;
    public function deleteByIdAndUserId(int $bookingId, int $userId): bool;
    public function getAll(array $filters = []): array;
    public function countAll(array $filters = []): int;
    public function deleteById(int $bookingId): void;
}
