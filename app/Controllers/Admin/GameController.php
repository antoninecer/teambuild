<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Repositories\GameRepository;
use App\Repositories\PlayerRepository;
use App\Support\Database;
use PDO;

final class GameController
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

    public function index(): void
    {
        $this->requireAdmin();

        $repo = new GameRepository();
        $games = $repo->all();

        require __DIR__ . '/../../../resources/views/admin/games/index.php';
    }

    public function createForm(): void
    {
        $this->requireAdmin();

        $old = $_SESSION['game_form_old'] ?? [];
        $errors = $_SESSION['game_form_errors'] ?? [];

        unset($_SESSION['game_form_old'], $_SESSION['game_form_errors']);

        require __DIR__ . '/../../../resources/views/admin/games/create.php';
    }

    public function store(): void
    {
        $adminUser = $this->requireAdmin();

        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $introText = trim($_POST['intro_text'] ?? '');
        $objectiveText = trim($_POST['objective_text'] ?? '');
        $playerGuideText = trim($_POST['player_guide_text'] ?? '');
        $startsAt = trim($_POST['starts_at'] ?? '');
        $endsAt = trim($_POST['ends_at'] ?? '');
        $status = trim($_POST['status'] ?? 'draft');
        $operationMode = trim($_POST['operation_mode'] ?? 'self_service');
        $registrationEnabled = isset($_POST['registration_enabled']) ? 1 : 0;
        $mapCenterLat = trim($_POST['map_center_lat'] ?? '');
        $mapCenterLon = trim($_POST['map_center_lon'] ?? '');
        $mapDefaultZoom = trim($_POST['map_default_zoom'] ?? '14');
        $sessionCookieDays = trim($_POST['session_cookie_days'] ?? '365');

        $errors = [];

        if ($name === '') {
            $errors[] = 'Název hry je povinný.';
        }

        if ($slug === '') {
            $errors[] = 'Slug je povinný.';
        } elseif (!preg_match('~^[a-z0-9-]+$~', $slug)) {
            $errors[] = 'Slug smí obsahovat jen malá písmena, čísla a pomlčky.';
        }

        if ($startsAt === '') {
            $errors[] = 'Začátek je povinný.';
        }

        if ($endsAt === '') {
            $errors[] = 'Konec je povinný.';
        }

        if ($startsAt !== '' && $endsAt !== '' && strtotime($startsAt) > strtotime($endsAt)) {
            $errors[] = 'Začátek nesmí být později než konec.';
        }

        $allowedStatuses = ['draft', 'registration_open', 'active', 'finished', 'archived'];
        if (!in_array($status, $allowedStatuses, true)) {
            $errors[] = 'Neplatný stav hry.';
        }

        $allowedOperationModes = ['self_service', 'moderated'];
        if (!in_array($operationMode, $allowedOperationModes, true)) {
            $errors[] = 'Neplatný režim hry.';
        }

        $repo = new GameRepository();

        if ($slug !== '' && $repo->existsBySlug($slug)) {
            $errors[] = 'Slug už existuje.';
        }

        if ($mapCenterLat !== '' && !is_numeric($mapCenterLat)) {
            $errors[] = 'Latitude středu mapy musí být číslo.';
        }

        if ($mapCenterLon !== '' && !is_numeric($mapCenterLon)) {
            $errors[] = 'Longitude středu mapy musí být číslo.';
        }

        if (!ctype_digit((string) $mapDefaultZoom)) {
            $errors[] = 'Zoom musí být celé číslo.';
        }

        if (!ctype_digit((string) $sessionCookieDays) || (int) $sessionCookieDays < 1) {
            $errors[] = 'Délka cookie musí být kladné celé číslo.';
        }

        if ($errors !== []) {
            $_SESSION['game_form_errors'] = $errors;
            $_SESSION['game_form_old'] = $_POST;
            header('Location: /admin/games/create');
            exit;
        }

        $gameId = $repo->create([
            'name' => $name,
            'slug' => $slug,
            'description' => $description !== '' ? $description : null,
            'intro_text' => $introText !== '' ? $introText : null,
            'objective_text' => $objectiveText !== '' ? $objectiveText : null,
            'player_guide_text' => $playerGuideText !== '' ? $playerGuideText : null,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'registration_enabled' => $registrationEnabled,
            'status' => $status,
            'operation_mode' => $operationMode,
            'map_center_lat' => $mapCenterLat !== '' ? (float) $mapCenterLat : null,
            'map_center_lon' => $mapCenterLon !== '' ? (float) $mapCenterLon : null,
            'map_default_zoom' => (int) $mapDefaultZoom,
            'session_cookie_days' => (int) $sessionCookieDays,
            'created_by' => (int) $adminUser['id'],
        ]);

        header('Location: /admin/games/' . $gameId);
        exit;
    }

    public function show(int $id): void
    {
        $this->requireAdmin();

        $repo = new GameRepository();
        $game = $repo->findById($id);

        if (!$game) {
            http_response_code(404);
            echo 'Hra nebyla nalezena.';
            exit;
        }

        require __DIR__ . '/../../../resources/views/admin/games/show.php';
    }

