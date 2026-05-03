/**
 * CronSprint Alarm Service Worker
 * Manages the sprint timer independently of the page's visibility/focus state.
 * Uses a periodic self-check instead of setTimeout to survive Chrome's SW throttling.
 */

const CHANNEL_NAME = 'cron-sprint-alarm';
const bc = new BroadcastChannel(CHANNEL_NAME);

let alarmCheckIntervalId = null;
let alarmTargetTs = null;

// ──────────────────────────────────────────────
// Install & Activate (skip waiting for instant activation)
// ──────────────────────────────────────────────
self.addEventListener('install', () => self.skipWaiting());
self.addEventListener('activate', (event) => {
    event.waitUntil(self.clients.claim());
});

// ──────────────────────────────────────────────
// Message handler (from page → SW)
// ──────────────────────────────────────────────
self.addEventListener('message', (event) => {
    const { type, targetTs } = event.data || {};

    if (type === 'SET_ALARM') {
        alarmTargetTs = targetTs;

        // Start periodic checking if not already running
        startAlarmCheck();

    } else if (type === 'CANCEL_ALARM') {
        stopAlarmCheck();
        alarmTargetTs = null;

    } else if (type === 'PING_ALARM_STATUS') {
        // Page is asking if an alarm is pending (e.g. after page reload)
        event.source?.postMessage({
            type: 'ALARM_STATUS',
            hasPendingAlarm: alarmTargetTs !== null,
            targetTs: alarmTargetTs
        });

    } else if (type === 'KEEPALIVE') {
        // Just a keepalive ping from the page to prevent SW termination
        // No action needed — receiving the message resets the SW idle timer
    }
});

// ──────────────────────────────────────────────
// Periodic alarm check (survives Chrome throttling)
// ──────────────────────────────────────────────
function startAlarmCheck() {
    // Clear any existing interval
    if (alarmCheckIntervalId !== null) {
        clearInterval(alarmCheckIntervalId);
    }

    // Check immediately
    if (checkAlarm()) return;

    // Then check every 1 second for precision
    alarmCheckIntervalId = setInterval(() => {
        checkAlarm();
    }, 1000);
}

function stopAlarmCheck() {
    if (alarmCheckIntervalId !== null) {
        clearInterval(alarmCheckIntervalId);
        alarmCheckIntervalId = null;
    }
}

function checkAlarm() {
    if (alarmTargetTs === null) {
        stopAlarmCheck();
        return true;
    }

    if (Date.now() >= alarmTargetTs) {
        stopAlarmCheck();
        fireAlarm();
        return true;
    }

    return false;
}

// ──────────────────────────────────────────────
// Notification click → focus the tab
// ──────────────────────────────────────────────
self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    event.waitUntil(
        self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clients) => {
            if (clients.length > 0) {
                return clients[0].focus();
            }
            return self.clients.openWindow('/');
        })
    );
});

// ──────────────────────────────────────────────
// Fire the alarm
// ──────────────────────────────────────────────
function fireAlarm() {
    alarmTargetTs = null;

    // 1. Notify ALL open tabs via BroadcastChannel
    bc.postMessage({ type: 'ALARM_FIRED' });

    // 2. Also send directly to all controlled clients (belt and suspenders)
    self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then(clients => {
        clients.forEach(client => {
            client.postMessage({ type: 'ALARM_FIRED' });
        });
    });

    // 3. Show a system notification (visible even if tab is minimized/closed)
    self.registration.showNotification('⏱ ¡CronSprint completado!', {
        body: 'Tu sprint ha terminado. ¡Registra el tiempo trabajado!',
        icon: '/images/flexiweek-Iso.png',
        badge: '/images/flexiweek-Iso.png',
        requireInteraction: true,
        tag: 'cron-sprint-alarm',
        vibrate: [200, 100, 200, 100, 200],
        actions: [
            { action: 'open', title: '📋 Registrar tiempo' }
        ]
    }).catch(() => {
        // Notification permission not granted — BC message is enough
    });
}
