<?php

namespace App\Repositories;

use App\Models\UserModel;

interface IUserRepository
{
    public function create(UserModel $user): UserModel;
    public function findByEmail(string $email): ?UserModel;
    public function findById(int $id): ?UserModel;
}
