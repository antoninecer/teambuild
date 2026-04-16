<?php
/** @var array $game */
/** @var array|null $errors */
/** @var array|null $old */

$pageTitle = 'Nový bod (POI)';
$pageSubtitle = 'Hra: ' . $game['name'];
$activeNav = 'games';

require __DIR__ . '/../partials/header.php';
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>

<div class="page-actions">
    <a class="btn btn-secondary" href="/admin/games/<?= (int) $game['id'] ?>/pois">← Zpět na seznam bodů</a>
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

<div class="card">
    <form action="/admin/games/<?= (int) $game['id'] ?>/pois" method="POST" enctype="multipart/form-data">
        <div class="form-grid">
            <div>
                <div class="form-group">
                    <label for="name">Název bodu*</label>
                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($old['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
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
                        $selectedType = $old['type'] ?? 'story_point';
                        foreach ($types as $val => $label):
                        ?>
                            <option value="<?= $val ?>" <?= $selectedType === $val ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="description">Popis (interní)</label>
                    <textarea id="description" name="description"><?= htmlspecialchars($old['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>
                <div class="form-group">
                    <label for="story_text">Příběhový text (pro hráče)</label>
                    <textarea id="story_text" name="story_text"><?= htmlspecialchars($old['story_text'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>
                <div class="form-group">
                    <label for="tts_text">TTS text (volitelné, pro hlasové čtení)</label>
                    <textarea id="tts_text" name="tts_text"><?= htmlspecialchars($old['tts_text'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>
            </div>
            <div>
                <div id="map" style="height: 400px; margin-bottom: 20px; border: 1px solid var(--line); border-radius: 12px; z-index: 1;"></div>
                <div class="form-grid" style="grid-template-columns: 1fr 1fr;">
                    <div class="form-group">
                        <label for="lat">Latitude*</label>
                        <input type="text" id="lat" name="lat" value="<?= htmlspecialchars($old['lat'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="lon">Longitude*</label>
                        <input type="text" id="lon" name="lon" value="<?= htmlspecialchars($old['lon'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                </div>
                <div class="form-grid" style="grid-template-columns: 1fr 1fr;">
                    <div class="form-group">
                        <label for="radius_m">Radius (metry)*</label>
                        <input type="number" id="radius_m" name="radius_m" value="<?= htmlspecialchars($old['radius_m'] ?? '40', ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="sort_order">Pořadí</label>
                        <input type="number" id="sort_order" name="sort_order" value="<?= htmlspecialchars($old['sort_order'] ?? '0', ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                </div>
            </div>
        </div>

        <div style="display: flex; gap: 20px; flex-wrap: wrap; margin: 20px 0;">
            <div class="checkbox-group">
                <input type="checkbox" id="auto_unlock_on_proximity" name="auto_unlock_on_proximity" value="1" <?= ($old['auto_unlock_on_proximity'] ?? '1') == '1' ? 'checked' : '' ?>>
                <label for="auto_unlock_on_proximity">Automaticky odemknout v dosahu</label>
            </div>
            <div class="checkbox-group">
                <input type="checkbox" id="is_required" name="is_required" value="1" <?= ($old['is_required'] ?? '1') == '1' ? 'checked' : '' ?>>
                <label for="is_required">Povinný bod</label>
            </div>
            <div class="checkbox-group">
                <input type="checkbox" id="is_enabled" name="is_enabled" value="1" <?= ($old['is_enabled'] ?? '1') == '1' ? 'checked' : '' ?>>
                <label for="is_enabled">Aktivní</label>
            </div>
        </div>

        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid var(--line);">
            <h3 style="margin-top: 0;">Média k bodu</h3>
            <p class="help" style="margin-bottom: 15px;">
                Libovolný počet příloh. Každá příloha může být obrázek z URL, YouTube video, nebo nahraný obrázek.
            </p>

            <div id="mediaContainer"></div>

            <button type="button" class="btn btn-secondary" onclick="addMediaRow()">+ Přidat přílohu</button>
        </div>

        <div style="margin-top: 30px; border-top: 1px solid var(--line); padding-top: 20px;">
            <button type="submit" class="btn btn-primary">Vytvořit bod</button>
        </div>
    </form>
</div>

<style>
    .media-row {
        background: rgba(255,255,255,0.4);
        border: 1px solid var(--line);
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 16px;
    }
    .media-row-grid {
        display: grid;
        grid-template-columns: 140px 1fr 1fr 1fr auto;
        gap: 12px;
        align-items: start;
    }
    @media (max-width: 900px) {
        .media-row-grid { grid-template-columns: 1fr; }
    }
</style>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    const mapCenter = [<?= (float) ($game['map_center_lat'] ?? 50.0755) ?>, <?= (float) ($game['map_center_lon'] ?? 14.4378) ?>];
    const mapZoom = <?= (int) ($game['map_default_zoom'] ?? 14) ?>;
    const map = L.map('map').setView(mapCenter, mapZoom);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    let marker = null;

    const latInput = document.getElementById('lat');
    const lonInput = document.getElementById('lon');

    if (latInput.value && lonInput.value) {
        marker = L.marker([latInput.value, lonInput.value]).addTo(map);
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

    let mediaIndex = 0;
    const initialMedia = <?= json_encode(array_values($old['media'] ?? []), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

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
        const title = data.title || '';
        const sortOrder = data.sort_order ?? index;

        const row = document.createElement('div');
        row.className = 'media-row';
        row.innerHTML = `
            <div class="media-row-grid">
                <div class="form-group">
                    <label>Typ média</label>
                    <select name="media[${index}][media_type]">
                        <option value="image" ${mediaType === 'image' ? 'selected' : ''}>Obrázek</option>
                        <option value="video" ${mediaType === 'video' ? 'selected' : ''}>YouTube video</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Externí URL</label>
                    <input type="text" name="media[${index}][file_path]" value="${escapeHtml(filePath)}" placeholder="https://...">
                </div>

                <div class="form-group">
                    <label>Nahrát soubor</label>
                    <input type="file" name="media_file_${index}" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                </div>

                <div class="form-group">
                    <label>Titulek / alt text</label>
                    <input type="text" name="media[${index}][title]" value="${escapeHtml(title)}" placeholder="Např. Popis fotky">
                </div>

                <div class="form-group" style="width: 80px;">
                    <label>Pořadí</label>
                    <input type="number" name="media[${index}][sort_order]" value="${escapeHtml(sortOrder)}">
                </div>
            </div>

            <div style="margin-top: 12px; text-align: right;">
                <button type="button" class="btn btn-secondary" style="color: #7a1b1b; border-color: rgba(122, 27, 27, 0.2);" onclick="removeMediaRow(this)">Odstranit přílohu</button>
            </div>
        `;

        container.appendChild(row);
    }

    function removeMediaRow(button) {
        button.closest('.media-row').remove();
    }

    if (initialMedia.length > 0) {
        initialMedia.forEach(item => addMediaRow(item));
    } else {
        addMediaRow();
    }
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
