<?php
/** @var array $treasure */
/** @var array $game */
/** @var array $pois */
/** @var array|null $errors */
/** @var array|null $old */

$pageTitle = 'Upravit poklad';
$pageSubtitle = $treasure['name'];
$activeNav = 'games';

require __DIR__ . '/../partials/header.php';
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>

<div class="page-actions">
    <a class="btn btn-secondary" href="/admin/games/<?= (int) $game['id'] ?>/treasures">← Zpět na poklady</a>
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
$form = $old ?: $treasure;
?>

<div class="card">
    <form action="/admin/treasures/<?= (int) $treasure['id'] ?>" method="POST">
        <div class="form-grid">
            <div>
                <div class="form-group">
                    <label for="name">Název pokladu*</label>
                    <input type="text" id="name" name="name" value="<?= htmlspecialchars((string) ($form['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
                </div>

                <div class="form-group">
                    <label for="treasure_type">Typ pokladu*</label>
                    <?php $selectedType = $form['treasure_type'] ?? 'public'; ?>
                    <select id="treasure_type" name="treasure_type" required>
                        <option value="public" <?= $selectedType === 'public' ? 'selected' : '' ?>>Public (první bere)</option>
                        <option value="hidden" <?= $selectedType === 'hidden' ? 'selected' : '' ?>>Hidden (skrytý)</option>
                        <option value="individual" <?= $selectedType === 'individual' ? 'selected' : '' ?>>Individual (každý jednou)</option>
                        <option value="team" <?= $selectedType === 'team' ? 'selected' : '' ?>>Team (týmový)</option>
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
            </div>

            <div>
                <div id="map" style="height: 400px; margin-bottom: 20px; border: 1px solid var(--line); border-radius: 12px; z-index: 1;"></div>
                
                <div class="form-grid" style="grid-template-columns: 1fr 1fr;">
                    <div class="form-group">
                        <label for="lat">Latitude*</label>
                        <input type="text" id="lat" name="lat" value="<?= htmlspecialchars((string) ($form['lat'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="lon">Longitude*</label>
                        <input type="text" id="lon" name="lon" value="<?= htmlspecialchars((string) ($form['lon'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                </div>

                <div class="form-grid" style="grid-template-columns: 1fr 1fr;">
                    <div class="form-group">
                        <label for="radius_m">Radius (metry)*</label>
                        <input type="number" id="radius_m" name="radius_m" value="<?= htmlspecialchars((string) ($form['radius_m'] ?? '20'), ENT_QUOTES, 'UTF-8') ?>" min="1" required>
                    </div>

                    <div class="form-group">
                        <label for="points">Body</label>
                        <input type="number" id="points" name="points" value="<?= htmlspecialchars((string) ($form['points'] ?? '0'), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="max_claims">Limit sebrání (celkem)</label>
                    <input type="number" id="max_claims" name="max_claims" value="<?= htmlspecialchars((string) ($form['max_claims'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" min="1" placeholder="Neomezeno">
                </div>
            </div>
        </div>


        <div style="margin-top: 24px; border-top: 1px solid var(--line); padding-top: 20px;">
            <h3 style="margin-top: 0;">Nález / inventář</h3>
            <p style="color: var(--muted); margin-top: -6px;">
                Příprava pro budoucí knihu nálezů a jednoduchý inventář. Tento krok zatím jen zobrazuje hodnoty z databáze.
            </p>

            <?php $findsMode = (string) ($form['finds_mode'] ?? 'log_entry'); ?>
            <div class="form-group">
                <label for="finds_mode">Co hráč po sebrání získá</label>
                <select id="finds_mode" name="finds_mode">
                    <option value="log_entry" <?= $findsMode === 'log_entry' ? 'selected' : '' ?>>Zápis do knihy nálezů</option>
                    <option value="inventory_item" <?= $findsMode === 'inventory_item' ? 'selected' : '' ?>>Předmět do inventáře</option>
                </select>
            </div>

            <div class="form-grid" style="grid-template-columns: 1fr 1fr;">
                <div class="form-group">
                    <label for="weight_grams">Váha v gramech</label>
                    <input type="number" id="weight_grams" name="weight_grams" value="<?= htmlspecialchars((string) ($form['weight_grams'] ?? '0'), ENT_QUOTES, 'UTF-8') ?>" min="0">
                </div>
            </div>

            <div style="display: flex; gap: 20px; flex-wrap: wrap; margin: 10px 0;">
                <div class="checkbox-group">
                    <input type="checkbox" id="drop_allowed" name="drop_allowed" value="1" <?= ((int) ($form['drop_allowed'] ?? 0) === 1) ? 'checked' : '' ?>>
                    <label for="drop_allowed">Lze odložit</label>
                </div>

                <div class="checkbox-group">
                    <input type="checkbox" id="public_drop_allowed" name="public_drop_allowed" value="1" <?= ((int) ($form['public_drop_allowed'] ?? 0) === 1) ? 'checked' : '' ?>>
                    <label for="public_drop_allowed">Lze položit veřejně</label>
                </div>

                <div class="checkbox-group">
                    <input type="checkbox" id="hidden_drop_allowed" name="hidden_drop_allowed" value="1" <?= ((int) ($form['hidden_drop_allowed'] ?? 0) === 1) ? 'checked' : '' ?>>
                    <label for="hidden_drop_allowed">Lze položit skrytě</label>
                </div>
            </div>

            <p id="individualTreasureNotice" style="display: none; color: var(--muted); margin-top: 8px;">
                U typu „Individual (každý jednou)“ bude odložení později serverově zakázané, aby se osobní nález omylem nepředával dalším hráčům.
            </p>
        </div>

        <div style="display: flex; gap: 20px; flex-wrap: wrap; margin: 20px 0;">
            <div class="checkbox-group">
                <input type="checkbox" id="is_visible_on_map" name="is_visible_on_map" value="1" <?= ((int) ($form['is_visible_on_map'] ?? 0) === 1) ? 'checked' : '' ?>>
                <label for="is_visible_on_map">Zobrazit na mapě</label>
            </div>

            <div class="checkbox-group">
                <input type="checkbox" id="is_enabled" name="is_enabled" value="1" <?= ((int) ($form['is_enabled'] ?? 0) === 1) ? 'checked' : '' ?>>
                <label for="is_enabled">Aktivní</label>
            </div>
        </div>

        <div style="margin-top: 30px; border-top: 1px solid var(--line); padding-top: 20px;">
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


    const treasureTypeInput = document.getElementById('treasure_type');
    const dropAllowedInput = document.getElementById('drop_allowed');
    const publicDropAllowedInput = document.getElementById('public_drop_allowed');
    const hiddenDropAllowedInput = document.getElementById('hidden_drop_allowed');
    const individualTreasureNotice = document.getElementById('individualTreasureNotice');

    function refreshInventoryFields() {
        const isIndividual = treasureTypeInput && treasureTypeInput.value === 'individual';

        if (individualTreasureNotice) {
            individualTreasureNotice.style.display = isIndividual ? 'block' : 'none';
        }

        [dropAllowedInput, publicDropAllowedInput, hiddenDropAllowedInput].forEach(function(input) {
            if (!input) {
                return;
            }

            input.disabled = isIndividual;

            if (isIndividual) {
                input.checked = false;
            }
        });
    }

    if (treasureTypeInput) {
        treasureTypeInput.addEventListener('change', refreshInventoryFields);
        refreshInventoryFields();
    }
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