public function editForm(int $id): void
{
    $this->requireAdmin();

    $gameRepo = new \App\Repositories\GameRepository();
    $game = $gameRepo->findById($id);

    if (!$game) {
        http_response_code(404);
        echo 'Hra nebyla nalezena.';
        exit;
    }

    require __DIR__ . '/../../../resources/views/admin/games/edit.php';
}

public function update(int $id): void
{
    $this->requireAdmin();

    $gameRepo = new \App\Repositories\GameRepository();
    $game = $gameRepo->findById($id);

    if (!$game) {
        http_response_code(404);
        echo 'Hra nebyla nalezena.';
        exit;
    }

    $name = trim((string)($_POST['name'] ?? ''));
    $slug = trim((string)($_POST['slug'] ?? ''));
    $description = trim((string)($_POST['description'] ?? ''));
    $introText = trim((string)($_POST['intro_text'] ?? ''));
    $objectiveText = trim((string)($_POST['objective_text'] ?? ''));
    $playerGuideText = trim((string)($_POST['player_guide_text'] ?? ''));
    $status = trim((string)($_POST['status'] ?? 'draft'));
    $operationMode = trim((string)($_POST['operation_mode'] ?? 'self_service'));
    $startsAt = trim((string)($_POST['starts_at'] ?? ''));
    $endsAt = trim((string)($_POST['ends_at'] ?? ''));
    $registrationEnabled = (int)($_POST['registration_enabled'] ?? 0);
    $sessionCookieDays = (int)($_POST['session_cookie_days'] ?? 365);

    if ($name === '' || $slug === '') {
        http_response_code(422);
        echo 'Název hry a slug musí být vyplněny.';
        exit;
    }

    $gameRepo->update($id, [
        'name' => $name,
        'slug' => $slug,
        'description' => $description,
        'intro_text' => $introText,
        'objective_text' => $objectiveText,
        'player_guide_text' => $playerGuideText,
        'status' => $status,
        'operation_mode' => $operationMode,
        'starts_at' => $startsAt !== '' ? $startsAt : null,
        'ends_at' => $endsAt !== '' ? $endsAt : null,
        'registration_enabled' => $registrationEnabled,
        'session_cookie_days' => $sessionCookieDays,
    ]);

    header('Location: /admin/games/' . $id);
    exit;
}

    public function playerDetail(int $playerId): void
{
    $this->requireAdmin();

    $playerRepo = new PlayerRepository();
    $player = $playerRepo->findById($playerId);

    if (!$player) {
        http_response_code(404);
        echo 'Hráč nebyl nalezen.';
        exit;
    }

    $gameRepo = new GameRepository();
    $game = $gameRepo->findById((int) $player['game_id']);

    $pdo = Database::connection();

    // Treasures found
    $treasuresStmt = $pdo->prepare(
        'SELECT tc.*, t.name, t.points
         FROM treasure_claims tc
         JOIN treasures t ON tc.treasure_id = t.id
         WHERE tc.player_id = :player_id
         ORDER BY tc.claimed_at DESC'
    );
    $treasuresStmt->execute(['player_id' => $playerId]);
    $claimedTreasures = $treasuresStmt->fetchAll(PDO::FETCH_ASSOC);

    // Location history
    $locationStmt = $pdo->prepare(
        'SELECT *
         FROM location_log
         WHERE player_id = :player_id
         ORDER BY created_at DESC
         LIMIT 100'
    );
    $locationStmt->execute(['player_id' => $playerId]);
    $locationHistory = $locationStmt->fetchAll(PDO::FETCH_ASSOC);

    // Last known position
    $lastKnownPosition = $locationHistory[0] ?? null;

    // Active SOS for this player
    $helpStmt = $pdo->prepare(
        'SELECT *
         FROM help_requests
         WHERE player_id = :player_id
           AND status IN (\'open\', \'acknowledged\')
         ORDER BY created_at DESC
         LIMIT 1'
    );
    $helpStmt->execute(['player_id' => $playerId]);
    $activeHelpRequest = $helpStmt->fetch(PDO::FETCH_ASSOC) ?: null;

    // Recent player events
    $eventsStmt = $pdo->prepare(
        'SELECT *
         FROM events
         WHERE player_id = :player_id
         ORDER BY created_at DESC
         LIMIT 20'
    );
    $eventsStmt->execute(['player_id' => $playerId]);
    $recentEvents = $eventsStmt->fetchAll(PDO::FETCH_ASSOC);

    require __DIR__ . '/../../../resources/views/admin/players/show.php';
}
}