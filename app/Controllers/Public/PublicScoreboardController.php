<?php

declare(strict_types=1);

namespace App\Controllers\Public;

use App\Repositories\GameRepository;
use App\Support\Database;
use PDO;

final class PublicScoreboardController
{
    public function show(string $slug): void
    {
        $gameRepo = new GameRepository();
        $game = $gameRepo->findBySlug($slug);

        if (!$game) {
            http_response_code(404);
            echo 'Hra nebyla nalezena.';
            exit;
        }

        $payload = $this->buildPayload((int) $game['id']);

        require __DIR__ . '/../../../resources/views/public/scoreboard.php';
    }

    public function data(string $slug): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $gameRepo = new GameRepository();
        $game = $gameRepo->findBySlug($slug);

        if (!$game) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'game_not_found',
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $payload = $this->buildPayload((int) $game['id']);

        echo json_encode([
            'success' => true,
            'game' => [
                'id' => (int) $game['id'],
                'name' => (string) $game['name'],
                'slug' => (string) $game['slug'],
            ],
            'stats' => $payload['stats'],
            'rows' => $payload['rows'],
            'updated_at' => date('Y-m-d H:i:s'),
        ], JSON_UNESCAPED_UNICODE);
    }

    private function buildPayload(int $gameId): array
    {
        $pdo = Database::connection();

        $statsStmt = $pdo->prepare(
            'SELECT
                (SELECT COUNT(*) FROM players WHERE game_id = :game_id) AS players_count,
                (SELECT COUNT(*) FROM pois WHERE game_id = :game_id AND is_enabled = 1) AS pois_count,
                (SELECT COUNT(*) FROM treasures WHERE game_id = :game_id AND is_enabled = 1) AS treasures_count,
                (SELECT COUNT(*) FROM treasure_claims tc INNER JOIN treasures t ON t.id = tc.treasure_id WHERE t.game_id = :game_id) AS treasure_claims_count'
        );
        $statsStmt->execute(['game_id' => $gameId]);
        $stats = $statsStmt->fetch(PDO::FETCH_ASSOC) ?: [
            'players_count' => 0,
            'pois_count' => 0,
            'treasures_count' => 0,
            'treasure_claims_count' => 0,
        ];

        $rowsStmt = $pdo->prepare(
            'SELECT
                p.id AS player_id,
                p.nickname,
                COALESCE(SUM(COALESCE(t.points, 0)), 0) AS points,
                COUNT(t.id) AS treasures_found,
                (
                    SELECT COUNT(*)
                    FROM events e
                    WHERE e.game_id = :game_id
                      AND e.player_id = p.id
                      AND e.event_type = :poi_event
                ) AS pois_visited,
                (
                    SELECT poi.name
                    FROM events e2
                    INNER JOIN pois poi ON poi.id = e2.poi_id
                    WHERE e2.game_id = :game_id
                      AND e2.player_id = p.id
                      AND e2.event_type = :poi_event
                    ORDER BY e2.created_at DESC, e2.id DESC
                    LIMIT 1
                ) AS last_checkpoint,
                (
                    SELECT e3.created_at
                    FROM events e3
                    WHERE e3.game_id = :game_id
                      AND e3.player_id = p.id
                      AND e3.event_type = :poi_event
                    ORDER BY e3.created_at DESC, e3.id DESC
                    LIMIT 1
                ) AS last_progress_at
             FROM players p
             LEFT JOIN treasure_claims tc
                ON tc.player_id = p.id
             LEFT JOIN treasures t
                ON t.id = tc.treasure_id
               AND t.game_id = :game_id
             WHERE p.game_id = :game_id
             GROUP BY p.id, p.nickname
             ORDER BY points DESC, treasures_found DESC, pois_visited DESC, p.nickname ASC'
        );
        $rowsStmt->execute([
            'game_id' => $gameId,
            'poi_event' => 'poi_visited',
        ]);

        $rows = $rowsStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $rank = 1;
        foreach ($rows as &$row) {
            $row['rank'] = $rank++;
            $row['player_id'] = (int) $row['player_id'];
            $row['points'] = (int) $row['points'];
            $row['treasures_found'] = (int) $row['treasures_found'];
            $row['pois_visited'] = (int) $row['pois_visited'];
            $row['nickname'] = (string) $row['nickname'];
            $row['last_checkpoint'] = $row['last_checkpoint'] !== null ? (string) $row['last_checkpoint'] : null;
            $row['last_progress_at'] = $row['last_progress_at'] !== null ? (string) $row['last_progress_at'] : null;
        }
        unset($row);

        return [
            'stats' => [
                'players_count' => (int) ($stats['players_count'] ?? 0),
                'pois_count' => (int) ($stats['pois_count'] ?? 0),
                'treasures_count' => (int) ($stats['treasures_count'] ?? 0),
                'treasure_claims_count' => (int) ($stats['treasure_claims_count'] ?? 0),
            ],
            'rows' => $rows,
        ];
    }
}
