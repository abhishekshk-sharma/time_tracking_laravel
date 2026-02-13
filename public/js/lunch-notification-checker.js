// Lunch notification checker
(function() {
    if (!window.employeeId) {
        console.log('No employee ID found');
        return;
    }
    
    let empId = window.employeeId;
    let notificationCheckInterval = null;
    
    function checkAndShowLunchNotification() {
        fetch(`/lunch-alarm/check/${empId}`)
            .then(response => response.json())
            .then(data => {
                console.log('Lunch check response:', data);
                
                if (data.should_notify) {
                    console.log('Should show notification!');
                    
                    if (Notification.permission === 'granted') {
                        if ('serviceWorker' in navigator) {
                            navigator.serviceWorker.ready.then(registration => {
                                registration.showNotification('🍽️ Lunch Break Reminder', {
                                    body: data.message || 'Your lunch break ends in 5 minutes!',
                                    icon: '/images/notification-icon.png',
                                    badge: '/images/badge-icon.png',
                                    vibrate: [200, 100, 200],
                                    tag: 'lunch-reminder',
                                    requireInteraction: true,
                                    data: {
                                        url: '/dashboard'
                                    }
                                });
                            });
                        } else {
                            // Fallback to regular notification
                            new Notification('🍽️ Lunch Break Reminder', {
                                body: data.message || 'Your lunch break ends in 5 minutes!',
                                icon: '/images/notification-icon.png'
                            });
                        }
                    } else {
                        console.log('Notification permission not granted');
                        alert(data.message || 'Your lunch break ends in 5 minutes!');
                    }
                }
            })
            .catch(error => console.error('Error checking lunch alarm:', error));
    }
    
    // Check every 30 seconds
    notificationCheckInterval = setInterval(checkAndShowLunchNotification, 30000);
    
    // Check immediately on page load
    setTimeout(checkAndShowLunchNotification, 2000);
    
    console.log('Lunch notification checker started for employee:', empId);
})();
