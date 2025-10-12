import React, { useState, useEffect, useContext } from 'react';
import { AuthContext } from '../../context/AuthContext';
import './AuditLogger.css';

const AuditLogger = {
  // Log an action to the audit system
  logAction: async (action, details = {}, userId = null, userRole = null) => {
    const logEntry = {
      id: `audit_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`,
      timestamp: new Date().toISOString(),
      userId: userId || 'system',
      userRole: userRole || 'system',
      action,
      details,
      ipAddress: '127.0.0.1', // In a real app, this would come from the server
      sessionId: localStorage.getItem('sessionId') || 'unknown',
      status: 'success'
    };

    try {
      // Store locally for demo purposes - in production, this would be sent to backend
      const existingLogs = JSON.parse(localStorage.getItem('auditLogs') || '[]');
      existingLogs.unshift(logEntry);
      
      // Keep only last 1000 entries to prevent localStorage overflow
      if (existingLogs.length > 1000) {
        existingLogs.splice(1000);
      }
      
      localStorage.setItem('auditLogs', JSON.stringify(existingLogs));
      
      // In production, send to backend:
      // await fetch('/api/audit-logs', {
      //   method: 'POST',
      //   headers: { 'Content-Type': 'application/json' },
      //   body: JSON.stringify(logEntry)
      // });
      
      return logEntry;
    } catch (error) {
      console.error('Audit logging failed:', error);
      return null;
    }
  },

  // Get audit logs with filtering
  getLogs: (filters = {}) => {
    try {
      const logs = JSON.parse(localStorage.getItem('auditLogs') || '[]');
      let filteredLogs = logs;

      if (filters.userId) {
        filteredLogs = filteredLogs.filter(log => log.userId === filters.userId);
      }
      
      if (filters.action) {
        filteredLogs = filteredLogs.filter(log => 
          log.action.toLowerCase().includes(filters.action.toLowerCase())
        );
      }
      
      if (filters.dateFrom) {
        filteredLogs = filteredLogs.filter(log => 
          new Date(log.timestamp) >= new Date(filters.dateFrom)
        );
      }
      
      if (filters.dateTo) {
        filteredLogs = filteredLogs.filter(log => 
          new Date(log.timestamp) <= new Date(filters.dateTo)
        );
      }

      return filteredLogs;
    } catch (error) {
      console.error('Failed to retrieve audit logs:', error);
      return [];
    }
  }
};

// React component for displaying audit logs
export const AuditLogViewer = ({ filters = {}, maxEntries = 100 }) => {
  const [logs, setLogs] = useState([]);
  const [loading, setLoading] = useState(true);
  const [currentFilters, setCurrentFilters] = useState(filters);

  useEffect(() => {
    const fetchLogs = () => {
      setLoading(true);
      const auditLogs = AuditLogger.getLogs(currentFilters);
      setLogs(auditLogs.slice(0, maxEntries));
      setLoading(false);
    };

    fetchLogs();
    
    // Refresh logs every 30 seconds
    const interval = setInterval(fetchLogs, 30000);
    return () => clearInterval(interval);
  }, [currentFilters, maxEntries]);

  const handleFilterChange = (newFilters) => {
    setCurrentFilters({ ...currentFilters, ...newFilters });
  };

  const formatTimestamp = (timestamp) => {
    return new Date(timestamp).toLocaleString();
  };

  const getActionIcon = (action) => {
    const actionLower = action.toLowerCase();
    if (actionLower.includes('login')) return '🔑';
    if (actionLower.includes('create')) return '➕';
    if (actionLower.includes('update') || actionLower.includes('edit')) return '✏️';
    if (actionLower.includes('delete')) return '🗑️';
    if (actionLower.includes('view') || actionLower.includes('access')) return '👁️';
    if (actionLower.includes('download')) return '⬇️';
    if (actionLower.includes('upload')) return '⬆️';
    return '📝';
  };

  if (loading) {
    return <div className="audit-loading">Loading audit logs...</div>;
  }

  return (
    <div className="audit-log-viewer">
      <div className="audit-filters">
        <input
          type="text"
          placeholder="Filter by action..."
          onChange={(e) => handleFilterChange({ action: e.target.value })}
          className="audit-filter-input"
        />
        <input
          type="date"
          onChange={(e) => handleFilterChange({ dateFrom: e.target.value })}
          className="audit-filter-input"
        />
        <input
          type="date"
          onChange={(e) => handleFilterChange({ dateTo: e.target.value })}
          className="audit-filter-input"
        />
      </div>

      <div className="audit-logs-container">
        {logs.length === 0 ? (
          <div className="no-logs">No audit logs found</div>
        ) : (
          logs.map(log => (
            <div key={log.id} className="audit-log-entry">
              <div className="log-header">
                <span className="log-icon">{getActionIcon(log.action)}</span>
                <span className="log-action">{log.action}</span>
                <span className="log-timestamp">{formatTimestamp(log.timestamp)}</span>
              </div>
              <div className="log-details">
                <span className="log-user">User: {log.userId} ({log.userRole})</span>
                {log.details && Object.keys(log.details).length > 0 && (
                  <div className="log-metadata">
                    {Object.entries(log.details).map(([key, value]) => (
                      <span key={key} className="log-detail">
                        {key}: {typeof value === 'object' ? JSON.stringify(value) : value}
                      </span>
                    ))}
                  </div>
                )}
              </div>
            </div>
          ))
        )}
      </div>
    </div>
  );
};

// HOC for automatic audit logging of component actions
export const withAuditLogging = (WrappedComponent, componentName) => {
  return (props) => {
    const { user } = useContext(AuthContext);

    const loggedProps = {
      ...props,
      onAction: (action, details) => {
        AuditLogger.logAction(
          `${componentName}: ${action}`,
          details,
          user?.id,
          user?.role
        );
        if (props.onAction) {
          props.onAction(action, details);
        }
      }
    };

    return <WrappedComponent {...loggedProps} />;
  };
};

export default AuditLogger;