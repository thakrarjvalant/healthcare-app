import React, { useState, useEffect } from 'react';
import './Notification.css';

const NotificationList = () => {
  const [notifications, setNotifications] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    fetchNotifications();
  }, []);

  const fetchNotifications = async () => {
    setLoading(true);
    setError('');
    
    try {
      // In a real app, this would be an API call
      // For now, we'll simulate with mock data
      const mockNotifications = [
        {
          id: 1,
          title: 'Appointment Reminder',
          message: 'You have an appointment with Dr. Smith tomorrow at 10:00 AM.',
          type: 'appointment',
          isRead: false,
          createdAt: '2023-08-20T14:30:00Z'
        },
        {
          id: 2,
          title: 'Payment Due',
          message: 'Your invoice #102 is due in 5 days. Please make the payment.',
          type: 'billing',
          isRead: false,
          createdAt: '2023-08-20T12:15:00Z'
        },
        {
          id: 3,
          title: 'Test Results Available',
          message: 'Your blood test results are now available in your medical records.',
          type: 'medical',
          isRead: true,
          createdAt: '2023-08-19T09:45:00Z'
        }
      ];
      
      setNotifications(mockNotifications);
    } catch (err) {
      setError('Failed to fetch notifications');
    } finally {
      setLoading(false);
    }
  };

  const markAsRead = async (notificationId) => {
    // In a real app, this would be an API call
    // For now, we'll just update the local state
    setNotifications(prevNotifications => 
      prevNotifications.map(notification => 
        notification.id === notificationId ? { ...notification, isRead: true } : notification
      )
    );
  };

  const markAllAsRead = async () => {
    // In a real app, this would be an API call
    // For now, we'll just update the local state
    setNotifications(prevNotifications => 
      prevNotifications.map(notification => ({ ...notification, isRead: true }))
    );
  };

  if (loading) {
    return <div className="notifications-list">Loading notifications...</div>;
  }

  if (error) {
    return <div className="notifications-list error-message">{error}</div>;
  }

  const unreadCount = notifications.filter(n => !n.isRead).length;

  return (
    <div className="notifications-list">
      <div className="notifications-header">
        <h2>Notifications</h2>
        {unreadCount > 0 && (
          <div className="unread-count">
            {unreadCount} unread
          </div>
        )}
        <button 
          className="btn btn-secondary"
          onClick={markAllAsRead}
          disabled={unreadCount === 0}
        >
          Mark All as Read
        </button>
      </div>
      
      {notifications.length === 0 ? (
        <p>No notifications found.</p>
      ) : (
        <div className="notifications-container">
          {notifications.map(notification => (
            <div 
              key={notification.id} 
              className={`notification-card ${notification.isRead ? 'read' : 'unread'}`}
            >
              <div className="notification-header">
                <h3 className="notification-title">{notification.title}</h3>
                {!notification.isRead && (
                  <span className="unread-indicator"></span>
                )}
              </div>
              
              <p className="notification-message">{notification.message}</p>
              
              <div className="notification-footer">
                <span className="notification-type">{notification.type}</span>
                <span className="notification-time">
                  {new Date(notification.createdAt).toLocaleString()}
                </span>
                {!notification.isRead && (
                  <button 
                    className="btn btn-sm btn-primary"
                    onClick={() => markAsRead(notification.id)}
                  >
                    Mark as Read
                  </button>
                )}
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
};

export default NotificationList;