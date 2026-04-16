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

        let titleBlinkTimer = null;
        let titleBlinkState = false;
        let pollingStarted = false;
        let currentCriticalEvent = null;

        let lastOpenSosCount = null;
        let seenEventIds = new Set();

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
            } catch (error) {
                // ignore
            }
        }

        function startTitleBlink(label) {
            if (titleBlinkTimer) {
                return;
            }

            titleBlinkTimer = window.setInterval(() => {
                titleBlinkState = !titleBlinkState;
                document.title = titleBlinkState ? (label + ' | VentureOut Admin') : titleBase;
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
            if (event.severity === 'critical') {
                return 'critical';
            }
            if (event.severity === 'warning') {
                return 'warning';
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

        function togglePanel(force) {
            const shouldOpen = typeof force === 'boolean'
                ? force
                : !panel.classList.contains('is-open');

            panel.classList.toggle('is-open', shouldOpen);
            panel.setAttribute('aria-hidden', shouldOpen ? 'false' : 'true');
        }

        async function acknowledgeHelp(helpId) {
            try {
                const response = await fetch(`/admin/api/help/${helpId}/acknowledge`, {
                    method: 'POST',
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

                return await response.json();
            } catch (error) {
                console.error('Nepodařilo se převzít SOS:', error);
                return null;
            }
        }

        async function resolveHelp(helpId) {
            try {
                const response = await fetch(`/admin/api/help/${helpId}/resolve`, {
                    method: 'POST',
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

                return await response.json();
            } catch (error) {
                console.error('Nepodařilo se uzavřít SOS:', error);
                return null;
            }
        }

        async function handleSosAcknowledge(event) {
            if (!event || !event.help_id) {
                togglePanel(true);
                return;
            }

            await acknowledgeHelp(event.help_id);

            if (event.player_id) {
                window.location.href = `/admin/players/${event.player_id}`;
                return;
            }

            await fetchStatus();
            togglePanel(true);
        }

        function createSosActions(event) {
            if (!event || !event.help_id) {
                return '';
            }

            const buttons = [];

            if (event.status === 'open') {
                buttons.push(
                    `<button class="alert-item-action" type="button" data-help-ack="${event.help_id}" data-player-id="${event.player_id || ''}">Převzít</button>`
                );
            }

            if (event.status === 'open' || event.status === 'acknowledged') {
                buttons.push(
                    `<button class="alert-item-action" type="button" data-help-resolve="${event.help_id}">Uzavřít</button>`
                );
            }

            if (buttons.length === 0) {
                return '';
            }

            return `<div class="alert-item-actions">${buttons.join('')}</div>`;
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
                const detailMessage = event.detail_message ? String(event.detail_message) : '';
                const time = formatTime(event.created_at || event.time || '');
                const helpIdAttr = event.help_id ? ` data-help-id="${event.help_id}"` : '';
                const playerIdAttr = event.player_id ? ` data-player-id="${event.player_id}"` : '';
                const actionsHtml = event.type === 'sos_open' ? createSosActions(event) : '';

                return `
                    <div class="alert-item ${klass}"${helpIdAttr}${playerIdAttr}>
                        <div class="alert-item-top">
                            <div class="alert-item-label">${label}</div>
                            <div class="alert-item-time">${time}</div>
                        </div>
                        <div class="alert-item-message">${message}</div>
                        ${detailMessage ? `<div class="alert-item-detail">${detailMessage}</div>` : ''}
                        ${actionsHtml}
                    </div>`;
            }).join('');

            const ackButtons = feed.querySelectorAll('[data-help-ack]');
            ackButtons.forEach((button) => {
                button.addEventListener('click', async function (domEvent) {
                    domEvent.preventDefault();
                    domEvent.stopPropagation();

                    const helpId = Number(button.dataset.helpAck || 0);
                    const playerId = Number(button.dataset.playerId || 0);

                    if (!helpId) {
                        return;
                    }

                    button.disabled = true;
                    await acknowledgeHelp(helpId);

                    if (playerId) {
                        window.location.href = `/admin/players/${playerId}`;
                        return;
                    }

                    await fetchStatus();
                });
            });

            const resolveButtons = feed.querySelectorAll('[data-help-resolve]');
            resolveButtons.forEach((button) => {
                button.addEventListener('click', async function (domEvent) {
                    domEvent.preventDefault();
                    domEvent.stopPropagation();

                    const helpId = Number(button.dataset.helpResolve || 0);
                    if (!helpId) {
                        return;
                    }

                    button.disabled = true;
                    await resolveHelp(helpId);
                    await fetchStatus();
                });
            });
        }

        function handleSounds(events, sosOpen) {
            if (!pollingStarted) {
                return;
            }

            if (lastOpenSosCount !== null && sosOpen > lastOpenSosCount) {
                safePlay(soundSos);
                startTitleBlink('SOS!');
                return;
            }

            const newEvents = events.filter((event) => !seenEventIds.has(String(event.id || '')));

            const newestNonCritical = newEvents.find((event) => {
                return event.severity !== 'critical';
            });

            if (!newestNonCritical) {
                return;
            }

            if (newestNonCritical.type === 'treasure' || newestNonCritical.type === 'treasure_claimed') {
                safePlay(soundTreasure);
            } else if (
                newestNonCritical.type === 'poi' ||
                newestNonCritical.type === 'poi_completed' ||
                newestNonCritical.type === 'message'
            ) {
                safePlay(soundInfo);
            }
        }

        function rememberSeenEvents(events) {
            if (!Array.isArray(events)) {
                return;
            }

            for (const event of events) {
                if (event && event.id) {
                    seenEventIds.add(String(event.id));
                }
            }
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

            const latestCritical = events.find((event) =>
                event.severity === 'critical' && (event.status === 'open' || !event.status)
            ) || null;

            currentCriticalEvent = latestCritical;

            if (latestCritical) {
                criticalBanner.classList.add('is-visible');
                criticalBannerText.textContent = latestCritical.message || 'Ve hře je nové kritické upozornění.';
                criticalBanner.dataset.helpId = latestCritical.help_id ? String(latestCritical.help_id) : '';
                criticalBanner.dataset.playerId = latestCritical.player_id ? String(latestCritical.player_id) : '';
                criticalBanner.style.cursor = latestCritical.help_id ? 'pointer' : 'default';
            } else {
                currentCriticalEvent = null;
                criticalBanner.classList.remove('is-visible');
                criticalBannerText.textContent = 'Organizátor zatím nemá žádné nové kritické upozornění.';
                criticalBanner.dataset.helpId = '';
                criticalBanner.dataset.playerId = '';
                criticalBanner.style.cursor = 'default';
                stopTitleBlink();
            }

            handleSounds(events, sosOpen);

            rememberSeenEvents(events);
            lastOpenSosCount = sosOpen;
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

        function openPanelAndRefresh() {
            togglePanel(true);
            fetchStatus();
        }

        sosPill.addEventListener('click', function () {
            togglePanel();
        });

        eventsPill.addEventListener('click', function () {
            togglePanel();
        });

        closeBtn.addEventListener('click', function () {
            togglePanel(false);
        });

        criticalBanner.addEventListener('click', async function () {
            if (currentCriticalEvent && currentCriticalEvent.help_id && currentCriticalEvent.status === 'open') {
                await handleSosAcknowledge(currentCriticalEvent);
                return;
            }

            openPanelAndRefresh();
        });

        criticalBanner.addEventListener('keydown', async function (event) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();

                if (currentCriticalEvent && currentCriticalEvent.help_id && currentCriticalEvent.status === 'open') {
                    await handleSosAcknowledge(currentCriticalEvent);
                    return;
                }

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