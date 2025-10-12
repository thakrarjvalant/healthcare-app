import React, { useState, useEffect, useContext, createContext } from 'react';
import './NotificationCenter.css';

// Context for global notification management
const NotificationContext = createContext();

export const useNotifications = () => {
  const context = useContext(NotificationContext);
  if (!context) {
    throw new Error('useNotifications must be used within a NotificationProvider');
  }
  return context;
};

// Notification Provider
export const NotificationProvider = ({ children }) => {
  const [notifications, setNotifications] = useState([]);
  const [isConnected, setIsConnected] = useState(false);

  // Add a new notification
  const addNotification = (notification) => {
    const newNotification = {
      id: `notif_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`,
      timestamp: new Date().toISOString(),
      read: false,
      ...notification
    };

    setNotifications(prev => [newNotification, ...prev.slice(0, 99)]); // Keep max 100 notifications
    return newNotification.id;
  };

  // Remove a notification
  const removeNotification = (id) => {
    setNotifications(prev => prev.filter(notif => notif.id !== id));
  };

  // Mark notification as read
  const markAsRead = (id) => {
    setNotifications(prev => 
      prev.map(notif => 
        notif.id === id ? { ...notif, read: true } : notif
      )
    );
  };

  // Mark all notifications as read
  const markAllAsRead = () => {
    setNotifications(prev => 
      prev.map(notif => ({ ...notif, read: true }))
    );
  };

  // Clear all notifications
  const clearAll = () => {
    setNotifications([]);
  };

  // Get unread count
  const getUnreadCount = () => {
    return notifications.filter(notif => !notif.read).length;
  };

  // Simulate real-time connection (in production, this would be WebSocket/SSE)
  useEffect(() => {
    setIsConnected(true);
    
    // Simulate incoming notifications for demo
    const simulateNotifications = () => {
      const sampleNotifications = [
        { type: 'appointment', title: 'New Appointment', message: 'You have a new appointment request', priority: 'medium' },
        { type: 'lab_result', title: 'Lab Results', message: 'Lab results are ready for review', priority: 'high' },
        { type: 'system', title: 'System Update', message: 'System maintenance scheduled for tonight', priority: 'low' },
        { type: 'emergency', title: 'Emergency Alert', message: 'Emergency situation in Room 302', priority: 'critical' },
        { type: 'payment', title: 'Payment Received', message: 'Payment received for invoice #12345', priority: 'medium' }
      ];

      // Add a random notification every 30 seconds for demo
      const interval = setInterval(() => {
        if (Math.random() > 0.7) { // 30% chance every 30 seconds
          const randomNotif = sampleNotifications[Math.floor(Math.random() * sampleNotifications.length)];
          addNotification(randomNotif);
        }
      }, 30000);

      return () => clearInterval(interval);
    };

    const cleanup = simulateNotifications();
    return cleanup;
  }, []);

  const contextValue = {
    notifications,
    addNotification,
    removeNotification,
    markAsRead,
    markAllAsRead,
    clearAll,
    getUnreadCount,
    isConnected
  };

  return (
    <NotificationContext.Provider value={contextValue}>
      {children}
    </NotificationContext.Provider>
  );
};

