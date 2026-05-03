/**
 * EditPro - Push Notifications for Admin
 * Browser-based push notifications for new orders
 */

// Notification configuration
const NOTIFICATION_ICON = '🔔';
const NOTIFICATION_TITLE = 'EditPro - New Order!';

// Request notification permission
function requestNotificationPermission() {
    if (!('Notification' in window)) {
        console.log('This browser does not support notifications');
        return false;
    }
    
    if (Notification.permission === 'granted') {
        console.log('Notification permission already granted');
        return true;
    }
    
    if (Notification.permission !== 'denied') {
        Notification.requestPermission().then(permission => {
            if (permission === 'granted') {
                showNotification('Notifications enabled!', 'You will be notified of new orders.');
                return true;
            }
        });
    }
    
    return false;
}

// Show a browser notification
function showNotification(title, body, icon = NOTIFICATION_ICON) {
    if (Notification.permission === 'granted') {
        const notification = new Notification(title, {
            body: body,
            icon: icon,
            badge: '/images/logo.png',
            vibrate: [200, 100, 200],
            tag: 'editpro-order',
            renotify: true
        });
        
        notification.onclick = function() {
            window.focus();
            window.location.href = 'admin_orders.php';
            this.close();
        };
        
        return notification;
    }
    return null;
}

// Play notification sound
function playNotificationSound() {
    // Create a simple beep sound using Web Audio API
    try {
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = audioContext.createOscillator();
        const gainNode = audioContext.createGain();
        
        oscillator.connect(gainNode);
        gainNode.connect(audioContext.destination);
        
        oscillator.frequency.value = 800;
        oscillator.type = 'sine';
        gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
        gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.3);
        
        oscillator.start(audioContext.currentTime);
        oscillator.stop(audioContext.currentTime + 0.3);
    } catch (e) {
        console.log('Could not play notification sound');
    }
}

// Poll for new orders
let lastOrderId = 0;

function checkForNewOrders() {
    fetch('check_new_orders.php?last_id=' + lastOrderId)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.new_orders > 0) {
                if (lastOrderId > 0) {
                    // This is not the first check, so it's a new order
                    const message = `You have ${data.new_orders} new order(s)!`;
                    showNotification(NOTIFICATION_TITLE, message);
                    playNotificationSound();
                    
                    // Update page if user is on the orders page
                    if (window.location.href.includes('admin_orders.php')) {
                        // Reload the page to show new orders
                        const toast = document.createElement('div');
                        toast.style.cssText = `
                            position: fixed;
                            top: 20px;
                            right: 20px;
                            background: #10b981;
                            color: white;
                            padding: 16px 24px;
                            border-radius: 12px;
                            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
                            z-index: 10000;
                            cursor: pointer;
                        `;
                        toast.textContent = `🎉 ${data.new_orders} new order(s) received! Click to refresh.`;
                        toast.onclick = () => window.location.reload();
                        document.body.appendChild(toast);
                        
                        // Auto-refresh after 3 seconds
                        setTimeout(() => window.location.reload(), 5000);
                    }
                }
                
                // Update last order ID
                lastOrderId = data.last_id;
            }
        })
        .catch(error => console.log('Error checking for new orders:', error));
}

// Initialize notifications
function initNotifications() {
    // Request permission on load
    requestNotificationPermission();
    
    // Get the highest order ID
    fetch('get_last_order_id.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                lastOrderId = data.last_id || 0;
            }
        })
        .catch(error => console.log('Error getting last order ID:', error));
    
    // Check for new orders every 30 seconds
    setInterval(checkForNewOrders, 30000);
}

// Function to test notifications
function testNotification() {
    showNotification('Test Notification', 'Notifications are working correctly!');
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initNotifications);
} else {
    initNotifications();
}

