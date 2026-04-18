<?php
/** @var array $player */
/** @var array $game */
/** @var array $claimedTreasures */
/** @var array $locationHistory */
/** @var array|null $lastKnownPosition */
/** @var array|null $activeHelpRequest */
/** @var array $recentEvents */

$pageTitle = 'Detail hráče';
$pageSubtitle = $player['nickname'] . ' (Hra: ' . $game['name'] . ')';
$activeNav = 'games';

require __DIR__ . '/../partials/header.php';

$mapsLat = null;
$mapsLon = null;
if (!empty($player['last_lat']) && !empty($player['last_lon'])) {
    $mapsLat = (float) $player['last_lat'];
    $mapsLon = (float) $player['last_lon'];
} elseif ($lastKnownPosition) {
    $mapsLat = (float) $lastKnownPosition['lat'];
    $mapsLon = (float) $lastKnownPosition['lon'];
}

$mapsUrl = null;
if ($mapsLat !== null && $mapsLon !== null) {
    $mapsUrl = 'https://maps.google.com/?q=' . rawurlencode((string) $mapsLat . ',' . (string) $mapsLon);
}

$phoneLink = null;
if (!empty($player['phone'])) {
    $phoneLink = 'tel:' . preg_replace('/[^\d+]/', '', (string)$player['phone']);
}

function eventLabel(array $event): string
{
    $type = (string)($event['event_type'] ?? '');

    return match ($type) {
        'poi_visited' => 'Dokončené POI',
        'treasure_claimed' => 'Sebraný poklad',
        'help_requested' => 'Žádost o pomoc',
        default => $type !== '' ? $type : 'Událost',
    };
}

function eventDetail(array $event): string
{
    $payload = [];
    if (!empty($event['payload_json'])) {
        $decoded = json_decode((string)$event['payload_json'], true);
        if (is_array($decoded)) {
            $payload = $decoded;
        }
    }

    $type = (string)($event['event_type'] ?? '');

    return match ($type) {
        'poi_visited' => (string)($payload['poi_name'] ?? ('POI #' . (int)($event['poi_id'] ?? 0))),
        'treasure_claimed' => (string)($payload['treasure_name'] ?? 'Poklad'),
        'help_requested' => (string)($payload['message'] ?? 'Hráč požádal o pomoc'),
        default => !empty($payload) ? json_encode($payload, JSON_UNESCAPED_UNICODE) ?: '' : '',
    };
}
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>

<style>
    .player-sos-box {
        border: 2px solid #c84d3a;
        background: #fff0eb;
        border-radius: 16px;
        padding: 18px 20px;
        margin-bottom: 20px;
    }

    .player-sos-box h3 {
        margin: 0 0 10px;
        color: #8f2d20;
    }

    .player-sos-meta {
        display: grid;
        grid-template-columns: 150px 1fr;
        gap: 8px;
        font-size: 14px;
        margin-bottom: 12px;
    }

    .player-sos-message {
        background: #fffaf7;
        border: 1px solid #e4c2b8;
        border-radius: 12px;
        padding: 12px 14px;
        white-space: pre-wrap;
        line-height: 1.45;
    }

    .quick-grid {
        display: grid;
        grid-template-columns: 160px 1fr;
        gap: 8px;
        font-size: 15px;
    }

    .event-chip {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 999px;
        background: #f2e7dc;
        font-size: 12px;
        font-weight: 700;
        color: #6b4428;
    }

    .small-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 14px;
    }

    .mini-help {
        color: #6d5a49;
        font-size: 13px;
    }

    .map-title-row {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        gap: 12px;
        margin-bottom: 8px;
    }

    .map-title-row h3 {
        margin: 0;
    }

    .map-subtitle {
        color: #6d5a49;
        font-size: 13px;
        line-height: 1.4;
        margin-bottom: 10px;
    }

    .location-row {
        cursor: pointer;
    }

    .location-row:hover {
        background: #f8f1e7;
    }

    .location-row.is-selected {
        background: #efe1ce;
    }

    .location-row.is-selected td {
        font-weight: 600;
    }
</style>

<div class="page-actions">
    <a class="btn btn-secondary" href="/admin/games/<?= (int) $game['id'] ?>">← Zpět na detail hry</a>
</div>

