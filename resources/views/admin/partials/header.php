<?php
/** @var array|null $adminUser */
/** @var string|null $pageTitle */
/** @var string|null $pageSubtitle */
/** @var string|null $activeNav */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$adminUser = $adminUser ?? ($_SESSION['admin_user'] ?? null);
$pageTitle = $pageTitle ?? 'Administrace';
$pageSubtitle = $pageSubtitle ?? '';
$activeNav = $activeNav ?? '';
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?> | VentureOut Admin</title>
    <link rel="stylesheet" href="/assets/css/admin-layout.css">
    <link rel="stylesheet" href="/assets/css/admin-header-alerts.css">
    <script src="/assets/js/admin-header-alerts.js" defer></script>
</head>
<body>
<div class="admin-page">
    <div class="admin-shell">
        <header class="admin-topbar">
            <div class="admin-topbar-row">
                <div>
                    <h1 class="admin-brand-title">VentureOut</h1>
                    <div class="admin-brand-subtitle">Admin rozhraní</div>
                </div>

                <div class="admin-userbox">
                    <?php if ($adminUser): ?>
                        Přihlášený uživatel:<br>
                        <strong><?= htmlspecialchars($adminUser['username'] ?? 'admin', ENT_QUOTES, 'UTF-8') ?></strong><br>
                        Role: <?= htmlspecialchars($adminUser['role'] ?? 'admin', ENT_QUOTES, 'UTF-8') ?>

                        <form class="admin-logout" method="post" action="/admin/logout">
                            <button class="btn btn-secondary" type="submit">Odhlásit se</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <div class="admin-nav-row">
                <nav class="admin-nav">
                    <a class="nav-link <?= $activeNav === 'games' ? 'active' : '' ?>" href="/admin/games">Hry</a>
                    <a class="nav-link <?= $activeNav === 'users' ? 'active' : '' ?>" href="/admin/users">Uživatelé</a>
                </nav>

                <?php require __DIR__ . '/header_alerts.php'; ?>
            </div>
        </header>

        <main class="admin-content">
            <div class="page-header">
                <h2 class="page-title"><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></h2>
                <?php if ($pageSubtitle !== ''): ?>
                    <p class="page-subtitle"><?= htmlspecialchars($pageSubtitle, ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>
            </div>
