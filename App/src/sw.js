// Some code from https://github.com/bpolaszek/webpush-js (MIT License, © 2025 Beno!t POLASZEK)

self.addEventListener('install', event => { event.waitUntil(self.skipWaiting()); });

self.addEventListener('activate', event => { event.waitUntil(self.clients.claim()); });

self.addEventListener('push', event => {
    try {
        const Notification = event.data.json();
        sendNotificationToAllClients(Notification);
        event.waitUntil(
            self.registration.showNotification(Notification.title || '', Notification.options || {})
        );
    } catch (error) {
        try {
            const Notification = event.data.text();
            event.waitUntil(
                self.registration.showNotification('Notification', { body: Notification })
            );
        } catch (error) {
            event.waitUntil(
                self.registration.showNotification('')
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
