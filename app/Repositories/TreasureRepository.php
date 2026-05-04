<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Support\Database;
use PDO;
use Throwable;

final class TreasureRepository
{
    private const PICKUP_RADIUS_M = 30;

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

        return array_map([$this, 'normalizeTreasureRow'], $stmt->fetchAll());
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
              AND (
                    (
                        t.treasure_type = \'public\'
                        AND NOT EXISTS (
                            SELECT 1
                            FROM treasure_claims tc_public
                            WHERE tc_public.treasure_id = t.id
                        )
                    )
                    OR
                    (
                        t.treasure_type <> \'public\'
                        AND (
                            t.max_claims IS NULL
                            OR (
                                SELECT COUNT(*)
                                FROM treasure_claims tc_limit
                                WHERE tc_limit.treasure_id = t.id
                            ) < t.max_claims
                        )
                    )
                  )
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

        return array_map([$this, 'normalizeTreasureRow'], $stmt->fetchAll());
    }

    public function visibleMapItemsForPlayer(int $gameId, int $playerId, ?int $teamId): array
    {
        $pdo = Database::connection();

        $sql = '
            SELECT
                ii.*,
                t.name,
                t.description,
                t.radius_m,
                t.points,
                t.weight_grams,
                t.drop_allowed,
                t.public_drop_allowed,
                t.hidden_drop_allowed,
                t.finds_mode,
                t.treasure_type
            FROM item_instances ii
            INNER JOIN treasures t ON t.id = ii.treasure_id
            WHERE ii.game_id = :game_id
              AND ii.state IN (\'dropped\', \'hidden\')
              AND ii.current_lat IS NOT NULL
              AND ii.current_lon IS NOT NULL
              AND (
                    ii.visibility = \'all\'
                    OR ii.owner_player_id = :player_id
                    ' . ($teamId !== null ? 'OR (ii.visibility = \'team\' AND ii.owner_team_id = :team_id)' : '') . '
                  )
            ORDER BY ii.updated_at DESC, ii.id DESC'
        ;

        $stmt = $pdo->prepare($sql);
        $params = [
            'game_id' => $gameId,
            'player_id' => $playerId,
        ];
        if ($teamId !== null) {
            $params['team_id'] = $teamId;
        }
        $stmt->execute($params);

        return array_map([$this, 'normalizeItemRow'], $stmt->fetchAll());
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

        return $treasure ? $this->normalizeTreasureRow($treasure) : null;
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
                is_enabled,
                finds_mode,
                drop_allowed,
                public_drop_allowed,
                hidden_drop_allowed,
                weight_grams
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
                :is_enabled,
                :finds_mode,
                :drop_allowed,
                :public_drop_allowed,
                :hidden_drop_allowed,
                :weight_grams
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
            'finds_mode' => $this->normalizeFindsMode($data['finds_mode'] ?? 'log_entry'),
            'drop_allowed' => (int) ($data['drop_allowed'] ?? 0),
            'public_drop_allowed' => (int) ($data['public_drop_allowed'] ?? 0),
            'hidden_drop_allowed' => (int) ($data['hidden_drop_allowed'] ?? 0),
            'weight_grams' => max(0, (int) ($data['weight_grams'] ?? 0)),
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
                is_enabled = :is_enabled,
                finds_mode = :finds_mode,
                drop_allowed = :drop_allowed,
                public_drop_allowed = :public_drop_allowed,
                hidden_drop_allowed = :hidden_drop_allowed,
                weight_grams = :weight_grams
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
            'finds_mode' => $this->normalizeFindsMode($data['finds_mode'] ?? 'log_entry'),
            'drop_allowed' => (int) ($data['drop_allowed'] ?? 0),
            'public_drop_allowed' => (int) ($data['public_drop_allowed'] ?? 0),
            'hidden_drop_allowed' => (int) ($data['hidden_drop_allowed'] ?? 0),
            'weight_grams' => max(0, (int) ($data['weight_grams'] ?? 0)),
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
                'SELECT COUNT(*) AS cnt
                 FROM treasure_claims
                 WHERE treasure_id = :treasure_id'
            );
            $stmt->execute(['treasure_id' => $treasureId]);
            $countRow = $stmt->fetch(PDO::FETCH_ASSOC);
            $claimCount = (int) ($countRow['cnt'] ?? 0);

            if (($treasure['treasure_type'] ?? '') === 'public' && $claimCount > 0) {
                $pdo->rollBack();
                return [
                    'success' => false,
                    'status' => 'empty',
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

            $claimId = (int) $pdo->lastInsertId();
            $itemInstanceId = null;
            $findsMode = $this->normalizeFindsMode($treasure['finds_mode'] ?? 'log_entry');

            if ($findsMode === 'inventory_item') {
                $itemInstanceId = $this->createItemInstanceFromClaim(
                    $pdo,
                    $gameId,
                    $treasureId,
                    $playerId,
                    $teamId,
                    $claimId,
                    $lat,
                    $lon,
                    $distance
                );
            }

            $this->logGeneralEvent($pdo, $gameId, $playerId, $teamId, null, 'treasure_claimed', [
                'treasure_id' => $treasureId,
                'claim_id' => $claimId,
                'item_instance_id' => $itemInstanceId,
                'finds_mode' => $findsMode,
                'lat' => $lat,
                'lon' => $lon,
                'distance_m' => round($distance, 2),
            ]);

            $pdo->commit();

            return [
                'success' => true,
                'status' => 'claimed',
                'treasure_id' => $treasureId,
                'claim_id' => $claimId,
                'finds_mode' => $findsMode,
                'item_instance_id' => $itemInstanceId,
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

    public function inventoryForPlayer(int $gameId, int $playerId, ?int $teamId): array
    {
        $pdo = Database::connection();

        $sql = '
            SELECT
                ii.*,
                t.name,
                t.description,
                t.points,
                t.radius_m,
                t.finds_mode,
                t.treasure_type,
                t.drop_allowed,
                t.public_drop_allowed,
                t.hidden_drop_allowed,
                t.weight_grams
            FROM item_instances ii
            INNER JOIN treasures t ON t.id = ii.treasure_id
            WHERE ii.game_id = :game_id
              AND ii.state = \'carried\'
              AND (
                    ii.owner_player_id = :player_id
                    ' . ($teamId !== null ? 'OR ii.owner_team_id = :team_id' : '') . '
                  )
            ORDER BY ii.picked_at DESC, ii.id DESC'
        ;

        $stmt = $pdo->prepare($sql);
        $params = [
            'game_id' => $gameId,
            'player_id' => $playerId,
        ];
        if ($teamId !== null) {
            $params['team_id'] = $teamId;
        }
        $stmt->execute($params);

        return array_map([$this, 'normalizeItemRow'], $stmt->fetchAll());
    }

    public function journalForPlayer(int $gameId, int $playerId, ?int $teamId, int $limit = 100): array
    {
        $pdo = Database::connection();
        $limit = max(1, min(200, $limit));

        $sql = '
            SELECT
                ie.id,
                ie.event_type,
                ie.note,
                ie.lat,
                ie.lon,
                ie.accuracy_m,
                ie.payload_json,
                ie.created_at,
                ii.state,
                ii.visibility,
                t.id AS treasure_id,
                t.name AS treasure_name,
                p.nickname AS player_nickname
            FROM item_events ie
            INNER JOIN item_instances ii ON ii.id = ie.item_instance_id
            INNER JOIN treasures t ON t.id = ie.treasure_id
            LEFT JOIN players p ON p.id = ie.player_id
            WHERE ie.game_id = :game_id
              AND (
                    ie.player_id = :player_id
                    ' . ($teamId !== null ? 'OR ie.team_id = :team_id' : '') . '
                  )
            ORDER BY ie.created_at DESC, ie.id DESC
            LIMIT ' . $limit;

        $stmt = $pdo->prepare($sql);
        $params = [
            'game_id' => $gameId,
            'player_id' => $playerId,
        ];
        if ($teamId !== null) {
            $params['team_id'] = $teamId;
        }
        $stmt->execute($params);

        $rows = $stmt->fetchAll() ?: [];
        foreach ($rows as &$row) {
            $row['id'] = (int) $row['id'];
            $row['treasure_id'] = (int) $row['treasure_id'];
            $row['lat'] = $row['lat'] !== null ? (float) $row['lat'] : null;
            $row['lon'] = $row['lon'] !== null ? (float) $row['lon'] : null;
            $row['accuracy_m'] = $row['accuracy_m'] !== null ? (float) $row['accuracy_m'] : null;
            $row['payload'] = $this->decodeJson($row['payload_json'] ?? null);
            unset($row['payload_json']);
        }
        unset($row);

        return $rows;
    }

    public function dropItem(
        int $itemInstanceId,
        int $gameId,
        int $playerId,
        ?int $teamId,
        float $lat,
        float $lon,
        float $accuracy,
        string $visibility = 'all'
    ): array {
        return $this->placeItemOnMap($itemInstanceId, $gameId, $playerId, $teamId, $lat, $lon, $accuracy, 'dropped', $visibility);
    }

    public function hideItem(
        int $itemInstanceId,
        int $gameId,
        int $playerId,
        ?int $teamId,
        float $lat,
        float $lon,
        float $accuracy,
        string $visibility = 'hint_only',
        ?string $hintText = null,
        ?int $toPlayerId = null,
        ?int $toTeamId = null,
        string $revealMode = 'none'
    ): array {
        $result = $this->placeItemOnMap($itemInstanceId, $gameId, $playerId, $teamId, $lat, $lon, $accuracy, 'hidden', $visibility);

        if (($result['success'] ?? false) && $hintText !== null && trim($hintText) !== '') {
            $message = $this->sendMessage(
                $gameId,
                $playerId,
                $teamId,
                $toPlayerId,
                $toTeamId,
                $itemInstanceId,
                (int) ($result['treasure_id'] ?? 0),
                null,
                'item_hint',
                'Stopa k ukrytému předmětu',
                trim($hintText),
                $revealMode,
                $revealMode === 'exact_location' || $revealMode === 'approx_location' ? $lat : null,
                $revealMode === 'exact_location' || $revealMode === 'approx_location' ? $lon : null,
                $revealMode === 'approx_location' ? 150 : null
            );
            $result['message_id'] = $message['message_id'] ?? null;
        }

        return $result;
    }

    public function pickupItem(
        int $itemInstanceId,
        int $gameId,
        int $playerId,
        ?int $teamId,
        float $lat,
        float $lon,
        float $accuracy
    ): array {
        $pdo = Database::connection();

        try {
            $pdo->beginTransaction();
            $item = $this->lockItemForAction($pdo, $itemInstanceId, $gameId);

            if (!$item) {
                $pdo->rollBack();
                return ['success' => false, 'status' => 'not_found'];
            }

            if (!in_array((string) $item['state'], ['dropped', 'hidden'], true)) {
                $pdo->rollBack();
                return ['success' => false, 'status' => 'not_on_map'];
            }

            if (!$this->canSeeMapItem($item, $playerId, $teamId)) {
                $pdo->rollBack();
                return ['success' => false, 'status' => 'forbidden'];
            }

            if ($item['current_lat'] === null || $item['current_lon'] === null) {
                $pdo->rollBack();
                return ['success' => false, 'status' => 'missing_location'];
            }

            $distance = $this->distanceMeters($lat, $lon, (float) $item['current_lat'], (float) $item['current_lon']);
            $radius = max(self::PICKUP_RADIUS_M, (int) ($item['radius_m'] ?? 0));

            if ($distance > $radius) {
                $pdo->rollBack();
                return [
                    'success' => false,
                    'status' => 'too_far',
                    'distance_m' => round($distance, 1),
                    'radius_m' => $radius,
                ];
            }

            $stmt = $pdo->prepare(
                'UPDATE item_instances
                 SET state = \'carried\',
                     owner_player_id = :player_id,
                     owner_team_id = :team_id,
                     current_lat = NULL,
                     current_lon = NULL,
                     accuracy_m = NULL,
                     visibility = \'owner\',
                     picked_at = NOW(),
                     dropped_at = NULL,
                     hidden_at = NULL,
                     updated_at = NOW()
                 WHERE id = :id'
            );
            $stmt->execute([
                'id' => $itemInstanceId,
                'player_id' => $playerId,
                'team_id' => $teamId,
            ]);

            $this->logItemEvent($pdo, $gameId, $itemInstanceId, (int) $item['treasure_id'], $playerId, $teamId, 'picked_from_map', $lat, $lon, $accuracy, 'Předmět sebrán z mapy.', [
                'distance_m' => round($distance, 2),
            ]);

            $pdo->commit();

            return [
                'success' => true,
                'status' => 'picked_up',
                'item_instance_id' => $itemInstanceId,
                'treasure_id' => (int) $item['treasure_id'],
            ];
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            return ['success' => false, 'status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function useItem(
        int $itemInstanceId,
        int $gameId,
        int $playerId,
        ?int $teamId,
        float $lat,
        float $lon,
        float $accuracy,
        ?int $targetPoiId = null
    ): array {
        $pdo = Database::connection();

        try {
            $pdo->beginTransaction();
            $item = $this->lockItemForAction($pdo, $itemInstanceId, $gameId);

            if (!$item) {
                $pdo->rollBack();
                return ['success' => false, 'status' => 'not_found'];
            }

            if ((string) $item['state'] !== 'carried') {
                $pdo->rollBack();
                return ['success' => false, 'status' => 'not_carried'];
            }

            if (!$this->canControlCarriedItem($item, $playerId, $teamId)) {
                $pdo->rollBack();
                return ['success' => false, 'status' => 'forbidden'];
            }

            $rule = $this->findUseRule($pdo, $gameId, (int) $item['treasure_id'], $targetPoiId);

            if ($rule === null) {
                $pdo->rollBack();
                return [
                    'success' => false,
                    'status' => 'no_interaction',
                    'message' => 'Tento předmět teď nelze použít. V dosahu není žádné místo ani objekt, se kterým by mohl interagovat.',
                ];
            }

            $ruleCheck = $this->checkUseRule($pdo, $rule, $lat, $lon, $targetPoiId);

            if (($ruleCheck['success'] ?? false) === false) {
                $pdo->rollBack();
                return $ruleCheck;
            }

            $consumesItem = ((int) $rule['consumes_item'] === 1);
            $effectType = (string) $rule['effect_type'];

            if ($consumesItem) {
                $stmt = $pdo->prepare(
                    'UPDATE item_instances
                     SET state = \'consumed\',
                         owner_player_id = NULL,
                         owner_team_id = NULL,
                         used_at = NOW(),
                         updated_at = NOW()
                     WHERE id = :id'
                );
                $stmt->execute(['id' => $itemInstanceId]);
            } else {
                $stmt = $pdo->prepare(
                    'UPDATE item_instances
                     SET used_at = NOW(),
                         updated_at = NOW()
                     WHERE id = :id'
                );
                $stmt->execute(['id' => $itemInstanceId]);
            }

            $this->logItemEvent($pdo, $gameId, $itemInstanceId, (int) $item['treasure_id'], $playerId, $teamId, $consumesItem ? 'consumed' : 'used', $lat, $lon, $accuracy, 'Předmět použit.', [
                'target_poi_id' => $targetPoiId,
                'effect_type' => $effectType,
                'consumes_item' => $consumesItem,
                'rule_id' => $rule ? (int) $rule['id'] : null,
            ]);

            $pdo->commit();

            return [
                'success' => true,
                'status' => $consumesItem ? 'consumed' : 'used',
                'item_instance_id' => $itemInstanceId,
                'treasure_id' => (int) $item['treasure_id'],
                'effect_type' => $effectType,
                'consumes_item' => $consumesItem,
            ];
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            return ['success' => false, 'status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function sendMessage(
        int $gameId,
        int $fromPlayerId,
        ?int $fromTeamId,
        ?int $toPlayerId,
        ?int $toTeamId,
        ?int $itemInstanceId,
        ?int $treasureId,
        ?int $poiId,
        string $messageType,
        ?string $subject,
        string $body,
        string $revealMode = 'none',
        ?float $hintLat = null,
        ?float $hintLon = null,
        ?int $hintRadiusM = null
    ): array {
        $pdo = Database::connection();

        $messageType = in_array($messageType, ['text', 'hint', 'item_hint', 'system', 'sos_reply'], true) ? $messageType : 'text';
        $revealMode = in_array($revealMode, ['none', 'approx_location', 'exact_location'], true) ? $revealMode : 'none';
        $body = trim($body);

        if ($body === '') {
            return ['success' => false, 'status' => 'empty_message'];
        }

        if ($toPlayerId === null && $toTeamId === null) {
            $toPlayerId = $fromPlayerId;
        }

        try {
            $pdo->beginTransaction();

            if ($itemInstanceId !== null) {
                $item = $this->lockItemForAction($pdo, $itemInstanceId, $gameId);
                if (!$item || !$this->canReferenceItem($item, $fromPlayerId, $fromTeamId)) {
                    $pdo->rollBack();
                    return ['success' => false, 'status' => 'forbidden'];
                }
                $treasureId = $treasureId ?: (int) $item['treasure_id'];
            }

            $stmt = $pdo->prepare(
                'INSERT INTO player_messages (
                    game_id,
                    from_player_id,
                    from_team_id,
                    to_player_id,
                    to_team_id,
                    item_instance_id,
                    treasure_id,
                    poi_id,
                    message_type,
                    subject,
                    body,
                    reveal_mode,
                    hint_lat,
                    hint_lon,
                    hint_radius_m,
                    payload_json,
                    created_at
                ) VALUES (
                    :game_id,
                    :from_player_id,
                    :from_team_id,
                    :to_player_id,
                    :to_team_id,
                    :item_instance_id,
                    :treasure_id,
                    :poi_id,
                    :message_type,
                    :subject,
                    :body,
                    :reveal_mode,
                    :hint_lat,
                    :hint_lon,
                    :hint_radius_m,
                    :payload_json,
                    NOW()
                )'
            );

            $payload = [
                'created_by_api' => true,
            ];

            $stmt->execute([
                'game_id' => $gameId,
                'from_player_id' => $fromPlayerId,
                'from_team_id' => $fromTeamId,
                'to_player_id' => $toPlayerId,
                'to_team_id' => $toTeamId,
                'item_instance_id' => $itemInstanceId,
                'treasure_id' => $treasureId,
                'poi_id' => $poiId,
                'message_type' => $messageType,
                'subject' => $subject,
                'body' => $body,
                'reveal_mode' => $revealMode,
                'hint_lat' => $hintLat,
                'hint_lon' => $hintLon,
                'hint_radius_m' => $hintRadiusM,
                'payload_json' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ]);

            $messageId = (int) $pdo->lastInsertId();

            if ($toPlayerId !== null) {
                $stmt = $pdo->prepare(
                    'INSERT IGNORE INTO player_message_reads (message_id, player_id, is_read, read_at, created_at)
                     VALUES (:message_id, :player_id, 0, NULL, NOW())'
                );
                $stmt->execute([
                    'message_id' => $messageId,
                    'player_id' => $toPlayerId,
                ]);
            }

            if ($itemInstanceId !== null && $treasureId !== null) {
                $this->logItemEvent($pdo, $gameId, $itemInstanceId, $treasureId, $fromPlayerId, $fromTeamId, 'message_sent', null, null, null, 'Zpráva k předmětu odeslána.', [
                    'message_id' => $messageId,
                    'message_type' => $messageType,
                    'to_player_id' => $toPlayerId,
                    'to_team_id' => $toTeamId,
                ]);
            }

            $pdo->commit();

            return ['success' => true, 'status' => 'sent', 'message_id' => $messageId];
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            return ['success' => false, 'status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function messagesForPlayer(int $gameId, int $playerId, ?int $teamId, int $limit = 100): array
    {
        $pdo = Database::connection();
        $limit = max(1, min(200, $limit));

        $sql = '
            SELECT
                pm.*,
                fp.nickname AS from_nickname,
                tp.nickname AS to_nickname,
                t.name AS treasure_name,
                poi.name AS poi_name,
                COALESCE(r.is_read, 0) AS is_read,
                r.read_at
            FROM player_messages pm
            LEFT JOIN players fp ON fp.id = pm.from_player_id
            LEFT JOIN players tp ON tp.id = pm.to_player_id
            LEFT JOIN treasures t ON t.id = pm.treasure_id
            LEFT JOIN pois poi ON poi.id = pm.poi_id
            LEFT JOIN player_message_reads r ON r.message_id = pm.id AND r.player_id = :player_id
            WHERE pm.game_id = :game_id
              AND (
                    pm.to_player_id = :player_id
                    OR pm.from_player_id = :player_id
                    ' . ($teamId !== null ? 'OR pm.to_team_id = :team_id OR pm.from_team_id = :team_id' : '') . '
                  )
            ORDER BY pm.created_at DESC, pm.id DESC
            LIMIT ' . $limit;

        $stmt = $pdo->prepare($sql);
        $params = [
            'game_id' => $gameId,
            'player_id' => $playerId,
        ];
        if ($teamId !== null) {
            $params['team_id'] = $teamId;
        }
        $stmt->execute($params);

        $rows = $stmt->fetchAll() ?: [];
        foreach ($rows as &$row) {
            $row['id'] = (int) $row['id'];
            $row['from_player_id'] = $row['from_player_id'] !== null ? (int) $row['from_player_id'] : null;
            $row['to_player_id'] = $row['to_player_id'] !== null ? (int) $row['to_player_id'] : null;
            $row['from_team_id'] = $row['from_team_id'] !== null ? (int) $row['from_team_id'] : null;
            $row['to_team_id'] = $row['to_team_id'] !== null ? (int) $row['to_team_id'] : null;
            $row['item_instance_id'] = $row['item_instance_id'] !== null ? (int) $row['item_instance_id'] : null;
            $row['treasure_id'] = $row['treasure_id'] !== null ? (int) $row['treasure_id'] : null;
            $row['poi_id'] = $row['poi_id'] !== null ? (int) $row['poi_id'] : null;
            $row['hint_lat'] = $row['hint_lat'] !== null ? (float) $row['hint_lat'] : null;
            $row['hint_lon'] = $row['hint_lon'] !== null ? (float) $row['hint_lon'] : null;
            $row['hint_radius_m'] = $row['hint_radius_m'] !== null ? (int) $row['hint_radius_m'] : null;
            $row['is_read'] = (int) $row['is_read'];
            $row['payload'] = $this->decodeJson($row['payload_json'] ?? null);
            unset($row['payload_json']);
        }
        unset($row);

        return $rows;
    }

    public function markMessageRead(int $messageId, int $gameId, int $playerId, ?int $teamId): array
    {
        $pdo = Database::connection();

        $sql = '
            SELECT id
            FROM player_messages
            WHERE id = :id
              AND game_id = :game_id
              AND (
                    to_player_id = :player_id
                    OR from_player_id = :player_id
                    ' . ($teamId !== null ? 'OR to_team_id = :team_id OR from_team_id = :team_id' : '') . '
                  )
            LIMIT 1'
        ;

        $stmt = $pdo->prepare($sql);
        $params = [
            'id' => $messageId,
            'game_id' => $gameId,
            'player_id' => $playerId,
        ];
        if ($teamId !== null) {
            $params['team_id'] = $teamId;
        }
        $stmt->execute($params);

        if (!$stmt->fetch()) {
            return ['success' => false, 'status' => 'not_found'];
        }

        $stmt = $pdo->prepare(
            'INSERT INTO player_message_reads (message_id, player_id, is_read, read_at, created_at)
             VALUES (:message_id, :player_id, 1, NOW(), NOW())
             ON DUPLICATE KEY UPDATE is_read = 1, read_at = NOW()'
        );
        $stmt->execute([
            'message_id' => $messageId,
            'player_id' => $playerId,
        ]);

        return ['success' => true, 'status' => 'read'];
    }

    private function placeItemOnMap(
        int $itemInstanceId,
        int $gameId,
        int $playerId,
        ?int $teamId,
        float $lat,
        float $lon,
        float $accuracy,
        string $state,
        string $visibility
    ): array {
        $pdo = Database::connection();

        $state = $state === 'hidden' ? 'hidden' : 'dropped';
        $visibility = in_array($visibility, ['owner', 'team', 'all', 'hint_only'], true) ? $visibility : 'all';

        try {
            $pdo->beginTransaction();
            $item = $this->lockItemForAction($pdo, $itemInstanceId, $gameId);

            if (!$item) {
                $pdo->rollBack();
                return ['success' => false, 'status' => 'not_found'];
            }

            if ((string) $item['state'] !== 'carried') {
                $pdo->rollBack();
                return ['success' => false, 'status' => 'not_carried'];
            }

            if (!$this->canControlCarriedItem($item, $playerId, $teamId)) {
                $pdo->rollBack();
                return ['success' => false, 'status' => 'forbidden'];
            }

            if ((int) ($item['drop_allowed'] ?? 0) !== 1) {
                $pdo->rollBack();
                return ['success' => false, 'status' => 'drop_not_allowed'];
            }

            if ($state === 'dropped' && (int) ($item['public_drop_allowed'] ?? 0) !== 1) {
                $pdo->rollBack();
                return ['success' => false, 'status' => 'public_drop_not_allowed'];
            }

            if ($state === 'hidden' && (int) ($item['hidden_drop_allowed'] ?? 0) !== 1) {
                $pdo->rollBack();
                return ['success' => false, 'status' => 'hidden_drop_not_allowed'];
            }

            if ($visibility === 'team' && $teamId === null) {
                $visibility = 'owner';
            }

            if ($state === 'dropped' && $visibility === 'hint_only') {
                $visibility = 'all';
            }

            $stmt = $pdo->prepare(
                'UPDATE item_instances
                 SET state = :state,
                     owner_player_id = NULL,
                     owner_team_id = NULL,
                     current_lat = :lat,
                     current_lon = :lon,
                     accuracy_m = :accuracy_m,
                     visibility = :visibility,
                     dropped_at = CASE WHEN :state_for_dropped = \'dropped\' THEN NOW() ELSE dropped_at END,
                     hidden_at = CASE WHEN :state_for_hidden = \'hidden\' THEN NOW() ELSE hidden_at END,
                     updated_at = NOW()
                 WHERE id = :id'
            );
            $stmt->execute([
                'id' => $itemInstanceId,
                'state' => $state,
                'lat' => $lat,
                'lon' => $lon,
                'accuracy_m' => $accuracy > 0 ? $accuracy : null,
                'visibility' => $visibility,
                'state_for_dropped' => $state,
                'state_for_hidden' => $state,
            ]);

            $eventType = $state === 'hidden' ? 'hidden' : 'dropped';
            $this->logItemEvent($pdo, $gameId, $itemInstanceId, (int) $item['treasure_id'], $playerId, $teamId, $eventType, $lat, $lon, $accuracy, $state === 'hidden' ? 'Předmět ukryt na mapě.' : 'Předmět položen na mapě.', [
                'visibility' => $visibility,
            ]);

            $pdo->commit();

            return [
                'success' => true,
                'status' => $state,
                'item_instance_id' => $itemInstanceId,
                'treasure_id' => (int) $item['treasure_id'],
                'visibility' => $visibility,
                'lat' => $lat,
                'lon' => $lon,
            ];
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            return ['success' => false, 'status' => 'error', 'message' => $e->getMessage()];
        }
    }

    private function createItemInstanceFromClaim(
        PDO $pdo,
        int $gameId,
        int $treasureId,
        int $playerId,
        ?int $teamId,
        int $claimId,
        float $lat,
        float $lon,
        float $distance
    ): int {
        $stmt = $pdo->prepare(
            'INSERT INTO item_instances (
                game_id,
                treasure_id,
                state,
                owner_player_id,
                owner_team_id,
                visibility,
                created_from_claim_id,
                picked_at,
                created_at,
                updated_at
            ) VALUES (
                :game_id,
                :treasure_id,
                \'carried\',
                :owner_player_id,
                :owner_team_id,
                \'owner\',
                :created_from_claim_id,
                NOW(),
                NOW(),
                NOW()
            )'
        );
        $stmt->execute([
            'game_id' => $gameId,
            'treasure_id' => $treasureId,
            'owner_player_id' => $playerId,
            'owner_team_id' => $teamId,
            'created_from_claim_id' => $claimId,
        ]);

        $itemInstanceId = (int) $pdo->lastInsertId();

        $this->logItemEvent($pdo, $gameId, $itemInstanceId, $treasureId, $playerId, $teamId, 'claimed', $lat, $lon, null, 'Předmět vložen do inventáře.', [
            'claim_id' => $claimId,
            'distance_m' => round($distance, 2),
        ]);

        return $itemInstanceId;
    }

    private function lockItemForAction(PDO $pdo, int $itemInstanceId, int $gameId): ?array
    {
        $stmt = $pdo->prepare(
            'SELECT
                ii.*,
                t.name,
                t.description,
                t.points,
                t.radius_m,
                t.finds_mode,
                t.treasure_type,
                t.drop_allowed,
                t.public_drop_allowed,
                t.hidden_drop_allowed,
                t.weight_grams
             FROM item_instances ii
             INNER JOIN treasures t ON t.id = ii.treasure_id
             WHERE ii.id = :id
               AND ii.game_id = :game_id
             LIMIT 1
             FOR UPDATE'
        );
        $stmt->execute([
            'id' => $itemInstanceId,
            'game_id' => $gameId,
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->normalizeItemRow($row) : null;
    }

    private function findUseRule(PDO $pdo, int $gameId, int $treasureId, ?int $targetPoiId): ?array
    {
        if ($targetPoiId !== null) {
            $stmt = $pdo->prepare(
                'SELECT *
                 FROM item_use_rules
                 WHERE game_id = :game_id
                   AND treasure_id = :treasure_id
                   AND is_enabled = 1
                   AND (target_poi_id = :target_poi_id OR target_poi_id IS NULL)
                 ORDER BY target_poi_id DESC, id ASC
                 LIMIT 1'
            );
            $stmt->execute([
                'game_id' => $gameId,
                'treasure_id' => $treasureId,
                'target_poi_id' => $targetPoiId,
            ]);
        } else {
            $stmt = $pdo->prepare(
                'SELECT *
                 FROM item_use_rules
                 WHERE game_id = :game_id
                   AND treasure_id = :treasure_id
                   AND is_enabled = 1
                   AND target_poi_id IS NULL
                 ORDER BY id ASC
                 LIMIT 1'
            );
            $stmt->execute([
                'game_id' => $gameId,
                'treasure_id' => $treasureId,
            ]);
        }

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private function checkUseRule(PDO $pdo, ?array $rule, float $lat, float $lon, ?int $targetPoiId): array
    {
        if ($rule === null) {
            return ['success' => true];
        }

        $useMode = (string) $rule['use_mode'];
        $radius = max(1, (int) ($rule['required_radius_m'] ?? 30));

        if ($useMode === 'anywhere') {
            return ['success' => true];
        }

        if ($useMode === 'near_coordinates') {
            if ($rule['target_lat'] === null || $rule['target_lon'] === null) {
                return ['success' => false, 'status' => 'rule_missing_coordinates'];
            }

            $distance = $this->distanceMeters($lat, $lon, (float) $rule['target_lat'], (float) $rule['target_lon']);
            if ($distance > $radius) {
                return [
                    'success' => false,
                    'status' => 'too_far',
                    'distance_m' => round($distance, 1),
                    'radius_m' => $radius,
                ];
            }

            return ['success' => true];
        }

        if ($useMode === 'near_poi') {
            $poiId = $targetPoiId ?: ($rule['target_poi_id'] !== null ? (int) $rule['target_poi_id'] : 0);
            if ($poiId <= 0) {
                return ['success' => false, 'status' => 'target_poi_required'];
            }

            $stmt = $pdo->prepare('SELECT lat, lon FROM pois WHERE id = :id LIMIT 1');
            $stmt->execute(['id' => $poiId]);
            $poi = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$poi) {
                return ['success' => false, 'status' => 'target_poi_not_found'];
            }

            $distance = $this->distanceMeters($lat, $lon, (float) $poi['lat'], (float) $poi['lon']);
            if ($distance > $radius) {
                return [
                    'success' => false,
                    'status' => 'too_far',
                    'distance_m' => round($distance, 1),
                    'radius_m' => $radius,
                ];
            }

            return ['success' => true];
        }

        return ['success' => true];
    }

    private function canControlCarriedItem(array $item, int $playerId, ?int $teamId): bool
    {
        if ((int) ($item['owner_player_id'] ?? 0) === $playerId) {
            return true;
        }

        return $teamId !== null && isset($item['owner_team_id']) && (int) $item['owner_team_id'] === $teamId;
    }

    private function canReferenceItem(array $item, int $playerId, ?int $teamId): bool
    {
        if ($this->canControlCarriedItem($item, $playerId, $teamId)) {
            return true;
        }

        return $this->canSeeMapItem($item, $playerId, $teamId);
    }

    private function canSeeMapItem(array $item, int $playerId, ?int $teamId): bool
    {
        $visibility = (string) ($item['visibility'] ?? 'owner');

        if ($visibility === 'all') {
            return true;
        }

        if ((int) ($item['owner_player_id'] ?? 0) === $playerId) {
            return true;
        }

        if ($visibility === 'team' && $teamId !== null && isset($item['owner_team_id']) && (int) $item['owner_team_id'] === $teamId) {
            return true;
        }

        return false;
    }

    private function logItemEvent(
        PDO $pdo,
        int $gameId,
        int $itemInstanceId,
        int $treasureId,
        ?int $playerId,
        ?int $teamId,
        string $eventType,
        ?float $lat,
        ?float $lon,
        ?float $accuracy,
        ?string $note,
        array $payload = []
    ): void {
        $stmt = $pdo->prepare(
            'INSERT INTO item_events (
                game_id,
                item_instance_id,
                treasure_id,
                player_id,
                team_id,
                event_type,
                lat,
                lon,
                accuracy_m,
                note,
                payload_json,
                created_at
            ) VALUES (
                :game_id,
                :item_instance_id,
                :treasure_id,
                :player_id,
                :team_id,
                :event_type,
                :lat,
                :lon,
                :accuracy_m,
                :note,
                :payload_json,
                NOW()
            )'
        );

        $stmt->execute([
            'game_id' => $gameId,
            'item_instance_id' => $itemInstanceId,
            'treasure_id' => $treasureId,
            'player_id' => $playerId,
            'team_id' => $teamId,
            'event_type' => $eventType,
            'lat' => $lat,
            'lon' => $lon,
            'accuracy_m' => $accuracy !== null && $accuracy > 0 ? $accuracy : null,
            'note' => $note,
            'payload_json' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    }

    private function logGeneralEvent(PDO $pdo, int $gameId, ?int $playerId, ?int $teamId, ?int $poiId, string $eventType, array $payload = []): void
    {
        $stmt = $pdo->prepare(
            'INSERT INTO events (
                game_id,
                player_id,
                team_id,
                poi_id,
                event_type,
                payload_json
            ) VALUES (
                :game_id,
                :player_id,
                :team_id,
                :poi_id,
                :event_type,
                :payload_json
            )'
        );

        $stmt->execute([
            'game_id' => $gameId,
            'player_id' => $playerId,
            'team_id' => $teamId,
            'poi_id' => $poiId,
            'event_type' => $eventType,
            'payload_json' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    }

    private function normalizeTreasureRow(array $treasure): array
    {
        $treasure['id'] = (int) ($treasure['id'] ?? 0);
        $treasure['game_id'] = (int) ($treasure['game_id'] ?? 0);
        $treasure['poi_id'] = $treasure['poi_id'] !== null ? (int) $treasure['poi_id'] : null;
        $treasure['lat'] = isset($treasure['lat']) ? (float) $treasure['lat'] : 0.0;
        $treasure['lon'] = isset($treasure['lon']) ? (float) $treasure['lon'] : 0.0;
        $treasure['radius_m'] = isset($treasure['radius_m']) ? (int) $treasure['radius_m'] : 0;
        $treasure['points'] = isset($treasure['points']) ? (int) $treasure['points'] : 0;
        $treasure['is_enabled'] = isset($treasure['is_enabled']) ? (int) $treasure['is_enabled'] : 0;
        $treasure['is_visible_on_map'] = isset($treasure['is_visible_on_map']) ? (int) $treasure['is_visible_on_map'] : 0;
        $treasure['claimed_by_player'] = isset($treasure['claimed_by_player']) ? (int) $treasure['claimed_by_player'] : 0;
        $treasure['claimed_by_team'] = isset($treasure['claimed_by_team']) ? (int) $treasure['claimed_by_team'] : 0;
        $treasure['claim_count'] = isset($treasure['claim_count']) ? (int) $treasure['claim_count'] : 0;
        $treasure['finds_mode'] = $this->normalizeFindsMode($treasure['finds_mode'] ?? 'log_entry');
        $treasure['drop_allowed'] = (int) ($treasure['drop_allowed'] ?? 0);
        $treasure['public_drop_allowed'] = (int) ($treasure['public_drop_allowed'] ?? 0);
        $treasure['hidden_drop_allowed'] = (int) ($treasure['hidden_drop_allowed'] ?? 0);
        $treasure['weight_grams'] = max(0, (int) ($treasure['weight_grams'] ?? 0));

        if (($treasure['treasure_type'] ?? '') === 'individual') {
            $treasure['drop_allowed'] = 0;
            $treasure['public_drop_allowed'] = 0;
            $treasure['hidden_drop_allowed'] = 0;
        }

        if ($treasure['drop_allowed'] !== 1) {
            $treasure['public_drop_allowed'] = 0;
            $treasure['hidden_drop_allowed'] = 0;
        }

        return $treasure;
    }

    private function normalizeItemRow(array $item): array
    {
        $item['id'] = (int) ($item['id'] ?? 0);
        $item['game_id'] = (int) ($item['game_id'] ?? 0);
        $item['treasure_id'] = (int) ($item['treasure_id'] ?? 0);
        $item['owner_player_id'] = $item['owner_player_id'] !== null ? (int) $item['owner_player_id'] : null;
        $item['owner_team_id'] = $item['owner_team_id'] !== null ? (int) $item['owner_team_id'] : null;
        $item['current_lat'] = $item['current_lat'] !== null ? (float) $item['current_lat'] : null;
        $item['current_lon'] = $item['current_lon'] !== null ? (float) $item['current_lon'] : null;
        $item['accuracy_m'] = $item['accuracy_m'] !== null ? (float) $item['accuracy_m'] : null;
        $item['created_from_claim_id'] = $item['created_from_claim_id'] !== null ? (int) $item['created_from_claim_id'] : null;
        $item['points'] = isset($item['points']) ? (int) $item['points'] : 0;
        $item['radius_m'] = isset($item['radius_m']) ? (int) $item['radius_m'] : self::PICKUP_RADIUS_M;
        $item['drop_allowed'] = (int) ($item['drop_allowed'] ?? 0);
        $item['public_drop_allowed'] = (int) ($item['public_drop_allowed'] ?? 0);
        $item['hidden_drop_allowed'] = (int) ($item['hidden_drop_allowed'] ?? 0);
        $item['weight_grams'] = max(0, (int) ($item['weight_grams'] ?? 0));
        $item['finds_mode'] = $this->normalizeFindsMode($item['finds_mode'] ?? 'inventory_item');

        return $item;
    }

    private function normalizeFindsMode(?string $findsMode): string
    {
        return in_array($findsMode, ['log_entry', 'inventory_item'], true)
            ? $findsMode
            : 'log_entry';
    }

    private function decodeJson(?string $json): array
    {
        if ($json === null || $json === '') {
            return [];
        }

        $decoded = json_decode($json, true);
        return is_array($decoded) ? $decoded : [];
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
