<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Repositories\GameRepository;
use App\Repositories\PoiRepository;
use App\Repositories\TreasureRepository;

final class TreasureController
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
        $treasureRepo = new TreasureRepository();

        $game = $gameRepo->findById($gameId);

        if (!$game) {
            http_response_code(404);
            echo 'Hra nebyla nalezena.';
            exit;
        }

        $treasures = $treasureRepo->allForGame($gameId);

        require __DIR__ . '/../../../resources/views/admin/treasures/index.php';
    }

    public function createForm(int $gameId): void
    {
        $this->requireAdmin();

        $gameRepo = new GameRepository();
        $poiRepo = new PoiRepository();

        $game = $gameRepo->findById($gameId);

        if (!$game) {
            http_response_code(404);
            echo 'Hra nebyla nalezena.';
            exit;
        }

        $pois = $poiRepo->allForGame($gameId);
        $old = $_SESSION['treasure_form_old'] ?? [];
        $errors = $_SESSION['treasure_form_errors'] ?? [];

        unset($_SESSION['treasure_form_old'], $_SESSION['treasure_form_errors']);

        require __DIR__ . '/../../../resources/views/admin/treasures/create.php';
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

        [$data, $errors] = $this->validateTreasureInput($_POST);

        if ($errors !== []) {
            $_SESSION['treasure_form_errors'] = $errors;
            $_SESSION['treasure_form_old'] = $_POST;
            header('Location: /admin/games/' . $gameId . '/treasures/create');
            exit;
        }

        $repo = new TreasureRepository();
        $data['game_id'] = $gameId;
        $repo->create($data);

        header('Location: /admin/games/' . $gameId . '/treasures');
        exit;
    }

    public function editForm(int $treasureId): void
    {
        $this->requireAdmin();

        $repo = new TreasureRepository();
        $gameRepo = new GameRepository();
        $poiRepo = new PoiRepository();

        $treasure = $repo->findById($treasureId);

        if (!$treasure) {
            http_response_code(404);
            echo 'Poklad nebyl nalezen.';
            exit;
        }

        $game = $gameRepo->findById((int) $treasure['game_id']);

        if (!$game) {
            http_response_code(404);
            echo 'Hra nebyla nalezena.';
            exit;
        }

        $pois = $poiRepo->allForGame((int) $game['id']);
        $old = $_SESSION['treasure_form_old'] ?? [];
        $errors = $_SESSION['treasure_form_errors'] ?? [];

        unset($_SESSION['treasure_form_old'], $_SESSION['treasure_form_errors']);

        require __DIR__ . '/../../../resources/views/admin/treasures/edit.php';
    }

    public function update(int $treasureId): void
    {
        $this->requireAdmin();

        $repo = new TreasureRepository();
        $treasure = $repo->findById($treasureId);

        if (!$treasure) {
            http_response_code(404);
            echo 'Poklad nebyl nalezen.';
            exit;
        }

        [$data, $errors] = $this->validateTreasureInput($_POST);

        if ($errors !== []) {
            $_SESSION['treasure_form_errors'] = $errors;
            $_SESSION['treasure_form_old'] = $_POST;
            header('Location: /admin/treasures/' . $treasureId . '/edit');
            exit;
        }

        $repo->update($treasureId, $data);

        header('Location: /admin/games/' . (int) $treasure['game_id'] . '/treasures');
        exit;
    }

    public function delete(int $treasureId): void
    {
        $this->requireAdmin();

        $repo = new TreasureRepository();
        $treasure = $repo->findById($treasureId);

        if (!$treasure) {
            http_response_code(404);
            echo 'Poklad nebyl nalezen.';
            exit;
        }

        $gameId = (int) $treasure['game_id'];
        $repo->delete($treasureId);

        header('Location: /admin/games/' . $gameId . '/treasures');
        exit;
    }

    private function validateTreasureInput(array $input): array
    {
        $name = trim($input['name'] ?? '');
        $description = trim($input['description'] ?? '');
        $poiId = trim($input['poi_id'] ?? '');
        $lat = trim($input['lat'] ?? '');
        $lon = trim($input['lon'] ?? '');
        $radiusM = trim($input['radius_m'] ?? '20');
        $treasureType = trim($input['treasure_type'] ?? 'public');
        $isVisibleOnMap = isset($input['is_visible_on_map']) ? 1 : 0;
        $maxClaims = trim($input['max_claims'] ?? '');
        $points = trim($input['points'] ?? '0');
        $isEnabled = isset($input['is_enabled']) ? 1 : 0;

        $errors = [];

        if ($name === '') {
            $errors[] = 'Název pokladu je povinný.';
        }

        if ($lat === '' || !is_numeric($lat)) {
            $errors[] = 'Latitude musí být číslo.';
        }

        if ($lon === '' || !is_numeric($lon)) {
            $errors[] = 'Longitude musí být číslo.';
        }

        if (!ctype_digit((string) $radiusM) || (int) $radiusM < 1) {
            $errors[] = 'Radius musí být kladné celé číslo.';
        }

        $allowedTypes = ['public', 'hidden', 'individual', 'team'];
        if (!in_array($treasureType, $allowedTypes, true)) {
            $errors[] = 'Neplatný typ pokladu.';
        }

        if ($maxClaims !== '' && (!ctype_digit((string) $maxClaims) || (int) $maxClaims < 1)) {
            $errors[] = 'Limit sebrání musí být kladné celé číslo nebo prázdný.';
        }

        if (!is_numeric($points)) {
            $errors[] = 'Body musí být číslo.';
        }

        return [[
            'poi_id' => $poiId !== '' ? (int) $poiId : null,
            'name' => $name,
            'description' => $description !== '' ? $description : null,
            'lat' => (float) $lat,
            'lon' => (float) $lon,
            'radius_m' => (int) $radiusM,
            'treasure_type' => $treasureType,
            'is_visible_on_map' => $isVisibleOnMap,
            'max_claims' => $maxClaims !== '' ? (int) $maxClaims : null,
            'points' => (int) $points,
            'is_enabled' => $isEnabled,
        ], $errors];
    }
}