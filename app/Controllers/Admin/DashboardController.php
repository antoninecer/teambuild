<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

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

    public function headerStatus(): void
{
    $this->requireAdmin();

    header('Content-Type: application/json; charset=utf-8');

    $pdo = \App\Support\Database::connection();

    // Otevřená SOS
    $sosStmt = $pdo->query(
        "SELECT 
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
         ORDER BY hr.created_at DESC
         LIMIT 20"
    );
    $sosRows = $sosStmt->fetchAll(\PDO::FETCH_ASSOC);

    // Poslední eventy
    $eventsStmt = $pdo->query(
        "SELECT 
            e.id,
            e.event_type,
            e.created_at,
            e.player_id,
            e.game_id,
            e.poi_id,
            e.treasure_id,
            p.nickname AS player_nickname,
            g.name AS game_name,
            poi.name AS poi_name,
            t.name AS treasure_name
         FROM events e
         LEFT JOIN players p ON p.id = e.player_id
         LEFT JOIN games g ON g.id = e.game_id
         LEFT JOIN pois poi ON poi.id = e.poi_id
         LEFT JOIN treasures t ON t.id = e.treasure_id
         ORDER BY e.created_at DESC
         LIMIT 30"
    );
    $eventRows = $eventsStmt->fetchAll(\PDO::FETCH_ASSOC);

    $events = [];

    foreach ($sosRows as $row) {
        $events[] = [
            'id' => 'sos-' . $row['id'],
            'type' => 'sos_open',
            'severity' => 'critical',
            'message' => 'Hráč ' . ($row['player_nickname'] ?: ('#' . $row['player_id'])) . ' potřebuje pomoc',
            'created_at' => $row['created_at'],
            'game_id' => (int) $row['game_id'],
            'player_id' => (int) $row['player_id'],
        ];
    }

    foreach ($eventRows as $row) {
        $type = (string) $row['event_type'];

        if ($type === 'treasure_claimed') {
            $events[] = [
                'id' => 'event-' . $row['id'],
                'type' => 'treasure_claimed',
                'severity' => 'info',
                'message' => 'Hráč ' . ($row['player_nickname'] ?: ('#' . $row['player_id'])) . ' sebral poklad ' . ($row['treasure_name'] ?: ''),
                'created_at' => $row['created_at'],
                'game_id' => (int) $row['game_id'],
                'player_id' => (int) $row['player_id'],
            ];
        } elseif ($type === 'poi_visited') {
            $events[] = [
                'id' => 'event-' . $row['id'],
                'type' => 'poi_completed',
                'severity' => 'info',
                'message' => 'Hráč ' . ($row['player_nickname'] ?: ('#' . $row['player_id'])) . ' dokončil POI ' . ($row['poi_name'] ?: ''),
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
            'sos_open' => count($sosRows),
            'new_events' => count($events),
        ],
        'events' => array_values($events),
    ], JSON_UNESCAPED_UNICODE);
}

    public function index(): void
    {
        $adminUser = $this->requireAdmin();

        require __DIR__ . '/../../../resources/views/admin/index.php';
    }
}