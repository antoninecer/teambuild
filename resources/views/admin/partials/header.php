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
    <style>
        :root {
            --paper: rgba(246, 236, 214, 0.94);
            --paper-soft: rgba(250, 242, 226, 0.90);
            --ink: #3b2818;
            --ink-soft: #6b5240;
            --accent: #6c4322;
            --accent-dark: #4f2f17;
            --line: rgba(92, 61, 35, 0.18);
            --shadow: rgba(22, 12, 6, 0.18);
            --ok-bg: #e8f5e9;
            --ok-fg: #1b5e20;
            --warn-bg: #fff3e0;
            --warn-fg: #e65100;
            --muted-bg: #eeeeee;
        }

        * {
            box-sizing: border-box;
        }

        html, body {
            margin: 0;
            min-height: 100%;
        }

        body {
            font-family: Georgia, "Times New Roman", serif;
            color: var(--ink);
            background:
                linear-gradient(rgba(24, 14, 8, 0.22), rgba(24, 14, 8, 0.38)),
                url('/assets/bg/ventureout-map.jpg') center center / cover no-repeat fixed;
        }

        a {
            color: inherit;
        }

        .admin-page {
            min-height: 100vh;
            padding: 28px 18px 36px;
        }

        .admin-shell {
            max-width: 1180px;
            margin: 0 auto;
        }

        .admin-topbar {
            background: var(--paper);
            border: 1px solid var(--line);
            border-radius: 20px;
            box-shadow: 0 20px 40px var(--shadow);
            padding: 24px 24px 20px;
            margin-bottom: 18px;
        }

        .admin-topbar-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 18px;
            flex-wrap: wrap;
        }

        .admin-brand-title {
            margin: 0;
            font-size: clamp(34px, 5vw, 56px);
            line-height: 1;
            letter-spacing: 0.01em;
        }

        .admin-brand-subtitle {
            margin: 8px 0 0;
            color: var(--ink-soft);
            font-size: 18px;
        }

        .admin-userbox {
            text-align: right;
            color: var(--ink-soft);
            font-size: 15px;
            line-height: 1.45;
        }

        .admin-userbox strong {
            color: var(--ink);
        }

        .admin-logout {
            margin-top: 10px;
        }

        .admin-nav {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 18px;
        }

        .nav-link {
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 11px 15px;
            border-radius: 12px;
            border: 1px solid var(--line);
            background: rgba(255,255,255,0.46);
            color: var(--ink);
            font-size: 15px;
            font-weight: 700;
            transition: transform 0.12s ease, opacity 0.12s ease;
        }

        .nav-link:hover {
            opacity: 0.96;
        }

        .nav-link:active {
            transform: translateY(1px);
        }

        .nav-link.active {
            background: linear-gradient(180deg, #7a4d27, #5e381b);
            color: #fff4e6;
            border-color: #5e381b;
            box-shadow: 0 10px 18px rgba(85, 49, 22, 0.22);
        }

        .btn {
            appearance: none;
            border: 0;
            border-radius: 12px;
            padding: 11px 15px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.12s ease, opacity 0.12s ease;
        }

        .btn:hover {
            opacity: 0.96;
        }

        .btn:active {
            transform: translateY(1px);
        }

        .btn-primary {
            background: linear-gradient(180deg, #7a4d27, #5e381b);
            color: #fff4e6;
            box-shadow: 0 10px 18px rgba(85, 49, 22, 0.22);
        }

        .btn-secondary {
            background: rgba(255,255,255,0.56);
            color: var(--ink);
            border: 1px solid var(--line);
        }

        .admin-content {
            background: var(--paper-soft);
            border: 1px solid var(--line);
            border-radius: 20px;
            box-shadow: 0 18px 34px var(--shadow);
            padding: 24px;
        }

        .page-header {
            margin-bottom: 22px;
        }

        .page-title {
            margin: 0;
            font-size: clamp(28px, 4vw, 42px);
            line-height: 1.1;
        }

        .page-subtitle {
            margin: 8px 0 0;
            color: var(--ink-soft);
            font-size: 16px;
            line-height: 1.5;
        }

        .page-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 18px;
        }

        .card {
            background: rgba(255,255,255,0.44);
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 18px;
            box-shadow: 0 8px 18px rgba(22, 12, 6, 0.06);
        }

        .table-wrap {
            overflow-x: auto;
            border: 1px solid var(--line);
            border-radius: 16px;
            background: rgba(255,255,255,0.44);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            text-align: left;
            padding: 12px 14px;
            border-bottom: 1px solid rgba(92, 61, 35, 0.12);
            vertical-align: top;
        }

        th {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: var(--ink-soft);
            background: rgba(255,255,255,0.28);
        }

        tr:last-child td {
            border-bottom: none;
        }

        .status-badge,
        .mode-badge,
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 13px;
            white-space: nowrap;
            background: var(--muted-bg);
        }

        .mode-self,
        .badge-self {
            background: var(--ok-bg);
            color: var(--ok-fg);
        }

        .mode-moderated,
        .badge-moderated {
            background: var(--warn-bg);
            color: var(--warn-fg);
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

        .full-width {
            grid-column: span 2;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            font-weight: 700;
            margin-bottom: 6px;
            font-size: 14px;
        }

        input[type="text"],
        input[type="email"],
        input[type="number"],
        input[type="datetime-local"],
        input[type="password"],
        textarea,
        select {
            width: 100%;
            padding: 11px 12px;
            border: 1px solid rgba(92, 61, 35, 0.22);
            border-radius: 12px;
            background: rgba(255,255,255,0.62);
            font-size: 15px;
            color: var(--ink);
        }

        textarea {
            min-height: 110px;
            resize: vertical;
        }

        input:focus,
        textarea:focus,
        select:focus {
            outline: none;
            border-color: rgba(108, 67, 34, 0.60);
            box-shadow: 0 0 0 3px rgba(108, 67, 34, 0.10);
        }

        .help {
            font-size: 12px;
            color: var(--ink-soft);
            margin-top: 6px;
            line-height: 1.45;
        }

        .errors {
            background: rgba(180, 36, 36, 0.10);
            border: 1px solid rgba(180, 36, 36, 0.24);
            color: #7a1b1b;
            padding: 16px;
            border-radius: 14px;
            margin-bottom: 20px;
        }

        .errors ul {
            margin: 8px 0 0;
            padding-left: 20px;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .checkbox-group input {
            width: auto;
            margin: 0;
        }

        @media (max-width: 900px) {
            .admin-userbox {
                text-align: left;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .full-width {
                grid-column: span 1;
            }
        }
    </style>
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

            <nav class="admin-nav">
                <a class="nav-link <?= $activeNav === 'games' ? 'active' : '' ?>" href="/admin/games">Hry</a>
                <a class="nav-link <?= $activeNav === 'users' ? 'active' : '' ?>" href="/admin/users">Uživatelé</a>
            </nav>
        </header>

        <main class="admin-content">
            <div class="page-header">
                <h2 class="page-title"><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></h2>
                <?php if ($pageSubtitle !== ''): ?>
                    <p class="page-subtitle"><?= htmlspecialchars($pageSubtitle, ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>
            </div>