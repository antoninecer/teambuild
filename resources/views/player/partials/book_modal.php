    <div id="bookModal" class="modal">
        <div class="modal-content glass-modal book-modal">
            <div class="book-header">
                <div>
                    <h2>Kniha hráče</h2>
                    <div class="book-subtitle">Deník cesty, inventář a zprávy ke hře.</div>
                </div>
                <button class="book-close" type="button" onclick="closeBookModal()">×</button>
            </div>

            <div class="book-tabs" role="tablist">
                <button id="bookTabOverview" class="book-tab active" type="button" onclick="switchBookTab('overview')">Přehled</button>
                <button id="bookTabJournal" class="book-tab" type="button" onclick="switchBookTab('journal')">Deník</button>
                <button id="bookTabInventory" class="book-tab" type="button" onclick="switchBookTab('inventory')">Inventář</button>
                <button id="bookTabMessages" class="book-tab" type="button" onclick="switchBookTab('messages')">Zprávy</button>
            </div>

            <section id="bookPanelOverview" class="book-panel active">
                <div class="results-summary">
                    <div class="results-box">
                        <div class="results-label">Tvoje body</div>
                        <div class="results-value" id="bookMyPoints"><?= (int) ($playerStats['points'] ?? 0) ?></div>
                    </div>
                    <div class="results-box">
                        <div class="results-label">Pořadí</div>
                        <div class="results-value" id="bookMyRank">#<?= (int) ($playerStats['rank'] ?? 0) ?></div>
                    </div>
                    <div class="results-box">
                        <div class="results-label">POI</div>
                        <div class="results-value" id="bookMyPois"><?= (int) ($playerStats['pois_visited'] ?? 0) ?></div>
                    </div>
                    <div class="results-box">
                        <div class="results-label">Poklady</div>
                        <div class="results-value" id="bookMyTreasures"><?= (int) ($playerStats['treasures_found'] ?? 0) ?></div>
                    </div>
                    <div class="results-box results-box-wide">
                        <div class="results-label">Naposledy navštíveno</div>
                        <div class="results-value results-value-small" id="bookMyLastPoi">
                            <?= htmlspecialchars((string) ($playerStats['last_checkpoint'] ?? '—'), ENT_QUOTES, 'UTF-8') ?>
                        </div>
                    </div>
                </div>

                <div class="book-note">
                    Výsledovka zůstává dostupná v menu hráče. Tady bude postupně hlavní herní kniha: co jsi našel, co neseš a jaké stopy jsi dostal.
                </div>
            </section>

            <section id="bookPanelJournal" class="book-panel">
                <div id="bookJournalList" class="book-list">
                    <div class="book-empty">Deník se načítá…</div>
                </div>
            </section>

            <section id="bookPanelInventory" class="book-panel">
                <div id="bookInventoryList" class="book-list">
                    <div class="book-empty">Inventář se načítá…</div>
                </div>
            </section>

            <section id="bookPanelMessages" class="book-panel">
                <div id="bookMessagesList" class="book-list">
                    <div class="book-empty">Zprávy se načítají…</div>
                </div>
            </section>
        </div>
    </div>
