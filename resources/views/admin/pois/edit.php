<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Upravit bod (POI)</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; max-width: 1100px; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; }
        input[type="text"], input[type="number"], input[type="datetime-local"], select, textarea {
            width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;
        }
        input[type="file"] {
            width: 100%;
            box-sizing: border-box;
        }
        textarea { height: 80px; }
        #map { height: 400px; margin-bottom: 20px; border: 1px solid #ccc; }
        .btn { padding: 10px 14px; cursor: pointer; text-decoration: none; border: 1px solid #999; background: #fff; color: #000; display: inline-block; border-radius: 4px; }
        .btn-primary { background: #000; color: #fff; border-color: #000; }
        .btn-danger { background: #b00020; color: #fff; border-color: #b00020; }
        .btn-secondary { background: #f3f3f3; }
        .errors { background: #fee; border: 1px solid #fcc; padding: 10px; margin-bottom: 20px; color: #900; }
        .checkbox-group { display: flex; align-items: center; gap: 8px; margin-bottom: 10px; }
        .checkbox-group input { margin: 0; }

        .media-section {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }

        .section-note {
            color: #666;
            font-size: 14px;
            margin-bottom: 15px;
        }

        .media-row {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 12px;
            background: #fafafa;
        }

        .media-row-grid {
            display: grid;
            grid-template-columns: 140px 1fr 1fr 140px 140px;
            gap: 12px;
            align-items: end;
        }

        .media-preview {
            margin-top: 10px;
            font-size: 13px;
            color: #666;
            word-break: break-all;
        }

        .media-preview a {
            color: #0b57d0;
        }

        @media (max-width: 900px) {
            .grid {
                grid-template-columns: 1fr;
            }

            .media-row-grid {
                grid-template-columns: 1fr;
            }
        }
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

    <?php
    $existingMedia = $poiMedia ?? [];
    $oldMedia = $old['media'] ?? null;
    $mediaRows = is_array($oldMedia) ? array_values($oldMedia) : array_values($existingMedia);
    ?>

    <form action="/admin/pois/<?= (int) $poi['id'] ?>" method="POST" enctype="multipart/form-data">
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
                <div class="form-group">
                    <label for="tts_text">TTS text (volitelné, pro hlasové čtení)</label>
                    <textarea id="tts_text" name="tts_text"><?= htmlspecialchars($old['tts_text'] ?? (string)($poi['tts_text'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
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

        <div class="media-section">
            <h2>Média k bodu</h2>
            <div class="section-note">
                Libovolný počet příloh. Můžeš kombinovat historické URL obrázky, vlastní uploady i YouTube.
            </div>

            <div id="mediaContainer"></div>

            <button type="button" class="btn btn-secondary" onclick="addMediaRow()">+ Přidat přílohu</button>
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

        let mediaIndex = 0;
        const initialMedia = <?= json_encode($mediaRows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

        function escapeHtml(value) {
            return String(value ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        function addMediaRow(data = {}) {
            const index = mediaIndex++;
            const container = document.getElementById('mediaContainer');

            const mediaType = data.media_type || 'image';
            const filePath = data.file_path || '';
            const title = data.title || data.alt_text || '';
            const sortOrder = data.sort_order ?? index;
            const existingInfo = filePath
                ? `<div class="media-preview">Aktuálně: <a href="${escapeHtml(filePath)}" target="_blank" rel="noopener noreferrer">${escapeHtml(filePath)}</a></div>`
                : '';

            const row = document.createElement('div');
            row.className = 'media-row';
            row.innerHTML = `
                <div class="media-row-grid">
                    <div class="form-group">
                        <label for="media_${index}_type">Typ média</label>
                        <select id="media_${index}_type" name="media[${index}][media_type]">
                            <option value="image" ${mediaType === 'image' ? 'selected' : ''}>Obrázek</option>
                            <option value="video" ${mediaType === 'video' ? 'selected' : ''}>YouTube video</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="media_${index}_file_path">Externí URL</label>
                        <input
                            type="text"
                            id="media_${index}_file_path"
                            name="media[${index}][file_path]"
                            value="${escapeHtml(filePath)}"
                            placeholder="https://..."
                        >
                        ${existingInfo}
                    </div>

                    <div class="form-group">
                        <label for="media_file_${index}">Nahrát soubor</label>
                        <input type="file" id="media_file_${index}" name="media_file_${index}" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                    </div>

                    <div class="form-group">
                        <label for="media_${index}_title">Titulek / alt text</label>
                        <input
                            type="text"
                            id="media_${index}_title"
                            name="media[${index}][title]"
                            value="${escapeHtml(title)}"
                            placeholder="Např. Historická fotografie"
                        >
                    </div>

                    <div class="form-group">
                        <label for="media_${index}_sort_order">Pořadí</label>
                        <input
                            type="number"
                            id="media_${index}_sort_order"
                            name="media[${index}][sort_order]"
                            value="${escapeHtml(sortOrder)}"
                        >
                    </div>
                </div>

                <div style="margin-top: 12px;">
                    <button type="button" class="btn btn-danger" onclick="removeMediaRow(this)">Odstranit přílohu</button>
                </div>
            `;

            container.appendChild(row);
        }

        function removeMediaRow(button) {
            const row = button.closest('.media-row');
            if (row) {
                row.remove();
            }
        }

        if (initialMedia.length > 0) {
            initialMedia.forEach(item => addMediaRow(item));
        } else {
            addMediaRow();
        }
    </script>
</body>
</html>