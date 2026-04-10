<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Repositories\UserRepository;

final class AuthController
{
    public function showLogin(): void
    {
        require __DIR__ . '/../../../resources/views/admin/login.php';
    }

    public function login(): void
    {
        session_start();

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === '' || $password === '') {
            $_SESSION['admin_error'] = 'Vyplň uživatelské jméno a heslo.';
            header('Location: /admin/login');
            exit;
        }

        $repo = new UserRepository();
        $user = $repo->findActiveByUsername($username);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $_SESSION['admin_error'] = 'Neplatné přihlašovací údaje.';
            header('Location: /admin/login');
            exit;
        }

        if (!in_array($user['role'], ['admin', 'editor'], true)) {
            $_SESSION['admin_error'] = 'Nemáš oprávnění pro vstup do administrace.';
            header('Location: /admin/login');
            exit;
        }

        $_SESSION['admin_user'] = [
            'id' => (int) $user['id'],
            'username' => $user['username'],
            'role' => $user['role'],
        ];

        header('Location: /admin/games');
        exit;
    }

    public function logout(): void
    {
        session_start();
        unset($_SESSION['admin_user']);
        session_destroy();

        header('Location: /admin/login');
        exit;
    }
}
