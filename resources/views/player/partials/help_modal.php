    <div id="helpModal" class="modal">
        <div class="modal-content">
            <h2>Nemohu pokračovat</h2>
            <p>
                Použij jen pokud opravdu potřebuješ pomoc organizátora nebo musíš hru ukončit.
                Odesláním této žádosti oznamuješ, že nemůžeš bezpečně nebo rozumně pokračovat.
            </p>
            <textarea id="helpMsg" placeholder="Napiš stručně, co se děje (např. zranění, nevolnost, puchýře, ztráta orientace, potřebuji vyzvednout...)"></textarea>
            <div class="modal-btns">
                <button class="modal-btn" style="background:#eee;" onclick="closeHelp()">Zpět do hry</button>
                <button class="modal-btn" style="background:#d32f2f; color:#fff;" onclick="sendHelp()">Přivolat pomoc</button>
            </div>
        </div>
    </div>
