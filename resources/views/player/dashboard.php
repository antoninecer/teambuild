<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Hra: <?= htmlspecialchars($game['name'], ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            font-family: sans-serif;
            overflow: hidden;
        }

        #map {
            height: 100%;
            width: 100%;
            z-index: 1;
        }

        .ui-overlay {
            position: absolute;
            top: 10px;
            left: 10px;
            right: 10px;
            z-index: 1000;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            pointer-events: none;
            gap: 10px;
        }

        .ui-box {
            background: rgba(255,255,255,0.04);
            backdrop-filter: blur(22px) saturate(150%);
            -webkit-backdrop-filter: blur(22px) saturate(150%);
            border-radius: 16px;
            border: 1px solid rgba(255,255,255,0.14);
            box-shadow:
                0 2px 10px rgba(0,0,0,0.06),
                inset 0 1px 0 rgba(255,255,255,0.10);
            pointer-events: auto;
            color: rgba(18,18,18,0.88);
        }

        .player-box {
            cursor: pointer;
            min-width: 170px;
            padding: 11px 13px;
        }

        .context-box {
            cursor: pointer;
            min-width: 86px;
            text-align: center;
            padding: 10px 12px;
        }

        .player-name {
            font-weight: 700;
            font-size: 15px;
            line-height: 1.2;
            margin-bottom: 4px;
            color: rgba(20,20,20,0.92);
        }

        .player-subline {
            font-size: 12px;
            line-height: 1.2;
            color: rgba(20,20,20,0.72);
        }

        .context-icon {
            font-size: 13px;
            font-weight: 700;
            line-height: 1.1;
            color: rgba(20,20,20,0.88);
        }

        .context-subline {
            margin-top: 3px;
            font-size: 11px;
            color: rgba(20,20,20,0.68);
            line-height: 1.1;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.62);
            z-index: 2000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: #fff;
            padding: 20px;
            border-radius: 16px;
            width: 88%;
            max-width: 460px;
            max-height: 82vh;
            overflow: auto;
            box-shadow: 0 14px 28px rgba(0,0,0,0.28);
        }

        .glass-modal {
            background: rgba(255,255,255,0.10);
            backdrop-filter: blur(26px) saturate(150%);
            -webkit-backdrop-filter: blur(26px) saturate(150%);
            border: 1px solid rgba(255,255,255,0.16);
            box-shadow:
                0 8px 24px rgba(0,0,0,0.12),
                inset 0 1px 0 rgba(255,255,255,0.10);
            color: rgba(18,18,18,0.94);
        }

        .modal-content h2 {
            margin-top: 0;
        }

        .modal-content textarea {
            width: 100%;
            height: 100px;
            margin: 10px 0;
            border: 1px solid rgba(0,0,0,0.14);
            padding: 10px;
            box-sizing: border-box;
            border-radius: 10px;
            background: rgba(255,255,255,0.84);
        }

        .modal-btns {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .modal-btn {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
        }

        /* Vlastní styly markerů */
        .marker-user {
            width: 20px !important;
            height: 20px !important;
            background: #3b82f6;
            border: 3px solid white;
            border-radius: 50%;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.3), 0 2px 4px rgba(0,0,0,0.3);
        }
        
        .marker-poi {
            font-size: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            filter: drop-shadow(0 2px 3px rgba(0,0,0,0.4));
        }

        .marker-treasure {
            font-size: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            filter: drop-shadow(0 2px 3px rgba(0,0,0,0.4));
        }

        .marker-treasure.claimed {
            opacity: 0.4;
            filter: grayscale(1) drop-shadow(0 1px 1px rgba(0,0,0,0.2));
        }

            font-weight: bold;
            cursor: pointer;
        }

        .accuracy-warn {
            position: absolute;
            top: 66px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(255, 152, 0, 0.84);
            color: #fff;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            display: none;
            z-index: 1000;
            box-shadow: 0 3px 10px rgba(0,0,0,0.12);
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

        .poi-media {
            margin-bottom: 14px;
        }

        .poi-media img {
            display: block;
            width: 100%;
            height: auto;
            border-radius: 10px;
            margin-bottom: 10px;
        }

        .poi-media iframe {
            display: block;
            width: 100%;
            min-height: 220px;
            border: 0;
            border-radius: 10px;
            margin-bottom: 10px;
        }

        .player-card-note {
            font-size: 13px;
            color: rgba(20,20,20,0.74);
            margin-bottom: 16px;
        }

        .player-card-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin: 14px 0 18px;
        }

        .player-stat {
            background: rgba(255,255,255,0.22);
            border-radius: 14px;
            padding: 12px;
            border: 1px solid rgba(255,255,255,0.14);
        }

        .player-stat-label {
            font-size: 12px;
            color: rgba(20,20,20,0.64);
            margin-bottom: 4px;
        }

        .player-stat-value {
            font-size: 18px;
            font-weight: bold;
            color: rgba(15,15,15,0.92);
        }

        .player-progress {
            margin: 16px 0 18px;
        }

        .player-progress-top {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            font-size: 13px;
            margin-bottom: 8px;
            color: rgba(20,20,20,0.78);
        }

        .progress-track {
            width: 100%;
            height: 10px;
            border-radius: 999px;
            background: rgba(255,255,255,0.24);
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.16);
        }

        .progress-fill {
            height: 100%;
            width: 0%;
            border-radius: 999px;
            background: linear-gradient(90deg, rgba(25,118,210,0.82), rgba(76,175,80,0.82));
        }

        .leaderboard-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
            font-size: 14px;
        }

        .leaderboard-table th,
        .leaderboard-table td {
            padding: 10px 8px;
            text-align: left;
            border-bottom: 1px solid rgba(255,255,255,0.14);
        }

        .leaderboard-table th {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: rgba(20,20,20,0.60);
        }

        .leaderboard-highlight {
            background: rgba(255,255,255,0.18);
        }

        .results-summary {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 10px;
            margin-bottom: 12px;
        }

        .results-box {
            background: rgba(255,255,255,0.20);
            border-radius: 14px;
            padding: 12px;
            border: 1px solid rgba(255,255,255,0.14);
        }

        .results-label {
            font-size: 12px;
            color: rgba(20,20,20,0.62);
            margin-bottom: 4px;
        }

        .results-value {
            font-size: 18px;
            font-weight: 700;
            color: rgba(15,15,15,0.92);
        }

        .results-value-small {
            font-size: 15px;
            line-height: 1.25;
        }

        .results-box-wide {
            grid-column: span 2;
        }

        .player-help-panels {
            margin: 18px 0;
            display: grid;
            gap: 12px;
        }

        .player-help-card {
            background: rgba(255,255,255,0.82);
            border: 1px solid rgba(80,60,35,0.12);
            border-radius: 16px;
            overflow: hidden;
        }

        .player-help-card summary {
            cursor: pointer;
            padding: 14px 16px;
            font-weight: 700;
            font-size: 16px;
            list-style: none;
            user-select: none;
        }

        .player-help-card summary::-webkit-details-marker {
            display: none;
        }

        .player-help-card[open] summary {
            border-bottom: 1px solid rgba(80,60,35,0.10);
        }

        .help-section {
            padding: 14px 16px 16px;
        }

        .help-block + .help-block {
            margin-top: 14px;
        }

        .help-block h3 {
            margin: 0 0 8px;
            font-size: 15px;
            color: rgba(30,25,20,0.9);
        }

        .help-block p,
        .help-block li {
            font-size: 14px;
            line-height: 1.5;
            color: rgba(20,20,20,0.82);
        }

        .help-block ul,
        .help-block ol {
            margin: 8px 0 0 18px;
            padding: 0;
        }

        .explore-panel {
            position: absolute;
            left: 10px;
            right: 10px;
            bottom: 18px;
            z-index: 1000;
            pointer-events: none;
            display: flex;
            justify-content: center;
        }

        .explore-card {
            width: min(100%, 520px);
            background: rgba(255,255,255,0.10);
            backdrop-filter: blur(22px) saturate(150%);
            -webkit-backdrop-filter: blur(22px) saturate(150%);
            border-radius: 18px;
            border: 1px solid rgba(255,255,255,0.16);
            box-shadow: 0 8px 24px rgba(0,0,0,0.12), inset 0 1px 0 rgba(255,255,255,0.10);
            padding: 14px;
            pointer-events: auto;
        }

        .explore-title {
            font-size: 16px;
            font-weight: 700;
            color: rgba(15,15,15,0.92);
            margin-bottom: 4px;
        }

        .explore-subline {
            font-size: 13px;
            color: rgba(20,20,20,0.74);
            line-height: 1.35;
            margin-bottom: 12px;
        }

        .explore-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .explore-btn {
            flex: 1;
            min-width: 180px;
            padding: 13px 14px;
            border: none;
            border-radius: 12px;
            font-weight: 700;
            cursor: pointer;
        }

        .explore-list {
            display: grid;
            gap: 10px;
            margin-top: 14px;
        }

        .explore-option {
            width: 100%;
            text-align: left;
            padding: 12px 14px;
            border-radius: 12px;
            border: 1px solid rgba(0,0,0,0.08);
            background: rgba(255,255,255,0.88);
            cursor: pointer;
        }

        .explore-option-title {
            font-weight: 700;
            font-size: 15px;
            color: rgba(15,15,15,0.92);
            margin-bottom: 3px;
        }

        .explore-option-subline {
            font-size: 12px;
            color: rgba(20,20,20,0.68);
        }

        @media (max-width: 520px) {
            .ui-overlay {
                top: 8px;
                left: 8px;
                right: 8px;
                gap: 8px;
            }

            .player-box {
                min-width: 145px;
                padding: 10px 11px;
            }

            .context-box {
                min-width: 78px;
                padding: 9px 10px;
            }

            .player-card-grid,
            .results-summary {
                grid-template-columns: 1fr 1fr;
            }

            .leaderboard-table {
                font-size: 13px;
            }
        }
    </style>
