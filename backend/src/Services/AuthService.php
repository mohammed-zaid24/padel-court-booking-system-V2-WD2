<?php

namespace App\Services;

use App\Models\UserModel;
use App\Repositories\UserRepository;

class AuthService implements IAuthService
{
    private UserRepository $userRepository;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
    }

    /**
     * Register a new user and return the created user model
     */
    public function register(string $name, string $email, string $password): UserModel
    {
        // Check if email already exists
        $existing = $this->userRepository->findByEmail($email);
        if ($existing !== null) {
            throw new \Exception('An account with this email already exists.');
        }

        // Hash the password
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // Create user model
        $user = new UserModel(
            null,
            $name,
            $email,
            $passwordHash,
            'user'
        );

        // Save user in DB and return with ID
        return $this->userRepository->create($user);
    }

    /**
     * Login and return user model, or null if credentials are wrong
     */
    public function login(string $email, string $password): ?UserModel
    {
        $user = $this->userRepository->findByEmail($email);

        if ($user === null) {
            return null;
        }

        // Check password against stored hash
        if (!password_verify($password, $user->passwordHash)) {
            return null;
        }

        return $user;
    }
}
