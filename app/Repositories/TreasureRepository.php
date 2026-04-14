<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Support\Database;
use PDO;
use Throwable;

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

    public function visibleForGameWithClaimState(int $gameId, int $playerId, ?int $teamId): array
    {
        $pdo = Database::connection();

        $sql = '
            SELECT
                t.*,
                EXISTS(
                    SELECT 1
                    FROM treasure_claims tc
                    WHERE tc.treasure_id = t.id
                      AND tc.player_id = :player_id
                ) AS claimed_by_player,
                ' . ($teamId !== null
                    ? 'EXISTS(
                        SELECT 1
                        FROM treasure_claims tc2
                        WHERE tc2.treasure_id = t.id
                          AND tc2.team_id = :team_id
                    )'
                    : '0'
                ) . ' AS claimed_by_team,
                (
                    SELECT COUNT(*)
                    FROM treasure_claims tc3
                    WHERE tc3.treasure_id = t.id
                ) AS claim_count
            FROM treasures t
            WHERE t.game_id = :game_id
              AND t.is_enabled = 1
              AND t.is_visible_on_map = 1
            ORDER BY t.id ASC
        ';

        $stmt = $pdo->prepare($sql);

        $params = [
            'game_id' => $gameId,
            'player_id' => $playerId,
        ];

        if ($teamId !== null) {
            $params['team_id'] = $teamId;
        }

        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare(
            'SELECT *
             FROM treasures
             WHERE id = :id
             LIMIT 1'
        );

        $stmt->execute(['id' => $id]);
        $treasure = $stmt->fetch();

        return $treasure ?: null;
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

    public function update(int $id, array $data): void
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare(
            'UPDATE treasures
             SET
                poi_id = :poi_id,
                name = :name,
                description = :description,
                lat = :lat,
                lon = :lon,
                radius_m = :radius_m,
                treasure_type = :treasure_type,
                is_visible_on_map = :is_visible_on_map,
                max_claims = :max_claims,
                points = :points,
                is_enabled = :is_enabled
             WHERE id = :id'
        );

        $stmt->execute([
            'id' => $id,
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
    }

    public function delete(int $id): void
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare('DELETE FROM treasures WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public function claimForPlayer(
        int $treasureId,
        int $gameId,
        int $playerId,
        ?int $teamId,
        float $lat,
        float $lon
    ): array {
        $pdo = Database::connection();

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare(
                'SELECT *
                 FROM treasures
                 WHERE id = :id
                   AND game_id = :game_id
                   AND is_enabled = 1
                 LIMIT 1
                 FOR UPDATE'
            );
            $stmt->execute([
                'id' => $treasureId,
                'game_id' => $gameId,
            ]);

            $treasure = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$treasure) {
                $pdo->rollBack();
                return [
                    'success' => false,
                    'status' => 'not_found',
                ];
            }

            $distance = $this->distanceMeters(
                $lat,
                $lon,
                (float) $treasure['lat'],
                (float) $treasure['lon']
            );

            if ($distance > (float) $treasure['radius_m']) {
                $pdo->rollBack();
                return [
                    'success' => false,
                    'status' => 'too_far',
                    'distance_m' => round($distance, 1),
                    'radius_m' => (int) $treasure['radius_m'],
                ];
            }

            $stmt = $pdo->prepare(
                'SELECT 1
                 FROM treasure_claims
                 WHERE treasure_id = :treasure_id
                   AND player_id = :player_id
                 LIMIT 1'
            );
            $stmt->execute([
                'treasure_id' => $treasureId,
                'player_id' => $playerId,
            ]);

            if ($stmt->fetch()) {
                $pdo->rollBack();
                return [
                    'success' => false,
                    'status' => 'already_claimed',
                ];
            }

            if ($teamId !== null && $treasure['treasure_type'] === 'team') {
                $stmt = $pdo->prepare(
                    'SELECT 1
                     FROM treasure_claims
                     WHERE treasure_id = :treasure_id
                       AND team_id = :team_id
                     LIMIT 1'
                );
                $stmt->execute([
                    'treasure_id' => $treasureId,
                    'team_id' => $teamId,
                ]);

                if ($stmt->fetch()) {
                    $pdo->rollBack();
                    return [
                        'success' => false,
                        'status' => 'already_claimed_team',
                    ];
                }
            }

            if ($treasure['max_claims'] !== null) {
                $stmt = $pdo->prepare(
                    'SELECT COUNT(*) AS cnt
                     FROM treasure_claims
                     WHERE treasure_id = :treasure_id'
                );
                $stmt->execute(['treasure_id' => $treasureId]);
                $countRow = $stmt->fetch(PDO::FETCH_ASSOC);
                $claimCount = (int) ($countRow['cnt'] ?? 0);

                if ($claimCount >= (int) $treasure['max_claims']) {
                    $pdo->rollBack();
                    return [
                        'success' => false,
                        'status' => 'empty',
                    ];
                }
            }

            $claimType = ($treasure['treasure_type'] === 'team' && $teamId !== null) ? 'team' : 'player';

            $stmt = $pdo->prepare(
                'INSERT INTO treasure_claims (
                    treasure_id,
                    player_id,
                    team_id,
                    claim_type,
                    claimed_at
                ) VALUES (
                    :treasure_id,
                    :player_id,
                    :team_id,
                    :claim_type,
                    NOW()
                )'
            );

            $stmt->execute([
                'treasure_id' => $treasureId,
                'player_id' => $playerId,
                'team_id' => $teamId,
                'claim_type' => $claimType,
            ]);

            $pdo->commit();

            return [
                'success' => true,
                'status' => 'claimed',
                'treasure_id' => $treasureId,
            ];
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            return [
                'success' => false,
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

    private function distanceMeters(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000.0;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}