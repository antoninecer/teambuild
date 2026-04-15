<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Detail hry</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; max-width: 1100px; }

        .grid { display: grid; grid-template-columns: 220px 1fr; gap: 10px 20px; }

        .actions { margin-top: 24px; display: flex; gap: 12px; flex-wrap: wrap; }

        .btn {
            padding: 10px 14px;
            cursor: pointer;
            text-decoration: none;
            border: 1px solid #999;
            background: #fff;
            color: #000;
            display: inline-block;
        }

        .btn-primary {
            background: #000;
            color: #fff;
        }

        .section {
            margin-top: 40px;
            padding: 20px;
            background: #f7f7f7;
            border: 1px solid #ddd;
        }

        .url-box {
            background: #fff;
            padding: 10px;
            border: 1px solid #ccc;
            font-family: monospace;
            word-break: break-all;
            margin-top: 10px;
        }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 13px;
            background: #eee;
        }

        .badge-self {
            background: #e8f5e9;
            color: #1b5e20;
        }

        .badge-moderated {
            background: #fff3e0;
            color: #e65100;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 18px;
        }

        .stat-card {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 12px;
            padding: 14px;
        }

        .stat-label {
            font-size: 12px;
            color: #666;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .stat-value {
            font-size: 24px;
            font-weight: bold;
        }

        .leaderboard-table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border: 1px solid #ddd;
        }

        .leaderboard-table th,
        .leaderboard-table td {
            padding: 12px 10px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .leaderboard-table th {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #666;
            background: #fafafa;
        }

        @media (max-width: 900px) {
            .stats-grid {
                grid-template-columns: 1fr 1fr;
            }
        }
    </style>
</head>
<body>

<?php
$pdo = \App\Support\Database::connection();

$leaderboardStmt = $pdo->prepare(
    'SELECT
        p.id AS player_id,
        p.nickname,
        COALESCE(SUM(COALESCE(t.points, 0)), 0) AS points,
        COUNT(t.id) AS treasures_found
     FROM players p
     LEFT JOIN treasure_claims tc
        ON tc.player_id = p.id
     LEFT JOIN treasures t
        ON t.id = tc.treasure_id
       AND t.game_id = :game_id
     WHERE p.game_id = :game_id
     GROUP BY p.id, p.nickname
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

<h1>Detail hry: <?= htmlspecialchars($game['name'], ENT_QUOTES, 'UTF-8') ?></h1>

<div class="grid">
    <div><strong>ID</strong></div><div><?= (int) $game['id'] ?></div>
    <div><strong>Název</strong></div><div><?= htmlspecialchars($game['name'], ENT_QUOTES, 'UTF-8') ?></div>
    <div><strong>Slug</strong></div><div><?= htmlspecialchars($game['slug'], ENT_QUOTES, 'UTF-8') ?></div>
    <div><strong>Stav</strong></div><div><?= htmlspecialchars($game['status'], ENT_QUOTES, 'UTF-8') ?></div>
    <div><strong>Režim hry</strong></div>
    <div>
        <?php if (($game['operation_mode'] ?? 'self_service') === 'moderated'): ?>
            <span class="badge badge-moderated">Hra s organizátorem</span>
        <?php else: ?>
            <span class="badge badge-self">Samostatná hra</span>
        <?php endif; ?>
    </div>
    <div><strong>Začátek</strong></div><div><?= htmlspecialchars($game['starts_at'], ENT_QUOTES, 'UTF-8') ?></div>
    <div><strong>Konec</strong></div><div><?= htmlspecialchars($game['ends_at'], ENT_QUOTES, 'UTF-8') ?></div>
    <div><strong>Registrace</strong></div><div><?= (int) $game['registration_enabled'] === 1 ? 'ano' : 'ne' ?></div>
    <div><strong>Cookie (dny)</strong></div><div><?= (int) $game['session_cookie_days'] ?></div>
    <div><strong>Střed mapy</strong></div>
    <div>
        <?= $game['map_center_lat'] !== null ? htmlspecialchars((string) $game['map_center_lat'], ENT_QUOTES, 'UTF-8') : '-' ?>
        ,
        <?= $game['map_center_lon'] !== null ? htmlspecialchars((string) $game['map_center_lon'], ENT_QUOTES, 'UTF-8') : '-' ?>
    </div>
    <div><strong>Intro</strong></div><div><?= nl2br(htmlspecialchars((string) $game['intro_text'], ENT_QUOTES, 'UTF-8')) ?></div>
    <div><strong>Popis</strong></div><div><?= nl2br(htmlspecialchars((string) $game['description'], ENT_QUOTES, 'UTF-8')) ?></div>
</div>

<div class="actions">
    <a class="btn" href="/admin/games">Zpět na seznam</a>
    <a class="btn btn-primary" href="/admin/games/<?= (int) $game['id'] ?>/pois">Správa bodů (POI)</a>
    <a class="btn btn-primary" href="/admin/games/<?= (int) $game['id'] ?>/treasures">Poklady</a>
    <a class="btn btn-primary" href="/admin/games/<?= (int) $game['id'] ?>/invites">Pozvánky & QR</a>
</div>

<div class="section">
    <h2>Sdílení hry</h2>

    <?php
        $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
        $gameUrl = $baseUrl . "/game/" . $game['slug'];
    ?>

    <div>
        <strong>Veřejný odkaz na hru:</strong>

        <div id="game-url" class="url-box">
            <?= htmlspecialchars($gameUrl, ENT_QUOTES, 'UTF-8') ?>
        </div>

        <button class="btn" onclick="copyGameUrl()">Kopírovat odkaz</button>
    </div>

    <div style="margin-top: 20px;">
        <a class="btn btn-primary" href="/admin/games/<?= (int) $game['id'] ?>/invites">
            Vytvořit / spravovat pozvánky
        </a>
    </div>
</div>

<div class="section">
    <strong>Aktuální stav:</strong>
    <?php if (($game['operation_mode'] ?? 'self_service') === 'moderated'): ?>
        Tato hra je připravena pro režim s organizátorem. Později sem přibude živý dohled a zásahy správce.
    <?php else: ?>
        Tato hra je vedena jako samostatná. Priorita je automatické odemykání bodů, příběhy a poklady bez nutnosti moderace.
    <?php endif; ?>
</div>

<div class="section">
    <h2>Výsledovka hry</h2>

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
            <div class="stat-label">Sebrání pokladů</div>
            <div class="stat-value"><?= (int) $gameStats['treasure_claims_count'] ?></div>
        </div>
    </div>

    <table class="leaderboard-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Hráč</th>
                <th>Body</th>
                <th>Poklady</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($leaderboardRows === []): ?>
                <tr>
                    <td colspan="4">Zatím nejsou žádná data.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($leaderboardRows as $row): ?>
                    <tr>
                        <td>#<?= (int) $row['rank'] ?></td>
                        <td><?= htmlspecialchars($row['nickname'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= (int) $row['points'] ?></td>
                        <td><?= (int) $row['treasures_found'] ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
function copyGameUrl() {
    const text = document.getElementById("game-url").innerText;
    navigator.clipboard.writeText(text);
    alert("Odkaz zkopírován");
}
</script>

</body>
</html>