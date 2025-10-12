import React, { useContext } from 'react';
import { AuthContext } from '../../context/AuthContext';

const FeatureGuard = ({ 
  requiredFeature, 
  requiredAccessLevel = 'read', 
  children, 
  fallback = null
}) => {
  const { user } = useContext(AuthContext);

  const hasFeatureAccess = () => {
    if (!user || !user.roles || !user.featureAccess) return false;
    
    // Super admin has access to all features
    if (user.role === 'super_admin') return true;
    
    // Check each role the user has
    for (const role of user.roles) {
      const roleFeatureAccess = user.featureAccess[role.id];
      if (roleFeatureAccess) {
        // Find the required feature
        const feature = roleFeatureAccess.find(f => f.name === requiredFeature);
        if (feature) {
          // Check access level (admin > write > read > none)
          const accessLevels = ['none', 'read', 'write', 'admin'];
          const requiredLevelIndex = accessLevels.indexOf(requiredAccessLevel);
          const userLevelIndex = accessLevels.indexOf(feature.access_level || 'none');
          
          if (userLevelIndex >= requiredLevelIndex) {
            return true;
          }
        }
      }
    }
    
    return false;
  };

  // If user has required feature access, render children, otherwise render fallback
  if (hasFeatureAccess()) {
    return children;
  }
  
  return fallback;
};

export default FeatureGuard;