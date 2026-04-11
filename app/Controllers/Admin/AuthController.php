<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Repositories\UserRepository;

final class AuthController
{
    public function showLogin(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        require __DIR__ . '/../../../resources/views/admin/login.php';
    }

    public function login(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

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

        if (!in_array($user['role'], ['admin', 'editor', 'viewer'], true)) {
            $_SESSION['admin_error'] = 'Nemáš oprávnění pro vstup do administrace.';
            header('Location: /admin/login');
            exit;
        }

        $repo->touchLastLogin((int) $user['id']);

        $_SESSION['admin_user'] = [
            'id' => (int) $user['id'],
            'username' => $user['username'],
            'email' => $user['email'] ?? null,
            'role' => $user['role'],
            'global_role' => $user['global_role'] ?? 'none',
        ];

        header('Location: /admin');
        exit;
    }

    public function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                (bool) $params['secure'],
                (bool) $params['httponly']
            );
        }

        session_destroy();

        header('Location: /admin/login');
        exit;
    }
}