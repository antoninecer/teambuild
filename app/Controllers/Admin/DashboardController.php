<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Repositories\HelpRepository;
use App\Repositories\UserRepository;
use App\Support\Database;

final class DashboardController
{
    private function requireAdmin(): array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['admin_user'])) {
            header('Location: /admin/login');
            exit;
        }

        return $_SESSION['admin_user'];
    }

    private function getAccessibleGameIds(array $adminUser): ?array
    {
        if (($adminUser['global_role'] ?? 'none') === 'superadmin') {
            return null;
        }

        $userRepo = new UserRepository();

        return $userRepo->findAccessibleGameIdsForUser((int) $adminUser['id']);
    }

    public function index(): void
    {
        $adminUser = $this->requireAdmin();

        if (($adminUser['global_role'] ?? 'none') !== 'superadmin') {
            header('Location: /admin/games');
            exit;
        }

        $pdo = Database::connection();

        $gamesCount = (int) $pdo->query('SELECT COUNT(*) FROM games')->fetchColumn();
        $playersCount = (int) $pdo->query('SELECT COUNT(*) FROM players')->fetchColumn();
        $usersCount = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();

        require __DIR__ . '/../../../resources/views/admin/index.php';
    }

    public function headerStatus(): void
    {
        $adminUser = $this->requireAdmin();

        header('Content-Type: application/json; charset=utf-8');

        $pdo = Database::connection();
        $accessibleGameIds = $this->getAccessibleGameIds($adminUser);

        if (is_array($accessibleGameIds) && empty($accessibleGameIds)) {
            echo json_encode([
                'counts' => [
                    'sos_open' => 0,
                    'new_events' => 0,
                ],
                'events' => [],
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $gameFilterSql = '';
        $gameFilterParams = [];

        if (is_array($accessibleGameIds)) {
            $placeholders = implode(',', array_fill(0, count($accessibleGameIds), '?'));
            $gameFilterSql = " AND hr.game_id IN ($placeholders)";
            $gameFilterParams = array_map('intval', $accessibleGameIds);
        }

        $sosSql = "
            SELECT 
                hr.id,
                hr.game_id,
                hr.player_id,
                hr.message,
                hr.status,
                hr.created_at,
                p.nickname AS player_nickname,
                g.name AS game_name
             FROM help_requests hr
             JOIN players p ON p.id = hr.player_id
             JOIN games g ON g.id = hr.game_id
             WHERE hr.status IN ('open', 'acknowledged')
             {$gameFilterSql}
             ORDER BY hr.created_at DESC
             LIMIT 20
        ";

        $sosStmt = $pdo->prepare($sosSql);
        $sosStmt->execute($gameFilterParams);
        $sosRows = $sosStmt->fetchAll(\PDO::FETCH_ASSOC);

        $eventFilterSql = '';
        $eventFilterParams = [];

        if (is_array($accessibleGameIds)) {
            $placeholders = implode(',', array_fill(0, count($accessibleGameIds), '?'));
            $eventFilterSql = " WHERE e.game_id IN ($placeholders)";
            $eventFilterParams = array_map('intval', $accessibleGameIds);
        }

        $eventsSql = "
            SELECT 
                e.id,
                e.event_type,
                e.created_at,
                e.player_id,
                e.game_id,
                e.poi_id,
                e.payload_json,
                p.nickname AS player_nickname,
                g.name AS game_name,
                poi.name AS poi_name
             FROM events e
             LEFT JOIN players p ON p.id = e.player_id
             LEFT JOIN games g ON g.id = e.game_id
             LEFT JOIN pois poi ON poi.id = e.poi_id
             {$eventFilterSql}
             ORDER BY e.created_at DESC
             LIMIT 30
        ";

        $eventsStmt = $pdo->prepare($eventsSql);
        $eventsStmt->execute($eventFilterParams);
        $eventRows = $eventsStmt->fetchAll(\PDO::FETCH_ASSOC);

        $events = [];

        foreach ($sosRows as $row) {
            $severity = ($row['status'] ?? 'open') === 'open' ? 'critical' : 'warning';

            $events[] = [
                'id' => 'sos-' . $row['id'],
                'help_id' => (int) $row['id'],
                'type' => 'sos_open',
                'severity' => $severity,
                'status' => (string) $row['status'],
                'message' => 'Hráč ' . ($row['player_nickname'] ?: ('#' . $row['player_id'])) . ' potřebuje pomoc',
                'detail_message' => (string) ($row['message'] ?? ''),
                'created_at' => $row['created_at'],
                'game_id' => (int) $row['game_id'],
                'player_id' => (int) $row['player_id'],
                'player_name' => (string) ($row['player_nickname'] ?? ''),
                'game_name' => (string) ($row['game_name'] ?? ''),
            ];
        }

        foreach ($eventRows as $row) {
            $type = (string) $row['event_type'];
            $payload = [];

            if (!empty($row['payload_json'])) {
                $decoded = json_decode((string) $row['payload_json'], true);
                if (is_array($decoded)) {
                    $payload = $decoded;
                }
            }

            if ($type === 'treasure_claimed') {
                $treasureName = (string) ($payload['treasure_name'] ?? $payload['name'] ?? 'poklad');

                $events[] = [
                    'id' => 'event-' . $row['id'],
                    'type' => 'treasure_claimed',
                    'severity' => 'info',
                    'message' => 'Hráč ' . ($row['player_nickname'] ?: ('#' . $row['player_id'])) . ' sebral poklad ' . $treasureName,
                    'created_at' => $row['created_at'],
                    'game_id' => (int) $row['game_id'],
                    'player_id' => (int) $row['player_id'],
                ];
            } elseif ($type === 'poi_visited') {
                $poiName = (string) ($row['poi_name'] ?: ($payload['poi_name'] ?? 'POI'));

                $events[] = [
                    'id' => 'event-' . $row['id'],
                    'type' => 'poi_completed',
                    'severity' => 'info',
                    'message' => 'Hráč ' . ($row['player_nickname'] ?: ('#' . $row['player_id'])) . ' dokončil POI ' . $poiName,
                    'created_at' => $row['created_at'],
                    'game_id' => (int) $row['game_id'],
                    'player_id' => (int) $row['player_id'],
                ];
            }
        }

        usort($events, static function (array $a, array $b): int {
            return strcmp((string) $b['created_at'], (string) $a['created_at']);
        });

        $events = array_slice($events, 0, 20);

        echo json_encode([
            'counts' => [
                'sos_open' => count(array_filter($sosRows, static function (array $row): bool {
                    return ($row['status'] ?? '') === 'open';
                })),
                'new_events' => count($events),
            ],
            'events' => array_values($events),
        ], JSON_UNESCAPED_UNICODE);
    }

    public function acknowledgeHelp(int $helpId): void
    {
        $this->requireAdmin();
        header('Content-Type: application/json; charset=utf-8');

        $repo = new HelpRepository();

        $ok = $repo->acknowledge($helpId);
        $row = $repo->findById($helpId);

        echo json_encode([
            'success' => $ok,
            'help' => $row,
        ], JSON_UNESCAPED_UNICODE);
    }

    public function resolveHelp(int $helpId): void
    {
        $this->requireAdmin();
        header('Content-Type: application/json; charset=utf-8');

        $repo = new HelpRepository();

        $ok = $repo->resolve($helpId);
        $row = $repo->findById($helpId);

        echo json_encode([
            'success' => $ok,
            'help' => $row,
        ], JSON_UNESCAPED_UNICODE);
    }
}