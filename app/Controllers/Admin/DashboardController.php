<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

final class DashboardController
{
    private function requireAdmin(): array
    {
        session_start();

        if (!isset($_SESSION['admin_user'])) {
            header('Location: /admin/login');
            exit;
        }

        return $_SESSION['admin_user'];
    }

    public function index(): void
    {
        $adminUser = $this->requireAdmin();

        require __DIR__ . '/../../../resources/views/admin/index.php';
    }
}
