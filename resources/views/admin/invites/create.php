<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Nová pozvánka</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; max-width: 600px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; }
        input[type="text"], input[type="number"], select, textarea {
            width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;
        }
        .btn { padding: 10px 14px; cursor: pointer; text-decoration: none; border: 1px solid #999; background: #fff; color: #000; display: inline-block; }
        .btn-primary { background: #000; color: #fff; border-color: #000; }
        .errors { background: #fee; border: 1px solid #fcc; padding: 10px; margin-bottom: 20px; color: #900; }
        .help-text { font-size: 0.85em; color: #666; margin-top: 4px; }
    </style>
</head>
<body>
    <h1>Nová pozvánka pro hru: <?= htmlspecialchars($game['name'], ENT_QUOTES, 'UTF-8') ?></h1>

    <div class="actions" style="margin-bottom: 20px;">
        <a class="btn" href="/admin/games/<?= (int) $game['id'] ?>/invites">← Zpět na seznam pozvánek</a>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="errors">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="/admin/games/<?= (int) $game['id'] ?>/invites" method="POST">
        <div class="form-group">
            <label for="code">Kód pozvánky</label>
            <input type="text" id="code" name="code" value="<?= htmlspecialchars($old['code'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Např. TEAM2024">
            <p class="help-text">Pokud necháte prázdné, bude vygenerován náhodný 8místný kód.</p>
        </div>

        <div class="form-group">
            <label for="label">Popisek (interní)</label>
            <input type="text" id="label" name="label" value="<?= htmlspecialchars($old['label'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Např. Pozvánka pro sponzory">
        </div>

        <div class="form-group">
            <label for="team_id">Přiřadit k týmu</label>
            <select id="team_id" name="team_id">
                <option value="">-- Libovolný tým / Vytvořit nový --</option>
                <?php foreach ($teams as $team): ?>
                    <option value="<?= (int) $team['id'] ?>" <?= ($old['team_id'] ?? '') == $team['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($team['name'], ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <p class="help-text">Pokud je vybrán tým, hráč bude po zadání kódu automaticky přiřazen k tomuto týmu.</p>
        </div>

        <div class="form-group">
            <label for="max_uses">Maximální počet použití</label>
            <input type="number" id="max_uses" name="max_uses" value="<?= htmlspecialchars($old['max_uses'] ?? '', ENT_QUOTES, 'UTF-8') ?>" min="1">
            <p class="help-text">Nechte prázdné pro neomezený počet použití.</p>
        </div>

        <div style="margin-top: 20px;">
            <button type="submit" class="btn btn-primary">Vytvořit pozvánku</button>
        </div>
    </form>
</body>
</html>
