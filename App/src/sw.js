// Some code from https://github.com/bpolaszek/webpush-js (MIT License, © 2025 Beno!t POLASZEK)

self.addEventListener('install', event => event.waitUntil(self.skipWaiting()));

self.addEventListener('activate', event => {
    event.waitUntil(
        (async () => {
            await self.clients.claim();
            informClientsOfNewVersion();
        })()
    );
});

self.addEventListener('push', event => {
    try {
        const notification = event.data.json();
        sendNotificationToAllClients(notification);
        event.waitUntil(
            checkIfAnyClientIsActive().then(hasActiveClient => {
                if (!hasActiveClient || notification.options.data.priority === 'high') {
                    return self.registration.showNotification(notification.title || '', notification.options || {});
                }
            })
        );
    } catch (error) {
        try {
            const notification = event.data.text();
            event.waitUntil(
                checkIfAnyClientIsActive().then(hasActiveClient => {
                    if (!hasActiveClient || notification.options.data.priority === 'high') {
                        return self.registration.showNotification('Notification', { body: notification });
                    }
                })
            );
        } catch (error) {
            event.waitUntil(
                checkIfAnyClientIsActive().then(hasActiveClient => {
                    if (!hasActiveClient) {
                        return self.registration.showNotification('');
                    }
                })
            );
        }
    }
});

self.addEventListener('notificationclick', event => {
    event.notification.close();
    const url = new URL(event.notification.data.link, self.location.origin);

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true })
            .then(windowClients => {
                for (const client of windowClients) {
                    if (client.url === url.toString() && 'focus' in client) {
                        return client.focus();
                    }
                }

                if (clients.openWindow) {
                    return clients.openWindow(url);
                }
            })
    );
});

function sendNotificationToAllClients(notification) {
    self.clients.matchAll({ includeUncontrolled: true, type: 'window' })
        .then(clients => {
            clients.forEach(client => {
                client.postMessage({
                    type: 'PUSH_NOTIFICATION',
                    data: notification
                });
            });
        });
}

function informClientsOfNewVersion() {
    self.clients.matchAll({ includeUncontrolled: true, type: 'window' })
        .then(clients => {
            clients.forEach(client => {
                client.postMessage({
                    type: 'NEW_VERSION'
                });
            });
        });
}

function checkIfAnyClientIsActive() {
    return self.clients.matchAll({ type: 'window', includeUncontrolled: true })
        .then(clients => {
            return clients.some(client => client.visibilityState === 'visible');
        });
}
