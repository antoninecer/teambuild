<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Support\Database;

final class PlayerRepository
{
    /**
     * @param array $data
     * @return int
     */
    public function create(array $data): int
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare(
            'INSERT INTO players (
                game_id,
                team_id,
                invite_id,
                nickname,
                status,
                created_at,
                updated_at
            ) VALUES (
                :game_id,
                :team_id,
                :invite_id,
                :nickname,
                :status,
                NOW(),
                NOW()
            )'
        );

        $stmt->execute([
            'game_id' => $data['game_id'],
            'team_id' => $data['team_id'] ?? null,
            'invite_id' => $data['invite_id'] ?? null,
            'nickname' => $data['nickname'],
            'status' => $data['status'] ?? 'active',
        ]);

        return (int) $pdo->lastInsertId();
    }

    /**
     * @param string $nickname
     * @param int $gameId
     * @return array|null
     */
    public function findByNicknameInGame(string $nickname, int $gameId): ?array
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare(
            'SELECT *
             FROM players
             WHERE nickname = :nickname AND game_id = :game_id
             LIMIT 1'
        );

        $stmt->execute([
            'nickname' => $nickname,
            'game_id' => $gameId,
        ]);

        $player = $stmt->fetch();

        return $player ?: null;
    }

    /**
     * @param int $id
     * @return array|null
     */
    public function findById(int $id): ?array
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare(
            'SELECT *
             FROM players
             WHERE id = :id
             LIMIT 1'
        );

        $stmt->execute(['id' => $id]);
        $player = $stmt->fetch();

        return $player ?: null;
    }

    /**
     * @param int $playerId
     * @param float $lat
     * @param float $lon
     * @param float $accuracy
     * @return void
     */
    public function updateLocation(int $playerId, float $lat, float $lon, float $accuracy): void
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare(
            'UPDATE players
             SET lat = :lat,
                 lon = :lon,
                 accuracy = :accuracy,
                 last_seen_at = NOW(),
                 updated_at = NOW()
             WHERE id = :id'
        );

        $stmt->execute([
            'id' => $playerId,
            'lat' => $lat,
            'lon' => $lon,
            'accuracy' => $accuracy,
        ]);
    }

    /**
     * @param int $playerId
     * @param float $lat
     * @param float $lon
     * @param float $accuracy
     * @return void
     */
    public function logLocation(int $playerId, float $lat, float $lon, float $accuracy): void
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare(
            'INSERT INTO location_log (
                player_id,
                lat,
                lon,
                accuracy,
                created_at
            ) VALUES (
                :player_id,
                :lat,
                :lon,
                :accuracy,
                NOW()
            )'
        );

        $stmt->execute([
            'player_id' => $playerId,
            'lat' => $lat,
            'lon' => $lon,
            'accuracy' => $accuracy,
        ]);
    }

    /**
     * @param int $playerId
     * @param string $tokenHash
     * @param string $expiresAt
     * @return void
     */
    public function createSession(int $playerId, string $tokenHash, string $expiresAt): void
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare(
            'INSERT INTO player_sessions (
                player_id,
                token_hash,
                expires_at,
                created_at
            ) VALUES (
                :player_id,
                :token_hash,
                :expires_at,
                NOW()
            )'
        );

        $stmt->execute([
            'player_id' => $playerId,
            'token_hash' => $tokenHash,
            'expires_at' => $expiresAt,
        ]);
    }

    /**
     * @param int $gameId
     * @return array
     */
    public function allForGame(int $gameId): array
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare(
            'SELECT 
                p.*,
                p.lat AS last_lat,
                p.lon AS last_lon,
                p.accuracy AS last_accuracy,
                p.last_seen_at,
                t.name AS team_name
             FROM players p
             LEFT JOIN teams t ON p.team_id = t.id
             WHERE p.game_id = :game_id'
        );

        $stmt->execute(['game_id' => $gameId]);

        return $stmt->fetchAll();
    }

    /**
     * @param string $tokenHash
     * @return array|null
     */
    public function findBySessionToken(string $tokenHash): ?array
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare(
            'SELECT 
                ps.token_hash,
                ps.expires_at,
                p.*,
                g.name AS game_name,
                g.status AS game_status,
                g.slug AS game_slug
             FROM player_sessions ps
             JOIN players p ON ps.player_id = p.id
             JOIN games g ON p.game_id = g.id
             WHERE ps.token_hash = :token_hash
             LIMIT 1'
        );

        $stmt->execute(['token_hash' => $tokenHash]);
        $session = $stmt->fetch();

        return $session ?: null;
    }
}
