import React, { useContext } from 'react';
import { AuthContext } from '../../context/AuthContext';

const PermissionGuard = ({ 
  requiredRole, 
  requiredPermissions = [], 
  children, 
  fallback = null,
  checkAny = false // If true, user needs ANY of the permissions, if false, user needs ALL
}) => {
  const { user } = useContext(AuthContext);

  const hasRole = (role) => {
    if (!user || !user.role) return false;
    return user.role === role || user.role === 'admin'; // Admin has all roles
  };

  const hasPermission = (permission) => {
    if (!user || !user.permissions) return false;
    return user.permissions.includes(permission) || user.role === 'admin';
  };

  const hasRequiredPermissions = () => {
    if (requiredPermissions.length === 0) return true;
    
    if (checkAny) {
      return requiredPermissions.some(permission => hasPermission(permission));
    } else {
      return requiredPermissions.every(permission => hasPermission(permission));
    }
  };

  // Check role if specified
  if (requiredRole && !hasRole(requiredRole)) {
    return fallback;
  }

  // Check permissions if specified
  if (requiredPermissions.length > 0 && !hasRequiredPermissions()) {
    return fallback;
  }

  return children;
};

// Higher-order component version
export const withPermissionGuard = (WrappedComponent, permissions) => {
  return (props) => (
    <PermissionGuard 
      requiredPermissions={permissions}
      fallback={<div className="access-denied">Access Denied</div>}
    >
      <WrappedComponent {...props} />
    </PermissionGuard>
  );
};

// Role-based wrapper components
export const AdminOnly = ({ children, fallback = null }) => (
  <PermissionGuard requiredRole="admin" fallback={fallback}>
    {children}
  </PermissionGuard>
);

export const DoctorOnly = ({ children, fallback = null }) => (
  <PermissionGuard requiredRole="doctor" fallback={fallback}>
    {children}
  </PermissionGuard>
);

export const ReceptionistOnly = ({ children, fallback = null }) => (
  <PermissionGuard requiredRole="receptionist" fallback={fallback}>
    {children}
  </PermissionGuard>
);

export const PatientOnly = ({ children, fallback = null }) => (
  <PermissionGuard requiredRole="patient" fallback={fallback}>
    {children}
  </PermissionGuard>
);

// Permission matrix component for RBAC management
export const PermissionMatrix = ({ roles, permissions, currentMatrix, onUpdate }) => {
  const handlePermissionToggle = (roleId, permission) => {
    const updatedMatrix = { ...currentMatrix };
    if (!updatedMatrix[roleId]) {
      updatedMatrix[roleId] = [];
    }
    
    const hasPermission = updatedMatrix[roleId].includes(permission);
    if (hasPermission) {
      updatedMatrix[roleId] = updatedMatrix[roleId].filter(p => p !== permission);
    } else {
      updatedMatrix[roleId] = [...updatedMatrix[roleId], permission];
    }
    
    onUpdate(updatedMatrix);
  };

  return (
    <div className="permission-matrix">
      <table>
        <thead>
          <tr>
            <th>Role / Permission</th>
            {permissions.map(permission => (
              <th key={permission} className="permission-header">
                {permission.replace(/_/g, ' ').toUpperCase()}
              </th>
            ))}
          </tr>
        </thead>
        <tbody>
          {roles.map(role => (
            <tr key={role.id}>
              <td className="role-name">{role.name}</td>
              {permissions.map(permission => (
                <td key={permission} className="permission-cell">
                  <input
                    type="checkbox"
                    checked={currentMatrix[role.id]?.includes(permission) || false}
                    onChange={() => handlePermissionToggle(role.id, permission)}
                    disabled={role.id === 'admin'} // Admin always has all permissions
                  />
                </td>
              ))}
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
};

export default PermissionGuard;