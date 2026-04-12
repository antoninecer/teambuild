<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Support\Database;

final class TreasureRepository
{
    public function allForGame(int $gameId): array
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare(
            'SELECT
                t.*,
                p.name AS poi_name
             FROM treasures t
             LEFT JOIN pois p ON t.poi_id = p.id
             WHERE t.game_id = :game_id
             ORDER BY t.id DESC'
        );

        $stmt->execute(['game_id' => $gameId]);

        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare(
            'INSERT INTO treasures (
                game_id,
                poi_id,
                name,
                description,
                lat,
                lon,
                radius_m,
                treasure_type,
                is_visible_on_map,
                max_claims,
                points,
                is_enabled
            ) VALUES (
                :game_id,
                :poi_id,
                :name,
                :description,
                :lat,
                :lon,
                :radius_m,
                :treasure_type,
                :is_visible_on_map,
                :max_claims,
                :points,
                :is_enabled
            )'
        );

        $stmt->execute([
            'game_id' => $data['game_id'],
            'poi_id' => $data['poi_id'] ?? null,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'lat' => $data['lat'],
            'lon' => $data['lon'],
            'radius_m' => $data['radius_m'],
            'treasure_type' => $data['treasure_type'],
            'is_visible_on_map' => $data['is_visible_on_map'],
            'max_claims' => $data['max_claims'],
            'points' => $data['points'],
            'is_enabled' => $data['is_enabled'],
        ]);

        return (int) $pdo->lastInsertId();
    }
}