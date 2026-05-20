<?php

namespace App\Middleware;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtHelper
{
    private static string $algorithm = 'HS256';
    private static int $expiresInSeconds = 3600; // 1 hour

    private static function secret(): string
    {
        return getenv('JWT_SECRET') ?: 'padel_booking_jwt_secret_key_2026';
    }

    /**
     * Generate a JWT token for a user
     */
    public static function generateToken(int $userId, string $email, string $role): string
    {
        $issuedAt = time();
        $expiresAt = $issuedAt + self::$expiresInSeconds;

        $payload = [
            'iss' => 'padel-booking-api',
            'aud' => 'padel-booking-frontend',
            'iat' => $issuedAt,
            'exp' => $expiresAt,
            'data' => [
                'id' => $userId,
                'email' => $email,
                'role' => $role,
            ]
        ];

        return JWT::encode($payload, self::secret(), self::$algorithm);
    }

    /**
     * Validate a JWT token and return decoded data
     * Returns null if invalid
     */
    public static function validateToken(string $token): ?object
    {
        try {
            $decoded = JWT::decode($token, new Key(self::secret(), self::$algorithm));
            return $decoded->data;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Extract JWT token from the Authorization header
     */
    public static function getTokenFromHeader(): ?string
    {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';

        if (preg_match('/Bearer\s+(.+)$/i', $authHeader, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Get authenticated user data from the request
     * Returns null if not authenticated
     */
    public static function getAuthenticatedUser(): ?object
    {
        $token = self::getTokenFromHeader();
        if ($token === null) {
            return null;
        }
        return self::validateToken($token);
    }

    /**
     * Require authentication - sends 401 if not authenticated
     * Returns user data if authenticated
     */
    public static function requireAuth(): object
    {
        $user = self::getAuthenticatedUser();
        if ($user === null) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized. Please provide a valid JWT token.']);
            exit;
        }
        return $user;
    }

    /**
     * Require admin role - sends 403 if not admin
     * Returns user data if admin
     */
    public static function requireAdmin(): object
    {
        $user = self::requireAuth();
        if ($user->role !== 'admin') {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden. Admin access required.']);
            exit;
        }
        return $user;
    }
}
