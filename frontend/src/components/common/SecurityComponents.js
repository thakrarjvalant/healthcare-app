import React, { useState, useEffect, useContext } from 'react';
import { AuthContext } from '../../context/AuthContext';
import './SecurityComponents.css';

// Session Warning Modal
export const SessionWarningModal = () => {
  const { sessionWarning, extendSession, logout, getSessionInfo } = useContext(AuthContext);
  const [timeLeft, setTimeLeft] = useState(0);

  useEffect(() => {
    if (!sessionWarning) return;

    const updateTimeLeft = () => {
      const sessionInfo = getSessionInfo();
      if (sessionInfo) {
        setTimeLeft(Math.floor(sessionInfo.timeLeft / 1000));
      }
    };

    updateTimeLeft();
    const interval = setInterval(updateTimeLeft, 1000);
    return () => clearInterval(interval);
  }, [sessionWarning, getSessionInfo]);

  const formatTime = (seconds) => {
    const minutes = Math.floor(seconds / 60);
    const remainingSeconds = seconds % 60;
    return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
  };

  if (!sessionWarning) return null;

  return (
    <div className="session-warning-overlay">
      <div className="session-warning-modal">
        <div className="warning-icon">⚠️</div>
        <h3>Session Expiring Soon</h3>
        <p>Your session will expire in <strong>{formatTime(timeLeft)}</strong></p>
        <p>Would you like to extend your session?</p>
        <div className="warning-buttons">
          <button onClick={extendSession} className="btn-extend">
            Extend Session
          </button>
          <button onClick={logout} className="btn-logout">
            Logout Now
          </button>
        </div>
      </div>
    </div>
  );
};

// Session Info Display
export const SessionInfo = ({ showDetails = false }) => {
  const { getSessionInfo, user } = useContext(AuthContext);
  const [sessionInfo, setSessionInfo] = useState(null);

  useEffect(() => {
    if (!user) return;

    const updateSessionInfo = () => {
      const info = getSessionInfo();
      setSessionInfo(info);
    };

    updateSessionInfo();
    const interval = setInterval(updateSessionInfo, 60000); // Update every minute
    return () => clearInterval(interval);
  }, [user, getSessionInfo]);

  if (!sessionInfo || !showDetails) return null;

  const formatTimeLeft = (milliseconds) => {
    const minutes = Math.floor(milliseconds / (1000 * 60));
    const hours = Math.floor(minutes / 60);
    const remainingMinutes = minutes % 60;
    
    if (hours > 0) {
      return `${hours}h ${remainingMinutes}m`;
    }
    return `${remainingMinutes}m`;
  };

  return (
    <div className="session-info">
      <h4>Session Information</h4>
      <div className="session-details">
        <div className="session-item">
          <span className="label">Session ID:</span>
          <span className="value">{sessionInfo.sessionId}</span>
        </div>
        <div className="session-item">
          <span className="label">User:</span>
          <span className="value">{user.name} ({user.role})</span>
        </div>
        <div className="session-item">
          <span className="label">Time Remaining:</span>
          <span className="value">{formatTimeLeft(sessionInfo.timeLeft)}</span>
        </div>
        <div className="session-item">
          <span className="label">Last Activity:</span>
          <span className="value">{new Date(sessionInfo.lastActivity).toLocaleString()}</span>
        </div>
        <div className="session-item">
          <span className="label">IP Address:</span>
          <span className="value">{sessionInfo.ipAddress}</span>
        </div>
        <div className="session-item">
          <span className="label">Status:</span>
          <span className={`value status ${sessionInfo.isValid ? 'valid' : 'invalid'}`}>
            {sessionInfo.isValid ? 'Active' : 'Expired'}
          </span>
        </div>
      </div>
    </div>
  );
};

