<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Support\Database;

final class TeamRepository
{
    /**
     * @return array<int, array>
     */
    public function allForGame(int $gameId): array
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare(
            'SELECT *
             FROM teams
             WHERE game_id = :game_id
             ORDER BY name ASC'
        );

        $stmt->execute(['game_id' => $gameId]);

        return $stmt->fetchAll();
    }
}
