<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Support\Database;

final class UserRepository
{
    public function findActiveByUsername(string $username): ?array
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare(
            'SELECT id, username, email, password_hash, global_role, role, is_active, last_login_at
             FROM users
             WHERE username = :username AND is_active = 1
             LIMIT 1'
        );

        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    public function allAdmins(): array
    {
        $pdo = Database::connection();

        $stmt = $pdo->query(
            'SELECT
                id,
                username,
                email,
                global_role,
                role,
                is_active,
                last_login_at,
                created_at
             FROM users
             ORDER BY username ASC'
        );

        return $stmt->fetchAll();
    }

    public function findById(int $userId): ?array
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare(
            'SELECT id, username, email, global_role, role, is_active
             FROM users
             WHERE id = :id
             LIMIT 1'
        );

        $stmt->execute(['id' => $userId]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    public function createUser(array $data): void
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare(
            'INSERT INTO users (username, email, password_hash, global_role, role, is_active)
             VALUES (:username, :email, :password_hash, :global_role, :role, 1)'
        );

        $stmt->execute([
            'username' => $data['username'],
            'email' => $data['email'],
            'password_hash' => $data['password_hash'],
            'global_role' => $data['global_role'],
            'role' => $data['role'],
        ]);
    }

    public function toggleActive(int $userId): void
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare(
            'UPDATE users
             SET is_active = IF(is_active = 1, 0, 1)
             WHERE id = :id'
        );

        $stmt->execute(['id' => $userId]);
    }

    public function updatePassword(int $userId, string $passwordHash): void
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare(
            'UPDATE users
             SET password_hash = :password_hash
             WHERE id = :id'
        );

        $stmt->execute([
            'password_hash' => $passwordHash,
            'id' => $userId,
        ]);
    }

    public function touchLastLogin(int $userId): void
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare(
            'UPDATE users
             SET last_login_at = NOW()
             WHERE id = :id'
        );

        $stmt->execute(['id' => $userId]);
    }
}