<?php

declare(strict_types=1);

namespace App\Controllers\Player;

use App\Repositories\GameRepository;
use App\Repositories\HelpRepository;
use App\Repositories\InviteRepository;
use App\Repositories\PlayerRepository;

final class PlayerController
{
    private GameRepository $gameRepo;
    private InviteRepository $inviteRepo;
    private PlayerRepository $playerRepo;
    private HelpRepository $helpRepo;

    public function __construct()
    {
        $this->gameRepo = new GameRepository();
        $this->inviteRepo = new InviteRepository();
        $this->playerRepo = new PlayerRepository();
        $this->helpRepo = new HelpRepository();
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

        // SESSION CHECK
        $token = $_COOKIE['player_session'] ?? null;
        if ($token) {
            $tokenHash = hash('sha256', $token);
            $session = $this->playerRepo->findBySessionToken($tokenHash);

            if ($session && (int)$session['game_id'] === (int)$game['id'] && strtotime($session['expires_at']) > time()) {
                $this->dashboard((int)$session['player_id'], $game);
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

        if ($this->playerRepo->findByNicknameInGame($nickname, (int)$game['id'])) {
            $error = "Tento nickname je již obsazen.";
            require __DIR__ . '/../../../resources/views/player/register.php';
            return;
        }

        $invite = null;

        if ($inviteCode !== '') {
            $invite = $this->inviteRepo->findByCode($inviteCode);

            if (!$invite || (int)$invite['game_id'] !== (int)$game['id'] || (int)$invite['is_active'] === 0) {
                $error = "Neplatný invite kód.";
                require __DIR__ . '/../../../resources/views/player/register.php';
                return;
            }

            if ($invite['max_uses'] !== null && (int)$invite['used_count'] >= (int)$invite['max_uses']) {
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
            $this->inviteRepo->incrementUsage((int)$invite['id']);
        }

        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $expiresAt = date('Y-m-d H:i:s', time() + 365 * 24 * 60 * 60);

        $this->playerRepo->createSession($playerId, $tokenHash, $expiresAt);

        setcookie('player_session', $token, time() + 365 * 24 * 60 * 60, '/');

        unset($_SESSION['invite_code']);

        header("Location: /game/" . $game['slug']);
        exit;
    }

    public function dashboard(int $playerId, array $game): void
    {
        $player = $this->playerRepo->findById($playerId);
        require __DIR__ . '/../../../resources/views/player/dashboard.php';
    }

    public function updateLocation(): void
    {
        $session = $this->getSession();
        if (!$session) {
            $this->unauthorized();
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        $lat = (float) ($data['lat'] ?? 0);
        $lon = (float) ($data['lon'] ?? 0);
        $accuracy = (float) ($data['accuracy'] ?? 0);

        $this->playerRepo->updateLocation((int)$session['player_id'], $lat, $lon, $accuracy);
        $this->playerRepo->logLocation((int)$session['player_id'], $lat, $lon, $accuracy);

        echo json_encode(['success' => true]);
    }

    public function requestHelp(): void
    {
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

        echo json_encode(['success' => true]);
    }

    private function getSession(): ?array
    {
        $token = $_COOKIE['player_session'] ?? null;
        if (!$token) return null;

        $session = $this->playerRepo->findBySessionToken(hash('sha256', $token));

        if (!$session || strtotime($session['expires_at']) <= time()) {
            return null;
        }

        return $session;
    }

    private function unauthorized(): void
    {
        http_response_code(401);
        echo json_encode(['success' => false]);
    }
}