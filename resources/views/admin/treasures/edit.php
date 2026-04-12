<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Upravit poklad</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            max-width: 1100px;
            background: #fffaf0;
        }

        h1 {
            margin-bottom: 20px;
        }

        .actions {
            margin-bottom: 20px;
        }

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
            border-color: #000;
        }

        .treasure-panel {
            background: #fff6dd;
            border: 1px solid #e6cf87;
            padding: 20px;
            border-radius: 10px;
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }

        input[type="text"],
        input[type="number"],
        select,
        textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccb96b;
            border-radius: 4px;
            box-sizing: border-box;
            background: #fffdf6;
        }

        textarea {
            height: 90px;
            resize: vertical;
        }

        #map {
            height: 420px;
            margin-bottom: 20px;
            border: 1px solid #ccb96b;
            border-radius: 6px;
            overflow: hidden;
        }

        .errors {
            background: #fee;
            border: 1px solid #fcc;
            padding: 10px;
            margin-bottom: 20px;
            color: #900;
            border-radius: 6px;
        }

        .errors ul {
            margin: 0;
            padding-left: 20px;
        }

        .checkbox-row {
            display: flex;
            gap: 20px;
            align-items: center;
            margin-top: 10px;
            margin-bottom: 10px;
        }

        .checkbox-row label {
            font-weight: normal;
            margin: 0;
        }

        .checkbox-row input {
            margin-right: 6px;
        }

        .hint {
            font-size: 12px;
            color: #6a5a2a;
            margin-top: 5px;
        }

        .badge {
            display: inline-block;
            background: #f0d77a;
            color: #5a4300;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: bold;
            margin-left: 10px;
            vertical-align: middle;
        }

        @media (max-width: 900px) {
            .grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <h1>
        Upravit poklad: <?= htmlspecialchars($treasure['name'], ENT_QUOTES, 'UTF-8') ?>
        <span class="badge">POKLAD</span>
    </h1>

    <div class="actions">
        <a class="btn" href="/admin/games/<?= (int) $game['id'] ?>/treasures">← Zpět na poklady</a>
    </div>

    <div class="treasure-panel">
        <?php if (!empty($errors)): ?>
            <div class="errors">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php
        $form = $old ?: $treasure;
        ?>
        <form action="/admin/treasures/<?= (int) $treasure['id'] ?>" method="POST">
            <div class="grid">
                <div>
                    <div class="form-group">
                        <label for="name">Název pokladu*</label>
                        <input type="text" id="name" name="name" value="<?= htmlspecialchars((string) ($form['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="treasure_type">Typ pokladu*</label>
                        <?php $selectedType = $form['treasure_type'] ?? 'public'; ?>
                        <select id="treasure_type" name="treasure_type" required>
                            <option value="public" <?= $selectedType === 'public' ? 'selected' : '' ?>>Public</option>
                            <option value="hidden" <?= $selectedType === 'hidden' ? 'selected' : '' ?>>Hidden</option>
                            <option value="individual" <?= $selectedType === 'individual' ? 'selected' : '' ?>>Individual</option>
                            <option value="team" <?= $selectedType === 'team' ? 'selected' : '' ?>>Team</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="description">Popis</label>
                        <textarea id="description" name="description"><?= htmlspecialchars((string) ($form['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="poi_id">Navázat na POI</label>
                        <select id="poi_id" name="poi_id">
                            <option value="">-- bez POI --</option>
                            <?php foreach ($pois as $poi): ?>
                                <option value="<?= (int) $poi['id'] ?>" <?= ((string) ($form['poi_id'] ?? '') === (string) $poi['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($poi['name'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="lat">Latitude*</label>
                        <input type="text" id="lat" name="lat" value="<?= htmlspecialchars((string) ($form['lat'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="lon">Longitude*</label>
                        <input type="text" id="lon" name="lon" value="<?= htmlspecialchars((string) ($form['lon'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="radius_m">Radius (metry)*</label>
                        <input type="number" id="radius_m" name="radius_m" value="<?= htmlspecialchars((string) ($form['radius_m'] ?? '20'), ENT_QUOTES, 'UTF-8') ?>" min="1" required>
                    </div>

                    <div class="form-group">
                        <label for="max_claims">Limit sebrání</label>
                        <input type="number" id="max_claims" name="max_claims" value="<?= htmlspecialchars((string) ($form['max_claims'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" min="1">
                    </div>

                    <div class="form-group">
                        <label for="points">Body</label>
                        <input type="number" id="points" name="points" value="<?= htmlspecialchars((string) ($form['points'] ?? '0'), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                </div>

                <div>
                    <div id="map"></div>

                    <div class="checkbox-row">
                        <div>
                            <input type="checkbox" id="is_visible_on_map" name="is_visible_on_map" value="1" <?= ((int) ($form['is_visible_on_map'] ?? 0) === 1) ? 'checked' : '' ?>>
                            <label for="is_visible_on_map">Zobrazit na mapě</label>
                        </div>

                        <div>
                            <input type="checkbox" id="is_enabled" name="is_enabled" value="1" <?= ((int) ($form['is_enabled'] ?? 0) === 1) ? 'checked' : '' ?>>
                            <label for="is_enabled">Aktivní</label>
                        </div>
                    </div>

                    <div class="hint">
                        Klikni do mapy pro přesun pokladu. Marker se přesune a souřadnice se automaticky vyplní.
                    </div>
                </div>
            </div>

            <div style="margin-top: 20px;">
                <button type="submit" class="btn btn-primary">Uložit změny</button>
            </div>
        </form>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        const mapCenter = [
            <?= (float) ($game['map_center_lat'] ?? 50.0755) ?>,
            <?= (float) ($game['map_center_lon'] ?? 14.4378) ?>
        ];
        const mapZoom = <?= (int) ($game['map_default_zoom'] ?? 14) ?>;

        const map = L.map('map').setView(mapCenter, mapZoom);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        const latInput = document.getElementById('lat');
        const lonInput = document.getElementById('lon');

        let marker = null;

        if (latInput.value && lonInput.value) {
            marker = L.marker([parseFloat(latInput.value), parseFloat(lonInput.value)]).addTo(map);
            map.setView([parseFloat(latInput.value), parseFloat(lonInput.value)], mapZoom);
        }

        map.on('click', function(e) {
            const lat = e.latlng.lat.toFixed(7);
            const lon = e.latlng.lng.toFixed(7);

            latInput.value = lat;
            lonInput.value = lon;

            if (marker) {
                marker.setLatLng(e.latlng);
            } else {
                marker = L.marker(e.latlng).addTo(map);
            }
        });
    </script>
</body>
</html>