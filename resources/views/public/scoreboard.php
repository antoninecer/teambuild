<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Výsledovka | <?= htmlspecialchars($game['name'], ENT_QUOTES, 'UTF-8') ?></title>
    <style>
        :root {
            --paper: rgba(246, 236, 214, 0.90);
            --paper-strong: rgba(241, 229, 205, 0.96);
            --ink: #3b2818;
            --ink-soft: #6b5240;
            --accent: #6c4322;
            --accent-dark: #4f2f17;
            --line: rgba(92, 61, 35, 0.18);
            --shadow: rgba(22, 12, 6, 0.34);
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: Georgia, "Times New Roman", serif;
            color: var(--ink);
            background:
                linear-gradient(rgba(24, 14, 8, 0.28), rgba(24, 14, 8, 0.46)),
                url('/assets/bg/ventureout-map.jpg') center center / cover no-repeat fixed;
        }
        .page { padding: 28px 18px 40px; }
        .shell { max-width: 1220px; margin: 0 auto; }
        .hero {
            background: var(--paper);
            border: 1px solid rgba(92, 61, 35, 0.24);
            border-radius: 22px;
            box-shadow: 0 20px 40px var(--shadow);
            padding: 26px 28px;
            backdrop-filter: blur(2px);
        }
        .title { margin: 0 0 6px; font-size: clamp(34px, 5vw, 68px); line-height: 0.95; }
        .subtitle { margin: 0; color: var(--ink-soft); font-size: clamp(16px, 2vw, 24px); }
        .meta { display: flex; gap: 12px 18px; flex-wrap: wrap; margin-top: 18px; color: var(--ink-soft); font-size: 15px; }
        .stats-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px; margin-top: 22px; }
        .stat-card, .panel, .podium-card {
            background: rgba(255,255,255,0.34);
            border: 1px solid var(--line);
            border-radius: 18px;
            box-shadow: 0 10px 24px rgba(32, 18, 8, 0.08);
        }
        .stat-card { padding: 18px 20px; }
        .stat-label { color: var(--ink-soft); font-size: 14px; margin-bottom: 6px; }
        .stat-value { font-size: 38px; font-weight: 700; line-height: 1; }
        .layout { display: grid; grid-template-columns: 360px minmax(0, 1fr); gap: 20px; margin-top: 22px; }
        .panel { padding: 18px 20px; }
        .panel h2 { margin: 0 0 14px; font-size: 30px; }
        .podium { display: grid; gap: 14px; }
        .podium-card { padding: 16px 18px; position: relative; overflow: hidden; }
        .podium-rank { font-size: 14px; color: var(--ink-soft); margin-bottom: 8px; }
        .podium-name { font-size: 28px; font-weight: 700; margin-bottom: 10px; }
        .podium-points { font-size: 22px; font-weight: 700; margin-bottom: 10px; }
        .podium-extra { color: var(--ink-soft); font-size: 14px; line-height: 1.5; }
        .rank-1::before, .rank-2::before, .rank-3::before { content: ""; position: absolute; inset: 0; opacity: 0.18; pointer-events: none; }
        .rank-1::before { background: linear-gradient(135deg, #c79a2f, transparent 52%); }
        .rank-2::before { background: linear-gradient(135deg, #9aa4b0, transparent 52%); }
        .rank-3::before { background: linear-gradient(135deg, #9f6a42, transparent 52%); }
        .table-wrap { overflow-x: auto; border-radius: 16px; border: 1px solid var(--line); background: rgba(255,255,255,0.30); }
        table { width: 100%; border-collapse: collapse; min-width: 760px; }
        thead th { text-align: left; font-size: 13px; letter-spacing: 0.04em; text-transform: uppercase; color: var(--ink-soft); padding: 14px; border-bottom: 1px solid var(--line); }
        tbody td { padding: 14px; border-bottom: 1px solid rgba(92, 61, 35, 0.12); vertical-align: top; }
        tbody tr:last-child td { border-bottom: 0; }
        tbody tr:nth-child(1) td { background: rgba(199, 154, 47, 0.10); }
        tbody tr:nth-child(2) td { background: rgba(154, 164, 176, 0.10); }
        tbody tr:nth-child(3) td { background: rgba(159, 106, 66, 0.10); }
        .checkpoint { font-weight: 700; margin-bottom: 4px; }
        .checkpoint-time { font-size: 13px; color: var(--ink-soft); }
        .empty { color: var(--ink-soft); padding: 18px 4px 4px; }
        @media (max-width: 980px) { .stats-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } .layout { grid-template-columns: 1fr; } }
        @media (max-width: 640px) { .stats-grid { grid-template-columns: 1fr; } .hero { padding: 20px; } }
    </style>
</head>
<body>
<div class="page">
    <div class="shell">
        <section class="hero">
            <h1 class="title"><?= htmlspecialchars($game['name'], ENT_QUOTES, 'UTF-8') ?></h1>
            <p class="subtitle">Veřejná výsledovka hry</p>
            <div class="meta">
                <div>Aktualizace každou minutu</div>
                <div id="updatedAt">Naposledy aktualizováno: <?= date('d.m.Y H:i:s') ?></div>
            </div>
            <div class="stats-grid" id="statsGrid">
                <div class="stat-card"><div class="stat-label">Hráčů</div><div class="stat-value"><?= (int) $payload['stats']['players_count'] ?></div></div>
                <div class="stat-card"><div class="stat-label">POI</div><div class="stat-value"><?= (int) $payload['stats']['pois_count'] ?></div></div>
                <div class="stat-card"><div class="stat-label">Pokladů</div><div class="stat-value"><?= (int) $payload['stats']['treasures_count'] ?></div></div>
                <div class="stat-card"><div class="stat-label">Sebrání</div><div class="stat-value"><?= (int) $payload['stats']['treasure_claims_count'] ?></div></div>
            </div>
        </section>
        <section class="layout">
            <div class="panel">
                <h2>Čelo výpravy</h2>
                <div class="podium" id="podium">
                    <?php if ($payload['rows'] === []): ?>
                        <div class="empty">Zatím nejsou žádná data.</div>
                    <?php else: ?>
                        <?php foreach (array_slice($payload['rows'], 0, 3) as $row): ?>
                            <div class="podium-card rank-<?= (int) $row['rank'] ?>">
                                <div class="podium-rank">#<?= (int) $row['rank'] ?></div>
                                <div class="podium-name"><?= htmlspecialchars($row['nickname'], ENT_QUOTES, 'UTF-8') ?></div>
                                <div class="podium-points"><?= (int) $row['points'] ?> bodů</div>
                                <div class="podium-extra">POI: <?= (int) $row['pois_visited'] ?><br>Poklady: <?= (int) $row['treasures_found'] ?><br>Poslední úsek: <?= htmlspecialchars($row['last_checkpoint'] ?: '—', ENT_QUOTES, 'UTF-8') ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="panel">
                <h2>Pořadí hráčů</h2>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>#</th><th>Hráč</th><th>Body</th><th>POI</th><th>Poklady</th><th>Poslední bodovaný úsek</th></tr></thead>
                        <tbody id="scoreboardBody">
                        <?php if ($payload['rows'] === []): ?>
                            <tr><td colspan="6">Zatím nejsou žádná data.</td></tr>
                        <?php else: ?>
                            <?php foreach ($payload['rows'] as $row): ?>
                                <tr>
                                    <td>#<?= (int) $row['rank'] ?></td>
                                    <td><?= htmlspecialchars($row['nickname'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= (int) $row['points'] ?></td>
                                    <td><?= (int) $row['pois_visited'] ?></td>
                                    <td><?= (int) $row['treasures_found'] ?></td>
                                    <td><div class="checkpoint"><?= htmlspecialchars($row['last_checkpoint'] ?: '—', ENT_QUOTES, 'UTF-8') ?></div><div class="checkpoint-time"><?= htmlspecialchars($row['last_progress_at'] ?: 'Bez postupu', ENT_QUOTES, 'UTF-8') ?></div></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
</div>
<script>
(function () {
    const endpoint = <?= json_encode('/scoreboard/' . $game['slug'] . '/data', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const intervalMs = 60000;
    function escapeHtml(value) {
        return String(value ?? '').replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;').replaceAll('"', '&quot;').replaceAll("'", '&#039;');
    }
    function formatDate(value) {
        if (!value) return 'Bez postupu';
        const parsed = new Date(value.replace(' ', 'T'));
        if (Number.isNaN(parsed.getTime())) return value;
        return parsed.toLocaleString('cs-CZ');
    }
    function renderStats(stats) {
        document.getElementById('statsGrid').innerHTML = `
            <div class="stat-card"><div class="stat-label">Hráčů</div><div class="stat-value">${Number(stats.players_count || 0)}</div></div>
            <div class="stat-card"><div class="stat-label">POI</div><div class="stat-value">${Number(stats.pois_count || 0)}</div></div>
            <div class="stat-card"><div class="stat-label">Pokladů</div><div class="stat-value">${Number(stats.treasures_count || 0)}</div></div>
            <div class="stat-card"><div class="stat-label">Sebrání</div><div class="stat-value">${Number(stats.treasure_claims_count || 0)}</div></div>`;
    }
    function renderPodium(rows) {
        const podium = document.getElementById('podium');
        if (!Array.isArray(rows) || rows.length === 0) { podium.innerHTML = '<div class="empty">Zatím nejsou žádná data.</div>'; return; }
        podium.innerHTML = rows.slice(0, 3).map((row) => `
            <div class="podium-card rank-${Number(row.rank || 0)}">
                <div class="podium-rank">#${Number(row.rank || 0)}</div>
                <div class="podium-name">${escapeHtml(row.nickname || '')}</div>
                <div class="podium-points">${Number(row.points || 0)} bodů</div>
                <div class="podium-extra">POI: ${Number(row.pois_visited || 0)}<br>Poklady: ${Number(row.treasures_found || 0)}<br>Poslední úsek: ${escapeHtml(row.last_checkpoint || '—')}</div>
            </div>`).join('');
    }
    function renderTable(rows) {
        const body = document.getElementById('scoreboardBody');
        if (!Array.isArray(rows) || rows.length === 0) { body.innerHTML = '<tr><td colspan="6">Zatím nejsou žádná data.</td></tr>'; return; }
        body.innerHTML = rows.map((row) => `
            <tr>
                <td>#${Number(row.rank || 0)}</td>
                <td>${escapeHtml(row.nickname || '')}</td>
                <td>${Number(row.points || 0)}</td>
                <td>${Number(row.pois_visited || 0)}</td>
                <td>${Number(row.treasures_found || 0)}</td>
                <td><div class="checkpoint">${escapeHtml(row.last_checkpoint || '—')}</div><div class="checkpoint-time">${escapeHtml(formatDate(row.last_progress_at))}</div></td>
            </tr>`).join('');
    }
    async function refreshData() {
        try {
            const response = await fetch(endpoint, { method: 'GET', headers: { 'Accept': 'application/json' }, cache: 'no-store', credentials: 'same-origin' });
            if (!response.ok) throw new Error('HTTP ' + response.status);
            const payload = await response.json();
            if (!payload.success) throw new Error(payload.error || 'unknown_error');
            renderStats(payload.stats || {});
            renderPodium(payload.rows || []);
            renderTable(payload.rows || []);
            document.getElementById('updatedAt').textContent = 'Naposledy aktualizováno: ' + formatDate(payload.updated_at);
        } catch (error) {
            console.error('Nepodařilo se obnovit výsledovku:', error);
        }
    }
    window.setInterval(refreshData, intervalMs);
})();
</script>
</body>
</html>
