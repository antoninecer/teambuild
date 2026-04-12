<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Nový poklad</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; max-width: 900px; }
        .actions { margin-bottom: 20px; }
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

        .errors {
            background: #ffe6e6;
            border: 1px solid #ff9999;
            padding: 15px;
            margin-bottom: 25px;
            color: #cc0000;
            border-radius: 4px;
        }

        .errors ul {
            margin: 0;
            padding-left: 20px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .full-width {
            grid-column: span 2;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 6px;
        }

        input[type="text"],
        input[type="number"],
        textarea,
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        textarea {
            min-height: 100px;
            resize: vertical;
        }

        .checkbox-row {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .checkbox-row label {
            font-weight: normal;
            margin: 0;
        }

        @media (max-width: 700px) {
            .form-grid {
                grid-template-columns: 1fr;
            }

            .full-width {
                grid-column: span 1;
            }
        }
    </style>
</head>
<body>
    <h1>Nový poklad pro hru: <?= htmlspecialchars($game['name'], ENT_QUOTES, 'UTF-8') ?></h1>

    <div class="actions">
        <a class="btn" href="/admin/games/<?= (int) $game['id'] ?>/treasures">← Zpět na poklady</a>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="errors">
            <strong>Při ukládání došlo k chybám:</strong>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="/admin/games/<?= (int) $game['id'] ?>/treasures" method="POST">
        <div class="form-grid">
            <div class="form-group">
                <label for="name">Název pokladu*</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($old['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
            </div>

            <div class="form-group">
                <label for="treasure_type">Typ pokladu*</label>
                <?php $selectedType = $old['treasure_type'] ?? 'public'; ?>
                <select id="treasure_type" name="treasure_type">
                    <option value="public" <?= $selectedType === 'public' ? 'selected' : '' ?>>public</option>
                    <option value="hidden" <?= $selectedType === 'hidden' ? 'selected' : '' ?>>hidden</option>
                    <option value="individual" <?= $selectedType === 'individual' ? 'selected' : '' ?>>individual</option>
                    <option value="team" <?= $selectedType === 'team' ? 'selected' : '' ?>>team</option>
                </select>
            </div>

            <div class="form-group full-width">
                <label for="description">Popis</label>
                <textarea id="description" name="description"><?= htmlspecialchars($old['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>

            <div class="form-group">
                <label for="poi_id">Navázat na POI</label>
                <select id="poi_id" name="poi_id">
                    <option value="">-- bez POI --</option>
                    <?php foreach ($pois as $poi): ?>
                        <option value="<?= (int) $poi['id'] ?>" <?= (($old['poi_id'] ?? '') == $poi['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($poi['name'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="radius_m">Radius (metry)*</label>
                <input type="number" id="radius_m" name="radius_m" value="<?= htmlspecialchars($old['radius_m'] ?? '20', ENT_QUOTES, 'UTF-8') ?>" min="1" required>
            </div>

            <div class="form-group">
                <label for="lat">Latitude*</label>
                <input type="text" id="lat" name="lat" value="<?= htmlspecialchars($old['lat'] ?? ($game['map_center_lat'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
            </div>

            <div class="form-group">
                <label for="lon">Longitude*</label>
                <input type="text" id="lon" name="lon" value="<?= htmlspecialchars($old['lon'] ?? ($game['map_center_lon'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
            </div>

            <div class="form-group">
                <label for="max_claims">Limit sebrání</label>
                <input type="number" id="max_claims" name="max_claims" value="<?= htmlspecialchars($old['max_claims'] ?? '', ENT_QUOTES, 'UTF-8') ?>" min="1">
            </div>

            <div class="form-group">
                <label for="points">Body</label>
                <input type="number" id="points" name="points" value="<?= htmlspecialchars($old['points'] ?? '0', ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="form-group full-width checkbox-row">
                <div>
                    <input type="checkbox" id="is_visible_on_map" name="is_visible_on_map" value="1" <?= isset($old['is_visible_on_map']) || !isset($old) ? 'checked' : '' ?>>
                    <label for="is_visible_on_map">Zobrazit na mapě</label>
                </div>

                <div>
                    <input type="checkbox" id="is_enabled" name="is_enabled" value="1" <?= isset($old['is_enabled']) || !isset($old) ? 'checked' : '' ?>>
                    <label for="is_enabled">Aktivní</label>
                </div>
            </div>
        </div>

        <div style="margin-top: 25px;">
            <button type="submit" class="btn btn-primary">Uložit poklad</button>
        </div>
    </form>
</body>
</html>