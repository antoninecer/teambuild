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

    public function findAdminsForGame(int $gameId): array
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare(
            'SELECT
                u.id,
                u.username,
                u.email,
                u.global_role,
                u.role,
                u.is_active,
                ugr.role AS game_role,
                ugr.created_at AS assigned_at
             FROM user_game_roles ugr
             JOIN users u ON u.id = ugr.user_id
             WHERE ugr.game_id = :game_id
             ORDER BY u.username ASC'
        );

        $stmt->execute(['game_id' => $gameId]);

        return $stmt->fetchAll();
    }

public function allAssignableGameAdminsForGame(int $gameId): array
{
    $pdo = Database::connection();

    $stmt = $pdo->prepare(
        "SELECT
            u.id,
            u.username,
            u.email,
            u.global_role,
            u.role,
            u.is_active
         FROM users u
         WHERE u.is_active = 1
           AND u.role = 'admin'
           AND u.global_role <> 'superadmin'
           AND u.id NOT IN (
               SELECT ugr.user_id
               FROM user_game_roles ugr
               WHERE ugr.game_id = :game_id
                 AND ugr.role = 'game_admin'
           )
         ORDER BY u.username ASC"
    );

    $stmt->execute([
        'game_id' => $gameId,
    ]);

    return $stmt->fetchAll();
}

    public function assignAdminToGame(int $userId, int $gameId, string $role = 'game_admin'): void
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare(
            'INSERT IGNORE INTO user_game_roles (user_id, game_id, role)
             VALUES (:user_id, :game_id, :role)'
        );

        $stmt->execute([
            'user_id' => $userId,
            'game_id' => $gameId,
            'role' => $role,
        ]);
    }

    public function removeAdminFromGame(int $userId, int $gameId, string $role = 'game_admin'): void
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare(
            'DELETE FROM user_game_roles
             WHERE user_id = :user_id
               AND game_id = :game_id
               AND role = :role'
        );

        $stmt->execute([
            'user_id' => $userId,
            'game_id' => $gameId,
            'role' => $role,
        ]);
    }

    public function hasGameAccess(int $userId, int $gameId): bool
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare(
            'SELECT 1
             FROM user_game_roles
             WHERE user_id = :user_id
               AND game_id = :game_id
               AND role = :role
             LIMIT 1'
        );

        $stmt->execute([
            'user_id' => $userId,
            'game_id' => $gameId,
            'role' => 'game_admin',
        ]);

        return (bool) $stmt->fetchColumn();
    }

    public function findAccessibleGameIdsForUser(int $userId): array
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare(
            'SELECT game_id
             FROM user_game_roles
             WHERE user_id = :user_id
               AND role = :role
             ORDER BY game_id ASC'
        );

        $stmt->execute([
            'user_id' => $userId,
            'role' => 'game_admin',
        ]);

        return array_map('intval', $stmt->fetchAll(\PDO::FETCH_COLUMN));
    }
}