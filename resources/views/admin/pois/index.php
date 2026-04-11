<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Body zájmu (POI) - <?= htmlspecialchars($game['name'], ENT_QUOTES, 'UTF-8') ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; max-width: 1000px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { text-align: left; padding: 12px; border-bottom: 1px solid #ddd; }
        th { background: #f8f8f8; }
        .actions { margin-bottom: 20px; }
        .btn {
            padding: 8px 12px;
            cursor: pointer;
            text-decoration: none;
            border: 1px solid #999;
            background: #fff;
            color: #000;
            display: inline-block;
            font-size: 0.9em;
        }
        .btn-primary { background: #000; color: #fff; border: 1px solid #000; }
        .btn-danger { color: #d00; border-color: #d00; background: #fff; }
        .btn-danger:hover { background: #fee; }
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.85em;
            background: #eee;
        }
        .status-active { background: #e6ffed; color: #22863a; }
        .status-inactive { background: #ffeef0; color: #cb2431; }
    </style>
</head>
<body>
    <h1>Body zájmu (POI): <?= htmlspecialchars($game['name'], ENT_QUOTES, 'UTF-8') ?></h1>

    <div class="actions">
        <a class="btn" href="/admin/games/<?= (int) $game['id'] ?>">Zpět na detail hry</a>
        <a class="btn btn-primary" href="/admin/games/<?= (int) $game['id'] ?>/pois/create">Přidat nový bod</a>
    </div>

    <?php if (empty($pois)): ?>
        <p>Pro tuto hru zatím nebyly vytvořeny žádné body zájmu.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Název</th>
                    <th>Typ</th>
                    <th>Pořadí</th>
                    <th>Aktivní</th>
                    <th>Akce</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pois as $poi): ?>
                    <tr>
                        <td><?= (int) $poi['id'] ?></td>
                        <td><?= htmlspecialchars($poi['name'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><code><?= htmlspecialchars($poi['type'], ENT_QUOTES, 'UTF-8') ?></code></td>
                        <td><?= (int) $poi['sort_order'] ?></td>
                        <td>
                            <?php if ((int) $poi['is_enabled'] === 1): ?>
                                <span class="status-badge status-active">Ano</span>
                            <?php else: ?>
                                <span class="status-badge status-inactive">Ne</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a class="btn" href="/admin/pois/<?= (int) $poi['id'] ?>/edit">Upravit</a>
                            <form action="/admin/pois/<?= (int) $poi['id'] ?>/delete" method="POST" style="display:inline;" onsubmit="return confirm('Opravdu smazat tento bod?')">
                                <button type="submit" class="btn btn-danger">Smazat</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>
</html>
