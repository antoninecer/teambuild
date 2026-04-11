<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Support\Database;

final class InviteRepository
{
    /**
     * @return array<int, array>
     */
    public function allForGame(int $gameId): array
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare(
            'SELECT i.*, t.name AS team_name
             FROM invites i
             LEFT JOIN teams t ON i.team_id = t.id
             WHERE i.game_id = :game_id
             ORDER BY i.id DESC'
        );

        $stmt->execute(['game_id' => $gameId]);

        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare(
            'SELECT *
             FROM invites
             WHERE id = :id
             LIMIT 1'
        );

        $stmt->execute(['id' => $id]);
        $invite = $stmt->fetch();

        return $invite ?: null;
    }

    public function findByCode(string $code): ?array
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare(
            'SELECT *
             FROM invites
             WHERE code = :code
             LIMIT 1'
        );

        $stmt->execute(['code' => $code]);
        $invite = $stmt->fetch();

        return $invite ?: null;
    }

    public function create(array $data): int
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare(
            'INSERT INTO invites (
                game_id,
                code,
                label,
                team_id,
                max_uses,
                used_count,
                valid_from,
                valid_to,
                is_active
            ) VALUES (
                :game_id,
                :code,
                :label,
                :team_id,
                :max_uses,
                :used_count,
                :valid_from,
                :valid_to,
                :is_active
            )'
        );

        $stmt->execute([
            'game_id' => $data['game_id'],
            'code' => $data['code'],
            'label' => $data['label'] ?? null,
            'team_id' => $data['team_id'] ?? null,
            'max_uses' => array_key_exists('max_uses', $data) ? $data['max_uses'] : 1,
            'used_count' => $data['used_count'] ?? 0,
            'valid_from' => $data['valid_from'] ?? null,
            'valid_to' => $data['valid_to'] ?? null,
            'is_active' => (int) ($data['is_active'] ?? 1),
        ]);

        return (int) $pdo->lastInsertId();
    }

    public function delete(int $id): bool
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare('DELETE FROM invites WHERE id = :id');

        return $stmt->execute(['id' => $id]);
    }

    public function incrementUsage(int $id): void
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare(
            'UPDATE invites
             SET used_count = used_count + 1
             WHERE id = :id'
        );

        $stmt->execute(['id' => $id]);
    }
}
