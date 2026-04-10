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
            'SELECT id, username, password_hash, role, is_active
             FROM users
             WHERE username = :username AND is_active = 1
             LIMIT 1'
        );

        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        return $user ?: null;
    }
}
