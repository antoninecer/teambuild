<?php
$pageTitle = 'Detail hry: ' . $game['name'];
$pageSubtitle = 'Správa parametrů, sledování postupu a výsledků';
$activeNav = 'games';

require __DIR__ . '/../partials/header.php';

$pdo = \App\Support\Database::connection();

$leaderboardStmt = $pdo->prepare(
    'SELECT
        p.id AS player_id,
        p.nickname,
        p.last_seen_at,
        p.last_lat,
        p.last_lon,
        COALESCE(SUM(COALESCE(t.points, 0)), 0) AS points,
        COUNT(t.id) AS treasures_found
     FROM players p
     LEFT JOIN treasure_claims tc
        ON tc.player_id = p.id
     LEFT JOIN treasures t
        ON t.id = tc.treasure_id
       AND t.game_id = :game_id
     WHERE p.game_id = :game_id
     GROUP BY p.id, p.nickname, p.last_seen_at, p.last_lat, p.last_lon
     ORDER BY points DESC, treasures_found DESC, p.nickname ASC'
);
$leaderboardStmt->execute(['game_id' => (int) $game['id']]);
$leaderboardRows = $leaderboardStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

$rank = 1;
foreach ($leaderboardRows as &$row) {
    $row['rank'] = $rank++;
}
unset($row);

$statsStmt = $pdo->prepare(
    'SELECT
        (SELECT COUNT(*) FROM players WHERE game_id = :game_id) AS players_count,
        (SELECT COUNT(*) FROM pois WHERE game_id = :game_id AND is_enabled = 1) AS pois_count,
        (SELECT COUNT(*) FROM treasures WHERE game_id = :game_id AND is_enabled = 1) AS treasures_count,
        (SELECT COUNT(*) FROM treasure_claims tc INNER JOIN treasures t ON t.id = tc.treasure_id WHERE t.game_id = :game_id) AS treasure_claims_count'
);
$statsStmt->execute(['game_id' => (int) $game['id']]);
$gameStats = $statsStmt->fetch(\PDO::FETCH_ASSOC) ?: [
    'players_count' => 0,
    'pois_count' => 0,
    'treasures_count' => 0,
    'treasure_claims_count' => 0,
];
?>

<style>
    .game-details-grid {
        display: grid;
        grid-template-columns: 220px 1fr;
        gap: 12px 24px;
        margin-bottom: 24px;
    }
    .game-details-grid dt {
        font-weight: 700;
        color: var(--ink-soft);
    }
    .game-details-grid dd {
        margin: 0;
    }
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 18px;
        margin-bottom: 28px;
    }
    .stat-card {
        background: rgba(255,255,255,0.44);
        border: 1px solid var(--line);
        border-radius: 16px;
        padding: 18px;
        box-shadow: 0 8px 18px rgba(22, 12, 6, 0.06);
    }
    .stat-label {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--ink-soft);
        margin-bottom: 8px;
    }
    .stat-value {
        font-size: 28px;
        font-weight: 700;
        color: var(--ink);
    }
    .url-box {
        background: rgba(255,255,255,0.6);
        padding: 12px;
        border: 1px solid var(--line);
        border-radius: 12px;
        font-family: monospace;
        word-break: break-all;
        margin: 12px 0;
    }
</style>

<div class="page-actions" style="margin-bottom: 24px;">
    <a class="btn btn-secondary" href="/admin/games">← Zpět na seznam</a>
    <a class="btn btn-primary" href="/admin/games/<?= (int) $game['id'] ?>/edit">Upravit</a>
    <a class="btn btn-primary" href="/admin/games/<?= (int) $game['id'] ?>/pois">Správa bodů (POI)</a>
    <a class="btn btn-primary" href="/admin/games/<?= (int) $game['id'] ?>/treasures">Poklady</a>
    <a class="btn btn-primary" href="/admin/games/<?= (int) $game['id'] ?>/invites">Pozvánky & QR</a>
</div>

