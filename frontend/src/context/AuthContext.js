import React, { createContext, useState, useEffect, useCallback } from 'react';
import ApiService from '../services/api';

// Create the AuthContext
export const AuthContext = createContext();

// Session timeout in milliseconds (30 minutes)
const SESSION_TIMEOUT = 30 * 60 * 1000;
const WARNING_TIME = 5 * 60 * 1000; // Show warning 5 minutes before timeout

// Create the AuthProvider component
export const AuthProvider = ({ children }) => {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);
  const [sessionWarning, setSessionWarning] = useState(false);
  const [sessionExpired, setSessionExpired] = useState(false);
  const [lastActivity, setLastActivity] = useState(Date.now());
  const [sessionId, setSessionId] = useState(null);

  // Generate session ID
  const generateSessionId = () => {
    return `session_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
  };

  // Update last activity timestamp
  const updateActivity = useCallback(() => {
    setLastActivity(Date.now());
    setSessionWarning(false);
  }, []);

  // Check session validity
  const isSessionValid = useCallback(() => {
    const now = Date.now();
    return (now - lastActivity) < SESSION_TIMEOUT;
  }, [lastActivity]);

  // Handle session timeout
  const handleSessionTimeout = useCallback(() => {
    setSessionExpired(true);
    logout();
  }, []);

  // Extend session
  const extendSession = useCallback(() => {
    updateActivity();
    setSessionWarning(false);
    setSessionExpired(false);
  }, [updateActivity]);

  // Session monitoring effect
  useEffect(() => {
    if (!user) return;

    const checkSession = () => {
      const now = Date.now();
      const timeLeft = SESSION_TIMEOUT - (now - lastActivity);

      if (timeLeft <= 0) {
        handleSessionTimeout();
      } else if (timeLeft <= WARNING_TIME && !sessionWarning) {
        setSessionWarning(true);
      }
    };

    const interval = setInterval(checkSession, 60000); // Check every minute
    return () => clearInterval(interval);
  }, [user, lastActivity, sessionWarning, handleSessionTimeout]);

  // Activity listeners
  useEffect(() => {
    if (!user) return;

    const events = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'];
    const handleActivity = () => updateActivity();

    events.forEach(event => {
      document.addEventListener(event, handleActivity, true);
    });

    return () => {
      events.forEach(event => {
        document.removeEventListener(event, handleActivity, true);
      });
    };
  }, [user, updateActivity]);

  // Check if user is logged in on initial load
  useEffect(() => {
    const token = localStorage.getItem('token');
    const storedSessionId = localStorage.getItem('sessionId');
    const storedLastActivity = localStorage.getItem('lastActivity');
    
    if (token && storedSessionId && storedLastActivity) {
      const lastActivityTime = parseInt(storedLastActivity);
      const now = Date.now();
      
      // Check if session is still valid
      if ((now - lastActivityTime) < SESSION_TIMEOUT) {
        // Try to get user data from localStorage
        const userData = localStorage.getItem('userData');
        if (userData) {
          try {
            const parsedUser = JSON.parse(userData);
            setUser(parsedUser);
            setSessionId(storedSessionId);
            setLastActivity(lastActivityTime);
          } catch (err) {
            console.log('Failed to parse user data, clearing localStorage');
            clearStoredAuth();
          }
        } else {
          clearStoredAuth();
        }
      } else {
        // Session expired
        setSessionExpired(true);
        clearStoredAuth();
      }
    } else if (token) {
      // We have a token but missing session data, clear everything for security
      clearStoredAuth();
    }
    setLoading(false);
  }, []);

  // Auto-refresh token before expiration
  useEffect(() => {
    if (!user) return;
    
    const refreshInterval = setInterval(() => {
      // In a real implementation, this would refresh the token with the server
      // For now, we'll just update the last activity to extend the session
      const token = localStorage.getItem('token');
      if (token) {
        updateActivity();
        console.log('Session refreshed');
      }
    }, SESSION_TIMEOUT - WARNING_TIME); // Refresh before warning time
    
    return () => clearInterval(refreshInterval);
  }, [user, updateActivity]);

  // Clear stored authentication data
  const clearStoredAuth = () => {
    localStorage.removeItem('token');
    localStorage.removeItem('userData');
    localStorage.removeItem('sessionId');
    localStorage.removeItem('lastActivity');
  };

  // Login function with proper async handling and state management
  const login = async (userData, token) => {
    try {
      // Store token first
      localStorage.setItem('token', token);
      
      // Fetch user permissions from backend
      let userPermissions = [];
      let userRoles = [];
      let userFeatureAccess = {};
      
      try {
        // Get user roles and permissions from backend
        const rolesResponse = await ApiService.getUserRoles(userData.id);
        userRoles = rolesResponse.data?.roles || [];
        
        // Get permissions and feature access for each role
        for (const role of userRoles) {
          try {
            // Get permissions for the role
            const permissionsResponse = await ApiService.getRolePermissions(role.id);
            if (permissionsResponse.data?.permissions) {
              userPermissions = [...userPermissions, ...permissionsResponse.data.permissions.map(p => p.name)];
            }
            
            // Get feature access for the role
            try {
              const featureAccessResponse = await ApiService.getRoleFeatureAccess(role.id);
              if (featureAccessResponse.data?.feature_access) {
                userFeatureAccess[role.id] = featureAccessResponse.data.feature_access;
              }
            } catch (featureError) {
              console.warn('Failed to fetch feature access for role:', role.id, featureError);
            }
          } catch (permError) {
            console.warn('Failed to fetch permissions for role:', role.id, permError);
          }
        }
        
        // Remove duplicate permissions
        userPermissions = [...new Set(userPermissions)];
      } catch (err) {
        console.warn('Failed to fetch user permissions, using empty arrays:', err);
      }
      
      // Create a clean user object with permissions and feature access
      const cleanUser = {
        id: userData.id,
        name: userData.name,
        email: userData.email,
        role: userData.role,
        roles: userRoles,
        permissions: userPermissions,
        featureAccess: userFeatureAccess,
        verified: userData.verified,
        created_at: userData.created_at,
        updated_at: userData.updated_at
      };
      
      // Remove undefined properties
      Object.keys(cleanUser).forEach(key => {
        if (cleanUser[key] === undefined) {
          delete cleanUser[key];
        }
      });
      
      const newSessionId = generateSessionId();
      const now = Date.now();
      
      // Update state
      setUser(cleanUser);
      setSessionId(newSessionId);
      setLastActivity(now);
      setSessionWarning(false);
      setSessionExpired(false);
      
      // Store in localStorage
      localStorage.setItem('userData', JSON.stringify(cleanUser));
      localStorage.setItem('sessionId', newSessionId);
      localStorage.setItem('lastActivity', now.toString());
    } catch (error) {
      console.error('Login failed:', error);
      // Clear token if login fails
      localStorage.removeItem('token');
      throw error;
    }
  };

  // Logout function
  const logout = () => {
    setUser(null);
    setSessionId(null);
    setLastActivity(Date.now());
    setSessionWarning(false);
    setSessionExpired(false);
    clearStoredAuth();
  };

  // Check if user is authenticated
  const isAuthenticated = () => {
    return !!user;
  };

  // Check if user has a specific role
  const hasRole = (role) => {
    return user && user.role === role;
  };

  // Check if user has any of the specified roles
  const hasAnyRole = (roles) => {
    return user && roles.includes(user.role);
  };

  // Check if user has specific permission
  const hasPermission = (permission) => {
    if (!user || !user.permissions) return false;
    return user.permissions.includes(permission) || user.role === 'admin' || user.role === 'super_admin';
  };

  // Check if user has any of the specified permissions
  const hasAnyPermission = (permissions) => {
    if (!user || !user.permissions) return false;
    return permissions.some(permission => hasPermission(permission));
  };

  // Refresh user permissions (useful when permissions change)
  const refreshPermissions = async () => {
    if (!user) return false;
    
    try {
      let userPermissions = [];
      let userRoles = [];
      let userFeatureAccess = {};
      
      try {
        // Get user roles and permissions from backend
        const rolesResponse = await ApiService.getUserRoles(user.id);
        userRoles = rolesResponse.data?.roles || [];
        
        // Get permissions and feature access for each role
        for (const role of userRoles) {
          try {
            // Get permissions for the role
            const permissionsResponse = await ApiService.getRolePermissions(role.id);
            if (permissionsResponse.data?.permissions) {
              userPermissions = [...userPermissions, ...permissionsResponse.data.permissions.map(p => p.name)];
            }
            
            // Get feature access for the role
            try {
              const featureAccessResponse = await ApiService.getRoleFeatureAccess(role.id);
              if (featureAccessResponse.data?.feature_access) {
                userFeatureAccess[role.id] = featureAccessResponse.data.feature_access;
              }
            } catch (featureError) {
              console.warn('Failed to fetch feature access for role:', role.id, featureError);
            }
          } catch (permError) {
            console.warn('Failed to fetch permissions for role:', role.id, permError);
          }
        }
        
        // Remove duplicate permissions
        userPermissions = [...new Set(userPermissions)];
      } catch (err) {
        console.warn('Failed to fetch user permissions, using empty arrays:', err);
      }
      
      // Update user with new permissions and feature access
      const updatedUser = {
        ...user,
        roles: userRoles,
        permissions: userPermissions,
        featureAccess: userFeatureAccess
      };
      
      setUser(updatedUser);
      localStorage.setItem('userData', JSON.stringify(updatedUser));
      return true;
    } catch (err) {
      console.warn('Failed to refresh user permissions:', err);
      return false;
    }
  };

  // Get user's IP address (simulated)
  const getUserIP = () => {
    // In a real application, this would be obtained from the server
    return '127.0.0.1';
  };

  // Get session information
  const getSessionInfo = () => {
    if (!user) return null;
    
    const now = Date.now();
    const timeLeft = SESSION_TIMEOUT - (now - lastActivity);
    
    return {
      sessionId,
      userId: user.id,
      userRole: user.role,
      lastActivity: new Date(lastActivity).toISOString(),
      timeLeft: Math.max(0, timeLeft),
      isValid: isSessionValid(),
      ipAddress: getUserIP()
    };
  };

  // Context value
  const contextValue = {
    user,
    login,
    logout,
    isAuthenticated,
    hasRole,
    hasAnyRole,
    hasPermission,
    hasAnyPermission,
    loading,
    sessionWarning,
    sessionExpired,
    extendSession,
    updateActivity,
    getSessionInfo,
    isSessionValid,
    lastActivity,
    sessionId,
    refreshPermissions
  };

  return (
    <AuthContext.Provider value={contextValue}>
      {children}
    </AuthContext.Provider>
  );
};