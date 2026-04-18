<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Repositories\GameRepository;
use App\Repositories\InviteRepository;
use App\Repositories\TeamRepository;

final class InviteController
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

        $inviteRepo = new InviteRepository();
        $invites = $inviteRepo->allForGame($gameId);

        require __DIR__ . '/../../../resources/views/admin/invites/index.php';
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

        $teamRepo = new TeamRepository();
        $teams = $teamRepo->allForGame($gameId);

        $old = $_SESSION['invite_form_old'] ?? [];
        $errors = $_SESSION['invite_form_errors'] ?? [];

        unset($_SESSION['invite_form_old'], $_SESSION['invite_form_errors']);

        require __DIR__ . '/../../../resources/views/admin/invites/create.php';
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

        $code = trim($_POST['code'] ?? '');
        $label = trim($_POST['label'] ?? '');
        $teamId = trim($_POST['team_id'] ?? '');
        $maxUses = trim($_POST['max_uses'] ?? '');

        if ($code === '') {
            $code = bin2hex(random_bytes(4));
        }

        $errors = [];

        $inviteRepo = new InviteRepository();
        if ($inviteRepo->findByCode($code)) {
            $errors[] = 'Pozvánka s tímto kódem již existuje.';
        }

        if ($maxUses !== '' && !ctype_digit($maxUses)) {
            $errors[] = 'Maximální počet použití musí být číslo.';
        }

        if ($errors !== []) {
            $_SESSION['invite_form_errors'] = $errors;
            $_SESSION['invite_form_old'] = $_POST;
            header('Location: /admin/games/' . $gameId . '/invites/create');
            exit;
        }

        $inviteRepo->create([
            'game_id' => $gameId,
            'code' => $code,
            'label' => $label !== '' ? $label : null,
            'team_id' => $teamId !== '' ? (int) $teamId : null,
            'max_uses' => $maxUses !== '' ? (int) $maxUses : null,
            'used_count' => 0,
            'is_active' => 1,
        ]);

        header('Location: /admin/games/' . $gameId . '/invites');
        exit;
    }

    public function delete(int $id): void
    {
        $this->requireAdmin();

        $inviteRepo = new InviteRepository();
        $invite = $inviteRepo->findById($id);

        if (!$invite) {
            http_response_code(404);
            echo 'Pozvánka nebyla nalezena.';
            exit;
        }

        $this->requireGameAccess((int) $invite['game_id']);

        $inviteRepo->delete($id);

        header('Location: /admin/games/' . $invite['game_id'] . '/invites');
        exit;
    }
}
