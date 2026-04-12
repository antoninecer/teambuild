<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Poklady - <?= htmlspecialchars($game['name'], ENT_QUOTES, 'UTF-8') ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; max-width: 1100px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { text-align: left; padding: 12px; border-bottom: 1px solid #ddd; vertical-align: top; }
        th { background: #f8f8f8; }
        .actions { margin-bottom: 20px; display: flex; gap: 12px; flex-wrap: wrap; }
        .btn {
            padding: 10px 14px;
            cursor: pointer;
            text-decoration: none;
            border: 1px solid #999;
            background: #fff;
            color: #000;
            display: inline-block;
        }
        .btn-primary { background: #000; color: #fff; border: 1px solid #000; }
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.85em;
            background: #eee;
        }
    </style>
</head>
<body>
    <h1>Poklady: <?= htmlspecialchars($game['name'], ENT_QUOTES, 'UTF-8') ?></h1>

    <div class="actions">
        <a class="btn" href="/admin/games/<?= (int) $game['id'] ?>">Zpět na detail hry</a>
        <a class="btn btn-primary" href="/admin/games/<?= (int) $game['id'] ?>/treasures/create">Vytvořit poklad</a>
    </div>

    <?php if (empty($treasures)): ?>
        <p>Pro tuto hru zatím nejsou definované žádné poklady.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Název</th>
                    <th>Typ</th>
                    <th>POI</th>
                    <th>Souřadnice</th>
                    <th>Radius</th>
                    <th>Body</th>
                    <th>Mapa</th>
                    <th>Aktivní</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($treasures as $treasure): ?>
                    <tr>
                        <td><?= (int) $treasure['id'] ?></td>
                        <td>
                            <strong><?= htmlspecialchars($treasure['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                            <?php if (!empty($treasure['description'])): ?>
                                <div style="margin-top:6px; color:#555;">
                                    <?= nl2br(htmlspecialchars((string) $treasure['description'], ENT_QUOTES, 'UTF-8')) ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge"><?= htmlspecialchars($treasure['treasure_type'], ENT_QUOTES, 'UTF-8') ?></span></td>
                        <td><?= htmlspecialchars((string) ($treasure['poi_name'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <?= htmlspecialchars((string) $treasure['lat'], ENT_QUOTES, 'UTF-8') ?>,
                            <?= htmlspecialchars((string) $treasure['lon'], ENT_QUOTES, 'UTF-8') ?>
                        </td>
                        <td><?= (int) $treasure['radius_m'] ?> m</td>
                        <td><?= (int) $treasure['points'] ?></td>
                        <td><?= (int) $treasure['is_visible_on_map'] === 1 ? 'ano' : 'ne' ?></td>
                        <td><?= (int) $treasure['is_enabled'] === 1 ? 'ano' : 'ne' ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>
</html>