(function () {
    function initAdminHeaderAlerts() {
        const root = document.getElementById('admin-alerts-root');
        if (!root) {
            return;
        }

        const endpoint = root.dataset.endpoint || '/admin/api/header-status';
        const pollIntervalMs = Number(root.dataset.pollMs || 10000);
        const titleBase = document.title;
        const panel = document.getElementById('admin-alerts-panel');
        const feed = document.getElementById('admin-alerts-feed');
        const closeBtn = document.getElementById('admin-alerts-close');
        const sosPill = document.getElementById('admin-sos-pill');
        const eventsPill = document.getElementById('admin-events-pill');
        const sosCount = document.getElementById('admin-sos-count');
        const eventsCount = document.getElementById('admin-events-count');
        const soundEnabledCheckbox = document.getElementById('admin-sound-enabled');
        const criticalBanner = document.getElementById('admin-critical-banner');
        const criticalBannerText = document.getElementById('admin-critical-banner-text');
        const soundInfo = document.getElementById('admin-sound-info');
        const soundTreasure = document.getElementById('admin-sound-treasure');
        const soundSos = document.getElementById('admin-sound-sos');

        let lastEventSignature = null;
        let lastCriticalSignature = null;
        let titleBlinkTimer = null;
        let titleBlinkState = false;
        let pollingStarted = false;

        function safePlay(audioEl) {
            if (!audioEl || !soundEnabledCheckbox || !soundEnabledCheckbox.checked) {
                return;
            }
            try {
                audioEl.currentTime = 0;
                const promise = audioEl.play();
                if (promise && typeof promise.catch === 'function') {
                    promise.catch(() => {});
                }
            } catch (error) {}
        }

        function startTitleBlink(label) {
            if (titleBlinkTimer) {
                return;
            }
            titleBlinkTimer = window.setInterval(() => {
                titleBlinkState = !titleBlinkState;
                document.title = titleBlinkState ? label + ' | VentureOut Admin' : titleBase;
            }, 1000);
        }

        function stopTitleBlink() {
            if (titleBlinkTimer) {
                window.clearInterval(titleBlinkTimer);
                titleBlinkTimer = null;
            }
            titleBlinkState = false;
            document.title = titleBase;
        }

        function formatTime(value) {
            if (!value) {
                return 'teď';
            }
            const parsed = new Date(value);
            if (Number.isNaN(parsed.getTime())) {
                return value;
            }
            return parsed.toLocaleString('cs-CZ', {
                day: '2-digit',
                month: '2-digit',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        function eventClass(event) {
            if (event.severity === 'critical' || event.type === 'sos' || event.type === 'sos_open') {
                return 'critical';
            }
            if (event.type === 'treasure' || event.type === 'treasure_claimed') {
                return 'reward';
            }
            return 'info';
        }

        function eventLabel(event) {
            if (event.type === 'sos' || event.type === 'sos_open') return 'SOS';
            if (event.type === 'treasure' || event.type === 'treasure_claimed') return 'Poklad';
            if (event.type === 'poi' || event.type === 'poi_completed') return 'POI';
            return 'Událost';
        }

        function renderEvents(events) {
            if (!Array.isArray(events) || events.length === 0) {
                feed.innerHTML = '<div class="alerts-empty">Zatím nejsou k dispozici žádné nové události.</div>';
                return;
            }
            feed.innerHTML = events.map((event) => {
                const klass = eventClass(event);
                const label = eventLabel(event);
                const message = event.message || 'Nová událost';
                const time = formatTime(event.created_at || event.time || '');
                return `
                    <div class="alert-item ${klass}">
                        <div class="alert-item-top">
                            <div class="alert-item-label">${label}</div>
                            <div class="alert-item-time">${time}</div>
                        </div>
                        <div class="alert-item-message">${message}</div>
                    </div>`;
            }).join('');
        }

        function applyState(data) {
            const counts = data && data.counts ? data.counts : {};
            const events = Array.isArray(data && data.events) ? data.events : [];
            const sosOpen = Number(counts.sos_open || 0);
            const totalEvents = Number(counts.new_events || events.length || 0);

            sosCount.textContent = String(sosOpen);
            eventsCount.textContent = String(totalEvents);
            eventsPill.classList.toggle('has-unread', totalEvents > 0);
            renderEvents(events);

            const latestEvent = events.length > 0 ? events[0] : null;
            const latestSignature = latestEvent ? String(latestEvent.id || latestEvent.signature || JSON.stringify(latestEvent)) : null;
            const latestCritical = events.find((event) => event.severity === 'critical' || event.type === 'sos' || event.type === 'sos_open') || null;
            const latestCriticalSignature = latestCritical ? String(latestCritical.id || latestCritical.signature || JSON.stringify(latestCritical)) : null;

            if (latestCritical) {
                criticalBanner.classList.add('is-visible');
                criticalBannerText.textContent = latestCritical.message || 'Ve hře je nové kritické upozornění.';
            } else {
                criticalBanner.classList.remove('is-visible');
                criticalBannerText.textContent = 'Organizátor zatím nemá žádné nové kritické upozornění.';
                stopTitleBlink();
            }

            if (pollingStarted) {
                if (latestCriticalSignature && latestCriticalSignature !== lastCriticalSignature) {
                    safePlay(soundSos);
                    startTitleBlink('SOS!');
                } else if (latestSignature && latestSignature !== lastEventSignature && latestEvent) {
                    if (latestEvent.type === 'treasure' || latestEvent.type === 'treasure_claimed') {
                        safePlay(soundTreasure);
                    } else if (latestEvent.type === 'poi' || latestEvent.type === 'poi_completed' || latestEvent.type === 'message') {
                        safePlay(soundInfo);
                    }
                }
            }

            lastEventSignature = latestSignature;
            lastCriticalSignature = latestCriticalSignature;
            pollingStarted = true;
        }

        async function fetchStatus() {
            try {
                const response = await fetch(endpoint, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin',
                    cache: 'no-store'
                });
                if (!response.ok) {
                    throw new Error('HTTP ' + response.status);
                }
                const data = await response.json();
                applyState(data);
            } catch (error) {
                if (!pollingStarted) {
                    feed.innerHTML = '<div class="alerts-empty">Nepodařilo se načíst živý přehled z <code>/admin/api/header-status</code>.</div>';
                }
            }
        }

        function togglePanel(force) {
            const shouldOpen = typeof force === 'boolean' ? force : !panel.classList.contains('is-open');
            panel.classList.toggle('is-open', shouldOpen);
            panel.setAttribute('aria-hidden', shouldOpen ? 'false' : 'true');
        }

        function openPanelAndRefresh() {
            togglePanel(true);
            fetchStatus();
        }

        sosPill.addEventListener('click', function () { togglePanel(); });
        eventsPill.addEventListener('click', function () { togglePanel(); });
        closeBtn.addEventListener('click', function () { togglePanel(false); });
        criticalBanner.addEventListener('click', openPanelAndRefresh);
        criticalBanner.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                openPanelAndRefresh();
            }
        });

        document.addEventListener('click', function (event) {
            if (!root.contains(event.target)) {
                togglePanel(false);
            }
        });

        document.addEventListener('visibilitychange', function () {
            if (document.visibilityState === 'visible') {
                stopTitleBlink();
                fetchStatus();
            }
        });

        window.addEventListener('focus', function () {
            stopTitleBlink();
            fetchStatus();
        });

        fetchStatus();
        window.setInterval(fetchStatus, pollIntervalMs);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAdminHeaderAlerts);
    } else {
        initAdminHeaderAlerts();
    }
})();
