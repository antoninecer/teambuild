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

    private function requireGameAccess(int $gameId): array
    {
        $adminUser = $this->requireAdmin();

        if (($adminUser['global_role'] ?? 'none') === 'superadmin') {
            return $adminUser;
        }

        $userRepo = new \App\Repositories\UserRepository();

        if (!$userRepo->hasGameAccess((int) $adminUser['id'], $gameId)) {
            http_response_code(403);
            echo 'Na tuto hru nemáte oprávnění.';
            exit;
        }

        return $adminUser;
    }

    public function index(int $gameId): void
    {
        $this->requireGameAccess($gameId);

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
        $this->requireGameAccess($gameId);

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
        $this->requireGameAccess($gameId);

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

        $this->requireGameAccess((int) $poi['game_id']);

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

        $this->requireGameAccess((int) $poi['game_id']);

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

        $this->requireGameAccess((int) $poi['game_id']);

        $gameId = (int) $poi['game_id'];
        $poiRepo->delete($id);

        header('Location: /admin/games/' . $gameId . '/pois');
        exit;
    }

    private function validateMediaInput(array $mediaRows, array $files): array
    {
        $errors = [];

        foreach ($mediaRows as $index => $row) {
            $type = $this->mediaTypeFromRow($row);
            $label = $this->mediaLabelFromRow($row);
            $externalUrl = $this->mediaExternalUrlFromRow($row);
            $existingPath = $this->mediaExistingPathFromRow($row);
            $sortOrder = trim((string) ($row['sort_order'] ?? '0'));
            $upload = $this->mediaUploadFromFiles($files, (int) $index);

            $hasNewUpload = $upload['has_upload'];
            $hasExistingPath = $existingPath !== '';
            $hasExternalUrl = $externalUrl !== '';
            $hasAnySource = $hasNewUpload || $hasExistingPath || $hasExternalUrl;

            if ($type === '' && $label === '' && !$hasAnySource) {
                continue;
            }

            if ($type === '') {
                $errors[] = 'U média je povinný typ.';
                continue;
            }

            if (!in_array($type, ['image', 'video', 'audio'], true)) {
                $errors[] = 'Neplatný typ média.';
                continue;
            }

            if (!is_numeric($sortOrder)) {
                $errors[] = 'Pořadí média musí být číslo.';
            }

            if (($hasNewUpload && $hasExternalUrl) || ($hasNewUpload && $hasExistingPath) || ($hasExistingPath && $hasExternalUrl)) {
                $errors[] = 'U jednoho média použij buď soubor, nebo externí URL.';
                continue;
            }

            if (!$hasAnySource) {
                $errors[] = 'U média je potřeba nahrát soubor nebo zadat externí URL.';
                continue;
            }

            if ($type === 'video' && $hasNewUpload) {
                $errors[] = 'U videa použij externí URL, ne upload souboru.';
                continue;
            }

            if ($hasExternalUrl && filter_var($externalUrl, FILTER_VALIDATE_URL) === false) {
                $errors[] = 'Externí URL média není platná.';
                continue;
            }

            if ($type === 'video' && $hasExternalUrl && !$this->isYoutubeUrl($externalUrl)) {
                $errors[] = 'U videa použij platnou YouTube URL.';
                continue;
            }

            if ($hasNewUpload) {
                $fileError = $upload['error'];

                if ($fileError !== UPLOAD_ERR_OK) {
                    $errors[] = 'Nahrání média selhalo.';
                    continue;
                }

                $tmpName = $upload['tmp_name'];
                if (!$tmpName || !is_uploaded_file($tmpName)) {
                    $errors[] = 'Dočasný soubor média nebyl nalezen.';
                    continue;
                }

                if ($type === 'image') {
                    $detectedMime = mime_content_type($tmpName) ?: '';
                    $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
                    if (!in_array($detectedMime, $allowedMimes, true)) {
                        $errors[] = 'Soubor média neodpovídá zvolenému typu.';
                    }
                }

                if ($type === 'audio') {
                    $detectedMime = mime_content_type($tmpName) ?: '';
                    $allowedMimes = ['audio/mpeg', 'audio/mp3', 'audio/mp4', 'audio/ogg', 'audio/wav', 'audio/webm'];
                    if (!in_array($detectedMime, $allowedMimes, true)) {
                        $errors[] = 'Soubor média neodpovídá zvolenému typu.';
                    }
                }
            }
        }

        return $errors;
    }

    private function replaceMedia(int $gameId, int $poiId, array $mediaRows, array $files): void
    {
        $pdo = Database::connection();
        $pdo->prepare('DELETE FROM poi_media WHERE poi_id = :poi_id')->execute(['poi_id' => $poiId]);

        $insertStmt = $pdo->prepare(
            'INSERT INTO poi_media (poi_id, media_type, file_path, external_url, mime_type, label, sort_order, created_at)
             VALUES (:poi_id, :media_type, :file_path, :external_url, :mime_type, :label, :sort_order, NOW())'
        );

        foreach ($mediaRows as $index => $row) {
            $type = $this->mediaTypeFromRow($row);
            $label = $this->mediaLabelFromRow($row);
            $externalUrl = $this->mediaExternalUrlFromRow($row);
            $existingPath = $this->mediaExistingPathFromRow($row);
            $sortOrder = (int) ($row['sort_order'] ?? 0);

            $upload = $this->mediaUploadFromFiles($files, (int) $index);
            $hasNewUpload = $upload['has_upload'];
            $filePath = null;
            $mimeType = null;

            if ($type === '' && $label === '' && $externalUrl === '' && !$hasNewUpload && $existingPath === '') {
                continue;
            }

            if ($externalUrl === '' && !$hasNewUpload && $existingPath === '') {
                continue;
            }

            if ($hasNewUpload) {
                $tmpName = $upload['tmp_name'];
                $originalName = (string) ($upload['name'] ?? 'file');
                $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

                $safeExtension = preg_replace('~[^a-z0-9]+~', '', $extension) ?: 'bin';
                $fileName = sprintf(
                    'poi_%d_%d_%s.%s',
                    $poiId,
                    $sortOrder,
                    bin2hex(random_bytes(4)),
                    $safeExtension
                );

                $targetDir = dirname(__DIR__, 3) . '/public/uploads/games/' . $gameId . '/poi';
                if (!is_dir($targetDir) && !mkdir($targetDir, 0775, true) && !is_dir($targetDir)) {
                    throw new RuntimeException('Nepodařilo se vytvořit adresář pro média.');
                }

                $targetPath = $targetDir . '/' . $fileName;

                if (!move_uploaded_file($tmpName, $targetPath)) {
                    throw new RuntimeException('Nepodařilo se uložit nahrané médium.');
                }

                $filePath = '/uploads/games/' . $gameId . '/poi/' . $fileName;
                $mimeType = mime_content_type($targetPath) ?: null;
            } elseif ($existingPath !== '') {
                $filePath = $existingPath;
            }

            $insertStmt->execute([
                'poi_id' => $poiId,
                'media_type' => $type !== '' ? $type : 'image',
                'file_path' => $filePath,
                'external_url' => $externalUrl !== '' ? $externalUrl : null,
                'mime_type' => $mimeType,
                'label' => $label !== '' ? $label : null,
                'sort_order' => $sortOrder,
            ]);
        }
    }

    private function loadPoiMedia(int $poiId): array
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare(
            'SELECT id, media_type, file_path, external_url, mime_type, label, sort_order
             FROM poi_media
             WHERE poi_id = :poi_id
             ORDER BY sort_order ASC, id ASC'
        );

        $stmt->execute(['poi_id' => $poiId]);

        return $stmt->fetchAll();
    }

    private function mediaTypeFromRow(array $row): string
    {
        return trim((string) ($row['media_type'] ?? ''));
    }

    private function mediaLabelFromRow(array $row): string
    {
        return trim((string) ($row['label'] ?? ($row['title'] ?? '')));
    }

    private function mediaExternalUrlFromRow(array $row): string
    {
        return trim((string) ($row['external_url'] ?? ($row['file_path'] ?? '')));
    }

    private function mediaExistingPathFromRow(array $row): string
    {
        return trim((string) ($row['existing_path'] ?? ''));
    }

    private function mediaUploadFromFiles(array $files, int $index): array
    {
        if (isset($files['media']['error'][$index]['upload'])) {
            $error = (int) ($files['media']['error'][$index]['upload'] ?? UPLOAD_ERR_NO_FILE);
            return [
                'has_upload' => $error !== UPLOAD_ERR_NO_FILE,
                'error' => $error,
                'tmp_name' => $files['media']['tmp_name'][$index]['upload'] ?? null,
                'name' => $files['media']['name'][$index]['upload'] ?? null,
            ];
        }

        $flatKey = 'media_file_' . $index;
        if (isset($files[$flatKey])) {
            $error = (int) ($files[$flatKey]['error'] ?? UPLOAD_ERR_NO_FILE);
            return [
                'has_upload' => $error !== UPLOAD_ERR_NO_FILE,
                'error' => $error,
                'tmp_name' => $files[$flatKey]['tmp_name'] ?? null,
                'name' => $files[$flatKey]['name'] ?? null,
            ];
        }

        return [
            'has_upload' => false,
            'error' => UPLOAD_ERR_NO_FILE,
            'tmp_name' => null,
            'name' => null,
        ];
    }

    private function isYoutubeUrl(string $url): bool
    {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        if ($host === '') {
            return false;
        }

        return in_array($host, [
            'youtube.com',
            'www.youtube.com',
            'm.youtube.com',
            'youtu.be',
            'www.youtu.be',
        ], true);
    }
}