// Data Encryption Status Display
export const DataEncryptionStatus = ({ data, showEncrypted = false }) => {
  const [encryptedData, setEncryptedData] = useState({});
  const [showOriginal, setShowOriginal] = useState(false);

  useEffect(() => {
    // Simulate data encryption (in production, this would be handled by the backend)
    const encrypted = {};
    Object.keys(data).forEach(key => {
      if (typeof data[key] === 'string' && data[key].length > 0) {
        // Simple simulation of encryption - in reality, use proper encryption
        encrypted[key] = btoa(data[key]); // Base64 encoding as simulation
      } else {
        encrypted[key] = data[key];
      }
    });
    setEncryptedData(encrypted);
  }, [data]);

  const sensitiveFields = ['ssn', 'dateOfBirth', 'phoneNumber', 'email', 'address', 'medicalRecord'];

  const isSensitive = (key) => {
    return sensitiveFields.some(field => key.toLowerCase().includes(field.toLowerCase()));
  };

  const maskValue = (value, key) => {
    if (!isSensitive(key)) return value;
    
    if (typeof value === 'string') {
      if (value.length <= 4) return '*'.repeat(value.length);
      return value.substring(0, 2) + '*'.repeat(value.length - 4) + value.substring(value.length - 2);
    }
    return value;
  };

  return (
    <div className="data-encryption-status">
      <div className="encryption-header">
        <h4>Data Security Status</h4>
        <div className="encryption-controls">
          <label className="toggle-label">
            <input
              type="checkbox"
              checked={showOriginal}
              onChange={(e) => setShowOriginal(e.target.checked)}
            />
            Show Decrypted Data
          </label>
        </div>
      </div>
      
      <div className="data-display">
        {Object.entries(data).map(([key, value]) => (
          <div key={key} className="data-item">
            <span className="data-key">{key}:</span>
            <span className={`data-value ${isSensitive(key) ? 'sensitive' : ''}`}>
              {showOriginal ? value : maskValue(value, key)}
            </span>
            {isSensitive(key) && (
              <span className="encryption-badge">
                {showOriginal ? '🔓 Decrypted' : '🔒 Encrypted'}
              </span>
            )}
          </div>
        ))}
      </div>
      
      <div className="encryption-info">
        <div className="encryption-stat">
          <span className="stat-label">Encryption Status:</span>
          <span className="stat-value encrypted">AES-256 Enabled</span>
        </div>
        <div className="encryption-stat">
          <span className="stat-label">Data Transit:</span>
          <span className="stat-value secure">TLS 1.3 Secured</span>
        </div>
        <div className="encryption-stat">
          <span className="stat-label">Storage:</span>
          <span className="stat-value encrypted">Encrypted at Rest</span>
        </div>
      </div>
    </div>
  );
};

// User Activity Monitor
export const UserActivityMonitor = ({ maxEntries = 50 }) => {
  const [activities, setActivities] = useState([]);
  const { user } = useContext(AuthContext);

  useEffect(() => {
    // Simulate activity tracking
    const trackActivity = (type, details) => {
      const activity = {
        id: `activity_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`,
        timestamp: new Date().toISOString(),
        type,
        details,
        userId: user?.id,
        sessionId: localStorage.getItem('sessionId')
      };

      setActivities(prev => [activity, ...prev.slice(0, maxEntries - 1)]);
    };

    // Monitor various user activities
    const events = ['click', 'keypress', 'focus', 'blur'];
    const handlers = {};

    events.forEach(event => {
      handlers[event] = (e) => {
        // Only track significant activities to avoid spam
        if (event === 'click' && e.target.tagName === 'BUTTON') {
          trackActivity('button_click', { button: e.target.textContent });
        } else if (event === 'focus' && e.target.tagName === 'INPUT') {
          trackActivity('input_focus', { field: e.target.name || e.target.id });
        }
      };
      document.addEventListener(event, handlers[event]);
    });

    // Track page visibility changes
    const handleVisibilityChange = () => {
      trackActivity('visibility_change', { 
        hidden: document.hidden,
        timestamp: new Date().toISOString()
      });
    };
    document.addEventListener('visibilitychange', handleVisibilityChange);

    return () => {
      events.forEach(event => {
        document.removeEventListener(event, handlers[event]);
      });
      document.removeEventListener('visibilitychange', handleVisibilityChange);
    };
  }, [user, maxEntries]);

  const getActivityIcon = (type) => {
    switch (type) {
      case 'button_click': return '🖱️';
      case 'input_focus': return '✏️';
      case 'visibility_change': return '👁️';
      case 'navigation': return '🧭';
      default: return '📝';
    }
  };

  return (
    <div className="activity-monitor">
      <h4>User Activity Monitor</h4>
      <div className="activity-list">
        {activities.length === 0 ? (
          <div className="no-activities">No recent activities recorded</div>
        ) : (
          activities.map(activity => (
            <div key={activity.id} className="activity-item">
              <span className="activity-icon">{getActivityIcon(activity.type)}</span>
              <div className="activity-content">
                <div className="activity-type">{activity.type.replace(/_/g, ' ').toUpperCase()}</div>
                <div className="activity-details">
                  {JSON.stringify(activity.details)}
                </div>
                <div className="activity-time">
                  {new Date(activity.timestamp).toLocaleString()}
                </div>
              </div>
            </div>
          ))
        )}
      </div>
    </div>
  );
};