<div class="card" style="margin-top: 24px;">
    <dl class="game-details-grid">
        <dt>ID</dt><dd><?= (int) $game['id'] ?></dd>
        <dt>Název</dt><dd><?= htmlspecialchars($game['name'], ENT_QUOTES, 'UTF-8') ?></dd>
        <dt>Slug</dt><dd><?= htmlspecialchars($game['slug'], ENT_QUOTES, 'UTF-8') ?></dd>
        <dt>Stav</dt><dd><span class="badge"><?= htmlspecialchars($game['status'], ENT_QUOTES, 'UTF-8') ?></span></dd>
        <dt>Režim hry</dt>
        <dd>
            <?php if (($game['operation_mode'] ?? 'self_service') === 'moderated'): ?>
                <span class="badge badge-moderated">Hra s organizátorem</span>
            <?php else: ?>
                <span class="badge badge-self">Samostatná hra</span>
            <?php endif; ?>
        </dd>
        <dt>Začátek</dt><dd><?= htmlspecialchars($game['starts_at'], ENT_QUOTES, 'UTF-8') ?></dd>
        <dt>Konec</dt><dd><?= htmlspecialchars($game['ends_at'], ENT_QUOTES, 'UTF-8') ?></dd>
        <dt>Registrace</dt><dd><?= (int) $game['registration_enabled'] === 1 ? 'ano' : 'ne' ?></dd>
        <dt>Cookie (dny)</dt><dd><?= (int) $game['session_cookie_days'] ?></dd>
        <dt>Střed mapy</dt>
        <dd>
            <?= $game['map_center_lat'] !== null ? htmlspecialchars((string) $game['map_center_lat'], ENT_QUOTES, 'UTF-8') : '-' ?>
            ,
            <?= $game['map_center_lon'] !== null ? htmlspecialchars((string) $game['map_center_lon'], ENT_QUOTES, 'UTF-8') : '-' ?>
        </dd>
        <dt>Popis</dt><dd><?= nl2br(htmlspecialchars((string) $game['description'], ENT_QUOTES, 'UTF-8')) ?></dd>
        <dt>Intro</dt><dd><?= nl2br(htmlspecialchars((string) $game['intro_text'], ENT_QUOTES, 'UTF-8')) ?></dd>
        <dt>Cíl hry</dt><dd><?= nl2br(htmlspecialchars((string) ($game['objective_text'] ?? ''), ENT_QUOTES, 'UTF-8')) ?></dd>
        <dt>Návod pro hráče</dt><dd><?= nl2br(htmlspecialchars((string) ($game['player_guide_text'] ?? ''), ENT_QUOTES, 'UTF-8')) ?></dd>
    </dl>
</div>

<div class="card" style="margin-top: 24px;">
    <h3>Sdílení hry</h3>

    <?php
        $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
        $gameUrl = $baseUrl . "/game/" . $game['slug'];
    ?>

    <div>
        <strong>Veřejný odkaz na hru:</strong>
        <div id="game-url" class="url-box">
            <?= htmlspecialchars($gameUrl, ENT_QUOTES, 'UTF-8') ?>
        </div>

        <div class="page-actions">
            <button class="btn btn-secondary" onclick="copyGameUrl()">Kopírovat odkaz</button>
            <a class="btn btn-primary" href="/admin/games/<?= (int) $game['id'] ?>/invites">
                Spravovat pozvánky
            </a>
        </div>
    </div>
</div>

<div class="card" style="margin-top: 24px;">
    <h3>Výsledovka hry</h3>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Hráčů</div>
            <div class="stat-value"><?= (int) $gameStats['players_count'] ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">POI</div>
            <div class="stat-value"><?= (int) $gameStats['pois_count'] ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Pokladů</div>
            <div class="stat-value"><?= (int) $gameStats['treasures_count'] ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Sebrání</div>
            <div class="stat-value"><?= (int) $gameStats['treasure_claims_count'] ?></div>
        </div>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Hráč</th>
                    <th>Body</th>
                    <th>Poklady</th>
                    <th>Poslední poloha</th>
                    <th>Akce</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($leaderboardRows === []): ?>
                    <tr>
                        <td colspan="6">Zatím nejsou žádná data.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($leaderboardRows as $row): ?>
                        <tr>
                            <td>#<?= (int) $row['rank'] ?></td>
                            <td>
                                <strong><?= htmlspecialchars($row['nickname'], ENT_QUOTES, 'UTF-8') ?></strong>
                            </td>
                            <td><?= (int) $row['points'] ?></td>
                            <td><?= (int) $row['treasures_found'] ?></td>
                            <td>
                                <?php if ($row['last_seen_at']): ?>
                                    <div style="font-size: 12px; color: var(--ink-soft);">
                                        <?= htmlspecialchars($row['last_seen_at'], ENT_QUOTES, 'UTF-8') ?><br>
                                        <a href="https://www.google.com/maps?q=<?= (float)$row['last_lat'] ?>,<?= (float)$row['last_lon'] ?>" target="_blank" style="color: var(--accent);">
                                            <?= round((float)$row['last_lat'], 5) ?>, <?= round((float)$row['last_lon'], 5) ?>
                                        </a>
                                    </div>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>
                                <a class="btn btn-secondary" style="padding: 5px 10px; font-size: 13px;" href="/admin/players/<?= (int) $row['player_id'] ?>">Detail</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function copyGameUrl() {
    const text = document.getElementById("game-url").innerText;
    navigator.clipboard.writeText(text);
    alert("Odkaz zkopírován");
}
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
