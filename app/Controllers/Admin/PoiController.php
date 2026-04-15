<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Repositories\GameRepository;
use App\Repositories\PoiRepository;
use App\Support\Database;
use RuntimeException;

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
        $ttsText = trim($_POST['tts_text'] ?? '');
        $radiusM = trim($_POST['radius_m'] ?? '50');
        $sortOrder = trim($_POST['sort_order'] ?? '0');
        $isEnabled = isset($_POST['is_enabled']) ? 1 : 0;
        $autoUnlockOnProximity = isset($_POST['auto_unlock_on_proximity']) ? 1 : 0;
        $isRequired = isset($_POST['is_required']) ? 1 : 0;
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

        $mediaErrors = $this->validateMediaInput($_POST['media'] ?? [], $_FILES);
        $errors = array_merge($errors, $mediaErrors);

        if ($errors !== []) {
            $_SESSION['poi_form_errors'] = $errors;
            $_SESSION['poi_form_old'] = $_POST;
            header('Location: /admin/games/' . $gameId . '/pois/create');
            exit;
        }

        $poiRepo = new PoiRepository();
        $poiId = $poiRepo->create([
            'game_id' => $gameId,
            'type' => $type,
            'name' => $name,
            'description' => $description !== '' ? $description : null,
            'story_text' => $storyText !== '' ? $storyText : null,
            'tts_text' => $ttsText !== '' ? $ttsText : null,
            'lat' => (float) $lat,
            'lon' => (float) $lon,
            'radius_m' => (int) $radiusM,
            'sort_order' => (int) $sortOrder,
            'is_enabled' => $isEnabled,
            'active_from' => $activeFrom !== '' ? $activeFrom : null,
            'active_to' => $activeTo !== '' ? $activeTo : null,
            'auto_unlock_on_proximity' => $autoUnlockOnProximity,
            'is_required' => $isRequired,
        ]);

        $this->replaceMedia((int) $gameId, $poiId, $_POST['media'] ?? [], $_FILES);

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
        $poiMedia = $this->loadPoiMedia($id);

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
        $ttsText = trim($_POST['tts_text'] ?? '');
        $radiusM = trim($_POST['radius_m'] ?? '50');
        $sortOrder = trim($_POST['sort_order'] ?? '0');
        $isEnabled = isset($_POST['is_enabled']) ? 1 : 0;
        $autoUnlockOnProximity = isset($_POST['auto_unlock_on_proximity']) ? 1 : 0;
        $isRequired = isset($_POST['is_required']) ? 1 : 0;
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

        $mediaErrors = $this->validateMediaInput($_POST['media'] ?? [], $_FILES);
        $errors = array_merge($errors, $mediaErrors);

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
            'tts_text' => $ttsText !== '' ? $ttsText : null,
            'lat' => (float) $lat,
            'lon' => (float) $lon,
            'radius_m' => (int) $radiusM,
            'sort_order' => (int) $sortOrder,
            'is_enabled' => $isEnabled,
            'active_from' => $activeFrom !== '' ? $activeFrom : null,
            'active_to' => $activeTo !== '' ? $activeTo : null,
            'auto_unlock_on_proximity' => $autoUnlockOnProximity,
            'is_required' => $isRequired,
        ]);

        $this->replaceMedia((int) $poi['game_id'], $id, $_POST['media'] ?? [], $_FILES);

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

    private function loadPoiMedia(int $poiId): array
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare(
            'SELECT *
             FROM poi_media
             WHERE poi_id = :poi_id
             ORDER BY sort_order ASC, id ASC'
        );

        $stmt->execute(['poi_id' => $poiId]);

        return $stmt->fetchAll();
    }

    private function validateMediaInput(array $mediaRows, array $files): array
    {
        $errors = [];

        foreach ($mediaRows as $index => $row) {
            $type = trim((string) ($row['media_type'] ?? 'image'));
            $path = trim((string) ($row['file_path'] ?? ''));
            $fileKey = 'media_file_' . $index;
            $hasUpload = isset($files[$fileKey]) && (int) ($files[$fileKey]['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;

            if ($type !== 'image' && $type !== 'video') {
                $errors[] = 'Neplatný typ média na řádku ' . ((int) $index + 1) . '.';
                continue;
            }

            if ($path === '' && !$hasUpload) {
                continue;
            }

            if ($type === 'video' && $hasUpload) {
                $errors[] = 'Na řádku ' . ((int) $index + 1) . ' nelze nahrávat soubor pro video. Použij YouTube URL.';
            }

            if ($type === 'video' && $path !== '' && !$this->isYoutubeUrl($path)) {
                $errors[] = 'Na řádku ' . ((int) $index + 1) . ' je neplatná YouTube URL.';
            }

            if ($hasUpload) {
                $uploadError = (int) $files[$fileKey]['error'];
                if ($uploadError !== UPLOAD_ERR_OK) {
                    $errors[] = 'Upload na řádku ' . ((int) $index + 1) . ' selhal.';
                    continue;
                }

                $size = (int) ($files[$fileKey]['size'] ?? 0);
                if ($size <= 0 || $size > 5 * 1024 * 1024) {
                    $errors[] = 'Soubor na řádku ' . ((int) $index + 1) . ' musí mít maximálně 5 MB.';
                }

                try {
                    $this->detectAllowedImageExtension($files[$fileKey]['tmp_name'], (string) $files[$fileKey]['name']);
                } catch (RuntimeException $e) {
                    $errors[] = 'Soubor na řádku ' . ((int) $index + 1) . ': ' . $e->getMessage();
                }
            }
        }

        return $errors;
    }

    private function replaceMedia(int $gameId, int $poiId, array $mediaRows, array $files): void
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare('DELETE FROM poi_media WHERE poi_id = :poi_id');
        $stmt->execute(['poi_id' => $poiId]);

        foreach ($mediaRows as $index => $row) {
            $type = trim((string) ($row['media_type'] ?? 'image'));
            $path = trim((string) ($row['file_path'] ?? ''));
            $title = trim((string) ($row['title'] ?? ''));
            $sortOrder = (int) ($row['sort_order'] ?? 0);
            $fileKey = 'media_file_' . $index;

            $hasUpload = isset($files[$fileKey]) && (int) ($files[$fileKey]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK;

            if ($path === '' && !$hasUpload) {
                continue;
            }

            if ($type === 'video') {
                if ($path !== '' && $this->isYoutubeUrl($path)) {
                    $this->insertPoiMedia($poiId, 'video', $path, $title !== '' ? $title : null, $sortOrder);
                }
                continue;
            }

            if ($path !== '') {
                $this->insertPoiMedia($poiId, 'image', $path, $title !== '' ? $title : null, $sortOrder);
            }

            if ($hasUpload) {
                $storedPath = $this->storeUploadedImage($gameId, $poiId, $files[$fileKey]);
                $this->insertPoiMedia($poiId, 'image', $storedPath, $title !== '' ? $title : null, $sortOrder);
            }
        }
    }

    private function insertPoiMedia(int $poiId, string $mediaType, string $filePath, ?string $title, int $sortOrder): void
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare(
            'INSERT INTO poi_media (poi_id, media_type, file_path, title, alt_text, sort_order, autoplay, created_at)
             VALUES (:poi_id, :media_type, :file_path, :title, :alt_text, :sort_order, 0, NOW())'
        );

        $stmt->execute([
            'poi_id' => $poiId,
            'media_type' => $mediaType,
            'file_path' => $filePath,
            'title' => $title,
            'alt_text' => $title,
            'sort_order' => $sortOrder,
        ]);
    }

    private function storeUploadedImage(int $gameId, int $poiId, array $file): string
    {
        $extension = $this->detectAllowedImageExtension((string) $file['tmp_name'], (string) $file['name']);

        $baseDir = dirname(__DIR__, 3) . '/public/uploads/games/' . $gameId . '/pois/' . $poiId;
        if (!is_dir($baseDir) && !mkdir($baseDir, 0775, true) && !is_dir($baseDir)) {
            throw new RuntimeException('Nepodařilo se vytvořit adresář pro upload.');
        }

        $filename = 'img-' . time() . '-' . bin2hex(random_bytes(4)) . '.' . $extension;
        $targetPath = $baseDir . '/' . $filename;

        if (!move_uploaded_file((string) $file['tmp_name'], $targetPath)) {
            throw new RuntimeException('Nepodařilo se uložit nahraný soubor.');
        }

        return '/uploads/games/' . $gameId . '/pois/' . $poiId . '/' . $filename;
    }

    private function detectAllowedImageExtension(string $tmpPath, string $originalName): string
    {
        $mime = @mime_content_type($tmpPath) ?: '';
        $ext = strtolower((string) pathinfo($originalName, PATHINFO_EXTENSION));

        $allowedByMime = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];

        if (isset($allowedByMime[$mime])) {
            return $allowedByMime[$mime];
        }

        $allowedByExt = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($ext, $allowedByExt, true)) {
            return $ext === 'jpeg' ? 'jpg' : $ext;
        }

        throw new RuntimeException('Povolené jsou pouze JPG, PNG nebo WEBP.');
    }

    private function isYoutubeUrl(string $url): bool
    {
        if ($url === '') {
            return false;
        }

        $host = parse_url($url, PHP_URL_HOST);
        if (!is_string($host) || $host === '') {
            return false;
        }

        $host = strtolower($host);

        return str_contains($host, 'youtube.com') || str_contains($host, 'youtu.be');
    }
}