<?php
/** @var array $poi */
/** @var array $game */
/** @var array|null $poiMedia */
/** @var array|null $errors */
/** @var array|null $old */

$pageTitle = 'Upravit bod (POI)';
$pageSubtitle = $poi['name'];
$activeNav = 'games';

require __DIR__ . '/../partials/header.php';
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>

<div class="page-actions">
    <a class="btn btn-secondary" href="/admin/games/<?= (int) $poi['game_id'] ?>/pois">← Zpět na seznam bodů</a>
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

<?php
$existingMedia = $poiMedia ?? [];
$oldMedia = $old['media'] ?? null;
$mediaRows = is_array($oldMedia) ? array_values($oldMedia) : array_values($existingMedia);
?>

<div class="card">
    <form action="/admin/pois/<?= (int) $poi['id'] ?>" method="POST" enctype="multipart/form-data">
        <div class="form-grid">
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
                    <textarea id="description" name="description"><?= htmlspecialchars($old['description'] ?? (string) $poi['description'], ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>
                <div class="form-group">
                    <label for="story_text">Příběhový text (pro hráče)</label>
                    <textarea id="story_text" name="story_text"><?= htmlspecialchars($old['story_text'] ?? (string) $poi['story_text'], ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>
                <div class="form-group">
                    <label for="tts_text">TTS text (volitelné, pro hlasové čtení)</label>
                    <textarea id="tts_text" name="tts_text"><?= htmlspecialchars($old['tts_text'] ?? (string) ($poi['tts_text'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>
            </div>
            <div>
                <div style="margin-bottom: 10px; display: flex; justify-content: flex-end;">
                    <button type="button" class="btn btn-secondary" id="btn-use-location" style="gap: 8px; font-size: 13px; padding: 8px 12px;">
                        📍 Použít aktuální polohu
                    </button>
                </div>
                <div id="map" style="height: 400px; margin-bottom: 20px; border: 1px solid var(--line); border-radius: 12px; z-index: 1;"></div>
                <div class="form-grid" style="grid-template-columns: 1fr 1fr;">
                    <div class="form-group">
                        <label for="lat">Latitude*</label>
                        <input type="text" id="lat" name="lat" value="<?= htmlspecialchars($old['lat'] ?? (string) $poi['lat'], ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="lon">Longitude*</label>
                        <input type="text" id="lon" name="lon" value="<?= htmlspecialchars($old['lon'] ?? (string) $poi['lon'], ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                </div>
                <div class="form-grid" style="grid-template-columns: 1fr 1fr;">
                    <div class="form-group">
                        <label for="radius_m">Radius (metry)*</label>
                        <input type="number" id="radius_m" name="radius_m" value="<?= htmlspecialchars($old['radius_m'] ?? (string) $poi['radius_m'], ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="sort_order">Pořadí</label>
                        <input type="number" id="sort_order" name="sort_order" value="<?= htmlspecialchars($old['sort_order'] ?? (string) $poi['sort_order'], ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                </div>
            </div>
        </div>

        <div style="display: flex; gap: 20px; flex-wrap: wrap; margin: 20px 0;">
            <div class="checkbox-group">
                <input type="checkbox" id="auto_unlock_on_proximity" name="auto_unlock_on_proximity" value="1" <?= ($old['auto_unlock_on_proximity'] ?? (string) $poi['auto_unlock_on_proximity']) == '1' ? 'checked' : '' ?>>
                <label for="auto_unlock_on_proximity">Automaticky odemknout v dosahu</label>
            </div>
            <div class="checkbox-group">
                <input type="checkbox" id="is_required" name="is_required" value="1" <?= ($old['is_required'] ?? (string) $poi['is_required']) == '1' ? 'checked' : '' ?>>
                <label for="is_required">Povinný bod</label>
            </div>
            <div class="checkbox-group">
                <input type="checkbox" id="is_enabled" name="is_enabled" value="1" <?= ($old['is_enabled'] ?? (string) $poi['is_enabled']) == '1' ? 'checked' : '' ?>>
                <label for="is_enabled">Aktivní</label>
            </div>
        </div>

        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid var(--line);">
            <h3 style="margin-top: 0;">Média k bodu</h3>
            <p class="help" style="margin-bottom: 15px;">
                Libovolný počet příloh. Můžeš kombinovat historické URL obrázky, vlastní uploady i YouTube.
            </p>

            <div id="mediaContainer"></div>

            <button type="button" class="btn btn-secondary" onclick="addMediaRow()">+ Přidat přílohu</button>
        </div>

        <div style="margin-top: 30px; border-top: 1px solid var(--line); padding-top: 20px;">
            <button type="submit" class="btn btn-primary">Uložit změny</button>
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
    .media-preview {
        margin-top: 8px;
        font-size: 13px;
        word-break: break-all;
    }
    .media-preview a { color: var(--accent); font-weight: 700; }
    @media (max-width: 900px) {
        .media-row-grid { grid-template-columns: 1fr; }
    }
</style>

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

    document.getElementById('btn-use-location').addEventListener('click', function() {
        if (!navigator.geolocation) {
            alert('Geolokace není podporována vaším prohlížečem.');
            return;
        }

        const btn = this;
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '⌛ Získávám...';

        navigator.geolocation.getCurrentPosition(function(position) {
            const lat = position.coords.latitude;
            const lon = position.coords.longitude;
            const latlng = L.latLng(lat, lon);
            
            map.setView(latlng, 17);
            
            latInput.value = lat.toFixed(7);
            lonInput.value = lon.toFixed(7);
            
            if (marker) {
                marker.setLatLng(latlng);
            } else {
                marker = L.marker(latlng).addTo(map);
            }
            
            btn.disabled = false;
            btn.innerHTML = originalText;
        }, function(error) {
            alert('Chyba při získávání polohy: ' + error.message);
            btn.disabled = false;
            btn.innerHTML = originalText;
        }, {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 0
        });
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
        const externalUrl = data.external_url || '';
        const existingPath = data.file_path || '';
        const title = data.title || data.label || data.alt_text || '';
        const sortOrder = data.sort_order ?? index;

        const existingInfo = existingPath
            ? `<div class="media-preview">Aktuálně: <a href="${escapeHtml(existingPath)}" target="_blank" rel="noopener noreferrer">${escapeHtml(existingPath)}</a></div>`
            : '';

        const row = document.createElement('div');
        row.className = 'media-row';
        row.innerHTML = `
            <div class="media-row-grid">
                <div class="form-group">
                    <label>Typ média</label>
                    <select name="media[${index}][media_type]">
                        <option value="image" ${mediaType === 'image' ? 'selected' : ''}>Obrázek</option>
                        <option value="video" ${mediaType === 'video' ? 'selected' : ''}>YouTube video</option>
                        <option value="audio" ${mediaType === 'audio' ? 'selected' : ''}>Audio</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Externí URL</label>
                    <input type="text" name="media[${index}][external_url]" value="${escapeHtml(externalUrl)}" placeholder="https://...">
                    <input type="hidden" name="media[${index}][existing_path]" value="${escapeHtml(existingPath)}">
                    ${existingInfo}
                </div>

                <div class="form-group">
                    <label>Nahrát soubor</label>
                    <input type="file" name="media_file_${index}" accept=".jpg,.jpeg,.png,.webp,.mp3,.wav,.ogg,.m4a,image/jpeg,image/png,image/webp,audio/mpeg,audio/mp3,audio/wav,audio/ogg,audio/mp4">
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
