<?php

namespace App\Repositories;

use App\Models\CourtModel;

interface ICourtRepository
{
    public function getAll(array $filters = []): array;
    public function countAll(array $filters = []): int;
    public function getById(int $id): ?CourtModel;
    public function create(string $name, string $location): int;
    public function update(int $id, string $name, string $location): void;
    public function delete(int $id): void;
}
