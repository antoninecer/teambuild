<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Hra: <?= htmlspecialchars($game['name'], ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        body, html { margin: 0; padding: 0; height: 100%; font-family: sans-serif; overflow: hidden; }
        #map { height: 100%; width: 100%; z-index: 1; }

        .ui-overlay {
            position: absolute; top: 10px; left: 10px; right: 10px; z-index: 1000;
            display: flex; justify-content: space-between; pointer-events: none;
        }
        .ui-box {
            background: rgba(255,255,255,0.92); padding: 10px; border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2); pointer-events: auto;
        }

        .bottom-actions {
            position: absolute; bottom: 20px; left: 10px; right: 10px; z-index: 1000;
            display: flex; gap: 10px;
        }
        .btn {
            flex: 1; padding: 15px; border: none; border-radius: 8px; font-weight: bold;
            font-size: 16px; box-shadow: 0 4px 10px rgba(0,0,0,0.3); cursor: pointer;
        }
        .btn-help { background: #d32f2f; color: #fff; }
        .btn-info { background: #1976d2; color: #fff; }

        .modal {
            display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.7); z-index: 2000; justify-content: center; align-items: center;
        }
        .modal-content {
            background: #fff; padding: 20px; border-radius: 12px; width: 85%; max-width: 420px;
            max-height: 80vh; overflow: auto;
        }
        .modal-content h2 { margin-top: 0; }
        .modal-content textarea {
            width: 100%; height: 100px; margin: 10px 0; border: 1px solid #ccc;
            padding: 10px; box-sizing: border-box;
        }
        .modal-btns { display: flex; gap: 10px; flex-wrap: wrap; }
        .modal-btn {
            flex: 1; padding: 12px; border: none; border-radius: 6px; font-weight: bold; cursor: pointer;
        }

        .accuracy-warn {
            position: absolute; top: 60px; left: 50%; transform: translateX(-50%);
            background: #ff9800; color: #fff; padding: 5px 15px; border-radius: 20px;
            font-size: 12px; display: none; z-index: 1000;
        }

        .poi-type {
            display: inline-block;
            font-size: 12px;
            font-weight: bold;
            color: #444;
            background: #f0f0f0;
            border-radius: 999px;
            padding: 4px 8px;
            margin-bottom: 10px;
        }

        .poi-text {
            white-space: pre-line;
            line-height: 1.45;
            color: #222;
            margin-bottom: 14px;
        }

        .poi-meta {
            font-size: 13px;
            color: #666;
            margin-bottom: 12px;
        }
    </style>
</head>
<body>
    <div id="map"></div>

    <div class="ui-overlay">
        <div class="ui-box">
            <strong><?= htmlspecialchars($player['nickname'], ENT_QUOTES, 'UTF-8') ?></strong>
            <div id="status">Hledám GPS...</div>
        </div>
        <div class="ui-box">
            <div id="timer">--:--:--</div>
        </div>
    </div>

    <div id="accuracy-warn" class="accuracy-warn">Slabý signál GPS</div>

    <div class="bottom-actions">
        <button class="btn btn-help" onclick="openHelp()">POMOC / SOS</button>
        <button class="btn btn-info" onclick="reloadMapData()">OBNOVIT</button>
    </div>

    <div id="helpModal" class="modal">
        <div class="modal-content">
            <h2>Žádost o pomoc</h2>
            <p>Potřebujete pomoc organizátora nebo týmu?</p>
            <textarea id="helpMsg" placeholder="Napište, co se děje (např. zranění, ztráta orientace...)"></textarea>
            <div class="modal-btns">
                <button class="modal-btn" style="background:#eee;" onclick="closeHelp()">ZRUŠIT</button>
                <button class="modal-btn" style="background:#d32f2f; color:#fff;" onclick="sendHelp()">ODESLAT POMOC</button>
            </div>
        </div>
    </div>

    <div id="poiModal" class="modal">
        <div class="modal-content">
            <h2 id="poiTitle">Detail</h2>
            <div id="poiType" class="poi-type" style="display:none;"></div>
            <div id="poiMeta" class="poi-meta"></div>
            <div id="poiText" class="poi-text"></div>
            <div class="modal-btns">
                <button class="modal-btn" style="background:#1976d2; color:#fff;" onclick="speakCurrentText()">PŘEČÍST NAHLAS</button>
                <button class="modal-btn" style="background:#757575; color:#fff;" onclick="stopSpeech()">ZASTAVIT</button>
                <button id="claimBtn" class="modal-btn" style="background:#2e7d32; color:#fff; display:none;" onclick="claimCurrentTreasure()">SEBRAT POKLAD</button>
                <button class="modal-btn" style="background:#eee;" onclick="closePoiModal()">ZAVŘÍT</button>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const gameSlug = "<?= htmlspecialchars($game['slug'], ENT_QUOTES, 'UTF-8') ?>";
        const mapCenter = [<?= (float) ($game['map_center_lat'] ?? 50.0755) ?>, <?= (float) ($game['map_center_lon'] ?? 14.4378) ?>];
        const mapZoom = <?= (int) ($game['map_default_zoom'] ?? 14) ?>;

        const map = L.map('map', { zoomControl: false }).setView(mapCenter, mapZoom);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OSM'
        }).addTo(map);

        let userMarker = null;
        let userCircle = null;
        let lastPos = null;

        let poiMarkers = [];
        let treasureMarkers = [];
        let pois = [];
        let treasures = [];
        let openedAutoKeys = new Set();
        let currentDetail = null;
        let speechUtterance = null;

        function escapeHtml(str) {
            return String(str ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        function distanceMeters(lat1, lon1, lat2, lon2) {
            const R = 6371000;
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLon = (lon2 - lon1) * Math.PI / 180;
            const a =
                Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                Math.sin(dLon / 2) * Math.sin(dLon / 2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            return R * c;
        }

        function updateLocation(pos) {
            const lat = pos.coords.latitude;
            const lon = pos.coords.longitude;
            const acc = pos.coords.accuracy;
            lastPos = { lat, lon, acc };

            document.getElementById('status').innerText = 'GPS OK (' + Math.round(acc) + 'm)';
            document.getElementById('accuracy-warn').style.display = (acc > 50) ? 'block' : 'none';

            if (!userMarker) {
                userMarker = L.marker([lat, lon]).addTo(map);
                userCircle = L.circle([lat, lon], { radius: acc, fillOpacity: 0.1 }).addTo(map);
                map.setView([lat, lon], 16);
            } else {
                userMarker.setLatLng([lat, lon]);
                userCircle.setLatLng([lat, lon]);
                userCircle.setRadius(acc);
            }

            fetch('/api/player/location', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ lat, lon, accuracy: acc })
            }).catch(console.warn);

            checkNearbyAutoOpen();
        }

        function handleError(err) {
            console.warn('ERROR(' + err.code + '): ' + err.message);
            document.getElementById('status').innerText = 'Chyba GPS: ' + err.message;
        }

        if (navigator.geolocation) {
            navigator.geolocation.watchPosition(updateLocation, handleError, {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            });
        } else {
            alert('Váš prohlížeč nepodporuje GPS.');
        }

        function clearMarkers() {
            poiMarkers.forEach(marker => map.removeLayer(marker));
            treasureMarkers.forEach(marker => map.removeLayer(marker));
            poiMarkers = [];
            treasureMarkers = [];
        }

        function renderPois() {
            pois.forEach(poi => {
                const marker = L.marker([parseFloat(poi.lat), parseFloat(poi.lon)]).addTo(map);
                marker.bindPopup('<strong>' + escapeHtml(poi.name) + '</strong>');
                marker.on('click', () => openPoiDetail(poi));
                poiMarkers.push(marker);
            });
        }

        function renderTreasures() {
            treasures.forEach(treasure => {
                const lat = parseFloat(treasure.lat);
                const lon = parseFloat(treasure.lon);

                let label = 'Poklad';
                if (treasure.claimed_by_player == 1 || treasure.claimed_by_team == 1) {
                    label = 'Poklad (sebrán)';
                }

                const marker = L.circleMarker([lat, lon], {
                    radius: 8,
                    weight: 2,
                    opacity: 1,
                    fillOpacity: 0.85
                }).addTo(map);

                marker.bindPopup('<strong>' + escapeHtml(treasure.name) + '</strong><br>' + escapeHtml(label));
                marker.on('click', () => openTreasureDetail(treasure));
                treasureMarkers.push(marker);
            });
        }

        function reloadMapData() {
            fetch('/api/player/map-data')
                .then(r => r.json())
                .then(data => {
                    if (!data.success) {
                        return;
                    }

                    pois = Array.isArray(data.pois) ? data.pois : [];
                    treasures = Array.isArray(data.treasures) ? data.treasures : [];

                    clearMarkers();
                    renderPois();
                    renderTreasures();
                    checkNearbyAutoOpen();
                })
                .catch(err => {
                    console.warn(err);
                });
        }

        function openPoiDetail(poi) {
            currentDetail = {
                kind: 'poi',
                id: Number(poi.id),
                title: poi.name || 'Bod',
                text: poi.tts_text || poi.story_text || poi.description || 'Tento bod zatím nemá popis.',
                radius_m: Number(poi.radius_m || 0),
                lat: Number(poi.lat),
                lon: Number(poi.lon),
                type: poi.type || 'poi',
            };

            document.getElementById('poiTitle').innerText = currentDetail.title;
            document.getElementById('poiType').style.display = 'inline-block';
            document.getElementById('poiType').innerText = currentDetail.type;
            document.getElementById('poiText').innerText = currentDetail.text;
            document.getElementById('claimBtn').style.display = 'none';

            let meta = '';
            if (lastPos) {
                const dist = distanceMeters(lastPos.lat, lastPos.lon, currentDetail.lat, currentDetail.lon);
                meta = 'Vzdálenost: ' + Math.round(dist) + ' m | Radius: ' + Math.round(currentDetail.radius_m) + ' m';
            } else {
                meta = 'Radius: ' + Math.round(currentDetail.radius_m) + ' m';
            }
            document.getElementById('poiMeta').innerText = meta;

            document.getElementById('poiModal').style.display = 'flex';
        }

        function openTreasureDetail(treasure) {
            currentDetail = {
                kind: 'treasure',
                id: Number(treasure.id),
                title: treasure.name || 'Poklad',
                text: treasure.description || 'Tento poklad zatím nemá popis.',
                radius_m: Number(treasure.radius_m || 0),
                lat: Number(treasure.lat),
                lon: Number(treasure.lon),
                type: 'treasure',
                claimed_by_player: Number(treasure.claimed_by_player || 0),
                claimed_by_team: Number(treasure.claimed_by_team || 0),
            };

            document.getElementById('poiTitle').innerText = currentDetail.title;
            document.getElementById('poiType').style.display = 'inline-block';
            document.getElementById('poiType').innerText = 'treasure';
            document.getElementById('poiText').innerText = currentDetail.text;

            let canClaim = true;
            let meta = '';

            if (lastPos) {
                const dist = distanceMeters(lastPos.lat, lastPos.lon, currentDetail.lat, currentDetail.lon);
                meta = 'Vzdálenost: ' + Math.round(dist) + ' m | Radius: ' + Math.round(currentDetail.radius_m) + ' m';

                if (dist > currentDetail.radius_m) {
                    canClaim = false;
                    meta += ' | Jsi zatím mimo dosah';
                }
            } else {
                meta = 'Radius: ' + Math.round(currentDetail.radius_m) + ' m';
            }

            if (currentDetail.claimed_by_player === 1 || currentDetail.claimed_by_team === 1) {
                canClaim = false;
                meta += ' | Už sebráno';
            }

            document.getElementById('poiMeta').innerText = meta;
            document.getElementById('claimBtn').style.display = canClaim ? 'inline-block' : 'none';

            document.getElementById('poiModal').style.display = 'flex';
        }

        function closePoiModal() {
            stopSpeech();
            document.getElementById('poiModal').style.display = 'none';
        }

        function speakCurrentText() {
            if (!currentDetail || !currentDetail.text) {
                return;
            }

            stopSpeech();

            if (!('speechSynthesis' in window)) {
                alert('Tento prohlížeč nepodporuje hlasové čtení.');
                return;
            }

            speechUtterance = new SpeechSynthesisUtterance(currentDetail.text);
            speechUtterance.lang = 'cs-CZ';
            window.speechSynthesis.speak(speechUtterance);
        }

        function stopSpeech() {
            if ('speechSynthesis' in window) {
                window.speechSynthesis.cancel();
            }
        }

        function claimCurrentTreasure() {
            if (!currentDetail || currentDetail.kind !== 'treasure') {
                return;
            }

            if (!lastPos) {
                alert('Neznám tvoji aktuální polohu.');
                return;
            }

            fetch('/api/player/claim', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    treasure_id: currentDetail.id,
                    lat: lastPos.lat,
                    lon: lastPos.lon
                })
            })
            .then(async response => {
                const data = await response.json();

                if (data.success) {
                    alert('Poklad byl úspěšně sebrán.');
                    closePoiModal();
                    reloadMapData();
                    return;
                }

                const status = data.status || 'error';

                if (status === 'too_far') {
                    alert('Jsi příliš daleko od pokladu.');
                } else if (status === 'already_claimed') {
                    alert('Tento poklad už máš sebraný.');
                } else if (status === 'already_claimed_team') {
                    alert('Tento týmový poklad už byl sebrán.');
                } else if (status === 'empty') {
                    alert('Poklad už byl vyčerpán.');
                } else if (status === 'not_found') {
                    alert('Poklad nebyl nalezen.');
                } else {
                    alert('Claim se nepodařilo dokončit.');
                }

                reloadMapData();
            })
            .catch(err => {
                console.warn(err);
                alert('Chyba komunikace se serverem.');
            });
        }

        function checkNearbyAutoOpen() {
            if (!lastPos) {
                return;
            }

            pois.forEach(poi => {
                const dist = distanceMeters(lastPos.lat, lastPos.lon, Number(poi.lat), Number(poi.lon));
                const key = 'poi-' + poi.id;

                if (dist <= Number(poi.radius_m || 0) && !openedAutoKeys.has(key)) {
                    openedAutoKeys.add(key);
                    openPoiDetail(poi);
                }
            });

            treasures.forEach(treasure => {
                const dist = distanceMeters(lastPos.lat, lastPos.lon, Number(treasure.lat), Number(treasure.lon));
                const key = 'treasure-' + treasure.id;

                if (dist <= Number(treasure.radius_m || 0) && !openedAutoKeys.has(key)) {
                    openedAutoKeys.add(key);
                    openTreasureDetail(treasure);
                }
            });
        }

        function openHelp() {
            document.getElementById('helpModal').style.display = 'flex';
        }

        function closeHelp() {
            document.getElementById('helpModal').style.display = 'none';
        }

        function sendHelp() {
            const msg = document.getElementById('helpMsg').value;
            const data = { message: msg };

            if (lastPos) {
                data.lat = lastPos.lat;
                data.lon = lastPos.lon;
            }

            fetch('/api/player/help', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            }).then(() => {
                alert('Žádost o pomoc byla odeslána. Organizátor o vás ví.');
                closeHelp();
            });
        }

        const endsAt = new Date("<?= $game['ends_at'] ?>").getTime();
        setInterval(function () {
            const now = new Date().getTime();
            const dist = endsAt - now;

            if (dist < 0) {
                document.getElementById('timer').innerHTML = 'KONEC HRY';
                return;
            }

            const hours = Math.floor((dist % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((dist % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((dist % (1000 * 60)) / 1000);
            document.getElementById('timer').innerHTML = hours + 'h ' + minutes + 'm ' + seconds + 's';
        }, 1000);

        reloadMapData();
    </script>
</body>
</html>