<?php

namespace App\Controllers;

use App\Services\AuthService;
use App\Middleware\JwtHelper;

class AuthController
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    /**
     * POST /api/auth/register
     * Register a new user account
     */
    public function register(array $body): void
    {
        $name = trim($body['name'] ?? '');
        $email = trim($body['email'] ?? '');
        $password = $body['password'] ?? '';

        // Validation
        $errors = [];
        if ($name === '') {
            $errors[] = 'Name is required.';
        }
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'A valid email is required.';
        }
        if (strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters.';
        }

        if (!empty($errors)) {
            http_response_code(422);
            echo json_encode(['errors' => $errors]);
            return;
        }

        try {
            $user = $this->authService->register($name, $email, $password);

            // Generate JWT token for the new user
            $token = JwtHelper::generateToken($user->id, $user->email, $user->role);

            http_response_code(201);
            echo json_encode([
                'message' => 'Registration successful.',
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ]
            ]);
        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * POST /api/auth/login
     * Login and receive a JWT token
     */
    public function login(array $body): void
    {
        $email = trim($body['email'] ?? '');
        $password = $body['password'] ?? '';

        if ($email === '' || $password === '') {
            http_response_code(422);
            echo json_encode(['error' => 'Email and password are required.']);
            return;
        }

        try {
            $user = $this->authService->login($email, $password);

            if ($user === null) {
                http_response_code(401);
                echo json_encode(['error' => 'Invalid email or password.']);
                return;
            }

            // Generate JWT token
            $token = JwtHelper::generateToken($user->id, $user->email, $user->role);

            echo json_encode([
                'message' => 'Login successful.',
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ]
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'An error occurred during login.']);
        }
    }
}
