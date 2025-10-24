// Notification Sync Manager - Synchronizes notifications between web and mobile
class NotificationSyncManager {
    constructor() {
        this.notifications = [];
        this.unreadCount = 0;
        this.isOnline = navigator.onLine;
        this.syncInProgress = false;
        this.lastSyncTime = null;
        this.baseUrl = '/api';
        this.pollingInterval = null;
        this.pollingDelay = 30000; // 30 seconds
        
        this.initializeEventListeners();
        this.initializeNotificationUI();
        
        // Try to load notifications if user is authenticated
        if (this.isAuthenticated()) {
            this.loadNotifications();
            this.startPolling();
        }
    }

    // Check if user is authenticated
    isAuthenticated() {
        const userIdMeta = document.querySelector('meta[name="user-id"]');
        return userIdMeta && userIdMeta.getAttribute('content');
    }

    // Get user ID
    getUserId() {
        const userIdMeta = document.querySelector('meta[name="user-id"]');
        return userIdMeta ? userIdMeta.getAttribute('content') : null;
    }

    // Initialize event listeners
    initializeEventListeners() {
        // Online/offline status
        window.addEventListener('online', () => {
            this.isOnline = true;
            if (this.isAuthenticated()) {
                this.loadNotifications();
                this.startPolling();
            }
        });
        
        window.addEventListener('offline', () => {
            this.isOnline = false;
            this.stopPolling();
        });

        // Auth status changes (for login/logout)
        const observer = new MutationObserver(() => {
            const isAuth = this.isAuthenticated();
            if (isAuth !== this.isAuthenticated()) {
                if (isAuth) {
                    this.loadNotifications();
                    this.startPolling();
                } else {
                    this.stopPolling();
                    this.clearNotifications();
                }
            }
        });
        
        observer.observe(document.head, { childList: true, subtree: true });
    }

    // Initialize notification UI
    initializeNotificationUI() {
        // Create notification bell if it doesn't exist
        if (!document.getElementById('notificationBell')) {
            this.createNotificationBell();
        }
    }

    // Create notification bell in the header
    createNotificationBell() {
        const header = document.querySelector('header, .header, nav, .navbar');
        if (!header) return;

        // Find existing notification area or create one
        let notificationArea = header.querySelector('.notification-area');
        if (!notificationArea) {
            notificationArea = document.createElement('div');
            notificationArea.className = 'notification-area flex items-center space-x-4';
            header.appendChild(notificationArea);
        }

        // Create notification bell
        const bellHTML = `
            <div class="relative">
                <button id="notificationBell" class="flex items-center space-x-2 text-gray-600 hover:text-gray-800 focus:outline-none transition-colors duration-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM11 19H6l5-5v5zM15 7h5l-5-5v5zM11 5H6l5 5V5z"/>
                    </svg>
                    <span id="notificationCount" class="bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center hidden">0</span>
                </button>
                
                <div id="notificationsDropdown" class="hidden fixed right-4 top-20 w-80 bg-white rounded-lg shadow-xl z-50 border border-gray-200">
                    <div class="p-3 border-b border-gray-200">
                        <div class="flex justify-between items-center">
                            <h3 class="text-lg font-semibold text-gray-900">Notifications</h3>
                            <button id="markAllRead" class="text-sm text-blue-600 hover:text-blue-800">Mark all read</button>
                        </div>
                    </div>
                    <div id="notificationsContent" class="max-h-[400px] overflow-y-auto p-3 space-y-3">
                        <div class="text-center text-gray-500 py-4">Loading notifications...</div>
                    </div>
                </div>
            </div>
        `;

        notificationArea.insertAdjacentHTML('beforeend', bellHTML);

        // Add event listeners
        this.addNotificationEventListeners();
    }

    // Add event listeners for notification interactions
    addNotificationEventListeners() {
        const bell = document.getElementById('notificationBell');
        const dropdown = document.getElementById('notificationsDropdown');
        const markAllRead = document.getElementById('markAllRead');

        if (bell && dropdown) {
            bell.addEventListener('click', (e) => {
                e.stopPropagation();
                dropdown.classList.toggle('hidden');
                
                // Mark all as read when opening dropdown
                if (!dropdown.classList.contains('hidden')) {
                    this.markAllAsRead();
                }
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', (e) => {
                if (!bell.contains(e.target) && !dropdown.contains(e.target)) {
                    dropdown.classList.add('hidden');
                }
            });
        }

        if (markAllRead) {
            markAllRead.addEventListener('click', () => {
                this.markAllAsRead();
            });
        }
    }

