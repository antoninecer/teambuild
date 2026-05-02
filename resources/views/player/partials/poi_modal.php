    <div id="poiModal" class="modal">
        <div class="modal-content">
            <h2 id="poiTitle">Detail</h2>
            <div id="poiType" class="poi-type" style="display:none;"></div>
            <div id="poiMeta" class="poi-meta"></div>
            <div id="poiMedia" class="poi-media"></div>
            <div id="poiText" class="poi-text"></div>
            <div class="modal-btns">
                <button class="modal-btn" style="background:#1976d2; color:#fff;" onclick="speakCurrentText()">PŘEČÍST NAHLAS</button>
                <button class="modal-btn" style="background:#757575; color:#fff;" onclick="stopSpeech()">ZASTAVIT</button>
                <button id="completePoiBtn" class="modal-btn" style="background:#2e7d32; color:#fff; display:none;" onclick="completeCurrentPoi()">POTVRDIT PRŮZKUM</button>
                <button id="claimBtn" class="modal-btn" style="background:#2e7d32; color:#fff; display:none;" onclick="claimCurrentTreasure()">SEBRAT POKLAD</button>
                <button id="pickupMapItemBtn" class="modal-btn" style="background:#6a1b9a; color:#fff; display:none;" onclick="pickupCurrentMapItem()">SEBRAT PŘEDMĚT</button>
                <button class="modal-btn" style="background:#eee;" onclick="closePoiModal()">ZAVŘÍT</button>
            </div>
        </div>
    </div>