</head>
<body>
    <?php require __DIR__ . '/partials/map_shell.php'; ?>

    <?php require __DIR__ . '/partials/player_modal.php'; ?>

    <?php require __DIR__ . '/partials/results_modal.php'; ?>

    <?php require __DIR__ . '/partials/explore_choice_modal.php'; ?>

    <?php require __DIR__ . '/partials/help_modal.php'; ?>

    <?php require __DIR__ . '/partials/poi_modal.php'; ?>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const gameSlug = "<?= htmlspecialchars($game['slug'], ENT_QUOTES, 'UTF-8') ?>";
        const mapCenter = [<?= (float) ($game['map_center_lat'] ?? 50.0755) ?>, <?= (float) ($game['map_center_lon'] ?? 14.4378) ?>];
        const mapZoom = <?= (int) ($game['map_default_zoom'] ?? 14) ?>;
        const gameStartedAt = new Date().getTime();

        const initialPlayerStats = <?= json_encode($playerStats, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

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
        let exploreCandidates = [];
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

        function normalizeYoutubeUrl(url) {
            if (!url) {
                return null;
            }

            try {
                const parsed = new URL(url);

                if (parsed.hostname.includes('youtu.be')) {
                    const videoId = parsed.pathname.replace('/', '').trim();
                    return videoId ? 'https://www.youtube.com/embed/' + videoId : null;
                }

                if (parsed.hostname.includes('youtube.com')) {
                    const videoId = parsed.searchParams.get('v');
                    if (videoId) {
                        return 'https://www.youtube.com/embed/' + videoId;
                    }

                    if (parsed.pathname.startsWith('/embed/')) {
                        return url;
                    }
                }
            } catch (e) {
                console.warn('Neplatná YouTube URL', url);
            }

            return null;
        }

        function renderPoiMedia(mediaList) {
            const container = document.getElementById('poiMedia');
            container.innerHTML = '';

            if (!Array.isArray(mediaList) || mediaList.length === 0) {
                return;
            }

            mediaList.forEach(media => {
                const mediaType = String(media.media_type || '').toLowerCase();
                const filePath = String(media.file_path || media.external_url || '').trim();

                if (!filePath) {
                    return;
                }

                if (mediaType === 'image') {
                    const img = document.createElement('img');
                    img.src = filePath;
                    img.alt = media.title || media.alt_text || 'Obrázek k bodu';
                    img.loading = 'lazy';
                    container.appendChild(img);
                    return;
                }

                if (mediaType === 'video') {
                    const youtubeEmbed = normalizeYoutubeUrl(filePath);

                    if (youtubeEmbed) {
                        const iframe = document.createElement('iframe');
                        iframe.src = youtubeEmbed;
                        iframe.title = media.title || 'Video';
                        iframe.loading = 'lazy';
                        iframe.allow =
                            'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share';
                        iframe.allowFullscreen = true;
                        container.appendChild(iframe);
                    }
                }
            });
        }

        function getGpsLabel(acc) {
            if (acc <= 20) {
                return '🟢 Přesná poloha';
            }
            if (acc <= 50) {
                return '🟡 Slabší signál';
            }
            return '🟠 Nepřesná poloha';
        }

        function updatePlayerCardStats() {
            const gpsState = lastPos ? getGpsLabel(Number(lastPos.acc || 999)) : '…';
            document.getElementById('playerGpsState').innerText = gpsState;

            document.getElementById('playerPoints').innerText = String(initialPlayerStats.points || 0);
            document.getElementById('playerRank').innerText = '#' + String(initialPlayerStats.rank || 0);
            document.getElementById('playerTreasures').innerText = String(initialPlayerStats.treasures_found || 0);
            document.getElementById('playerTasksDone').innerText = String(initialPlayerStats.tasks_done || 0);
            document.getElementById('playerTasksTotal').innerText = String(initialPlayerStats.tasks_total || 0);
            document.getElementById('playerProgressLabel').innerText = String(initialPlayerStats.progress_percent || 0) + ' %';
            document.getElementById('playerProgressFill').style.width = String(initialPlayerStats.progress_percent || 0) + '%';

            document.getElementById('resultsMyPoints').innerText = String(initialPlayerStats.points || 0);
            document.getElementById('resultsMyRank').innerText = '#' + String(initialPlayerStats.rank || 0);
            document.getElementById('resultsMyPois').innerText = String(initialPlayerStats.pois_visited || 0);
            document.getElementById('resultsMyTreasures').innerText = String(initialPlayerStats.treasures_found || 0);
            document.getElementById('resultsMyLastPoi').innerText = String(initialPlayerStats.last_checkpoint || '—');
        }

        function updateLocation(pos) {
            const lat = pos.coords.latitude;
            const lon = pos.coords.longitude;
            const acc = pos.coords.accuracy;
            lastPos = { lat, lon, acc };

            document.getElementById('status').innerText = getGpsLabel(acc);
            document.getElementById('accuracy-warn').style.display = (acc > 50) ? 'block' : 'none';

            if (!userMarker) {
                const userIcon = L.divIcon({
                    className: 'marker-user',
                    iconSize: [20, 20],
                    iconAnchor: [10, 10]
                });
                userMarker = L.marker([lat, lon], { icon: userIcon, zIndexOffset: 1000 }).addTo(map);
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

            updatePlayerCardStats();
            refreshExploreAvailability();
        }

        function handleError(err) {
            console.warn('ERROR(' + err.code + '): ' + err.message);
            document.getElementById('status').innerText = '🔴 GPS problém';
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
                const poiIcon = L.divIcon({
                    className: 'marker-poi',
                    html: '🚩',
                    iconSize: [30, 30],
                    iconAnchor: [15, 25]
                });
                const marker = L.marker([parseFloat(poi.lat), parseFloat(poi.lon)], { icon: poiIcon }).addTo(map);
                marker.bindPopup('<strong>' + escapeHtml(poi.name) + '</strong>');
                marker.on('click', () => openPoiDetail(poi));
                poiMarkers.push(marker);
            });
        }

        function renderTreasures() {
            treasures.forEach(treasure => {
                const lat = parseFloat(treasure.lat);
                const lon = parseFloat(treasure.lon);

                const isClaimed = (treasure.claimed_by_player == 1 || treasure.claimed_by_team == 1);
                let label = isClaimed ? 'Poklad (sebrán)' : 'Poklad';

                const treasureIcon = L.divIcon({
                    className: 'marker-treasure' + (isClaimed ? ' claimed' : ''),
                    html: '💰',
                    iconSize: [30, 30],
                    iconAnchor: [15, 15]
                });

                const marker = L.marker([lat, lon], { icon: treasureIcon }).addTo(map);

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
                    updatePlayerCardStats();
                    refreshExploreAvailability();
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
                media: Array.isArray(poi.media) ? poi.media : [],
            };

            document.getElementById('poiTitle').innerText = currentDetail.title;
            document.getElementById('poiType').style.display = 'inline-block';
            document.getElementById('poiType').innerText = currentDetail.type;
            document.getElementById('poiText').innerText = currentDetail.text;
            renderPoiMedia(currentDetail.media);
            document.getElementById('claimBtn').style.display = 'none';
            document.getElementById('completePoiBtn').style.display = 'inline-block';

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
                points: Number(treasure.points || 0),
            };

            document.getElementById('poiTitle').innerText = currentDetail.title;
            document.getElementById('poiType').style.display = 'inline-block';
            document.getElementById('poiType').innerText = 'treasure';
            document.getElementById('poiText').innerText = currentDetail.text;
            renderPoiMedia([]);

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
            document.getElementById('completePoiBtn').style.display = 'none';
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
                    alert('Poklad byl úspěšně sebrán. Stránka se teď obnoví, aby se správně přepočítaly body i výsledovka.');
                    closePoiModal();
                    window.location.reload();
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

        function isTreasureDiscoverable(treasure) {
            return Number(treasure.claimed_by_player || 0) !== 1
                && Number(treasure.claimed_by_team || 0) !== 1;
        }

        function hasNearbyDiscoverableContent() {
            if (!lastPos) {
                return false;
            }

            const hasPoi = pois.some(poi => Number(poi.visited_by_player || 0) !== 1 && distanceMeters(lastPos.lat, lastPos.lon, Number(poi.lat), Number(poi.lon)) <= Number(poi.radius_m || 0));
            const hasTreasure = treasures.some(treasure => isTreasureDiscoverable(treasure) && distanceMeters(lastPos.lat, lastPos.lon, Number(treasure.lat), Number(treasure.lon)) <= Number(treasure.radius_m || 0));
            return hasPoi || hasTreasure;
        }

        function refreshExploreAvailability() {
            if (hasNearbyDiscoverableContent()) {
                document.getElementById('explorePanel').style.display = 'flex';
                return;
            }

            hideExplorePanel();
        }

        function hideExplorePanel() {
            document.getElementById('explorePanel').style.display = 'none';
        }

        function exploreNearby() {
            if (!lastPos) {
                alert('Nejdřív potřebuji znát tvoji polohu.');
                return;
            }

            const button = document.getElementById('exploreBtn');
            button.disabled = true;
            button.innerText = 'Prozkoumávám…';

            fetch('/api/player/explore', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ lat: lastPos.lat, lon: lastPos.lon })
            })
            .then(r => r.json())
            .then(data => {
                if (!data.success) {
                    alert('Průzkum se nepodařilo dokončit.');
                    return;
                }

                if (data.type === 'none') {
                    alert(data.message || 'V okolí jsi nic zajímavého nenašel.');
                    hideExplorePanel();
                    return;
                }

                if (data.type === 'single' && data.object) {
                    hideExplorePanel();
                    openExploreObject(data.object);
                    return;
                }

                if (data.type === 'multiple' && Array.isArray(data.objects)) {
                    exploreCandidates = data.objects;
                    openExploreChoiceModal();
                }
            })
            .catch(err => {
                console.warn(err);
                alert('Chyba komunikace se serverem.');
            })
            .finally(() => {
                button.disabled = false;
                button.innerText = 'Prozkoumat okolí';
            });
        }

        function openExploreObject(item) {
            if (item.kind === 'treasure') {
                openTreasureDetail(item);
                return;
            }

            openPoiDetail(item);
        }

        function openExploreChoiceModal() {
            const container = document.getElementById('exploreChoiceList');
            container.innerHTML = '';

            exploreCandidates.forEach(item => {
                const button = document.createElement('button');
                button.className = 'explore-option';
                button.type = 'button';
                button.onclick = () => {
                    closeExploreChoiceModal();
                    hideExplorePanel();
                    openExploreObject(item);
                };

                const title = document.createElement('div');
                title.className = 'explore-option-title';
                title.innerText = item.name || 'Místo';

                const sub = document.createElement('div');
                sub.className = 'explore-option-subline';
                const typeLabel = item.kind === 'treasure' ? 'Poklad' : 'Bod zájmu';
                const distance = Math.round(Number(item.distance_m || 0));
                sub.innerText = typeLabel + ' • přibližně ' + distance + ' m';

                button.appendChild(title);
                button.appendChild(sub);
                container.appendChild(button);
            });

            document.getElementById('exploreChoiceModal').style.display = 'flex';
        }

        function closeExploreChoiceModal() {
            document.getElementById('exploreChoiceModal').style.display = 'none';
        }

        function completeCurrentPoi() {
            if (!currentDetail || currentDetail.kind !== 'poi') {
                return;
            }

            if (!lastPos) {
                alert('Neznám tvoji aktuální polohu.');
                return;
            }

            fetch('/api/player/poi/complete', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    poi_id: currentDetail.id,
                    lat: lastPos.lat,
                    lon: lastPos.lon,
                    accuracy: Number(lastPos.acc || 0)
                })
            })
            .then(async response => {
                const data = await response.json();

                if (!data.success) {
                    if (data.status === 'too_far') {
                        alert('Na potvrzení průzkumu jsi zatím příliš daleko.');
                    } else {
                        alert('Nepodařilo se potvrdit průzkum místa.');
                    }
                    return;
                }

                closePoiModal();

                if (Array.isArray(data.unlocked_treasures) && data.unlocked_treasures.length > 0) {
                    const names = data.unlocked_treasures.map(item => item.name || 'Poklad').join(', ');
                    alert('Průzkum dokončen. Odemčeno: ' + names + '. Stránka se teď obnoví.');
                } else {
                    alert('Průzkum místa byl potvrzen. Stránka se teď obnoví, aby se správně propsal stav hry.');
                }

                window.location.reload();
            })
            .catch(err => {
                console.warn(err);
                alert('Chyba komunikace se serverem.');
            });
        }

        function openPlayerCard() {
            updatePlayerCardStats();
            document.getElementById('playerModal').style.display = 'flex';
        }

        function closePlayerCard() {
            document.getElementById('playerModal').style.display = 'none';
        }

        function openResultsModal() {
            updatePlayerCardStats();
            document.getElementById('resultsModal').style.display = 'flex';
        }

        function closeResultsModal() {
            document.getElementById('resultsModal').style.display = 'none';
        }

        function openResultsFromPlayerCard() {
            closePlayerCard();
            openResultsModal();
        }

        function openHelpFromPlayerCard() {
            closePlayerCard();
            openHelp();
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

        reloadMapData();
        updatePlayerCardStats();
        refreshExploreAvailability();
    </script>
</body>
</html>