    // Load notifications from server
    async loadNotifications() {
        if (!this.isAuthenticated() || this.syncInProgress || !this.isOnline) {
            return;
        }

        this.syncInProgress = true;
        
        try {
            const response = await fetch(`${this.baseUrl}/notifications`, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                credentials: 'same-origin'
            });

            if (response.ok) {
                const data = await response.json();
                if (data.success) {
                    this.notifications = data.notifications || [];
                    this.updateUnreadCount();
                    this.updateNotificationUI();
                    this.lastSyncTime = new Date();
                    
                    console.log('✅ Notifications loaded successfully');
                } else {
                    console.error('❌ Notification load failed:', data.message);
                }
            } else {
                console.error('❌ Notification load HTTP error:', response.status);
            }
        } catch (error) {
            console.error('❌ Notification load error:', error);
        } finally {
            this.syncInProgress = false;
        }
    }

    // Mark all notifications as read
    async markAllAsRead() {
        if (!this.isAuthenticated() || !this.isOnline) {
            return;
        }

        try {
            const response = await fetch(`${this.baseUrl}/notifications/mark-all-as-read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                credentials: 'same-origin'
            });

            if (response.ok) {
                const data = await response.json();
                if (data.success) {
                    // Update local state
                    this.notifications.forEach(notification => {
                        notification.read_at = new Date().toISOString();
                    });
                    this.updateUnreadCount();
                    this.updateNotificationUI();
                    
                    console.log('✅ All notifications marked as read');
                }
            }
        } catch (error) {
            console.error('❌ Mark all as read error:', error);
        }
    }

    // Update unread count
    updateUnreadCount() {
        this.unreadCount = this.notifications.filter(n => !n.read_at).length;
        this.updateNotificationCount();
    }

    // Update notification count display
    updateNotificationCount() {
        const countElement = document.getElementById('notificationCount');
        if (countElement) {
            if (this.unreadCount > 0) {
                countElement.textContent = this.unreadCount > 99 ? '99+' : this.unreadCount;
                countElement.classList.remove('hidden');
            } else {
                countElement.classList.add('hidden');
            }
        }
    }

    // Update notification UI
    updateNotificationUI() {
        const content = document.getElementById('notificationsContent');
        if (!content) return;

        if (this.notifications.length === 0) {
            content.innerHTML = '<div class="text-center text-gray-500 py-4">No notifications yet</div>';
            return;
        }

        const notificationsHTML = this.notifications.map(notification => {
            const isRead = notification.read_at;
            const timeAgo = this.getTimeAgo(notification.created_at);
            
            return `
                <div class="notification-item p-3 rounded-lg border ${isRead ? 'bg-gray-50' : 'bg-blue-50 border-blue-200'}">
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            <div class="w-2 h-2 rounded-full ${isRead ? 'bg-gray-300' : 'bg-blue-500'}"></div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900">${this.escapeHtml(notification.data.title || 'Notification')}</p>
                            <p class="text-sm text-gray-600 mt-1">${this.escapeHtml(notification.data.message || notification.data.body || '')}</p>
                            <p class="text-xs text-gray-400 mt-2">${timeAgo}</p>
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        content.innerHTML = notificationsHTML;
    }

    // Get time ago string
    getTimeAgo(dateString) {
        const now = new Date();
        const date = new Date(dateString);
        const diffInSeconds = Math.floor((now - date) / 1000);

        if (diffInSeconds < 60) return 'Just now';
        if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)}m ago`;
        if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)}h ago`;
        return `${Math.floor(diffInSeconds / 86400)}d ago`;
    }

    // Escape HTML to prevent XSS
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Start polling for new notifications
    startPolling() {
        if (this.pollingInterval) return;
        
        this.pollingInterval = setInterval(() => {
            if (this.isOnline && this.isAuthenticated()) {
                this.loadNotifications();
            }
        }, this.pollingDelay);
    }

    // Stop polling
    stopPolling() {
        if (this.pollingInterval) {
            clearInterval(this.pollingInterval);
            this.pollingInterval = null;
        }
    }

    // Clear notifications (on logout)
    clearNotifications() {
        this.notifications = [];
        this.unreadCount = 0;
        this.updateNotificationCount();
        this.updateNotificationUI();
    }

    // Get notification status
    getNotificationStatus() {
        return {
            isOnline: this.isOnline,
            syncInProgress: this.syncInProgress,
            lastSyncTime: this.lastSyncTime,
            hasAuthToken: this.isAuthenticated(),
            unreadCount: this.unreadCount,
            totalCount: this.notifications.length
        };
    }
}

// Initialize notification sync manager
window.notificationSyncManager = new NotificationSyncManager();

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = NotificationSyncManager;
}



