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

$mapsUrl = null;
if (!empty($player['last_lat']) && !empty($player['last_lon'])) {
    $mapsUrl = 'https://maps.google.com/?q=' . rawurlencode((string)$player['last_lat'] . ',' . (string)$player['last_lon']);
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
                <div><strong>Registrace:</strong></div><div><?= htmlspecialchars((string)$player['registered_at'], ENT_QUOTES, 'UTF-8') ?></div>
                <div><strong>Poslední aktivita:</strong></div><div><?= $player['last_seen_at'] ? htmlspecialchars((string)$player['last_seen_at'], ENT_QUOTES, 'UTF-8') : 'Nikdy' ?></div>
                <div><strong>Přesnost GPS:</strong></div><div><?= $player['last_accuracy'] ? round((float)$player['last_accuracy'], 1) . ' m' : '-' ?></div>
            </div>
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
                    <a class="btn btn-secondary" href="<?= htmlspecialchars($mapsUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">Otevřít poslední polohu v mapách</a>
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
            <h3>Poslední známá poloha</h3>
            <div id="map" style="height: 400px; border-radius: 12px; border: 1px solid var(--line); z-index: 1;"></div>
            <?php if (!$player['last_lat']): ?>
                <p class="help" style="margin-top: 10px;">Poloha hráče zatím není známa.</p>
            <?php endif; ?>
        </div>

        <div class="card" style="margin-top: 20px;">
            <h3>Historie pohybu (posledních 100 bodů)</h3>
            <?php if (empty($locationHistory)): ?>
                <p class="help">Žádné záznamy o pohybu.</p>
            <?php else: ?>
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
                            <?php foreach ($locationHistory as $log): ?>
                                <tr>
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

    const playerLat = <?= $player['last_lat'] ? (float)$player['last_lat'] : 'null' ?>;
    const playerLon = <?= $player['last_lon'] ? (float)$player['last_lon'] : 'null' ?>;
    const mapCenter = [
        playerLat || <?= (float)($game['map_center_lat'] ?? 50.0755) ?>,
        playerLon || <?= (float)($game['map_center_lon'] ?? 14.4378) ?>
    ];

    const map = L.map('map').setView(mapCenter, playerLat ? 16 : <?= (int)($game['map_default_zoom'] ?? 14) ?>);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    if (playerLat && playerLon) {
        L.marker([playerLat, playerLon]).addTo(map)
            .bindPopup("<?= htmlspecialchars($player['nickname'], ENT_QUOTES, 'UTF-8') ?>")
            .openPopup();

        <?php if (!empty($locationHistory)): ?>
            const pathPoints = [
                <?php foreach (array_reverse($locationHistory) as $log): ?>
                    [<?= (float)$log['lat'] ?>, <?= (float)$log['lon'] ?>],
                <?php endforeach; ?>
            ];
            L.polyline(pathPoints, {color: 'red', weight: 3, opacity: 0.5}).addTo(map);
        <?php endif; ?>
    }
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>