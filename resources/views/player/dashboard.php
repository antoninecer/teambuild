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
            background: rgba(255,255,255,0.9); padding: 10px; border-radius: 8px;
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
            background: #fff; padding: 20px; border-radius: 12px; width: 85%; max-width: 400px;
        }
        .modal-content h2 { margin-top: 0; }
        .modal-content textarea { width: 100%; height: 100px; margin: 10px 0; border: 1px solid #ccc; padding: 10px; box-sizing: border-box; }
        .modal-btns { display: flex; gap: 10px; }
        .modal-btn { flex: 1; padding: 12px; border: none; border-radius: 6px; font-weight: bold; }
        
        .accuracy-warn {
            position: absolute; top: 60px; left: 50%; transform: translateX(-50%);
            background: #ff9800; color: #fff; padding: 5px 15px; border-radius: 20px;
            font-size: 12px; display: none; z-index: 1000;
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
        <button class="btn btn-info" onclick="location.reload()">OBNOVIT</button>
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

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const gameSlug = "<?= $game['slug'] ?>";
        const mapCenter = [<?= (float) ($game['map_center_lat'] ?? 50.0755) ?>, <?= (float) ($game['map_center_lon'] ?? 14.4378) ?>];
        const mapZoom = <?= (int) ($game['map_default_zoom'] ?? 14) ?>;
        
        const map = L.map('map', { zoomControl: false }).setView(mapCenter, mapZoom);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OSM'
        }).addTo(map);

        let userMarker = null;
        let userCircle = null;
        let lastPos = null;

        function updateLocation(pos) {
            const lat = pos.coords.latitude;
            const lon = pos.coords.longitude;
            const acc = pos.coords.accuracy;
            lastPos = { lat, lon, acc };

            document.getElementById('status').innerText = "GPS OK (" + Math.round(acc) + "m)";
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

            // Odeslat na server
            fetch('/api/player/location', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ lat, lon, accuracy: acc })
            });
        }

        function handleError(err) {
            console.warn('ERROR(' + err.code + '): ' + err.message);
            document.getElementById('status').innerText = "Chyba GPS: " + err.message;
        }

        if (navigator.geolocation) {
            navigator.geolocation.watchPosition(updateLocation, handleError, {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            });
        } else {
            alert("Váš prohlížeč nepodporuje GPS.");
        }

        // Help Modal
        function openHelp() { document.getElementById('helpModal').style.display = 'flex'; }
        function closeHelp() { document.getElementById('helpModal').style.display = 'none'; }
        
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
                alert("Žádost o pomoc byla odeslána. Organizátor o vás ví.");
                closeHelp();
            });
        }

        // Jednoduchý časovač hry
        const endsAt = new Date("<?= $game['ends_at'] ?>").getTime();
        setInterval(function() {
            const now = new Date().getTime();
            const dist = endsAt - now;
            if (dist < 0) {
                document.getElementById("timer").innerHTML = "KONEC HRY";
                return;
            }
            const hours = Math.floor((dist % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((dist % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((dist % (1000 * 60)) / 1000);
            document.getElementById("timer").innerHTML = hours + "h " + minutes + "m " + seconds + "s";
        }, 1000);
    </script>
</body>
</html>
