<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Support\Database;

final class HelpRepository
{
    /**
     * @param array $data
     * @return int
     */
    public function create(array $data): int
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare(
            'INSERT INTO help_requests (
                game_id,
                player_id,
                team_id,
                lat,
                lon,
                message,
                status,
                created_at
            ) VALUES (
                :game_id,
                :player_id,
                :team_id,
                :lat,
                :lon,
                :message,
                :status,
                NOW()
            )'
        );

        $stmt->execute([
            'game_id' => $data['game_id'],
            'player_id' => $data['player_id'],
            'team_id' => $data['team_id'] ?? null,
            'lat' => $data['lat'],
            'lon' => $data['lon'],
            'message' => $data['message'] ?? null,
            'status' => $data['status'] ?? 'pending',
        ]);

        return (int) $pdo->lastInsertId();
    }

    /**
     * @param int $gameId
     * @return array
     */
    public function allActiveForGame(int $gameId): array
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare(
            'SELECT 
                hr.*,
                p.nickname AS player_nickname
             FROM help_requests hr
             JOIN players p ON hr.player_id = p.id
             WHERE hr.game_id = :game_id AND hr.status IN (\'open\', \'acknowledged\')
             ORDER BY hr.created_at DESC'
        );

        $stmt->execute(['game_id' => $gameId]);

        return $stmt->fetchAll();
    }
}
