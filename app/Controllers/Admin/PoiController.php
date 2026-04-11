<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Repositories\GameRepository;
use App\Repositories\PoiRepository;

final class PoiController
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

    public function index(int $gameId): void
    {
        $this->requireAdmin();

        $gameRepo = new GameRepository();
        $game = $gameRepo->findById($gameId);

        if (!$game) {
            http_response_code(404);
            echo 'Hra nebyla nalezena.';
            exit;
        }

        $poiRepo = new PoiRepository();
        $pois = $poiRepo->allForGame($gameId);

        require __DIR__ . '/../../../resources/views/admin/pois/index.php';
    }

    public function createForm(int $gameId): void
    {
        $this->requireAdmin();

        $gameRepo = new GameRepository();
        $game = $gameRepo->findById($gameId);

        if (!$game) {
            http_response_code(404);
            echo 'Hra nebyla nalezena.';
            exit;
        }

        $old = $_SESSION['poi_form_old'] ?? [];
        $errors = $_SESSION['poi_form_errors'] ?? [];

        unset($_SESSION['poi_form_old'], $_SESSION['poi_form_errors']);

        $allowedTypes = ['start_point', 'story_point', 'checkpoint', 'rescue_point', 'hint_point', 'finish_point', 'meetup_point'];

        require __DIR__ . '/../../../resources/views/admin/pois/create.php';
    }

    public function store(int $gameId): void
    {
        $this->requireAdmin();

        $gameRepo = new GameRepository();
        $game = $gameRepo->findById($gameId);

        if (!$game) {
            http_response_code(404);
            echo 'Hra nebyla nalezena.';
            exit;
        }

        $name = trim($_POST['name'] ?? '');
        $type = trim($_POST['type'] ?? '');
        $lat = trim($_POST['lat'] ?? '');
        $lon = trim($_POST['lon'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $storyText = trim($_POST['story_text'] ?? '');
        $radiusM = trim($_POST['radius_m'] ?? '50');
        $sortOrder = trim($_POST['sort_order'] ?? '0');
        $isEnabled = isset($_POST['is_enabled']) ? 1 : 0;
        $activeFrom = trim($_POST['active_from'] ?? '');
        $activeTo = trim($_POST['active_to'] ?? '');

        $errors = [];
        $allowedTypes = ['start_point', 'story_point', 'checkpoint', 'rescue_point', 'hint_point', 'finish_point', 'meetup_point'];

        if ($name === '') {
            $errors[] = 'Název POI je povinný.';
        }

        if (!in_array($type, $allowedTypes, true)) {
            $errors[] = 'Neplatný typ POI.';
        }

        if ($lat === '' || !is_numeric($lat)) {
            $errors[] = 'Latitude musí být číslo.';
        }

        if ($lon === '' || !is_numeric($lon)) {
            $errors[] = 'Longitude musí být číslo.';
        }

        if (!ctype_digit((string) $radiusM)) {
            $errors[] = 'Rádius musí být celé číslo.';
        }

        if (!is_numeric($sortOrder)) {
            $errors[] = 'Pořadí musí být číslo.';
        }

        if ($errors !== []) {
            $_SESSION['poi_form_errors'] = $errors;
            $_SESSION['poi_form_old'] = $_POST;
            header('Location: /admin/games/' . $gameId . '/pois/create');
            exit;
        }

        $poiRepo = new PoiRepository();
        $poiRepo->create([
            'game_id' => $gameId,
            'type' => $type,
            'name' => $name,
            'description' => $description !== '' ? $description : null,
            'story_text' => $storyText !== '' ? $storyText : null,
            'lat' => (float) $lat,
            'lon' => (float) $lon,
            'radius_m' => (int) $radiusM,
            'sort_order' => (int) $sortOrder,
            'is_enabled' => $isEnabled,
            'active_from' => $activeFrom !== '' ? $activeFrom : null,
            'active_to' => $activeTo !== '' ? $activeTo : null,
        ]);

        header('Location: /admin/games/' . $gameId . '/pois');
        exit;
    }

    public function editForm(int $id): void
    {
        $this->requireAdmin();

        $poiRepo = new PoiRepository();
        $poi = $poiRepo->findById($id);

        if (!$poi) {
            http_response_code(404);
            echo 'POI nebyl nalezen.';
            exit;
        }

        $gameRepo = new GameRepository();
        $game = $gameRepo->findById((int) $poi['game_id']);

        $old = $_SESSION['poi_form_old'] ?? $poi;
        $errors = $_SESSION['poi_form_errors'] ?? [];

        unset($_SESSION['poi_form_old'], $_SESSION['poi_form_errors']);

        $allowedTypes = ['start_point', 'story_point', 'checkpoint', 'rescue_point', 'hint_point', 'finish_point', 'meetup_point'];

        require __DIR__ . '/../../../resources/views/admin/pois/edit.php';
    }

    public function update(int $id): void
    {
        $this->requireAdmin();

        $poiRepo = new PoiRepository();
        $poi = $poiRepo->findById($id);

        if (!$poi) {
            http_response_code(404);
            echo 'POI nebyl nalezen.';
            exit;
        }

        $name = trim($_POST['name'] ?? '');
        $type = trim($_POST['type'] ?? '');
        $lat = trim($_POST['lat'] ?? '');
        $lon = trim($_POST['lon'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $storyText = trim($_POST['story_text'] ?? '');
        $radiusM = trim($_POST['radius_m'] ?? '50');
        $sortOrder = trim($_POST['sort_order'] ?? '0');
        $isEnabled = isset($_POST['is_enabled']) ? 1 : 0;
        $activeFrom = trim($_POST['active_from'] ?? '');
        $activeTo = trim($_POST['active_to'] ?? '');

        $errors = [];
        $allowedTypes = ['start_point', 'story_point', 'checkpoint', 'rescue_point', 'hint_point', 'finish_point', 'meetup_point'];

        if ($name === '') {
            $errors[] = 'Název POI je povinný.';
        }

        if (!in_array($type, $allowedTypes, true)) {
            $errors[] = 'Neplatný typ POI.';
        }

        if ($lat === '' || !is_numeric($lat)) {
            $errors[] = 'Latitude musí být číslo.';
        }

        if ($lon === '' || !is_numeric($lon)) {
            $errors[] = 'Longitude musí být číslo.';
        }

        if (!ctype_digit((string) $radiusM)) {
            $errors[] = 'Rádius musí být celé číslo.';
        }

        if (!is_numeric($sortOrder)) {
            $errors[] = 'Pořadí musí být číslo.';
        }

        if ($errors !== []) {
            $_SESSION['poi_form_errors'] = $errors;
            $_SESSION['poi_form_old'] = $_POST;
            header('Location: /admin/pois/' . $id . '/edit');
            exit;
        }

        $poiRepo->update($id, [
            'type' => $type,
            'name' => $name,
            'description' => $description !== '' ? $description : null,
            'story_text' => $storyText !== '' ? $storyText : null,
            'lat' => (float) $lat,
            'lon' => (float) $lon,
            'radius_m' => (int) $radiusM,
            'sort_order' => (int) $sortOrder,
            'is_enabled' => $isEnabled,
            'active_from' => $activeFrom !== '' ? $activeFrom : null,
            'active_to' => $activeTo !== '' ? $activeTo : null,
        ]);

        header('Location: /admin/games/' . $poi['game_id'] . '/pois');
        exit;
    }

    public function delete(int $id): void
    {
        $this->requireAdmin();

        $poiRepo = new PoiRepository();
        $poi = $poiRepo->findById($id);

        if (!$poi) {
            http_response_code(404);
            echo 'POI nebyl nalezen.';
            exit;
        }

        $poiRepo->delete($id);

        header('Location: /admin/games/' . $poi['game_id'] . '/pois');
        exit;
    }
}
