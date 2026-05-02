    <div id="map"></div>

    <div class="ui-overlay">
        <div class="ui-box player-box" onclick="openPlayerCard()">
            <div class="player-name"><?= htmlspecialchars($player['nickname'], ENT_QUOTES, 'UTF-8') ?></div>
            <div class="player-subline" style="font-weight:600; margin-bottom:4px;">
                <?= htmlspecialchars($game['name'] ?? 'Aktuální hra', ENT_QUOTES, 'UTF-8') ?>
            </div>
            <div id="status" class="player-subline">Zjišťuji polohu…</div>
        </div>

        <div class="ui-box context-box" onclick="openBookModal()">
            <div class="context-icon">📖</div>
            <div class="context-subline">Kniha</div>
        </div>
    </div>

    <div id="accuracy-warn" class="accuracy-warn">Slabý signál GPS</div>

    <div id="explorePanel" class="explore-panel" style="display:none;">
        <div class="explore-card">
            <div id="exploreTitle" class="explore-title">Místo je dost blízko na průzkum.</div>
            <div id="exploreSubline" class="explore-subline">Jsi v oblasti, kde můžeš něco objevit.</div>
            <div class="explore-actions">
                <button id="exploreBtn" class="explore-btn" style="background:#1565c0; color:#fff;" onclick="exploreNearby()">Prozkoumat okolí</button>
                <button class="explore-btn" style="background:#eceff1; color:#263238;" onclick="hideExplorePanel()">Teď ne</button>
            </div>
        </div>
    </div>
