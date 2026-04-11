<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Support\Database;

final class PoiRepository
{
    public function allForGame(int $gameId): array
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare(
            'SELECT *
             FROM pois
             WHERE game_id = :game_id
             ORDER BY sort_order ASC, name ASC'
        );

        $stmt->execute(['game_id' => $gameId]);

        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare(
            'SELECT *
             FROM pois
             WHERE id = :id
             LIMIT 1'
        );

        $stmt->execute(['id' => $id]);
        $poi = $stmt->fetch();

        return $poi ?: null;
    }

    public function create(array $data): int
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare(
            'INSERT INTO pois (
                game_id,
                type,
                name,
                description,
                story_text,
                lat,
                lon,
                radius_m,
                sort_order,
                is_enabled,
                active_from,
                active_to,
                created_at,
                updated_at
            ) VALUES (
                :game_id,
                :type,
                :name,
                :description,
                :story_text,
                :lat,
                :lon,
                :radius_m,
                :sort_order,
                :is_enabled,
                :active_from,
                :active_to,
                NOW(),
                NOW()
            )'
        );

        $stmt->execute([
            'game_id' => $data['game_id'],
            'type' => $data['type'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'story_text' => $data['story_text'] ?? null,
            'lat' => $data['lat'],
            'lon' => $data['lon'],
            'radius_m' => $data['radius_m'] ?? 50,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_enabled' => $data['is_enabled'] ?? 1,
            'active_from' => $data['active_from'] ?? null,
            'active_to' => $data['active_to'] ?? null,
        ]);

        return (int) $pdo->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $pdo = Database::connection();

        $fields = [];
        $params = ['id' => $id];

        // List of allowed columns to update
        $allowedColumns = [
            'game_id', 'type', 'name', 'description', 'story_text',
            'lat', 'lon', 'radius_m', 'sort_order', 'is_enabled',
            'active_from', 'active_to'
        ];

        foreach ($data as $key => $value) {
            if (in_array($key, $allowedColumns, true)) {
                $fields[] = "$key = :$key";
                $params[$key] = $value;
            }
        }

        if (empty($fields)) {
            return false;
        }

        $fields[] = 'updated_at = NOW()';

        $sql = 'UPDATE pois SET ' . implode(', ', $fields) . ' WHERE id = :id';
        $stmt = $pdo->prepare($sql);

        return $stmt->execute($params);
    }

    public function delete(int $id): bool
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare('DELETE FROM pois WHERE id = :id');

        return $stmt->execute(['id' => $id]);
    }
}
