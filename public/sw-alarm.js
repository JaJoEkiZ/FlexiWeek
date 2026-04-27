/**
 * CronSprint Alarm Service Worker
 * Manages the sprint timer independently of the page's visibility/focus state.
 * This ensures the alarm fires even when the tab is minimized, backgrounded, or inactive.
 */

const CHANNEL_NAME = 'cron-sprint-alarm';
const bc = new BroadcastChannel(CHANNEL_NAME);

let alarmTimeoutId = null;
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
        // Cancel any existing alarm first
        if (alarmTimeoutId !== null) {
            clearTimeout(alarmTimeoutId);
            alarmTimeoutId = null;
        }

        alarmTargetTs = targetTs;
        const delay = targetTs - Date.now();

        if (delay <= 0) {
            // Already expired — fire immediately
            fireAlarm();
            return;
        }

        alarmTimeoutId = setTimeout(() => {
            alarmTimeoutId = null;
            fireAlarm();
        }, delay);

    } else if (type === 'CANCEL_ALARM') {
        if (alarmTimeoutId !== null) {
            clearTimeout(alarmTimeoutId);
            alarmTimeoutId = null;
        }
        alarmTargetTs = null;

    } else if (type === 'PING_ALARM_STATUS') {
        // Page is asking if an alarm is pending (e.g. after page reload)
        event.source?.postMessage({
            type: 'ALARM_STATUS',
            hasPendingAlarm: alarmTimeoutId !== null,
            targetTs: alarmTargetTs
        });
    }
});

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

    // 1. Notify the page via BroadcastChannel (works if tab is open, even if hidden)
    bc.postMessage({ type: 'ALARM_FIRED' });

    // 2. Show a system notification as fallback (visible even if tab is minimized/closed)
    self.registration.showNotification('⏱ ¡CronSprint completado!', {
        body: 'Tu sprint ha terminado. ¡Registra el tiempo trabajado!',
        icon: '/images/flexiweek-Iso.png',
        badge: '/images/flexiweek-Iso.png',
        requireInteraction: true,
        tag: 'cron-sprint-alarm',
        vibrate: [200, 100, 200],
        actions: [
            { action: 'open', title: '📋 Registrar tiempo' }
        ]
    }).catch(() => {
        // Notification permission not granted — BC message is enough
    });
}
