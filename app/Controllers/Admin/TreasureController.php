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
        $poiRepo = new PoiRepository();
        $treasureRepo = new TreasureRepository();

        $game = $gameRepo->findById($gameId);

        if (!$game) {
            http_response_code(404);
            echo 'Hra nebyla nalezena.';
            exit;
        }

        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $poiId = trim($_POST['poi_id'] ?? '');
        $lat = trim($_POST['lat'] ?? '');
        $lon = trim($_POST['lon'] ?? '');
        $radiusM = trim($_POST['radius_m'] ?? '20');
        $treasureType = trim($_POST['treasure_type'] ?? 'public');
        $isVisibleOnMap = isset($_POST['is_visible_on_map']) ? 1 : 0;
        $maxClaims = trim($_POST['max_claims'] ?? '');
        $points = trim($_POST['points'] ?? '0');
        $isEnabled = isset($_POST['is_enabled']) ? 1 : 0;

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

        if ($errors !== []) {
            $_SESSION['treasure_form_errors'] = $errors;
            $_SESSION['treasure_form_old'] = $_POST;
            header('Location: /admin/games/' . $gameId . '/treasures/create');
            exit;
        }

        $treasureRepo->create([
            'game_id' => $gameId,
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
        ]);

        header('Location: /admin/games/' . $gameId . '/treasures');
        exit;
    }
}