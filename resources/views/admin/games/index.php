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
        .placeholder {
            margin-top: 30px;
            padding: 18px;
            background: #f5f5f5;
            border: 1px solid #ddd;
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
    </div>

    <div class="placeholder">
        <strong>Další krok:</strong> sem přidáme editor bodů, mapu, POI, média a pozvánky.
    </div>
</body>
</html>