// Notification Bell Component
export const NotificationBell = ({ className = '' }) => {
  const { notifications, getUnreadCount, markAsRead, clearAll } = useNotifications();
  const [isOpen, setIsOpen] = useState(false);
  const unreadCount = getUnreadCount();

  const handleNotificationClick = (notification) => {
    if (!notification.read) {
      markAsRead(notification.id);
    }
    // Handle notification-specific actions here
    setIsOpen(false);
  };

  const getPriorityClass = (priority) => {
    switch (priority) {
      case 'critical': return 'priority-critical';
      case 'high': return 'priority-high';
      case 'medium': return 'priority-medium';
      case 'low': return 'priority-low';
      default: return 'priority-medium';
    }
  };

  const getTypeIcon = (type) => {
    switch (type) {
      case 'appointment': return '📅';
      case 'lab_result': return '🧪';
      case 'system': return '⚙️';
      case 'emergency': return '🚨';
      case 'payment': return '💳';
      case 'message': return '💬';
      default: return '🔔';
    }
  };

  const formatTimestamp = (timestamp) => {
    const now = new Date();
    const notifTime = new Date(timestamp);
    const diffInMinutes = Math.floor((now - notifTime) / (1000 * 60));
    
    if (diffInMinutes < 1) return 'Just now';
    if (diffInMinutes < 60) return `${diffInMinutes}m ago`;
    if (diffInMinutes < 1440) return `${Math.floor(diffInMinutes / 60)}h ago`;
    return `${Math.floor(diffInMinutes / 1440)}d ago`;
  };

  return (
    <div className={`notification-bell ${className}`}>
      <button 
        className="bell-button"
        onClick={() => setIsOpen(!isOpen)}
        aria-label={`Notifications (${unreadCount} unread)`}
      >
        🔔
        {unreadCount > 0 && (
          <span className="notification-badge">{unreadCount > 99 ? '99+' : unreadCount}</span>
        )}
      </button>

      {isOpen && (
        <div className="notifications-dropdown">
          <div className="notifications-header">
            <h3>Notifications</h3>
            {notifications.length > 0 && (
              <button onClick={clearAll} className="clear-all-btn">
                Clear All
              </button>
            )}
          </div>

          <div className="notifications-list">
            {notifications.length === 0 ? (
              <div className="no-notifications">
                <span>🔕</span>
                <p>No notifications</p>
              </div>
            ) : (
              notifications.slice(0, 10).map(notification => (
                <div
                  key={notification.id}
                  className={`notification-item ${!notification.read ? 'unread' : ''} ${getPriorityClass(notification.priority)}`}
                  onClick={() => handleNotificationClick(notification)}
                >
                  <div className="notification-icon">
                    {getTypeIcon(notification.type)}
                  </div>
                  <div className="notification-content">
                    <div className="notification-title">{notification.title}</div>
                    <div className="notification-message">{notification.message}</div>
                    <div className="notification-time">{formatTimestamp(notification.timestamp)}</div>
                  </div>
                  {!notification.read && <div className="unread-indicator"></div>}
                </div>
              ))
            )}
          </div>

          {notifications.length > 10 && (
            <div className="notifications-footer">
              <button className="view-all-btn">View All Notifications</button>
            </div>
          )}
        </div>
      )}
    </div>
  );
};

// Toast Notification Component
export const ToastNotification = ({ notification, onClose }) => {
  useEffect(() => {
    const timer = setTimeout(() => {
      onClose();
    }, 5000); // Auto-close after 5 seconds

    return () => clearTimeout(timer);
  }, [onClose]);

  const getPriorityClass = (priority) => {
    switch (priority) {
      case 'critical': return 'toast-critical';
      case 'high': return 'toast-high';
      case 'medium': return 'toast-medium';
      case 'low': return 'toast-low';
      default: return 'toast-medium';
    }
  };

  return (
    <div className={`toast-notification ${getPriorityClass(notification.priority)}`}>
      <div className="toast-content">
        <div className="toast-title">{notification.title}</div>
        <div className="toast-message">{notification.message}</div>
      </div>
      <button className="toast-close" onClick={onClose}>×</button>
    </div>
  );
};

// Toast Container for managing multiple toasts
export const ToastContainer = () => {
  const { notifications } = useNotifications();
  const [activeToasts, setActiveToasts] = useState([]);

  useEffect(() => {
    // Show toasts for critical and high priority notifications
    const criticalNotifications = notifications.filter(
      notif => !notif.read && (notif.priority === 'critical' || notif.priority === 'high')
    );

    setActiveToasts(criticalNotifications.slice(0, 3)); // Show max 3 toasts
  }, [notifications]);

  const handleToastClose = (notificationId) => {
    setActiveToasts(prev => prev.filter(toast => toast.id !== notificationId));
  };

  return (
    <div className="toast-container">
      {activeToasts.map(notification => (
        <ToastNotification
          key={notification.id}
          notification={notification}
          onClose={() => handleToastClose(notification.id)}
        />
      ))}
    </div>
  );
};

export default NotificationContext;