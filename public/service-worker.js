
// public/service-worker.js

self.addEventListener('push', function(event) {
    if (!(self.Notification && self.Notification.permission === 'granted')) {
        return;
    }

    let data = {};
    
    if (event.data) {
        try {
            data = event.data.json();
        } catch (e) {
            data = {
                title: 'Lunch Reminder',
                body: event.data.text() || 'Your lunch break ends soon!',
                icon: '/images/notification-icon.png',
                badge: '/images/badge-icon.png'
            };
        }
    }

    const options = {
        body: data.body || 'Your lunch break ends in 5 minutes!',
        icon: data.icon || '/images/notification-icon.png',
        badge: data.badge || '/images/badge-icon.png',
        vibrate: [200, 100, 200],
        sound: '/sounds/notification.mp3',
        data: {
            url: data.data?.url || '/employee/dashboard',
            timestamp: Date.now(),
            ...data.data
        },
        actions: data.actions || [
            { action: 'view_dashboard', title: 'View Dashboard' },
            { action: 'dismiss', title: 'Dismiss' }
        ]
    };

    event.waitUntil(
        self.registration.showNotification(
            data.title || '🍽️ Lunch Break Reminder',
            options
        )
    );
});

self.addEventListener('notificationclick', function(event) {
    event.notification.close();

    if (event.action === 'dismiss') {
        return;
    }

    const urlToOpen = event.notification.data?.url || '/employee/dashboard';

    event.waitUntil(
        clients.matchAll({
            type: 'window',
            includeUncontrolled: true
        }).then(function(clientList) {
            for (let i = 0; i < clientList.length; i++) {
                let client = clientList[i];
                if (client.url === urlToOpen && 'focus' in client) {
                    return client.focus();
                }
            }
            if (clients.openWindow) {
                return clients.openWindow(urlToOpen);
            }
        })
    );
});

self.addEventListener('install', function(event) {
    self.skipWaiting();
});

self.addEventListener('activate', function(event) {
    event.waitUntil(clients.claim());
});