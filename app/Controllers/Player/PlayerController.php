<?php

declare(strict_types=1);

namespace App\Controllers\Player;

use App\Repositories\GameRepository;
use App\Repositories\HelpRepository;
use App\Repositories\InviteRepository;
use App\Repositories\PlayerRepository;
use App\Repositories\PoiRepository;
use App\Repositories\TreasureRepository;
use App\Support\Database;
use PDO;

final class PlayerController
{
    private GameRepository $gameRepo;
    private InviteRepository $inviteRepo;
    private PlayerRepository $playerRepo;
    private HelpRepository $helpRepo;
    private PoiRepository $poiRepo;
    private TreasureRepository $treasureRepo;

    public function __construct()
    {
        $this->gameRepo = new GameRepository();
        $this->inviteRepo = new InviteRepository();
        $this->playerRepo = new PlayerRepository();
        $this->helpRepo = new HelpRepository();
        $this->poiRepo = new PoiRepository();
        $this->treasureRepo = new TreasureRepository();
    }

    public function showGame(string $slug): void
    {
        $game = $this->gameRepo->findBySlug($slug);

        if (!$game) {
            http_response_code(404);
            echo "Hra nenalezena.";
            return;
        }

        if (!in_array($game['status'], ['registration_open', 'active'], true)) {
            http_response_code(403);
            echo "Registrace do této hry není možná.";
            return;
        }

        $token = $_COOKIE['player_session'] ?? null;
        if ($token) {
            $tokenHash = hash('sha256', $token);
            $session = $this->playerRepo->findBySessionToken($tokenHash);

            if ($session && (int) $session['game_id'] === (int) $game['id'] && strtotime($session['expires_at']) > time()) {
                $this->dashboard((int) $session['player_id'], $game);
                return;
            }
        }

        $inviteCode = $_GET['invite'] ?? '';

        if ($inviteCode !== '') {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['invite_code'] = $inviteCode;
        }

        require __DIR__ . '/../../../resources/views/player/register.php';
    }

    public function register(string $slug): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $game = $this->gameRepo->findBySlug($slug);

        if (!$game) {
            http_response_code(404);
            echo "Hra nenalezena.";
            return;
        }

        if (!in_array($game['status'], ['registration_open', 'active'], true)) {
            http_response_code(403);
            echo "Registrace do této hry není možná.";
            return;
        }

        $nickname = trim($_POST['nickname'] ?? '');
        $inviteCode = trim($_POST['invite_code'] ?? '');

        if ($inviteCode === '' && isset($_SESSION['invite_code'])) {
            $inviteCode = $_SESSION['invite_code'];
        }

        if ($nickname === '') {
            $error = "Nickname musí být vyplněn.";
            require __DIR__ . '/../../../resources/views/player/register.php';
            return;
        }

        if ($this->playerRepo->findByNicknameInGame($nickname, (int) $game['id'])) {
            $error = "Tento nickname je již obsazen.";
            require __DIR__ . '/../../../resources/views/player/register.php';
            return;
        }

        $invite = null;

        if ($inviteCode !== '') {
            $invite = $this->inviteRepo->findByCode($inviteCode);

            if (!$invite || (int) $invite['game_id'] !== (int) $game['id'] || (int) $invite['is_active'] === 0) {
                $error = "Neplatný invite kód.";
                require __DIR__ . '/../../../resources/views/player/register.php';
                return;
            }

            if ($invite['max_uses'] !== null && (int) $invite['used_count'] >= (int) $invite['max_uses']) {
                $error = "Invite je vyčerpaný.";
                require __DIR__ . '/../../../resources/views/player/register.php';
                return;
            }
        }

        $playerId = $this->playerRepo->create([
            'game_id' => $game['id'],
            'nickname' => $nickname,
            'invite_id' => $invite ? $invite['id'] : null,
            'team_id' => $invite ? $invite['team_id'] : null,
            'status' => 'active',
        ]);

        if ($invite) {
            $this->inviteRepo->incrementUsage((int) $invite['id']);
        }

        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $expiresAt = date('Y-m-d H:i:s', time() + 365 * 24 * 60 * 60);

        $this->playerRepo->createSession($playerId, $tokenHash, $expiresAt);

        setcookie('player_session', $token, time() + 365 * 24 * 60 * 60, '/');

