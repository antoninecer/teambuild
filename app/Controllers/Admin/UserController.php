<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Repositories\UserRepository;

final class UserController
{
    private UserRepository $repo;

    public function __construct()
    {
        $this->repo = new UserRepository();
    }

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
        $adminUser = $this->requireAdmin();

        $users = $this->repo->allAdmins();

        require __DIR__ . '/../../../resources/views/admin/users/index.php';
    }

    // =========================
    // CREATE USER
    // =========================
    public function create(): void
    {
        $this->requireAdmin();

        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $globalRole = $_POST['global_role'] ?? 'none';
        $role = $_POST['role'] ?? 'admin';

        if ($username === '' || $password === '') {
            header('Location: /admin/users');
            exit;
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $this->repo->createUser([
            'username' => $username,
            'email' => $email !== '' ? $email : null,
            'password_hash' => $passwordHash,
            'global_role' => $globalRole,
            'role' => $role,
        ]);

        header('Location: /admin/users');
        exit;
    }

    // =========================
    // ACTIVATE / DEACTIVATE
    // =========================
    public function toggle(int $userId): void
    {
        $this->requireAdmin();

        $this->repo->toggleActive($userId);

        header('Location: /admin/users');
        exit;
    }

    // =========================
    // CHANGE PASSWORD FORM
    // =========================
    public function changePasswordForm(int $userId): void
    {
        $this->requireAdmin();

        $user = $this->repo->findById($userId);

        if (!$user) {
            header('Location: /admin/users');
            exit;
        }

        require __DIR__ . '/../../../resources/views/admin/users/password.php';
    }

    // =========================
    // CHANGE PASSWORD
    // =========================
    public function changePassword(int $userId): void
    {
        $this->requireAdmin();

        $password = $_POST['password'] ?? '';

        if ($password === '') {
            header('Location: /admin/users');
            exit;
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $this->repo->updatePassword($userId, $passwordHash);

        header('Location: /admin/users');
        exit;
    }
}