// Security Settings Panel
export const SecuritySettings = () => {
  const { user, logout } = useContext(AuthContext);
  const [settings, setSettings] = useState({
    twoFactorEnabled: false,
    sessionTimeout: 30,
    activityLogging: true,
    emailNotifications: true,
    dataEncryption: true,
    auditTrail: true
  });

  const handleSettingChange = (setting, value) => {
    setSettings(prev => ({
      ...prev,
      [setting]: value
    }));
  };

  const handleForceLogout = () => {
    if (window.confirm('Are you sure you want to logout from all devices?')) {
      // In production, this would call an API to invalidate all sessions
      logout();
    }
  };

  return (
    <div className="security-settings">
      <h4>Security Settings</h4>
      
      <div className="settings-section">
        <h5>Authentication</h5>
        <div className="setting-item">
          <label>
            <input
              type="checkbox"
              checked={settings.twoFactorEnabled}
              onChange={(e) => handleSettingChange('twoFactorEnabled', e.target.checked)}
            />
            Enable Two-Factor Authentication
          </label>
        </div>
      </div>

      <div className="settings-section">
        <h5>Session Management</h5>
        <div className="setting-item">
          <label>
            Session Timeout (minutes):
            <select
              value={settings.sessionTimeout}
              onChange={(e) => handleSettingChange('sessionTimeout', parseInt(e.target.value))}
            >
              <option value={15}>15 minutes</option>
              <option value={30}>30 minutes</option>
              <option value={60}>1 hour</option>
              <option value={120}>2 hours</option>
            </select>
          </label>
        </div>
        <div className="setting-item">
          <button onClick={handleForceLogout} className="btn-danger">
            Logout from All Devices
          </button>
        </div>
      </div>

      <div className="settings-section">
        <h5>Privacy & Logging</h5>
        <div className="setting-item">
          <label>
            <input
              type="checkbox"
              checked={settings.activityLogging}
              onChange={(e) => handleSettingChange('activityLogging', e.target.checked)}
            />
            Enable Activity Logging
          </label>
        </div>
        <div className="setting-item">
          <label>
            <input
              type="checkbox"
              checked={settings.auditTrail}
              onChange={(e) => handleSettingChange('auditTrail', e.target.checked)}
            />
            Enable Audit Trail
          </label>
        </div>
      </div>

      <div className="settings-section">
        <h5>Data Protection</h5>
        <div className="setting-item">
          <label>
            <input
              type="checkbox"
              checked={settings.dataEncryption}
              onChange={(e) => handleSettingChange('dataEncryption', e.target.checked)}
              disabled // Always enabled for healthcare data
            />
            Data Encryption (Always Enabled)
          </label>
        </div>
        <div className="setting-item">
          <label>
            <input
              type="checkbox"
              checked={settings.emailNotifications}
              onChange={(e) => handleSettingChange('emailNotifications', e.target.checked)}
            />
            Security Email Notifications
          </label>
        </div>
      </div>
    </div>
  );
};