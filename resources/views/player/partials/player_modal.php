    <div id="playerModal" class="modal">
        <div class="modal-content glass-modal">
            <h2><?= htmlspecialchars($player['nickname'], ENT_QUOTES, 'UTF-8') ?></h2>
            <div class="player-card-note">
                Tvoje karta hráče, body, úkoly a přehled postupu ve hře.
            </div>

            <div class="player-card-grid">
                <div class="player-stat">
                    <div class="player-stat-label">Body</div>
                    <div class="player-stat-value" id="playerPoints"><?= (int) ($playerStats['points'] ?? 0) ?></div>
                </div>
                <div class="player-stat">
                    <div class="player-stat-label">Pořadí</div>
                    <div class="player-stat-value" id="playerRank">#<?= (int) ($playerStats['rank'] ?? 0) ?></div>
                </div>
                <div class="player-stat">
                    <div class="player-stat-label">Poklady</div>
                    <div class="player-stat-value" id="playerTreasures"><?= (int) ($playerStats['treasures_found'] ?? 0) ?></div>
                </div>
                <div class="player-stat">
                    <div class="player-stat-label">Úkoly hotovo</div>
                    <div class="player-stat-value" id="playerTasksDone"><?= (int) ($playerStats['tasks_done'] ?? 0) ?></div>
                </div>
                <div class="player-stat">
                    <div class="player-stat-label">Úkolů celkem</div>
                    <div class="player-stat-value" id="playerTasksTotal"><?= (int) ($playerStats['tasks_total'] ?? 0) ?></div>
                </div>
                <div class="player-stat">
                    <div class="player-stat-label">Stav GPS</div>
                    <div class="player-stat-value" id="playerGpsState">…</div>
                </div>
            </div>

            <div class="player-progress">
                <div class="player-progress-top">
                    <span>Progress hry</span>
                    <span id="playerProgressLabel"><?= (int) ($playerStats['progress_percent'] ?? 0) ?> %</span>
                </div>
                <div class="progress-track">
                    <div id="playerProgressFill" class="progress-fill" style="width: <?= (int) ($playerStats['progress_percent'] ?? 0) ?>%;"></div>
                </div>
            </div>

            <div class="modal-btns">
                <button class="modal-btn" style="background:#1976d2; color:#fff;" onclick="openResultsFromPlayerCard()">Výsledovka</button>
                <button class="modal-btn" style="background:#d32f2f; color:#fff;" onclick="openHelpFromPlayerCard()">SOS / Pomoc</button>
                <button class="modal-btn" style="background:#eee;" onclick="closePlayerCard()">Zavřít</button>
            </div>
        </div>
    </div>

