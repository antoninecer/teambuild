<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Upravit bod (POI)</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; max-width: 1000px; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; }
        input[type="text"], input[type="number"], select, textarea {
            width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;
        }
        textarea { height: 80px; }
        #map { height: 400px; margin-bottom: 20px; border: 1px solid #ccc; }
        .btn { padding: 10px 14px; cursor: pointer; text-decoration: none; border: 1px solid #999; background: #fff; color: #000; display: inline-block; }
        .btn-primary { background: #000; color: #fff; border-color: #000; }
        .errors { background: #fee; border: 1px solid #fcc; padding: 10px; margin-bottom: 20px; color: #900; }
        .checkbox-group { display: flex; align-items: center; gap: 8px; margin-bottom: 10px; }
        .checkbox-group input { margin: 0; }
    </style>
</head>
<body>
    <h1>Upravit bod: <?= htmlspecialchars($poi['name'], ENT_QUOTES, 'UTF-8') ?></h1>

    <div class="actions" style="margin-bottom: 20px;">
        <a class="btn" href="/admin/games/<?= (int) $poi['game_id'] ?>/pois">← Zpět na seznam bodů</a>
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

    <form action="/admin/pois/<?= (int) $poi['id'] ?>" method="POST">
        <div class="grid">
            <div>
                <div class="form-group">
                    <label for="name">Název bodu*</label>
                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($old['name'] ?? $poi['name'], ENT_QUOTES, 'UTF-8') ?>" required>
                </div>
                <div class="form-group">
                    <label for="type">Typ bodu*</label>
                    <select id="type" name="type" required>
                        <?php
                        $types = [
                            'story_point' => 'Příběhový bod',
                            'start_point' => 'Start',
                            'checkpoint' => 'Kontrolní bod',
                            'rescue_point' => 'Záchranný bod',
                            'hint_point' => 'Nápověda',
                            'finish_point' => 'Cíl',
                            'meetup_point' => 'Sraz'
                        ];
                        $selectedType = $old['type'] ?? $poi['type'];
                        foreach ($types as $val => $label):
                        ?>
                            <option value="<?= $val ?>" <?= $selectedType === $val ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="description">Popis (interní)</label>
                    <textarea id="description" name="description"><?= htmlspecialchars($old['description'] ?? (string)$poi['description'], ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>
                <div class="form-group">
                    <label for="story_text">Příběhový text (pro hráče)</label>
                    <textarea id="story_text" name="story_text"><?= htmlspecialchars($old['story_text'] ?? (string)$poi['story_text'], ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>
            </div>
            <div>
                <div id="map"></div>
                <div class="form-group">
                    <label for="lat">Latitude*</label>
                    <input type="text" id="lat" name="lat" value="<?= htmlspecialchars($old['lat'] ?? (string)$poi['lat'], ENT_QUOTES, 'UTF-8') ?>" required>
                </div>
                <div class="form-group">
                    <label for="lon">Longitude*</label>
                    <input type="text" id="lon" name="lon" value="<?= htmlspecialchars($old['lon'] ?? (string)$poi['lon'], ENT_QUOTES, 'UTF-8') ?>" required>
                </div>
                <div class="form-group">
                    <label for="radius_m">Radius (metry)*</label>
                    <input type="number" id="radius_m" name="radius_m" value="<?= htmlspecialchars($old['radius_m'] ?? (string)$poi['radius_m'], ENT_QUOTES, 'UTF-8') ?>" required>
                </div>
                <div class="form-group">
                    <label for="sort_order">Pořadí</label>
                    <input type="number" id="sort_order" name="sort_order" value="<?= htmlspecialchars($old['sort_order'] ?? (string)$poi['sort_order'], ENT_QUOTES, 'UTF-8') ?>">
                </div>
            </div>
        </div>

        <div class="checkbox-group">
            <input type="checkbox" id="auto_unlock_on_proximity" name="auto_unlock_on_proximity" value="1" <?= ($old['auto_unlock_on_proximity'] ?? (string)$poi['auto_unlock_on_proximity']) == '1' ? 'checked' : '' ?>>
            <label for="auto_unlock_on_proximity">Automaticky odemknout v dosahu</label>
        </div>
        <div class="checkbox-group">
            <input type="checkbox" id="is_required" name="is_required" value="1" <?= ($old['is_required'] ?? (string)$poi['is_required']) == '1' ? 'checked' : '' ?>>
            <label for="is_required">Povinný bod</label>
        </div>
        <div class="checkbox-group">
            <input type="checkbox" id="is_enabled" name="is_enabled" value="1" <?= ($old['is_enabled'] ?? (string)$poi['is_enabled']) == '1' ? 'checked' : '' ?>>
            <label for="is_enabled">Aktivní</label>
        </div>

        <div style="margin-top: 20px;">
            <button type="submit" class="btn btn-primary">Uložit změny</button>
        </div>
    </form>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        const initialLat = <?= (float) $poi['lat'] ?>;
        const initialLon = <?= (float) $poi['lon'] ?>;
        const map = L.map('map').setView([initialLat, initialLon], 15);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        let marker = L.marker([initialLat, initialLon]).addTo(map);

        const latInput = document.getElementById('lat');
        const lonInput = document.getElementById('lon');

        map.on('click', function(e) {
            const lat = e.latlng.lat.toFixed(7);
            const lon = e.latlng.lng.toFixed(7);
            
            latInput.value = lat;
            lonInput.value = lon;

            marker.setLatLng(e.latlng);
        });
    </script>
</body>
</html>
