// resources/js/push-notifications.js

class PushNotificationManager {
    constructor() {
        this.vapidPublicKey = document.querySelector('meta[name="vapid-public-key"]')?.content;
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        this.subscribeButton = document.getElementById('enable-notifications');
        
        this.init();
    }

    async init() {
        if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
            console.log('Push notifications not supported');
            return;
        }

        const permission = await this.checkPermission();
        
        if (permission === 'granted') {
            await this.registerServiceWorker();
        }

        if (this.subscribeButton) {
            this.subscribeButton.addEventListener('click', () => this.requestPermission());
        }
    }

    async checkPermission() {
        if (!('Notification' in window)) return 'denied';
        
        if (Notification.permission === 'granted') {
            this.updateUI('enabled');
            return 'granted';
        } else if (Notification.permission === 'denied') {
            this.updateUI('denied');
            return 'denied';
        }
        
        this.updateUI('prompt');
        return 'prompt';
    }

    async requestPermission() {
        try {
            const permission = await Notification.requestPermission();
            
            if (permission === 'granted') {
                this.updateUI('enabled');
                await this.registerServiceWorker();
            } else {
                this.updateUI('denied');
            }
        } catch (error) {
            console.error('Error requesting permission:', error);
        }
    }

    async registerServiceWorker() {
        try {
            const registration = await navigator.serviceWorker.register('/service-worker.js');
            console.log('Service Worker registered:', registration);
            
            const subscription = await registration.pushManager.getSubscription();
            
            if (!subscription) {
                await this.subscribeUser(registration);
            } else {
                await this.sendSubscriptionToServer(subscription);
            }
        } catch (error) {
            console.error('Service Worker registration failed:', error);
        }
    }

    async subscribeUser(registration) {
        try {
            console.log('VAPID Key:', this.vapidPublicKey);
            
            if (!this.vapidPublicKey) {
                alert('ERROR: VAPID key not found! Check your .env configuration.');
                console.error('VAPID key is missing');
                return;
            }
            
            const subscription = await registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: this.urlBase64ToUint8Array(this.vapidPublicKey)
            });

            console.log('Subscribed to push notifications:', subscription);
            await this.sendSubscriptionToServer(subscription);
            
        } catch (error) {
            console.error('Failed to subscribe:', error);
            alert('Failed to subscribe: ' + error.message);
        }
    }

    async sendSubscriptionToServer(subscription) {
        try {
            console.log('Sending subscription to server:', subscription);
            
            const response = await fetch('/push-subscription', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(subscription)
            });

            const data = await response.json();
            console.log('Server response:', data);
            
            if (response.ok) {
                console.log('Subscription saved to server');
                this.showSuccess('Notifications enabled successfully!');
            } else {
                console.error('Failed to save subscription:', data);
                alert('Failed to save subscription: ' + (data.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error saving subscription:', error);
            alert('Error saving subscription: ' + error.message);
        }
    }

    urlBase64ToUint8Array(base64String) {
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

    updateUI(status) {
        if (!this.subscribeButton) return;
        
        switch(status) {
            case 'enabled':
                this.subscribeButton.innerHTML = '<i class="fas fa-check-circle"></i> Notifications Enabled';
                this.subscribeButton.classList.remove('btn-primary', 'btn-warning');
                this.subscribeButton.classList.add('btn-success');
                this.subscribeButton.disabled = true;
                break;
                
            case 'denied':
                this.subscribeButton.innerHTML = '<i class="fas fa-times-circle"></i> Notifications Blocked';
                this.subscribeButton.classList.remove('btn-primary', 'btn-success');
                this.subscribeButton.classList.add('btn-danger');
                this.subscribeButton.disabled = true;
                break;
                
            case 'prompt':
                this.subscribeButton.innerHTML = '<i class="fas fa-bell"></i> Enable Notifications';
                this.subscribeButton.classList.remove('btn-success', 'btn-danger');
                this.subscribeButton.classList.add('btn-primary');
                this.subscribeButton.disabled = false;
                break;
        }
    }

    showSuccess(message) {
        const toast = document.createElement('div');
        toast.className = 'alert alert-success alert-dismissible fade show position-fixed top-0 end-0 m-3';
        toast.style.zIndex = '9999';
        toast.innerHTML = `
            <strong>Success!</strong> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(toast);
        
        setTimeout(() => toast.remove(), 5000);
    }
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.pushManager = new PushNotificationManager();
    });
} else {
    window.pushManager = new PushNotificationManager();
}