        unset($_SESSION['invite_code']);

        header('Location: /game/' . $game['slug']);
        exit;
    }

    public function dashboard(int $playerId, array $game): void
    {
        $player = $this->playerRepo->findById($playerId);
        $playerStats = $this->buildPlayerStats($playerId, (int) $game['id']);
        $leaderboard = $this->buildLeaderboard((int) $game['id']);

        require __DIR__ . '/../../../resources/views/player/dashboard.php';
    }

    public function updateLocation(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $session = $this->getSession();
        if (!$session) {
            $this->unauthorized();
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        $lat = (float) ($data['lat'] ?? 0);
        $lon = (float) ($data['lon'] ?? 0);
        $accuracy = (float) ($data['accuracy'] ?? 0);

        $playerId = (int) $session['player_id'];
        $gameId = (int) $session['game_id'];
        $teamId = isset($session['team_id']) ? (int) $session['team_id'] : null;

        //$this->playerRepo->updateLocation($playerId, $lat, $lon, $accuracy);
        $this->playerRepo->logLocation($playerId, $lat, $lon, $accuracy);


        echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
    }

    public function requestHelp(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $session = $this->getSession();
        if (!$session) {
            $this->unauthorized();
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        $this->helpRepo->create([
            'game_id' => $session['game_id'],
            'player_id' => $session['player_id'],
            'team_id' => $session['team_id'] ?? null,
            'lat' => (float) ($data['lat'] ?? 0),
            'lon' => (float) ($data['lon'] ?? 0),
            'message' => trim($data['message'] ?? ''),
            'status' => 'open',
        ]);

        echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
    }

    public function mapData(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $session = $this->getSession();
        if (!$session) {
            $this->unauthorized();
            return;
        }

        $gameId = (int) $session['game_id'];
        $playerId = (int) $session['player_id'];
        $teamId = isset($session['team_id']) ? (int) $session['team_id'] : null;

        $pois = $this->poiRepo->activeForGame($gameId);
        $visitedPoiIds = $this->getVisitedPoiIds($playerId, $gameId);

        foreach ($pois as &$poi) {
            $poi['media'] = $this->poiRepo->getMedia((int) $poi['id']);
            $poi['visited_by_player'] = in_array((int) $poi['id'], $visitedPoiIds, true) ? 1 : 0;
        }
        unset($poi);

        $treasures = $this->treasureRepo->visibleForGameWithClaimState($gameId, $playerId, $teamId);

        echo json_encode([
            'success' => true,
            'pois' => array_values($pois),
            'treasures' => array_values($treasures),
            'visited_poi_ids' => $visitedPoiIds,
        ], JSON_UNESCAPED_UNICODE);
    }

   public function exploreNearby(): void
{
    header('Content-Type: application/json; charset=utf-8');

    try {
        $session = $this->getSession();
        if (!$session) {
            $this->unauthorized();
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        $lat = (float) ($data['lat'] ?? 0);
        $lon = (float) ($data['lon'] ?? 0);

        if ($lat === 0.0 && $lon === 0.0) {
            http_response_code(422);
            echo json_encode([
                'success' => false,
                'error' => 'missing_location',
                'message' => 'Chybí poloha hráče.',
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $gameId = (int) $session['game_id'];
        $playerId = (int) $session['player_id'];
        $teamId = isset($session['team_id']) ? (int) $session['team_id'] : null;

        $candidates = [];

        // 1) POI
        $pois = $this->poiRepo->activeForGame($gameId);

        foreach ($pois as $poi) {
            $poiId = (int) $poi['id'];

            if ($this->hasPoiVisit($playerId, $poiId)) {
                continue;
            }

            $distance = $this->distanceMeters(
                $lat,
                $lon,
                (float) $poi['lat'],
                (float) $poi['lon']
            );

            if ($distance > (float) $poi['radius_m']) {
                continue;
            }

            $poi['distance_m'] = round($distance, 1);
            $poi['media'] = $this->poiRepo->getMedia($poiId);
            $poi['kind'] = 'poi';

            $candidates[] = $poi;
        }

        // 2) TREASURES
        $treasures = $this->treasureRepo->visibleForGameWithClaimState($gameId, $playerId, $teamId);

        foreach ($treasures as $treasure) {
            if ((int) ($treasure['claimed_by_player'] ?? 0) === 1) {
                continue;
            }

            if ((int) ($treasure['claimed_by_team'] ?? 0) === 1) {
                continue;
            }

            $distance = $this->distanceMeters(
                $lat,
                $lon,
                (float) $treasure['lat'],
                (float) $treasure['lon']
            );

            if ($distance > (float) $treasure['radius_m']) {
                continue;
            }

            $treasure['distance_m'] = round($distance, 1);
            $treasure['kind'] = 'treasure';

            $candidates[] = $treasure;
        }

        usort($candidates, static function (array $a, array $b): int {
            return ($a['distance_m'] <=> $b['distance_m']);
        });

        $count = count($candidates);

        if ($count === 0) {
            echo json_encode([
                'success' => true,
                'type' => 'none',
                'message' => 'V okolí jsi nic zajímavého nenašel.',
                'debug' => [
                    'player_lat' => $lat,
                    'player_lon' => $lon,
                    'pois_total' => count($pois),
                    'treasures_total' => count($treasures),
                ],
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        if ($count === 1) {
            echo json_encode([
                'success' => true,
                'type' => 'single',
                'object' => $this->serializeExploreObject($candidates[0]),
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $objects = array_map(function (array $item): array {
            return $this->serializeExploreObject($item);
        }, $candidates);

        echo json_encode([
            'success' => true,
            'type' => 'multiple',
            'objects' => $objects,
        ], JSON_UNESCAPED_UNICODE);
    } catch (\Throwable $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'explore_failed',
            'message' => $e->getMessage(),
            'file' => basename($e->getFile()),
            'line' => $e->getLine(),
        ], JSON_UNESCAPED_UNICODE);
    }
}
    
    
    public function completePoi(): void
{
    header('Content-Type: application/json; charset=utf-8');

    try {
        $session = $this->getSession();
        if (!$session) {
            $this->unauthorized();
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        $poiId = (int) ($data['poi_id'] ?? 0);
        $lat = (float) ($data['lat'] ?? 0);
        $lon = (float) ($data['lon'] ?? 0);
        $accuracy = (float) ($data['accuracy'] ?? 0);

        if ($poiId <= 0) {
            http_response_code(422);
            echo json_encode([
                'success' => false,
                'error' => 'invalid_poi',
                'message' => 'Neplatné POI.',
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $playerId = (int) $session['player_id'];
        $gameId = (int) $session['game_id'];
        $teamId = isset($session['team_id']) && $session['team_id'] !== null ? (int) $session['team_id'] : null;

        if ($this->hasPoiVisit($playerId, $poiId)) {
            echo json_encode([
                'success' => true,
                'status' => 'already_completed',
                'poi_id' => $poiId,
                'unlocked_treasures' => [],
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $pois = $this->poiRepo->activeForGame($gameId);
        $targetPoi = null;

        foreach ($pois as $poi) {
            if ((int) $poi['id'] === $poiId) {
                $targetPoi = $poi;
                break;
            }
        }

        if (!$targetPoi) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'poi_not_found',
                'message' => 'POI nebylo nalezeno.',
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $distance = $this->distanceMeters(
            $lat,
            $lon,
            (float) $targetPoi['lat'],
            (float) $targetPoi['lon']
        );

        if ($distance > (float) $targetPoi['radius_m']) {
            http_response_code(409);
            echo json_encode([
                'success' => false,
                'status' => 'too_far',
                'message' => 'Pro potvrzení průzkumu musíš být blíž.',
                'distance_m' => round($distance, 1),
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $this->recordPoiVisit(
            $gameId,
            $playerId,
            $teamId,
            $poiId,
            $lat,
            $lon,
            $accuracy,
            $distance
        );

        echo json_encode([
            'success' => true,
            'status' => 'completed',
            'poi_id' => $poiId,
            'unlocked_treasures' => [],
        ], JSON_UNESCAPED_UNICODE);
    } catch (\Throwable $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'complete_poi_failed',
            'message' => $e->getMessage(),
            'file' => basename($e->getFile()),
            'line' => $e->getLine(),
        ], JSON_UNESCAPED_UNICODE);
    }
}

    public function claimTreasure(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $session = $this->getSession();
        if (!$session) {
            $this->unauthorized();
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        $treasureId = (int) ($data['treasure_id'] ?? 0);
        $lat = (float) ($data['lat'] ?? 0);
        $lon = (float) ($data['lon'] ?? 0);

        if ($treasureId <= 0) {
            http_response_code(422);
            echo json_encode([
                'success' => false,
                'status' => 'invalid_treasure',
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $result = $this->treasureRepo->claimForPlayer(
            $treasureId,
            (int) $session['game_id'],
            (int) $session['player_id'],
            isset($session['team_id']) ? (int) $session['team_id'] : null,
            $lat,
            $lon
        );

        if (($result['success'] ?? false) === false) {
            $statusCode = match ($result['status'] ?? 'error') {
                'not_found' => 404,
                'too_far' => 409,
                'already_claimed' => 409,
                'already_claimed_team' => 409,
                'empty' => 409,
                default => 400,
            };

            http_response_code($statusCode);
        }

        echo json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    private function buildPlayerStats(int $playerId, int $gameId): array
    {
        $pdo = Database::connection();

        $totalsStmt = $pdo->prepare(
            'SELECT
                (SELECT COUNT(*) FROM pois WHERE game_id = :game_id AND is_enabled = 1) AS total_pois,
                (SELECT COUNT(*) FROM treasures WHERE game_id = :game_id AND is_enabled = 1) AS total_treasures'
        );
        $totalsStmt->execute(['game_id' => $gameId]);
        $totals = $totalsStmt->fetch(PDO::FETCH_ASSOC) ?: ['total_pois' => 0, 'total_treasures' => 0];

        $playerStmt = $pdo->prepare(
            'SELECT
                COALESCE(SUM(COALESCE(t.points, 0)), 0) AS points,
                COUNT(t.id) AS treasures_found
             FROM treasure_claims tc
             INNER JOIN treasures t ON t.id = tc.treasure_id
             WHERE tc.player_id = :player_id
               AND t.game_id = :game_id'
        );
        $playerStmt->execute([
            'player_id' => $playerId,
            'game_id' => $gameId,
        ]);
        $playerRow = $playerStmt->fetch(PDO::FETCH_ASSOC) ?: ['points' => 0, 'treasures_found' => 0];

        $poiVisitsStmt = $pdo->prepare(
            'SELECT COUNT(*) AS pois_visited
             FROM events
             WHERE game_id = :game_id
               AND player_id = :player_id
               AND event_type = :event_type'
        );
        $poiVisitsStmt->execute([
            'game_id' => $gameId,
            'player_id' => $playerId,
            'event_type' => 'poi_visited',
        ]);
        $poiVisitsRow = $poiVisitsStmt->fetch(PDO::FETCH_ASSOC) ?: ['pois_visited' => 0];

        $leaderboard = $this->buildLeaderboard($gameId);

        $rank = 0;
        foreach ($leaderboard as $row) {
            if ((int) $row['player_id'] === $playerId) {
                $rank = (int) $row['rank'];
                break;
            }
        }

        $poisVisited = (int) $poiVisitsRow['pois_visited'];
        $treasuresFound = (int) $playerRow['treasures_found'];

        $tasksTotal = (int) $totals['total_pois'] + (int) $totals['total_treasures'];
        $tasksDone = $poisVisited + $treasuresFound;
        $progressPercent = $tasksTotal > 0 ? (int) round(($tasksDone / $tasksTotal) * 100) : 0;

        return [
            'points' => (int) $playerRow['points'],
            'treasures_found' => $treasuresFound,
            'pois_visited' => $poisVisited,
            'total_pois' => (int) $totals['total_pois'],
            'total_treasures' => (int) $totals['total_treasures'],
            'tasks_total' => $tasksTotal,
            'tasks_done' => $tasksDone,
            'progress_percent' => $progressPercent,
            'rank' => $rank,
        ];
    }

    private function buildLeaderboard(int $gameId): array
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare(
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
                      AND e.event_type = :event_type
                ) AS pois_visited
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

        $stmt->execute([
            'game_id' => $gameId,
            'event_type' => 'poi_visited',
        ]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $rank = 1;
        foreach ($rows as &$row) {
            $row['rank'] = $rank++;
        }
        unset($row);

        return $rows;
    }

    private function checkPoiVisits(
        int $playerId,
        int $gameId,
        ?int $teamId,
        float $lat,
        float $lon,
        float $accuracy
    ): void {
        $pois = $this->poiRepo->activeForGame($gameId);

        foreach ($pois as $poi) {
            $poiId = (int) $poi['id'];

            if ($this->hasPoiVisit($playerId, $poiId)) {
                continue;
            }

            $distance = $this->distanceMeters(
                $lat,
                $lon,
                (float) $poi['lat'],
                (float) $poi['lon']
            );

            if ($distance > (float) $poi['radius_m']) {
                continue;
            }

            $this->recordPoiVisit(
                $gameId,
                $playerId,
                $teamId,
                $poiId,
                $lat,
                $lon,
                $accuracy,
                $distance
            );
        }
    }

    private function hasPoiVisit(int $playerId, int $poiId): bool
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare(
            'SELECT 1
             FROM events
             WHERE player_id = :player_id
               AND poi_id = :poi_id
               AND event_type = :event_type
             LIMIT 1'
        );

        $stmt->execute([
            'player_id' => $playerId,
            'poi_id' => $poiId,
            'event_type' => 'poi_visited',
        ]);

        return (bool) $stmt->fetchColumn();
    }

    private function recordPoiVisit(
        int $gameId,
        int $playerId,
        ?int $teamId,
        int $poiId,
        float $lat,
        float $lon,
        float $accuracy,
        float $distance
    ): void {
        $pdo = Database::connection();

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

        $payload = [
            'lat' => $lat,
            'lon' => $lon,
            'accuracy' => $accuracy,
            'distance_m' => round($distance, 2),
        ];

        $stmt->execute([
            'game_id' => $gameId,
            'player_id' => $playerId,
            'team_id' => $teamId,
            'poi_id' => $poiId,
            'event_type' => 'poi_visited',
            'payload_json' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    }

    private function serializeExploreObject(array $item): array
    {
        if (($item['kind'] ?? '') === 'treasure') {
            return [
                'kind' => 'treasure',
                'id' => (int) $item['id'],
                'name' => (string) ($item['name'] ?? 'Poklad'),
                'description' => (string) ($item['description'] ?? ''),
                'lat' => (float) $item['lat'],
                'lon' => (float) $item['lon'],
                'radius_m' => (int) ($item['radius_m'] ?? 0),
                'distance_m' => (float) ($item['distance_m'] ?? 0),
                'type' => 'treasure',
                'claimed_by_player' => (int) ($item['claimed_by_player'] ?? 0),
                'claimed_by_team' => (int) ($item['claimed_by_team'] ?? 0),
                'points' => (int) ($item['points'] ?? 0),
            ];
        }

        return [
            'kind' => 'poi',
            'id' => (int) $item['id'],
            'name' => (string) ($item['name'] ?? 'Bod'),
            'description' => (string) ($item['description'] ?? ''),
            'story_text' => (string) ($item['story_text'] ?? ''),
            'tts_text' => (string) ($item['tts_text'] ?? ''),
            'lat' => (float) $item['lat'],
            'lon' => (float) $item['lon'],
            'radius_m' => (int) ($item['radius_m'] ?? 0),
            'distance_m' => (float) ($item['distance_m'] ?? 0),
            'type' => (string) ($item['type'] ?? 'poi'),
            'media' => $item['media'] ?? [],
        ];
    }

    private function getVisitedPoiIds(int $playerId, int $gameId): array
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare(
            'SELECT poi_id
             FROM events
             WHERE game_id = :game_id
               AND player_id = :player_id
               AND event_type = :event_type
               AND poi_id IS NOT NULL'
        );

        $stmt->execute([
            'game_id' => $gameId,
            'player_id' => $playerId,
            'event_type' => 'poi_visited',
        ]);

        $rows = $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];

        return array_map('intval', $rows);
    }

    private function distanceMeters(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000.0;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a =
            sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    private function getSession(): ?array
    {
        $token = $_COOKIE['player_session'] ?? null;
        if (!$token) {
            return null;
        }

        $session = $this->playerRepo->findBySessionToken(hash('sha256', $token));

        if (!$session || strtotime($session['expires_at']) <= time()) {
            return null;
        }

        return $session;
    }

    private function unauthorized(): void
    {
        http_response_code(401);
        echo json_encode(['success' => false], JSON_UNESCAPED_UNICODE);
    }
}
