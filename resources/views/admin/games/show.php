<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Detail hry</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; max-width: 1000px; }

        .grid { display: grid; grid-template-columns: 220px 1fr; gap: 10px 20px; }

        .actions { margin-top: 24px; display: flex; gap: 12px; }

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
    </style>
</head>
<body>

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

<script>
function copyGameUrl() {
    const text = document.getElementById("game-url").innerText;
    navigator.clipboard.writeText(text);
    alert("Odkaz zkopírován");
}
</script>

</body>
</html>