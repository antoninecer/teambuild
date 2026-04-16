<?php
/** @var array $player */
/** @var array $game */
/** @var array $claimedTreasures */
/** @var array $locationHistory */

$pageTitle = 'Detail hráče';
$pageSubtitle = $player['nickname'] . ' (Hra: ' . $game['name'] . ')';
$activeNav = 'games';

require __DIR__ . '/../partials/header.php';
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>

<div class="page-actions">
    <a class="btn btn-secondary" href="/admin/games/<?= (int) $game['id'] ?>">← Zpět na detail hry</a>
</div>

<div class="form-grid">
    <div>
        <div class="card">
            <h3>Základní informace</h3>
            <div style="display: grid; grid-template-columns: 140px 1fr; gap: 8px; font-size: 15px;">
                <div><strong>ID:</strong></div><div><?= (int) $player['id'] ?></div>
                <div><strong>Nickname:</strong></div><div><?= htmlspecialchars($player['nickname'], ENT_QUOTES, 'UTF-8') ?></div>
                <div><strong>Registrace:</strong></div><div><?= htmlspecialchars($player['registered_at'], ENT_QUOTES, 'UTF-8') ?></div>
                <div><strong>Poslední aktivita:</strong></div><div><?= $player['last_seen_at'] ? htmlspecialchars($player['last_seen_at'], ENT_QUOTES, 'UTF-8') : 'Nikdy' ?></div>
                <div><strong>Přesnost GPS:</strong></div><div><?= $player['last_accuracy'] ? round((float)$player['last_accuracy'], 1) . ' m' : '-' ?></div>
            </div>
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
                                    <td><?= htmlspecialchars($claim['name'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><strong><?= (int) $claim['points'] ?></strong></td>
                                    <td style="font-size: 12px;"><?= htmlspecialchars($claim['claimed_at'], ENT_QUOTES, 'UTF-8') ?></td>
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
                                    <td style="font-size: 12px;"><?= htmlspecialchars($log['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
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
