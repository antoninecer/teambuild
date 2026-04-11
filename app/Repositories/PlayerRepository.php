<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Support\Database;

final class PlayerRepository
{
    public function create(array $data): int
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare(
            'INSERT INTO players (
                game_id,
                team_id,
                invite_id,
                nickname,
                registered_at,
                status
            ) VALUES (
                :game_id,
                :team_id,
                :invite_id,
                :nickname,
                NOW(),
                :status
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

    public function updateLocation(int $playerId, float $lat, float $lon, float $accuracy): void
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare(
            'UPDATE players
             SET last_lat = :lat,
                 last_lon = :lon,
                 last_accuracy = :accuracy,
                 last_seen_at = NOW()
             WHERE id = :id'
        );

        $stmt->execute([
            'id' => $playerId,
            'lat' => $lat,
            'lon' => $lon,
            'accuracy' => $accuracy,
        ]);
    }

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

    public function findBySessionToken(string $tokenHash): ?array
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare(
            'SELECT
                ps.player_id,
                ps.token_hash,
                ps.expires_at,
                p.*,
                g.slug AS game_slug
             FROM player_sessions ps
             JOIN players p ON ps.player_id = p.id
             JOIN games g ON p.game_id = g.id
             WHERE ps.token_hash = :token_hash
             LIMIT 1'
        );

        $stmt->execute(['token_hash' => $tokenHash]);
        return $stmt->fetch() ?: null;
    }
}