<?php

namespace App\Services;

use App\Models\UserModel;

interface IAuthService
{
    public function register(string $name, string $email, string $password): UserModel;
    public function login(string $email, string $password): ?UserModel;
}
