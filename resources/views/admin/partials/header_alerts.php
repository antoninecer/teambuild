<div class="admin-alerts" id="admin-alerts-root" data-endpoint="/admin/api/header-status" data-poll-ms="10000">
    <button type="button" class="alert-pill sos" id="admin-sos-pill" title="Otevřená SOS hlášení">
        <span>SOS</span>
        <span class="count" id="admin-sos-count">0</span>
    </button>

    <button type="button" class="alert-pill events" id="admin-events-pill" title="Poslední události hry">
        <span>Události</span>
        <span class="count" id="admin-events-count">0</span>
    </button>

    <label class="header-sound-toggle" title="Zvuk kritických upozornění a událostí">
        <input type="checkbox" id="admin-sound-enabled" checked>
        <span>Zvuk upozornění</span>
    </label>

    <div class="alerts-panel" id="admin-alerts-panel" aria-hidden="true">
        <div class="alerts-panel-header">
            <div>
                <h3 class="alerts-panel-title">Živý přehled</h3>
                <div class="alerts-panel-subtitle">SOS, poklady, POI a další poslední události</div>
            </div>
            <button type="button" class="btn btn-secondary" id="admin-alerts-close">Skrýt</button>
        </div>

        <div class="alerts-feed" id="admin-alerts-feed">
            <div class="alerts-empty">Načítám události…</div>
        </div>
    </div>
</div>

<div class="alert-banner" id="admin-critical-banner" role="button" tabindex="0" aria-live="polite">
    <strong>Nové SOS hlášení</strong>
    <span id="admin-critical-banner-text">Organizátor zatím nemá žádné nové kritické upozornění.</span>
</div>

<audio id="admin-sound-info" preload="auto">
    <source src="/assets/sounds/alert_info.mp3" type="audio/mpeg">
</audio>
<audio id="admin-sound-treasure" preload="auto">
    <source src="/assets/sounds/alert_treasure.mp3" type="audio/mpeg">
</audio>
<audio id="admin-sound-sos" preload="auto">
    <source src="/assets/sounds/alert_sos.mp3" type="audio/mpeg">
</audio>
