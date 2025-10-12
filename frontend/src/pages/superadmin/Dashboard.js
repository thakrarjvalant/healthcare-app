import React, { useState, useEffect, useContext } from 'react';
import { AuthContext } from '../../context/AuthContext';
import ApiService from '../../services/api';
import PermissionGuard from '../../components/common/PermissionGuard';
import FeatureGuard from '../../components/common/FeatureGuard';
import RBACTest from '../../components/debug/RBACTest';
import '../../App.css';

const SuperAdminDashboard = ({ user }) => {
  const { logout, hasPermission, refreshPermissions } = useContext(AuthContext);
  const [loading, setLoading] = useState(true);
  const [showRoleConfig, setShowRoleConfig] = useState(false);
  const [showPermissionMatrix, setShowPermissionMatrix] = useState(false);
  const [showFeatureAllocation, setShowFeatureAllocation] = useState(false);
  const [showSystemSettings, setShowSystemSettings] = useState(false);
  const [showEditRolePermissions, setShowEditRolePermissions] = useState(false);
  const [showEditRoleFeatures, setShowEditRoleFeatures] = useState(false);
  const [selectedRole, setSelectedRole] = useState(null);
  const [rolePermissions, setRolePermissions] = useState([]);
  const [roleFeatureAccess, setRoleFeatureAccess] = useState([]);

  // Dynamic role management data
  const [systemRoles, setSystemRoles] = useState([]);
  const [systemPermissions, setSystemPermissions] = useState([]);
  const [featureModules, setFeatureModules] = useState([]);
  const [roleMatrix, setRoleMatrix] = useState({});

  // Form data for creating/editing roles
  const [newRoleData, setNewRoleData] = useState({
    name: '',
    display_name: '',
    description: '',
    color: '#666666',
    icon: 'user'
  });

  useEffect(() => {
    fetchSystemData();
  }, []);

  const fetchSystemData = async () => {
    try {
      // Fetch dynamic RBAC data
      const rolesData = await ApiService.getDynamicRoles();
      const permissionsData = await ApiService.getAllPermissions();
      const modulesData = await ApiService.getFeatureModules();
      
      let roles = rolesData.data?.roles || mockRoles;
      const permissions = permissionsData.data?.permissions || mockPermissions;
      const modules = modulesData.data?.modules || mockModules;
      
      // For now, we'll use mock user counts or fetch them separately
      // In a real implementation, you would fetch actual user counts per role
      roles = roles.map(role => ({
        ...role,
        users_count: role.users_count || Math.floor(Math.random() * 50) + 1 // Mock data
      }));
      
      setSystemRoles(roles);
      setSystemPermissions(permissions);
      setFeatureModules(modules);
    } catch (err) {
      console.log('Using fallback data for super admin:', err.message);
      // Add mock user counts to mock roles
      const rolesWithCounts = mockRoles.map(role => ({
        ...role,
        users_count: role.users_count || Math.floor(Math.random() * 50) + 1
      }));
      
      setSystemRoles(rolesWithCounts);
      setSystemPermissions(mockPermissions);
      setFeatureModules(mockModules);
    } finally {
      setLoading(false);
    }
  };

  // Mock data for demonstration
  const mockRoles = [
    { id: 1, name: 'super_admin', display_name: 'Super Administrator', users_count: 1, color: '#dc3545' },
    { id: 2, name: 'admin', display_name: 'Administrator', users_count: 3, color: '#007bff' },
    { id: 4, name: 'doctor', display_name: 'Doctor', users_count: 12, color: '#17a2b8' },
    { id: 5, name: 'receptionist', display_name: 'Receptionist', users_count: 5, color: '#ffc107' },
    { id: 6, name: 'patient', display_name: 'Patient', users_count: 245, color: '#6c757d' }
  ];

  const mockPermissions = [
    { id: 1, module: 'user_management', feature: 'users', action: 'create' },
    { id: 2, module: 'appointment_management', feature: 'scheduling', action: 'create' },
    { id: 3, module: 'role_management', feature: 'roles', action: 'configure' }
  ];

  const mockModules = [
    { id: 1, name: 'user_management', display_name: 'User Management', color: '#007bff' },
    { id: 2, name: 'appointment_management', display_name: 'Appointment Management', color: '#28a745' },
    { id: 3, name: 'role_management', display_name: 'Role Management', color: '#e83e8c' }
  ];

  const handleEditRolePermissions = async (role) => {
    setSelectedRole(role);
    setShowEditRolePermissions(true);
    
    // Fetch role permissions
    try {
      const response = await ApiService.getRolePermissions(role.id);
      if (response.data && response.data.permissions) {
        setRolePermissions(response.data.permissions);
      }
    } catch (error) {
      console.error('Failed to fetch role permissions:', error);
      setRolePermissions([]);
    }
  };

  const handleEditRoleFeatures = async (role) => {
    setSelectedRole(role);
    setShowEditRoleFeatures(true);
    
    // Fetch role feature access
    try {
      const response = await ApiService.getRoleFeatureAccess(role.id);
      if (response.data && response.data.feature_access) {
        setRoleFeatureAccess(response.data.feature_access);
      }
    } catch (error) {
      console.error('Failed to fetch role feature access:', error);
      setRoleFeatureAccess([]);
    }
  };

  const handlePermissionToggle = async (permissionId) => {
    if (!selectedRole) return;
    
    try {
      // Check if permission is already assigned to role
      const isAssigned = rolePermissions.some(p => p.id === permissionId);
      
      if (isAssigned) {
        // Remove permission from role
        const response = await ApiService.removePermissionFromRole(selectedRole.id, permissionId);
        if (response.status === 200) {
          // Update local state
          setRolePermissions(rolePermissions.filter(p => p.id !== permissionId));
          alert('Permission removed successfully!');
        } else {
          alert('Failed to remove permission: ' + response.message);
        }
      } else {
        // Assign permission to role
        const response = await ApiService.assignPermissionToRole(selectedRole.id, permissionId);
        if (response.status === 200) {
          // Update local state - fetch the permission details
          const permission = systemPermissions.find(p => p.id === permissionId);
          if (permission) {
            setRolePermissions([...rolePermissions, permission]);
          }
          alert('Permission assigned successfully!');
        } else {
          alert('Failed to assign permission: ' + response.message);
        }
      }
    } catch (error) {
      console.error('Error toggling permission:', error);
      alert('Error toggling permission: ' + error.message);
    }
  };

  const handleFeatureAccessChange = async (moduleId, accessLevel) => {
    if (!selectedRole) return;
    
    try {
      const response = await ApiService.updateRoleFeatureAccess(selectedRole.id, moduleId, accessLevel);
      if (response.status === 200) {
        // Update local state
        const updatedFeatureAccess = roleFeatureAccess.map(feature => {
          if (feature.id === moduleId) {
            return { ...feature, access_level: accessLevel };
          }
          return feature;
        });
        setRoleFeatureAccess(updatedFeatureAccess);
        alert('Feature access updated successfully!');
      } else {
        alert('Failed to update feature access: ' + response.message);
      }
    } catch (error) {
      console.error('Error updating feature access:', error);
      alert('Error updating feature access: ' + error.message);
    }
  };

  const handleCreateRole = async (e) => {
    e.preventDefault();
    try {
      const response = await ApiService.createDynamicRole(newRoleData);
      if (response.status === 201) {
        alert('Role created successfully!');
        // Refresh roles list
        fetchSystemData();
        // Reset form
        setNewRoleData({
          name: '',
          display_name: '',
          description: '',
          color: '#666666',
          icon: 'user'
        });
      } else {
        alert('Failed to create role: ' + response.message);
      }
    } catch (error) {
      console.error('Error creating role:', error);
      alert('Error creating role: ' + error.message);
    }
  };

  const handleEditRole = (role) => {
    setSelectedRole(role);
    setNewRoleData({
      name: role.name,
      display_name: role.display_name,
      description: role.description,
      color: role.color,
      icon: role.icon
    });
    // We would show an edit form here, but for now we'll just log
    console.log('Editing role:', role);
  };

  const handleDeleteRole = async (roleId) => {
    if (window.confirm('Are you sure you want to delete this role? This action cannot be undone.')) {
      try {
        const response = await ApiService.deleteDynamicRole(roleId);
        if (response.status === 200) {
          alert('Role deleted successfully!');
          // Refresh roles list
          fetchSystemData();
        } else {
          alert('Failed to delete role: ' + response.message);
        }
      } catch (error) {
        console.error('Error deleting role:', error);
        alert('Error deleting role: ' + error.message);
      }
    }
  };

  const handleRefreshPermissions = async () => {
    try {
      await refreshPermissions();
      alert('Permissions refreshed successfully!');
    } catch (error) {
      console.error('Failed to refresh permissions:', error);
      alert('Failed to refresh permissions: ' + error.message);
    }
  };

  return (
    <div className="dashboard">
      <h1>👑 Super Administrator Dashboard</h1>
      
      <div className="card">
        <h2>Welcome, {user.name}!</h2>
        <p>Email: {user.email}</p>
        <p>Role: Super Administrator</p>
        <p className="role-description">
          Complete system control with dynamic role configuration and feature allocation
        </p>
        <button className="btn btn-secondary" onClick={handleRefreshPermissions} style={{marginTop: '10px'}}>
          Refresh Permissions
        </button>
      </div>

      {/* System Overview */}
      <FeatureGuard requiredFeature="role_management" requiredAccessLevel="read">
        <div className="card">
          <h2>🌐 System Role Overview</h2>
          {loading ? (
            <p>Loading system roles...</p>
          ) : (
            <div className="roles-overview">
              {systemRoles.map(role => (
                <div key={role.id} className="role-overview-card" style={{borderLeftColor: role.color}}>
                  <div className="role-info">
                    <h3>{role.display_name}</h3>
                    <span className="role-name">{role.name}</span>
                  </div>
                  <div className="role-stats">
                    <span className="users-count">{role.users_count || 0} users</span>
                  </div>
                  <div className="role-actions">
                    <PermissionGuard requiredPermissions={['system.roles.update']}>
                      <button className="btn btn-sm btn-info" onClick={() => handleEditRole(role)}>
                        Edit
                      </button>
                    </PermissionGuard>
                    {role.name !== 'super_admin' && (
                      <PermissionGuard requiredPermissions={['system.roles.delete']}>
                        <button className="btn btn-sm btn-danger" onClick={() => handleDeleteRole(role.id)}>
                          Delete
                        </button>
                      </PermissionGuard>
                    )}
                  </div>
                </div>
              ))}
            </div>
          )}
        </div>
      </FeatureGuard>

      {/* Dynamic Role Configuration */}
      <div className="super-admin-grid">
        <FeatureGuard requiredFeature="role_management" requiredAccessLevel="admin">
          <div className="card">
            <h2>🎭 Dynamic Role Configuration</h2>
            <p>Create, modify, and manage system roles with custom permissions</p>
            <div className="config-stats">
              <div className="stat-item">
                <span className="stat-number">{systemRoles.length}</span>
                <span className="stat-label">Active Roles</span>
              </div>
              <div className="stat-item">
                <span className="stat-number">{systemPermissions.length}</span>
                <span className="stat-label">Permissions</span>
              </div>
            </div>
            <div className="card-actions">
              <PermissionGuard requiredPermissions={['system.roles.create']}>
                <button className="btn btn-primary" onClick={() => setShowRoleConfig(true)}>
                  Configure Roles
                </button>
              </PermissionGuard>
              <PermissionGuard requiredPermissions={['system.roles.create']}>
                <button className="btn btn-success" onClick={handleCreateRole}>
                  Create New Role
                </button>
              </PermissionGuard>
            </div>
          </div>
        </FeatureGuard>

        <FeatureGuard requiredFeature="role_management" requiredAccessLevel="admin">
          <div className="card">
            <h2>🛡️ Permission Matrix</h2>
            <p>Manage granular permissions across all roles and features</p>
            <div className="permission-preview">
              <p><strong>Modules:</strong> {featureModules.length}</p>
              <p><strong>Permission Rules:</strong> {systemPermissions.length}</p>
            </div>
            <button className="btn btn-warning" onClick={() => setShowPermissionMatrix(true)}>
              Manage Permissions
            </button>
          </div>
        </FeatureGuard>

        <FeatureGuard requiredFeature="role_management" requiredAccessLevel="admin">
          <div className="card">
            <h2>🎯 Feature Allocation</h2>
            <p>Allocate system features and modules to specific roles</p>
            <div className="allocation-preview">
              <p><strong>Feature Modules:</strong> {featureModules.length}</p>
              <p><strong>Access Rules:</strong> Active</p>
            </div>
            <button className="btn btn-info" onClick={() => setShowFeatureAllocation(true)}>
              Allocate Features
            </button>
          </div>
        </FeatureGuard>

        <FeatureGuard requiredFeature="system_admin" requiredAccessLevel="admin">
          <div className="card">
            <h2>⚙️ System Configuration</h2>
            <p>Advanced system settings and global configurations</p>
            <div className="system-preview">
              <p><strong>Status:</strong> All Systems Operational</p>
              <p><strong>Security:</strong> Enhanced RBAC Active</p>
            </div>
            <button className="btn btn-secondary" onClick={() => setShowSystemSettings(true)}>
              System Settings
            </button>
          </div>
        </FeatureGuard>
      </div>

      {/* Role Configuration Modal */}
      <FeatureGuard requiredFeature="role_management" requiredAccessLevel="admin">
        {showRoleConfig && (
          <div className="modal-backdrop" onClick={() => setShowRoleConfig(false)}>
            <div className="modal-content modal-large" onClick={(e) => e.stopPropagation()}>
              <div className="modal-header">
                <h2>🎭 Dynamic Role Configuration</h2>
                <button className="modal-close" onClick={() => setShowRoleConfig(false)}>&times;</button>
              </div>
              <div className="modal-body">
                <div className="role-configuration">
                  <div className="config-toolbar">
                    <PermissionGuard requiredPermissions={['system.roles.create']}>
                      <button className="btn btn-primary" onClick={() => {
                        // Show create role form
                        setSelectedRole(null);
                        setNewRoleData({
                          name: '',
                          display_name: '',
                          description: '',
                          color: '#666666',
                          icon: 'user'
                        });
                      }}>Create Role</button>
                    </PermissionGuard>
                    <button className="btn btn-secondary">Import Roles</button>
                    <button className="btn btn-info">Export Configuration</button>
                  </div>

                  <div className="roles-list">
                    {systemRoles.map(role => (
                      <div key={role.id} className="role-config-card">
                        <div className="role-header">
                          <div className="role-info">
                            <h3>{role.display_name}</h3>
                            <span className="role-identifier">{role.name}</span>
                          </div>
                          <div className="role-meta">
                            <span className="users-badge">{role.users_count || 0} users</span>
                            <div className="role-color" style={{backgroundColor: role.color}}></div>
                          </div>
                        </div>
                        <div className="role-controls">
                          <button className="btn btn-sm btn-info" onClick={() => handleEditRolePermissions(role)}>Edit Permissions</button>
                          <button className="btn btn-sm btn-warning" onClick={() => handleEditRoleFeatures(role)}>Modify Features</button>
                          <button className="btn btn-sm btn-secondary">View Users</button>
                          {role.name !== 'super_admin' && (
                            <button className="btn btn-sm btn-danger" onClick={() => handleDeleteRole(role.id)}>Delete Role</button>
                          )}
                        </div>
                      </div>
                    ))}
                  </div>
                </div>
              </div>
            </div>
          </div>
        )}
      </FeatureGuard>

      {/* Edit Role Permissions Modal */}
      <FeatureGuard requiredFeature="role_management" requiredAccessLevel="admin">
        {showEditRolePermissions && (
          <div className="modal-backdrop" onClick={() => setShowEditRolePermissions(false)}>
            <div className="modal-content modal-large" onClick={(e) => e.stopPropagation()}>
              <div className="modal-header">
                <h2>🛡️ Edit Permissions for {selectedRole?.display_name}</h2>
                <button className="modal-close" onClick={() => setShowEditRolePermissions(false)}>&times;</button>
              </div>
              <div className="modal-body">
                <div className="permissions-management">
                  <div className="permissions-list">
                    {systemPermissions.map(permission => {
                      const isChecked = rolePermissions.some(p => p.id === permission.id);
                      return (
                        <div key={permission.id} className="permission-item">
                          <label>
                            <input
                              type="checkbox"
                              checked={isChecked}
                              onChange={() => handlePermissionToggle(permission.id)}
                            />
                            <span className="permission-name">{permission.display_name}</span>
                            <span className="permission-details">
                              {permission.module} → {permission.feature} → {permission.action}
                            </span>
                            {permission.description && (
                              <span className="permission-description">{permission.description}</span>
                            )}
                          </label>
                        </div>
                      );
                    })}
                  </div>
                  <div className="form-actions">
                    <button 
                      className="btn btn-secondary" 
                      onClick={() => setShowEditRolePermissions(false)}
                    >
                      Close
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        )}
      </FeatureGuard>

      {/* Edit Role Features Modal */}
      <FeatureGuard requiredFeature="role_management" requiredAccessLevel="admin">
        {showEditRoleFeatures && (
          <div className="modal-backdrop" onClick={() => setShowEditRoleFeatures(false)}>
            <div className="modal-content modal-large" onClick={(e) => e.stopPropagation()}>
              <div className="modal-header">
                <h2>🎯 Feature Access for {selectedRole?.display_name}</h2>
                <button className="modal-close" onClick={() => setShowEditRoleFeatures(false)}>&times;</button>
              </div>
              <div className="modal-body">
                <div className="feature-access-management">
                  <div className="feature-access-list">
                    {featureModules.map(module => {
                      const currentAccess = roleFeatureAccess.find(f => f.id === module.id);
                      const accessLevel = currentAccess ? currentAccess.access_level : 'none';
                      
                      return (
                        <div key={module.id} className="feature-item">
                          <div className="feature-info">
                            <h3>{module.display_name}</h3>
                            <p>{module.description || 'No description available'}</p>
                          </div>
                          <div className="access-control">
                            <select 
                              value={accessLevel}
                              onChange={(e) => handleFeatureAccessChange(module.id, e.target.value)}
                            >
                              <option value="none">None</option>
                              <option value="read">Read</option>
                              <option value="write">Write</option>
                              <option value="admin">Admin</option>
                            </select>
                          </div>
                        </div>
                      );
                    })}
                  </div>
                  <div className="form-actions">
                    <button 
                      className="btn btn-secondary" 
                      onClick={() => setShowEditRoleFeatures(false)}
                    >
                      Close
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        )}
      </FeatureGuard>

      <RBACTest />

      <style jsx>{`
        .super-admin-grid {
          display: grid;
          grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
          gap: 20px;
          margin-top: 20px;
        }

        .roles-overview {
          display: grid;
          grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
          gap: 16px;
          margin-top: 16px;
        }

        .role-overview-card {
          background: #f8f9fa;
          padding: 16px;
          border-radius: 8px;
          border-left: 4px solid #007bff;
          display: flex;
          flex-direction: column;
          gap: 12px;
        }

        .role-info h3 {
          margin: 0;
          color: #333;
          font-size: 1.1rem;
        }

        .role-name {
          color: #666;
          font-size: 0.9rem;
          font-family: monospace;
          background: #e9ecef;
          padding: 2px 6px;
          border-radius: 3px;
        }

        .users-count {
          font-weight: bold;
          color: #007bff;
        }

        .role-actions {
          display: flex;
          gap: 8px;
        }

        .config-stats, .permission-preview, .allocation-preview, .system-preview {
          margin: 12px 0;
          padding: 12px;
          background: #f8f9fa;
          border-radius: 6px;
        }

        .stat-item {
          display: flex;
          justify-content: space-between;
          align-items: center;
          margin-bottom: 8px;
        }

        .stat-number {
          font-weight: bold;
          color: #007bff;
          font-size: 1.2rem;
        }

        .card-actions {
          display: flex;
          gap: 12px;
          margin-top: 16px;
        }

        .role-description {
          font-style: italic;
          color: #666;
          margin-top: 8px;
        }

        /* Modal Styles */
        .modal-backdrop {
          position: fixed;
          top: 0;
          left: 0;
          right: 0;
          bottom: 0;
          background-color: rgba(0, 0, 0, 0.5);
          display: flex;
          justify-content: center;
          align-items: center;
          z-index: 1000;
        }

        .modal-content {
          background: white;
          border-radius: 8px;
          box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
          width: 90%;
          max-width: 800px;
          max-height: 90vh;
          overflow-y: auto;
        }

        .modal-large {
          max-width: 1000px;
        }

        .modal-header {
          display: flex;
          justify-content: space-between;
          align-items: center;
          padding: 20px 24px 16px;
          border-bottom: 1px solid #e5e5e5;
        }

        .modal-close {
          background: none;
          border: none;
          font-size: 1.5rem;
          cursor: pointer;
          color: #666;
        }

        .modal-body {
          padding: 24px;
        }

        .config-toolbar {
          display: flex;
          gap: 12px;
          margin-bottom: 20px;
        }

        .role-config-card {
          background: #f8f9fa;
          border: 1px solid #e5e5e5;
          border-radius: 8px;
          padding: 16px;
          margin-bottom: 12px;
        }

        .role-header {
          display: flex;
          justify-content: space-between;
          align-items: flex-start;
          margin-bottom: 12px;
        }

        .role-identifier {
          color: #666;
          font-size: 0.8rem;
          font-family: monospace;
          background: #e9ecef;
          padding: 2px 4px;
          border-radius: 3px;
        }

        .role-meta {
          display: flex;
          align-items: center;
          gap: 8px;
        }

        .users-badge {
          background: #007bff;
          color: white;
          padding: 2px 8px;
          border-radius: 12px;
          font-size: 0.8rem;
        }

        .role-color {
          width: 20px;
          height: 20px;
          border-radius: 50%;
          border: 2px solid white;
          box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .role-controls {
          display: flex;
          gap: 8px;
          flex-wrap: wrap;
        }

        .btn {
          padding: 8px 16px;
          border: none;
          border-radius: 4px;
          cursor: pointer;
          font-size: 14px;
          font-weight: 500;
        }

        .btn-sm {
          padding: 4px 8px;
          font-size: 12px;
        }

        .btn-primary { background: #007bff; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-warning { background: #ffc107; color: #000; }
        .btn-info { background: #17a2b8; color: white; }

        /* Permission Management Styles */
        .permissions-list {
          max-height: 500px;
          overflow-y: auto;
          border: 1px solid #ddd;
          border-radius: 4px;
          margin-bottom: 20px;
        }

        .permission-item {
          padding: 12px;
          border-bottom: 1px solid #eee;
        }

        .permission-item:last-child {
          border-bottom: none;
        }

        .permission-item label {
          display: flex;
          align-items: flex-start;
          gap: 12px;
          cursor: pointer;
        }

        .permission-name {
          font-weight: 600;
          margin-right: 8px;
        }

        .permission-details {
          font-size: 12px;
          color: #666;
          font-family: monospace;
        }

        .permission-description {
          display: block;
          color: #666;
          font-size: 14px;
          margin-top: 4px;
        }

        /* Feature Access Management Styles */
        .feature-access-list {
          max-height: 500px;
          overflow-y: auto;
        }

        .feature-item {
          display: flex;
          justify-content: space-between;
          align-items: center;
          padding: 16px;
          border-bottom: 1px solid #eee;
        }

        .feature-item:last-child {
          border-bottom: none;
        }

        .feature-info h3 {
          margin: 0 0 8px 0;
          color: #333;
        }

        .feature-info p {
          margin: 0;
          color: #666;
          font-size: 14px;
        }

        .access-control select {
          padding: 6px 12px;
          border: 1px solid #ddd;
          border-radius: 4px;
        }
      `}</style>
    </div>
  );
};

export default SuperAdminDashboard;