<?php if ($activeHelpRequest): ?>
    <div class="player-sos-box">
        <h3>Aktivní SOS</h3>

        <div class="player-sos-meta">
            <div><strong>Stav:</strong></div>
            <div><?= htmlspecialchars((string)$activeHelpRequest['status'], ENT_QUOTES, 'UTF-8') ?></div>

            <div><strong>Vytvořeno:</strong></div>
            <div><?= htmlspecialchars((string)$activeHelpRequest['created_at'], ENT_QUOTES, 'UTF-8') ?></div>

            <div><strong>Hráč:</strong></div>
            <div><?= htmlspecialchars((string)$player['nickname'], ENT_QUOTES, 'UTF-8') ?></div>

            <div><strong>Hra:</strong></div>
            <div><?= htmlspecialchars((string)$game['name'], ENT_QUOTES, 'UTF-8') ?></div>
        </div>

        <div class="player-sos-message"><?= htmlspecialchars((string)($activeHelpRequest['message'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>

        <div class="small-actions">
            <?php if (($activeHelpRequest['status'] ?? '') === 'open'): ?>
                <button class="btn btn-secondary" type="button" onclick="acknowledgeHelp(<?= (int)$activeHelpRequest['id'] ?>)">Převzít</button>
            <?php endif; ?>

            <?php if (in_array(($activeHelpRequest['status'] ?? ''), ['open', 'acknowledged'], true)): ?>
                <button class="btn btn-primary" type="button" onclick="resolveHelp(<?= (int)$activeHelpRequest['id'] ?>)">Uzavřít</button>
            <?php endif; ?>

            <?php if ($mapsUrl): ?>
                <a class="btn btn-secondary" href="<?= htmlspecialchars($mapsUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">Otevřít v mapách</a>
            <?php endif; ?>

            <?php if ($phoneLink): ?>
                <a class="btn btn-secondary" href="<?= htmlspecialchars($phoneLink, ENT_QUOTES, 'UTF-8') ?>">Zavolat</a>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<div class="form-grid">
    <div>
        <div class="card">
            <h3>Základní informace</h3>
            <div class="quick-grid">
                <div><strong>ID:</strong></div><div><?= (int) $player['id'] ?></div>
                <div><strong>Nickname:</strong></div><div><?= htmlspecialchars($player['nickname'], ENT_QUOTES, 'UTF-8') ?></div>
                <div><strong>Telefon:</strong></div><div><?= !empty($player['phone']) ? htmlspecialchars((string)$player['phone'], ENT_QUOTES, 'UTF-8') : '-' ?></div>
                <div><strong>Registrace:</strong></div><div><?= htmlspecialchars((string)$player['registered_at'], ENT_QUOTES, 'UTF-8') ?></div>
                <div><strong>Poslední aktivita:</strong></div><div><?= $player['last_seen_at'] ? htmlspecialchars((string)$player['last_seen_at'], ENT_QUOTES, 'UTF-8') : 'Nikdy' ?></div>
                <div><strong>Přesnost GPS:</strong></div><div><?= $player['last_accuracy'] ? round((float)$player['last_accuracy'], 1) . ' m' : '-' ?></div>
            </div>

            <?php if ($phoneLink): ?>
                <div class="small-actions">
                    <a class="btn btn-secondary" href="<?= htmlspecialchars($phoneLink, ENT_QUOTES, 'UTF-8') ?>">Zavolat hráči</a>
                </div>
            <?php endif; ?>
        </div>

        <div class="card" style="margin-top: 20px;">
            <h3>Rychlý stav hráče</h3>
            <div class="quick-grid">
                <div><strong>Poslední kontakt:</strong></div>
                <div><?= !empty($player['last_seen_at']) ? htmlspecialchars((string)$player['last_seen_at'], ENT_QUOTES, 'UTF-8') : 'Neznámý' ?></div>

                <div><strong>Poslední známá poloha:</strong></div>
                <div>
                    <?php if ($lastKnownPosition): ?>
                        <span style="font-family: monospace;">
                            <?= round((float)$lastKnownPosition['lat'], 6) ?>, <?= round((float)$lastKnownPosition['lon'], 6) ?>
                        </span>
                    <?php else: ?>
                        Neznámá
                    <?php endif; ?>
                </div>

                <div><strong>Přesnost poslední polohy:</strong></div>
                <div>
                    <?php if ($lastKnownPosition && isset($lastKnownPosition['accuracy'])): ?>
                        <?= round((float)$lastKnownPosition['accuracy'], 1) ?> m
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </div>

                <div><strong>Záznam pohybu:</strong></div>
                <div><?= count($locationHistory) ?> bodů</div>
            </div>

            <?php if ($mapsUrl): ?>
                <div class="small-actions">
                    <a id="selected-location-link" class="btn btn-secondary" href="<?= htmlspecialchars($mapsUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">Otevřít zobrazenou polohu v mapách</a>
                </div>
            <?php endif; ?>
        </div>

        <div class="card" style="margin-top: 20px;">
            <h3>Poslední události hráče</h3>
            <?php if (empty($recentEvents)): ?>
                <p class="help">Zatím nejsou k dispozici žádné události.</p>
            <?php else: ?>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Typ</th>
                                <th>Detail</th>
                                <th>Čas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentEvents as $event): ?>
                                <tr>
                                    <td><span class="event-chip"><?= htmlspecialchars(eventLabel($event), ENT_QUOTES, 'UTF-8') ?></span></td>
                                    <td><?= htmlspecialchars(eventDetail($event), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td style="font-size: 12px;"><?= htmlspecialchars((string)$event['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <p class="mini-help" style="margin-top: 10px;">Tahle sekce pomáhá rychle pochopit, co hráč dělal před žádostí o pomoc.</p>
            <?php endif; ?>
        </div>

        <div class="card" style="margin-top: 20px;">
            <h3>Sebrané poklady</h3>
            <?php if (empty($claimedTreasures)): ?>
                <p class="help">Zatím nebyly sebrány žádné poklady.</p>
            <?php else: ?>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Poklad</th>
                                <th>Body</th>
                                <th>Kdy</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($claimedTreasures as $claim): ?>
                                <tr>
                                    <td><?= htmlspecialchars((string)$claim['name'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><strong><?= (int) $claim['points'] ?></strong></td>
                                    <td style="font-size: 12px;"><?= htmlspecialchars((string)$claim['claimed_at'], ENT_QUOTES, 'UTF-8') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div>
        <div class="card">
            <div class="map-title-row">
                <h3 id="player-map-title">Poslední známá poloha</h3>
            </div>
            <div id="player-map-subtitle" class="map-subtitle">
                <?php if ($lastKnownPosition): ?>
                    Zobrazená poloha odpovídá poslednímu známému bodu hráče.
                <?php else: ?>
                    Zatím není k dispozici žádný bod polohy.
                <?php endif; ?>
            </div>
            <div id="map" style="height: 400px; border-radius: 12px; border: 1px solid var(--line); z-index: 1;"></div>
            <?php if (!$player['last_lat'] && !$lastKnownPosition): ?>
                 <p class="help" style="margin-top: 10px;">Poloha hráče zatím není známa.</p>
            <?php endif; ?>
        </div>

        <div class="card" style="margin-top: 20px;">
            <h3>Historie pohybu (posledních 100 bodů)</h3>
            <?php if (empty($locationHistory)): ?>
                <p class="help">Žádné záznamy o pohybu.</p>
            <?php else: ?>
                <p class="mini-help" style="margin-bottom: 10px;">Kliknutím na řádek přesuneš mapu na vybraný bod a uvidíš stav hráče v daném čase.</p>
                <div class="table-wrap" style="max-height: 300px; overflow-y: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Čas</th>
                                <th>Souřadnice</th>
                                <th>Přesnost</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($locationHistory as $index => $log): ?>
                                <tr class="location-row<?= $index === 0 ? ' is-selected' : '' ?>"
                                    data-lat="<?= htmlspecialchars((string)$log['lat'], ENT_QUOTES, 'UTF-8') ?>"
                                    data-lon="<?= htmlspecialchars((string)$log['lon'], ENT_QUOTES, 'UTF-8') ?>"
                                    data-accuracy="<?= htmlspecialchars((string)$log['accuracy'], ENT_QUOTES, 'UTF-8') ?>"
                                    data-created-at="<?= htmlspecialchars((string)$log['created_at'], ENT_QUOTES, 'UTF-8') ?>">
                                    <td style="font-size: 12px;"><?= htmlspecialchars((string)$log['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td style="font-size: 12px; font-family: monospace;">
                                        <?= round((float)$log['lat'], 6) ?>, <?= round((float)$log['lon'], 6) ?>
                                    </td>
                                    <td style="font-size: 12px;"><?= round((float)$log['accuracy'], 1) ?> m</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    async function acknowledgeHelp(helpId) {
        try {
            const response = await fetch(`/admin/api/help/${helpId}/acknowledge`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });

            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }

            window.location.reload();
        } catch (error) {
            alert('Nepodařilo se převzít SOS.');
        }
    }

    async function resolveHelp(helpId) {
        try {
            const response = await fetch(`/admin/api/help/${helpId}/resolve`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });

            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }

            window.location.reload();
        } catch (error) {
            alert('Nepodařilo se uzavřít SOS.');
        }
    }

    const playerLat = <?= $player['last_lat'] !== null ? (float)$player['last_lat'] : 'null' ?>;
    const playerLon = <?= $player['last_lon'] !== null ? (float)$player['last_lon'] : 'null' ?>;

    const fallbackLat = <?= $lastKnownPosition ? (float)$lastKnownPosition['lat'] : 'null' ?>;
    const fallbackLon = <?= $lastKnownPosition ? (float)$lastKnownPosition['lon'] : 'null' ?>;

    const effectiveLat = playerLat !== null ? playerLat : fallbackLat;
    const effectiveLon = playerLon !== null ? playerLon : fallbackLon;

    const mapCenter = [
        effectiveLat !== null ? effectiveLat : <?= (float)($game['map_center_lat'] ?? 50.0755) ?>,
        effectiveLon !== null ? effectiveLon : <?= (float)($game['map_center_lon'] ?? 14.4378) ?>
    ];

    const map = L.map('map').setView(
        mapCenter,
        effectiveLat !== null && effectiveLon !== null ? 16 : <?= (int)($game['map_default_zoom'] ?? 14) ?>
    );

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    <?php if (!empty($locationHistory)): ?>
    const pathPoints = [
        <?php foreach (array_reverse($locationHistory) as $log): ?>
            [<?= (float)$log['lat'] ?>, <?= (float)$log['lon'] ?>],
        <?php endforeach; ?>
    ];
    <?php else: ?>
    const pathPoints = [];
    <?php endif; ?>

    let pathLine = null;
    if (pathPoints.length > 0) {
        pathLine = L.polyline(pathPoints, { color: 'red', weight: 3, opacity: 0.5 }).addTo(map);
    }

    let selectedMarker = null;

    function mapsUrlFor(lat, lon) {
        return `https://maps.google.com/?q=${encodeURIComponent(String(lat) + ',' + String(lon))}`;
    }

    function updateMapTexts(createdAt, accuracy, lat, lon) {
        const titleEl = document.getElementById('player-map-title');
        const subtitleEl = document.getElementById('player-map-subtitle');
        const linkEl = document.getElementById('selected-location-link');

        if (titleEl) {
            titleEl.textContent = createdAt ? 'Poloha hráče k ' + createdAt : 'Poslední známá poloha';
        }

        if (subtitleEl) {
            const accuracyText = accuracy !== null && accuracy !== '' ? `${Number(accuracy).toFixed(1)} m` : '-';
            subtitleEl.textContent = `Souřadnice ${Number(lat).toFixed(6)}, ${Number(lon).toFixed(6)} | Přesnost ${accuracyText}`;
        }

        if (linkEl) {
            linkEl.href = mapsUrlFor(lat, lon);
        }
    }

    function focusLocation(lat, lon, createdAt, accuracy) {
        if (selectedMarker) {
            map.removeLayer(selectedMarker);
        }

        selectedMarker = L.marker([lat, lon]).addTo(map);
        selectedMarker
            .bindPopup(
                `<strong><?= htmlspecialchars($player['nickname'], ENT_QUOTES, 'UTF-8') ?></strong><br>` +
                `Poloha k: ${createdAt || 'neznámý čas'}<br>` +
                `Přesnost: ${accuracy !== null && accuracy !== '' ? Number(accuracy).toFixed(1) + ' m' : '-'}`
            )
            .openPopup();

        map.setView([lat, lon], Math.max(map.getZoom(), 17), { animate: true });
        updateMapTexts(createdAt, accuracy, lat, lon);
    }

    document.querySelectorAll('.location-row').forEach((row) => {
        row.addEventListener('click', () => {
            document.querySelectorAll('.location-row').forEach((item) => item.classList.remove('is-selected'));
            row.classList.add('is-selected');

            const lat = Number(row.dataset.lat);
            const lon = Number(row.dataset.lon);
            const accuracy = row.dataset.accuracy ?? '';
            const createdAt = row.dataset.createdAt ?? '';

            if (!Number.isNaN(lat) && !Number.isNaN(lon)) {
                focusLocation(lat, lon, createdAt, accuracy);
            }
        });
    });

    if (effectiveLat !== null && effectiveLon !== null) {
        focusLocation(effectiveLat, effectiveLon, <?= json_encode($player['last_seen_at'] ?? ($lastKnownPosition['created_at'] ?? null), JSON_UNESCAPED_UNICODE) ?>, <?= json_encode($player['last_accuracy'] ?? ($lastKnownPosition['accuracy'] ?? null), JSON_UNESCAPED_UNICODE) ?>);
    }
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
