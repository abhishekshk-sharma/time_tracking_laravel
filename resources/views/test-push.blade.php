<!DOCTYPE html>
<html>
<head>
    <title>Push Notification Test</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="vapid-public-key" content="{{ config('webpush.vapid.public_key') }}">
</head>
<body>
    <h1>Push Notification Test</h1>
    
    <div id="status">
        <p>VAPID Key: <span id="vapid-key"></span></p>
        <p>CSRF Token: <span id="csrf-token"></span></p>
        <p>Notification Permission: <span id="permission"></span></p>
        <p>Service Worker: <span id="sw-status"></span></p>
    </div>
    
    <button id="test-btn" onclick="testNotification()">Test Notification</button>
    <button id="enable-btn" onclick="enableNotifications()">Enable Push Notifications</button>
    
    <div id="log" style="margin-top: 20px; padding: 10px; background: #f0f0f0; max-height: 400px; overflow-y: auto;"></div>
    
    <script>
        function log(message) {
            const logDiv = document.getElementById('log');
            const time = new Date().toLocaleTimeString();
            logDiv.innerHTML += `<div>[${time}] ${message}</div>`;
            console.log(message);
        }
        
        // Display status
        document.getElementById('vapid-key').textContent = document.querySelector('meta[name="vapid-public-key"]')?.content || 'NOT FOUND';
        document.getElementById('csrf-token').textContent = document.querySelector('meta[name="csrf-token"]')?.content || 'NOT FOUND';
        document.getElementById('permission').textContent = Notification.permission;
        
        // Check service worker
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.getRegistration().then(reg => {
                document.getElementById('sw-status').textContent = reg ? 'Registered' : 'Not Registered';
            });
        } else {
            document.getElementById('sw-status').textContent = 'Not Supported';
        }
        
        async function testNotification() {
            log('Testing browser notification...');
            
            if (!('Notification' in window)) {
                log('ERROR: Notifications not supported');
                return;
            }
            
            if (Notification.permission === 'granted') {
                new Notification('Test Notification', {
                    body: 'This is a test notification!',
                    icon: '/images/notification-icon.png'
                });
                log('SUCCESS: Test notification sent');
            } else {
                log('ERROR: Permission not granted. Current: ' + Notification.permission);
            }
        }
        
        async function enableNotifications() {
            log('Requesting notification permission...');
            
            try {
                const permission = await Notification.requestPermission();
                log('Permission result: ' + permission);
                document.getElementById('permission').textContent = permission;
                
                if (permission === 'granted') {
                    log('Registering service worker...');
                    const registration = await navigator.serviceWorker.register('/service-worker.js');
                    log('Service worker registered: ' + registration.scope);
                    
                    log('Subscribing to push...');
                    const vapidKey = document.querySelector('meta[name="vapid-public-key"]')?.content;
                    
                    if (!vapidKey) {
                        log('ERROR: VAPID key not found!');
                        return;
                    }
                    
                    log('VAPID Key length: ' + vapidKey.length);
                    
                    const subscription = await registration.pushManager.subscribe({
                        userVisibleOnly: true,
                        applicationServerKey: urlBase64ToUint8Array(vapidKey)
                    });
                    
                    log('Push subscription created');
                    log('Endpoint: ' + subscription.endpoint);
                    
                    log('Sending subscription to server...');
                    const response = await fetch('/push-subscription', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(subscription)
                    });
                    
                    const data = await response.json();
                    log('Server response: ' + JSON.stringify(data));
                    
                    if (response.ok) {
                        log('SUCCESS: Subscription saved to server!');
                    } else {
                        log('ERROR: Failed to save subscription: ' + JSON.stringify(data));
                    }
                }
            } catch (error) {
                log('ERROR: ' + error.message);
                console.error(error);
            }
        }
        
        function urlBase64ToUint8Array(base64String) {
            const padding = '='.repeat((4 - base64String.length % 4) % 4);
            const base64 = (base64String + padding)
                .replace(/\-/g, '+')
                .replace(/_/g, '/');
            
            const rawData = window.atob(base64);
            const outputArray = new Uint8Array(rawData.length);
            
            for (let i = 0; i < rawData.length; ++i) {
                outputArray[i] = rawData.charCodeAt(i);
            }
            return outputArray;
        }
    </script>
</body>
</html>
