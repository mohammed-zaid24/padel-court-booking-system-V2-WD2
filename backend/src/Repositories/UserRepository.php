<?php

namespace App\Repositories;

use App\Models\UserModel;
use PDO;

class UserRepository extends Repository implements IUserRepository
{
    /**
     * Create a new user and return the model with generated ID
     */
    public function create(UserModel $user): UserModel
    {
        $pdo = $this->getConnection();

        $sql = "
            INSERT INTO users (name, email, password_hash, role)
            VALUES (:name, :email, :password_hash, :role)
        ";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            'name' => $user->name,
            'email' => $user->email,
            'password_hash' => $user->passwordHash,
            'role' => $user->role
        ]);

        $user->id = (int)$pdo->lastInsertId();
        return $user;
    }

    /**
     * Find a user by email
     */
    public function findByEmail(string $email): ?UserModel
    {
        $pdo = $this->getConnection();

        $sql = "SELECT id, name, email, password_hash, role FROM users WHERE email = :email LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['email' => $email]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return new UserModel(
            (int)$row['id'],
            $row['name'],
            $row['email'],
            $row['password_hash'],
            $row['role']
        );
    }

    /**
     * Find a user by ID
     */
    public function findById(int $id): ?UserModel
    {
        $pdo = $this->getConnection();

        $sql = "SELECT id, name, email, password_hash, role FROM users WHERE id = :id LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return new UserModel(
            (int)$row['id'],
            $row['name'],
            $row['email'],
            $row['password_hash'],
            $row['role']
        );
    }
}
