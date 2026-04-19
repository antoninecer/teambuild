    <div id="resultsModal" class="modal">
        <div class="modal-content glass-modal">
            <h2>Výsledovka</h2>

            <div class="results-summary">
                <div class="results-box">
                    <div class="results-label">Tvoje body</div>
                    <div class="results-value" id="resultsMyPoints"><?= (int) ($playerStats['points'] ?? 0) ?></div>
                </div>
                <div class="results-box">
                    <div class="results-label">Pořadí</div>
                    <div class="results-value" id="resultsMyRank">#<?= (int) ($playerStats['rank'] ?? 0) ?></div>
                </div>
                <div class="results-box">
                    <div class="results-label">Poklady</div>
                    <div class="results-value" id="resultsMyTreasures"><?= (int) ($playerStats['treasures_found'] ?? 0) ?></div>
                </div>
            </div>

            <table class="leaderboard-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Hráč</th>
                        <th>Body</th>
                        <th>Poklady</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($leaderboard as $row): ?>
                        <tr class="<?= (int) $row['player_id'] === (int) $player['id'] ? 'leaderboard-highlight' : '' ?>">
                            <td>#<?= (int) $row['rank'] ?></td>
                            <td><?= htmlspecialchars($row['nickname'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= (int) $row['points'] ?></td>
                            <td><?= (int) $row['treasures_found'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="modal-btns" style="margin-top:16px;">
                <button class="modal-btn" style="background:#eee;" onclick="closeResultsModal()">Zavřít</button>
            </div>
        </div>
    </div>


