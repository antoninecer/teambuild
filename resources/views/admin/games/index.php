<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Seznam her</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; max-width: 1100px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { text-align: left; padding: 12px; border-bottom: 1px solid #ddd; vertical-align: top; }
        th { background: #f8f8f8; }
        .actions { margin-bottom: 20px; display: flex; gap: 10px; align-items: center; }
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

        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.85em;
            background: #eee;
            display: inline-block;
        }

        .mode-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.85em;
            display: inline-block;
            white-space: nowrap;
        }

        .mode-self {
            background: #e8f5e9;
            color: #1b5e20;
        }

        .mode-moderated {
            background: #fff3e0;
            color: #e65100;
        }
    </style>
</head>
<body>
    <h1>Seznam her</h1>

    <div class="actions">
        <a class="btn btn-primary" href="/admin/games/create">Vytvořit novou hru</a>
        <form action="/admin/logout" method="POST" style="display:inline;">
            <button class="btn" type="submit">Odhlásit se</button>
        </form>
    </div>

    <?php if (empty($games)): ?>
        <p>Zatím nebyly vytvořeny žádné hry.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Název</th>
                    <th>Stav</th>
                    <th>Režim hry</th>
                    <th>Začátek</th>
                    <th>Konec</th>
                    <th>Akce</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($games as $game): ?>
                    <tr>
                        <td><?= (int) $game['id'] ?></td>
                        <td><?= htmlspecialchars($game['name'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <span class="status-badge">
                                <?= htmlspecialchars($game['status'], ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        </td>
                        <td>
                            <?php if (($game['operation_mode'] ?? 'self_service') === 'moderated'): ?>
                                <span class="mode-badge mode-moderated">Hra s organizátorem</span>
                            <?php else: ?>
                                <span class="mode-badge mode-self">Samostatná hra</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($game['starts_at'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($game['ends_at'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <a href="/admin/games/<?= (int) $game['id'] ?>">Detail</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>
</html>