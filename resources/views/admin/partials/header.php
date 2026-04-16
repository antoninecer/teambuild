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
            --danger-bg: #7f1d1d;
            --danger-soft: rgba(127, 29, 29, 0.12);
            --danger-border: rgba(127, 29, 29, 0.28);
            --danger-text: #fff1f1;
            --info-bg: rgba(255,255,255,0.56);
        }

        * { box-sizing: border-box; }
        html, body { margin: 0; min-height: 100%; }

        body {
            font-family: Georgia, "Times New Roman", serif;
            color: var(--ink);
            background:
                linear-gradient(rgba(24, 14, 8, 0.22), rgba(24, 14, 8, 0.38)),
                url('/assets/bg/ventureout-map.jpg') center center / cover no-repeat fixed;
        }

        a { color: inherit; }

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

        .admin-nav-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
            margin-top: 18px;
        }

        .admin-nav {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
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

        .nav-link:hover { opacity: 0.96; }
        .nav-link:active { transform: translateY(1px); }

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

        .btn:hover { opacity: 0.96; }
        .btn:active { transform: translateY(1px); }

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

        .admin-alerts {
            position: relative;
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .alert-pill {
            position: relative;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border-radius: 999px;
            padding: 10px 14px;
            border: 1px solid var(--line);
            background: var(--info-bg);
            color: var(--ink);
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: transform 0.12s ease, opacity 0.12s ease, box-shadow 0.12s ease;
        }

        .alert-pill:hover { opacity: 0.96; }
        .alert-pill:active { transform: translateY(1px); }

        .alert-pill .count {
            min-width: 24px;
            height: 24px;
            padding: 0 6px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(59, 40, 24, 0.12);
            color: var(--ink);
            font-size: 13px;
            line-height: 1;
        }

        .alert-pill.sos {
            background: linear-gradient(180deg, #8f2424, #6f1818);
            border-color: rgba(100, 16, 16, 0.55);
            color: var(--danger-text);
            box-shadow: 0 10px 22px rgba(108, 18, 18, 0.24);
        }

        .alert-pill.sos .count {
            background: rgba(255,255,255,0.20);
            color: #fff;
        }

        .alert-pill.events.has-unread {
            box-shadow: 0 0 0 3px rgba(108, 67, 34, 0.10);
        }

        .header-sound-toggle {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 12px;
            border-radius: 999px;
            background: rgba(255,255,255,0.46);
            border: 1px solid var(--line);
            color: var(--ink-soft);
            font-size: 13px;
            font-weight: 700;
            user-select: none;
        }

        .header-sound-toggle input {
            margin: 0;
            width: auto;
        }

        .alerts-panel {
            position: absolute;
            top: calc(100% + 10px);
            right: 0;
            width: min(460px, calc(100vw - 40px));
            background: rgba(250, 242, 226, 0.98);
            border: 1px solid var(--line);
            border-radius: 16px;
            box-shadow: 0 22px 36px rgba(22, 12, 6, 0.18);
            padding: 14px;
            display: none;
            z-index: 40;
        }

        .alerts-panel.is-open { display: block; }

        .alerts-panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
        }

        .alerts-panel-title {
            margin: 0;
            font-size: 18px;
        }

        .alerts-panel-subtitle {
            margin: 4px 0 0;
            color: var(--ink-soft);
            font-size: 13px;
        }

        .alerts-feed {
            display: grid;
            gap: 10px;
            max-height: 380px;
            overflow: auto;
            padding-right: 4px;
        }

        .alerts-empty {
            padding: 18px;
            border-radius: 12px;
            background: rgba(255,255,255,0.44);
            color: var(--ink-soft);
            font-size: 14px;
            text-align: center;
        }

        .alert-item {
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 12px 14px;
            background: rgba(255,255,255,0.50);
        }

        .alert-item.critical {
            background: var(--danger-soft);
            border-color: var(--danger-border);
        }

        .alert-item.reward {
            background: rgba(125, 94, 28, 0.08);
        }

        .alert-item-top {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: baseline;
            margin-bottom: 6px;
        }

        .alert-item-label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--ink-soft);
            font-weight: 700;
        }

        .alert-item-time {
            font-size: 12px;
            color: var(--ink-soft);
            white-space: nowrap;
        }

        .alert-item-message {
            font-size: 15px;
            line-height: 1.45;
        }

        .alert-banner {
            display: none;
            margin-top: 16px;
            padding: 14px 16px;
            border-radius: 14px;
            border: 1px solid var(--danger-border);
            background: linear-gradient(180deg, rgba(143, 36, 36, 0.15), rgba(127, 29, 29, 0.11));
            color: #6b1616;
        }

        .alert-banner.is-visible { display: block; }

        .alert-banner strong {
            display: block;
            margin-bottom: 4px;
            color: #5a0f0f;
        }

        .admin-content {
            background: var(--paper-soft);
            border: 1px solid var(--line);
            border-radius: 20px;
            box-shadow: 0 18px 34px var(--shadow);
            padding: 24px;
        }

        .page-header { margin-bottom: 22px; }
        .page-title { margin: 0; font-size: clamp(28px, 4vw, 42px); line-height: 1.1; }
        .page-subtitle { margin: 8px 0 0; color: var(--ink-soft); font-size: 16px; line-height: 1.5; }
        .page-actions { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 18px; }

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

        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 12px 14px; border-bottom: 1px solid rgba(92, 61, 35, 0.12); vertical-align: top; }
        th { font-size: 12px; text-transform: uppercase; letter-spacing: 0.04em; color: var(--ink-soft); background: rgba(255,255,255,0.28); }
        tr:last-child td { border-bottom: none; }

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

        .mode-self, .badge-self { background: var(--ok-bg); color: var(--ok-fg); }
        .mode-moderated, .badge-moderated { background: var(--warn-bg); color: var(--warn-fg); }

        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
        .full-width { grid-column: span 2; }
        .form-group { margin-bottom: 18px; }

        label { display: block; font-weight: 700; margin-bottom: 6px; font-size: 14px; }

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

        textarea { min-height: 110px; resize: vertical; }

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

        .errors ul { margin: 8px 0 0; padding-left: 20px; }

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
            .admin-userbox { text-align: left; }
            .form-grid { grid-template-columns: 1fr; }
            .full-width { grid-column: span 1; }
            .admin-nav-row { align-items: stretch; }
            .admin-alerts { width: 100%; }
            .alerts-panel { right: auto; left: 0; width: 100%; }
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

            <div class="admin-nav-row">
                <nav class="admin-nav">
                    <a class="nav-link <?= $activeNav === 'games' ? 'active' : '' ?>" href="/admin/games">Hry</a>
                    <a class="nav-link <?= $activeNav === 'users' ? 'active' : '' ?>" href="/admin/users">Uživatelé</a>
                </nav>

                <div class="admin-alerts" id="admin-alerts-root">
                    <button type="button" class="alert-pill sos" id="admin-sos-pill" title="Otevřená SOS hlášení">
                        <span>SOS</span>
                        <span class="count" id="admin-sos-count">0</span>
                    </button>

                    <button type="button" class="alert-pill events" id="admin-events-pill" title="Poslední události hry">
                        <span>Události</span>
                        <span class="count" id="admin-events-count">0</span>
                    </button>

                    <label class="header-sound-toggle" title="Zvuk kritických upozornění a událostí">
                        <input type="checkbox" id="admin-sound-enabled" checked>
                        <span>Zvuk upozornění</span>
                    </label>

                    <div class="alerts-panel" id="admin-alerts-panel" aria-hidden="true">
                        <div class="alerts-panel-header">
                            <div>
                                <h3 class="alerts-panel-title">Živý přehled</h3>
                                <div class="alerts-panel-subtitle">SOS, poklady, POI a další poslední události</div>
                            </div>
                            <button type="button" class="btn btn-secondary" id="admin-alerts-close">Zavřít</button>
                        </div>

                        <div class="alerts-feed" id="admin-alerts-feed">
                            <div class="alerts-empty">Načítám události…</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="alert-banner" id="admin-critical-banner">
                <strong>Nové SOS hlášení</strong>
                <span id="admin-critical-banner-text">Organizátor zatím nemá žádné nové kritické upozornění.</span>
            </div>
        </header>

        <main class="admin-content">
            <div class="page-header">
                <h2 class="page-title"><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></h2>
                <?php if ($pageSubtitle !== ''): ?>
                    <p class="page-subtitle"><?= htmlspecialchars($pageSubtitle, ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>
            </div>

            <audio id="admin-sound-info" preload="auto">
                <source src="/assets/sounds/alert_info.mp3" type="audio/mpeg">
            </audio>
            <audio id="admin-sound-treasure" preload="auto">
                <source src="/assets/sounds/alert_treasure.mp3" type="audio/mpeg">
            </audio>
            <audio id="admin-sound-sos" preload="auto">
                <source src="/assets/sounds/alert_sos.mp3" type="audio/mpeg">
            </audio>

            <script>
                (function () {
                    const endpoint = '/admin/api/header-status';
                    const pollIntervalMs = 10000;
                    const titleBase = document.title;
                    const alertsRoot = document.getElementById('admin-alerts-root');
                    const panel = document.getElementById('admin-alerts-panel');
                    const feed = document.getElementById('admin-alerts-feed');
                    const closeBtn = document.getElementById('admin-alerts-close');
                    const sosPill = document.getElementById('admin-sos-pill');
                    const eventsPill = document.getElementById('admin-events-pill');
                    const sosCount = document.getElementById('admin-sos-count');
                    const eventsCount = document.getElementById('admin-events-count');
                    const soundEnabledCheckbox = document.getElementById('admin-sound-enabled');
                    const criticalBanner = document.getElementById('admin-critical-banner');
                    const criticalBannerText = document.getElementById('admin-critical-banner-text');
                    const soundInfo = document.getElementById('admin-sound-info');
                    const soundTreasure = document.getElementById('admin-sound-treasure');
                    const soundSos = document.getElementById('admin-sound-sos');

                    let lastEventSignature = null;
                    let lastCriticalSignature = null;
                    let titleBlinkTimer = null;
                    let titleBlinkState = false;
                    let pollingStarted = false;

                    function safePlay(audioEl) {
                        if (!audioEl || !soundEnabledCheckbox || !soundEnabledCheckbox.checked) {
                            return;
                        }

                        try {
                            audioEl.currentTime = 0;
                            const promise = audioEl.play();
                            if (promise && typeof promise.catch === 'function') {
                                promise.catch(() => {});
                            }
                        } catch (error) {
                            // ignored on purpose
                        }
                    }

                    function startTitleBlink(label) {
                        if (titleBlinkTimer) {
                            return;
                        }

                        titleBlinkTimer = window.setInterval(() => {
                            titleBlinkState = !titleBlinkState;
                            document.title = titleBlinkState ? label + ' | VentureOut Admin' : titleBase;
                        }, 1000);
                    }

                    function stopTitleBlink() {
                        if (titleBlinkTimer) {
                            window.clearInterval(titleBlinkTimer);
                            titleBlinkTimer = null;
                        }
                        titleBlinkState = false;
                        document.title = titleBase;
                    }

                    function formatTime(value) {
                        if (!value) {
                            return 'teď';
                        }

                        const parsed = new Date(value);
                        if (Number.isNaN(parsed.getTime())) {
                            return value;
                        }

                        return parsed.toLocaleString('cs-CZ', {
                            day: '2-digit',
                            month: '2-digit',
                            hour: '2-digit',
                            minute: '2-digit'
                        });
                    }

                    function eventClass(event) {
                        if (event.severity === 'critical' || event.type === 'sos' || event.type === 'sos_open') {
                            return 'critical';
                        }
                        if (event.type === 'treasure' || event.type === 'treasure_claimed') {
                            return 'reward';
                        }
                        return 'info';
                    }

                    function eventLabel(event) {
                        if (event.type === 'sos' || event.type === 'sos_open') {
                            return 'SOS';
                        }
                        if (event.type === 'treasure' || event.type === 'treasure_claimed') {
                            return 'Poklad';
                        }
                        if (event.type === 'poi' || event.type === 'poi_completed') {
                            return 'POI';
                        }
                        return 'Událost';
                    }

                    function renderEvents(events) {
                        if (!Array.isArray(events) || events.length === 0) {
                            feed.innerHTML = '<div class="alerts-empty">Zatím nejsou k dispozici žádné nové události.</div>';
                            return;
                        }

                        feed.innerHTML = events.map((event) => {
                            const klass = eventClass(event);
                            const label = eventLabel(event);
                            const message = event.message || 'Nová událost';
                            const time = formatTime(event.created_at || event.time || '');

                            return `
                                <div class="alert-item ${klass}">
                                    <div class="alert-item-top">
                                        <div class="alert-item-label">${label}</div>
                                        <div class="alert-item-time">${time}</div>
                                    </div>
                                    <div class="alert-item-message">${message}</div>
                                </div>
                            `;
                        }).join('');
                    }

                    function applyState(data) {
                        const counts = data && data.counts ? data.counts : {};
                        const events = Array.isArray(data && data.events) ? data.events : [];
                        const sosOpen = Number(counts.sos_open || 0);
                        const totalEvents = Number(counts.new_events || events.length || 0);

                        sosCount.textContent = String(sosOpen);
                        eventsCount.textContent = String(totalEvents);

                        sosPill.style.display = sosOpen > 0 ? 'inline-flex' : 'inline-flex';
                        eventsPill.classList.toggle('has-unread', totalEvents > 0);

                        renderEvents(events);

                        const latestEvent = events.length > 0 ? events[0] : null;
                        const latestSignature = latestEvent ? String(latestEvent.id || latestEvent.signature || JSON.stringify(latestEvent)) : null;
                        const latestCritical = events.find((event) => event.severity === 'critical' || event.type === 'sos' || event.type === 'sos_open') || null;
                        const latestCriticalSignature = latestCritical ? String(latestCritical.id || latestCritical.signature || JSON.stringify(latestCritical)) : null;

                        if (latestCritical) {
                            criticalBanner.classList.add('is-visible');
                            criticalBannerText.textContent = latestCritical.message || 'Ve hře je nové kritické upozornění.';
                        } else {
                            criticalBanner.classList.remove('is-visible');
                            criticalBannerText.textContent = 'Organizátor zatím nemá žádné nové kritické upozornění.';
                        }

                        if (pollingStarted) {
                            if (latestCriticalSignature && latestCriticalSignature !== lastCriticalSignature) {
                                safePlay(soundSos);
                                startTitleBlink('SOS!');
                            } else if (latestSignature && latestSignature !== lastEventSignature && latestEvent) {
                                if (latestEvent.type === 'treasure' || latestEvent.type === 'treasure_claimed') {
                                    safePlay(soundTreasure);
                                } else if (latestEvent.type === 'poi' || latestEvent.type === 'poi_completed' || latestEvent.type === 'message') {
                                    safePlay(soundInfo);
                                }
                            }
                        }

                        lastEventSignature = latestSignature;
                        lastCriticalSignature = latestCriticalSignature;
                        pollingStarted = true;
                    }

                    async function fetchStatus() {
                        try {
                            const response = await fetch(endpoint, {
                                method: 'GET',
                                headers: {
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                credentials: 'same-origin',
                                cache: 'no-store'
                            });

                            if (!response.ok) {
                                throw new Error('HTTP ' + response.status);
                            }

                            const data = await response.json();
                            applyState(data);
                        } catch (error) {
                            if (!pollingStarted) {
                                feed.innerHTML = '<div class="alerts-empty">Header zatím nemá napojený živý endpoint <code>/admin/api/header-status</code>.</div>';
                            }
                        }
                    }

                    function togglePanel(force) {
                        const shouldOpen = typeof force === 'boolean' ? force : !panel.classList.contains('is-open');
                        panel.classList.toggle('is-open', shouldOpen);
                        panel.setAttribute('aria-hidden', shouldOpen ? 'false' : 'true');
                    }

                    sosPill.addEventListener('click', function () {
                        togglePanel(true);
                    });

                    eventsPill.addEventListener('click', function () {
                        togglePanel(true);
                    });

                    closeBtn.addEventListener('click', function () {
                        togglePanel(false);
                    });

                    document.addEventListener('click', function (event) {
                        if (!alertsRoot.contains(event.target)) {
                            togglePanel(false);
                        }
                    });

                    document.addEventListener('visibilitychange', function () {
                        if (document.visibilityState === 'visible') {
                            stopTitleBlink();
                        }
                    });

                    window.addEventListener('focus', function () {
                        stopTitleBlink();
                    });

                    fetchStatus();
                    window.setInterval(fetchStatus, pollIntervalMs);
                })();
            </script>
