import React, { useState, useEffect, useContext } from 'react';
import { AuthContext } from '../../context/AuthContext';
import ApiService from '../../services/api';
import UserManagement from '../../components/admin/UserManagement';
import EscalationManagement from '../../components/admin/EscalationManagement';
import PermissionGuard from '../../components/common/PermissionGuard';
import '../../App.css';

const AdminDashboard = ({ user }) => {
  const { logout, hasPermission, refreshPermissions } = useContext(AuthContext);
  const [users, setUsers] = useState([]);
  const [systemStats, setSystemStats] = useState({
    totalUsers: 0,
    totalAppointments: 0,
    activeUsers: 0
  });
  const [loading, setLoading] = useState(true);
  const [showUserManagement, setShowUserManagement] = useState(false);
  const [showSystemSettings, setShowSystemSettings] = useState(false);
  const [showReports, setShowReports] = useState(false);
  const [showAuditLogs, setShowAuditLogs] = useState(false);
  const [showRoleManagement, setShowRoleManagement] = useState(false);
  const [showSystemMonitoring, setShowSystemMonitoring] = useState(false);
  const [showAdvancedSettings, setShowAdvancedSettings] = useState(false);
  const [showEscalationManagement, setShowEscalationManagement] = useState(false);
  const [showCreateRoleForm, setShowCreateRoleForm] = useState(false);
  const [showEditRoleForm, setShowEditRoleForm] = useState(false);
  const [showEditPermissionsForm, setShowEditPermissionsForm] = useState(false);
  const [selectedRole, setSelectedRole] = useState(null);
  const [roles, setRoles] = useState([]);
  const [permissions, setPermissions] = useState([]);
  const [rolePermissions, setRolePermissions] = useState([]);
  const [newRoleData, setNewRoleData] = useState({
    name: '',
    display_name: '',
    description: '',
    color: '#666666',
    icon: 'user'
  });
  
  // State for permission matrix
  const [permissionMatrix, setPermissionMatrix] = useState({});
  const [originalPermissionMatrix, setOriginalPermissionMatrix] = useState({});
  
  useEffect(() => {
    fetchSystemData();
  }, []);
  
  const fetchSystemData = async () => {
    try {
      const usersResponse = await ApiService.getUsers();
      setUsers(usersResponse.users || []);
      
      // Fetch roles
      const rolesResponse = await ApiService.getDynamicRoles();
      if (rolesResponse.data && rolesResponse.data.roles) {
        setRoles(rolesResponse.data.roles);
      }
      
      // Calculate system stats
      setSystemStats({
        totalUsers: usersResponse.users?.length || 0,
        totalAppointments: 45, // Mock data
        activeUsers: usersResponse.users?.filter(u => u.status === 'active').length || 0
      });
    } catch (err) {
      console.log('Failed to fetch system data, using mock data:', err.message);
      // Mock data for testing
      setSystemStats({
        totalUsers: 125,
        totalAppointments: 45,
        activeUsers: 98
      });
    } finally {
      setLoading(false);
    }
  };
  
  const handleManageUsers = () => {
    setShowUserManagement(true);
  };
  
  const handleSystemSettings = () => {
    setShowSystemSettings(true);
  };
  
  const handleViewReports = () => {
    setShowReports(true);
  };
  
  const handleViewAuditLogs = () => {
    setShowAuditLogs(true);
  };
  
  const handleRoleManagement = () => {
    setShowRoleManagement(true);
    // Refresh roles when opening role management
    fetchRoles();
    // Initialize permission matrix
    initializePermissionMatrix();
  };
  
  // Initialize permission matrix with current values
  const initializePermissionMatrix = async () => {
    try {
      // Fetch all roles and permissions if not already fetched
      let rolesData = roles;
      let permissionsData = permissions;
      
      if (rolesData.length === 0) {
        const rolesResponse = await ApiService.getDynamicRoles();
        rolesData = rolesResponse.data?.roles || [];
        setRoles(rolesData);
      }
      
      if (permissionsData.length === 0) {
        const permissionsResponse = await ApiService.getAllPermissions();
        permissionsData = permissionsResponse.data?.permissions || [];
        setPermissions(permissionsData);
      }
      
      if (rolesData.length > 0 && permissionsData.length > 0) {
        // Initialize matrix state
        const matrix = {};
        const originalMatrix = {};
        
        // For each permission and role combination, check if the role has that permission
        for (const permission of permissionsData) {
          matrix[permission.id] = {};
          originalMatrix[permission.id] = {};
          
          for (const role of rolesData) {
            // Fetch role permissions to check if this permission is assigned
            try {
              const rolePermissionsResponse = await ApiService.getRolePermissions(role.id);
              const hasPermission = rolePermissionsResponse.data?.permissions?.some(p => p.id === permission.id);
              matrix[permission.id][role.id] = hasPermission || false;
              originalMatrix[permission.id][role.id] = hasPermission || false;
            } catch (error) {
              console.error(`Failed to fetch permissions for role ${role.id}:`, error);
              matrix[permission.id][role.id] = false;
              originalMatrix[permission.id][role.id] = false;
            }
          }
        }
        
        setPermissionMatrix(matrix);
        setOriginalPermissionMatrix(originalMatrix);
      }
    } catch (error) {
      console.error('Failed to initialize permission matrix:', error);
    }
  };

  const fetchRoles = async () => {
    try {
      const response = await ApiService.getDynamicRoles();
      if (response.data && response.data.roles) {
        setRoles(response.data.roles);
      }
    } catch (error) {
      console.error('Failed to fetch roles:', error);
    }
  };

  const fetchPermissions = async () => {
    try {
      const response = await ApiService.getAllPermissions();
      if (response.data && response.data.permissions) {
        setPermissions(response.data.permissions);
      }
    } catch (error) {
      console.error('Failed to fetch permissions:', error);
    }
  };

  const fetchRolePermissions = async (roleId) => {
    try {
      const response = await ApiService.getRolePermissions(roleId);
      if (response.data && response.data.permissions) {
        setRolePermissions(response.data.permissions);
      }
    } catch (error) {
      console.error('Failed to fetch role permissions:', error);
    }
  };

  const handleEditRole = (role) => {
    setSelectedRole(role);
    setShowEditRoleForm(true);
    // Set form data to current role values
    setNewRoleData({
      name: role.name,
      display_name: role.display_name,
      description: role.description,
      color: role.color,
      icon: role.icon
    });
  };
  
  const handleEditPermissions = (role) => {
    setSelectedRole(role);
    setShowEditPermissionsForm(true);
    // Fetch role permissions
    fetchRolePermissions(role.id);
  };

  const handleDeleteRole = async (roleId) => {
    if (window.confirm('Are you sure you want to delete this role?')) {
      try {
        const response = await ApiService.deleteDynamicRole(roleId);
        if (response.status === 200) {
          alert('Role deleted successfully!');
          // Refresh roles list
          fetchRoles();
        } else {
          alert('Failed to delete role: ' + response.message);
        }
      } catch (error) {
        console.error('Error deleting role:', error);
        alert('Error deleting role: ' + error.message);
      }
    }
  };
  
  const handleRoleInputChange = (e) => {
    const { name, value } = e.target;
    setNewRoleData(prev => ({
      ...prev,
      [name]: value
    }));
  };
  
  const handleCreateRole = async (e) => {
    e.preventDefault();
    try {
      const response = await ApiService.createDynamicRole(newRoleData);
      if (response.status === 201) {
        alert('Role created successfully!');
        setShowCreateRoleForm(false);
        setNewRoleData({
          name: '',
          display_name: '',
          description: '',
          color: '#666666',
          icon: 'user'
        });
        // Refresh roles list
        fetchRoles();
      } else {
        alert('Failed to create role: ' + response.message);
      }
    } catch (error) {
      console.error('Error creating role:', error);
      alert('Error creating role: ' + error.message);
    }
  };
  
  const handleUpdateRole = async (e) => {
    e.preventDefault();
    try {
      const response = await ApiService.updateDynamicRole(selectedRole.id, newRoleData);
      if (response.status === 200) {
        alert('Role updated successfully!');
        setShowEditRoleForm(false);
        setSelectedRole(null);
        // Refresh roles list
        fetchRoles();
      } else {
        alert('Failed to update role: ' + response.message);
      }
    } catch (error) {
      console.error('Error updating role:', error);
      alert('Error updating role: ' + error.message);
    }
  };

  const handlePermissionToggle = async (permissionId) => {
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
          const permission = permissions.find(p => p.id === permissionId);
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

  // Handle permission matrix checkbox changes
  const handleMatrixPermissionToggle = (permissionId, roleId) => {
    setPermissionMatrix(prev => ({
      ...prev,
      [permissionId]: {
        ...prev[permissionId],
        [roleId]: !prev[permissionId][roleId]
      }
    }));
  };

  // Save permission matrix changes
  const handleSavePermissionMatrix = async () => {
    try {
      // Find what has changed
      const changes = [];
      
      for (const permissionId in permissionMatrix) {
        for (const roleId in permissionMatrix[permissionId]) {
          const currentValue = permissionMatrix[permissionId][roleId];
          const originalValue = originalPermissionMatrix[permissionId][roleId];
          
          if (currentValue !== originalValue) {
            changes.push({
              permissionId: parseInt(permissionId),
              roleId: parseInt(roleId),
              action: currentValue ? 'assign' : 'remove'
            });
          }
        }
      }
      
      if (changes.length === 0) {
        alert('No changes to save.');
        return;
      }
      
      // Process changes
      let successCount = 0;
      const errorMessages = [];
      
      for (const change of changes) {
        try {
          if (change.action === 'assign') {
            const response = await ApiService.assignPermissionToRole(change.roleId, change.permissionId);
            if (response.status === 200) {
              successCount++;
            } else {
              errorMessages.push(`Failed to assign permission ${change.permissionId} to role ${change.roleId}: ${response.message}`);
            }
          } else {
            const response = await ApiService.removePermissionFromRole(change.roleId, change.permissionId);
            if (response.status === 200) {
              successCount++;
            } else {
              errorMessages.push(`Failed to remove permission ${change.permissionId} from role ${change.roleId}: ${response.message}`);
            }
          }
        } catch (error) {
          errorMessages.push(`Error processing permission ${change.permissionId} for role ${change.roleId}: ${error.message}`);
        }
      }
      
      // Update original matrix to reflect current state
      setOriginalPermissionMatrix({...permissionMatrix});
      
      if (errorMessages.length > 0) {
        alert(`Saved ${successCount} changes. Errors: ${errorMessages.join(', ')}`);
      } else {
        alert(`Successfully saved ${successCount} permission changes!`);
      }
      
      // Reinitialize the matrix to reflect current state
      initializePermissionMatrix();
    } catch (error) {
      console.error('Error saving permission matrix:', error);
      alert('Error saving permission matrix: ' + error.message);
    }
  };

  const handleEscalations = () => {
    setShowEscalationManagement(true);
  };
  
  const handleSystemMonitoring = () => {
    setShowSystemMonitoring(true);
  };
  
  const handleAdvancedSettings = () => {
    setShowAdvancedSettings(true);
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

  // Add useEffect to fetch permissions when role management is opened
  useEffect(() => {
    if (showRoleManagement) {
      fetchPermissions();
      // Initialize permission matrix when role management is opened
      initializePermissionMatrix();
    }
  }, [showRoleManagement]);

  return (
    <div className="dashboard">
      <h1>Admin Dashboard</h1>
      <div className="card">
        <h2>Welcome, {user.name}!</h2>
        <p>Email: {user.email}</p>
        <p>Role: {user.role}</p>
        <p className="role-description">
          System administrator - User management, audit oversight, and escalation handling
        </p>
        <button className="btn btn-secondary" onClick={handleRefreshPermissions} style={{marginTop: '10px'}}>
          Refresh Permissions
        </button>
      </div>
      
      <PermissionGuard requiredPermissions={['system.read']}>
        <div className="card">
          <h2>System Overview</h2>
          {loading ? (
            <p>Loading system data...</p>
          ) : (
            <div className="stats-grid">
              <div className="stat-item">
                <span className="stat-number">{systemStats.totalUsers}</span>
                <span className="stat-label">Total Users</span>
              </div>
              <div className="stat-item">
                <span className="stat-number">{systemStats.totalAppointments}</span>
                <span className="stat-label">Total Appointments</span>
              </div>
              <div className="stat-item">
                <span className="stat-number">{systemStats.activeUsers}</span>
                <span className="stat-label">Active Users</span>
              </div>
            </div>
          )}
        </div>
      </PermissionGuard>
      
      <PermissionGuard requiredPermissions={['users.manage']}>
        <div className="card">
          <h2>User Management</h2>
          <p>Manage users, roles, and permissions.</p>
          <button className="btn" onClick={handleManageUsers}>Manage Users</button>
        </div>
      </PermissionGuard>
      
      <PermissionGuard requiredPermissions={['system.settings']}>
        <div className="card">
          <h2>System Configuration</h2>
          <p>Configure system settings and preferences.</p>
          <div className="card-actions">
            <button className="btn" onClick={handleSystemSettings}>Basic Settings</button>
            {hasPermission('system.advanced_settings') && (
              <button className="btn btn-secondary" onClick={handleAdvancedSettings}>Advanced Settings</button>
            )}
          </div>
        </div>
      </PermissionGuard>
      
      <PermissionGuard requiredPermissions={['escalations.manage']}>
        <div className="card">
          <h2>🚑 Escalation Management</h2>
          <p>Handle escalated issues and high-priority system concerns.</p>
          <div className="escalation-stats">
            <p><strong>Open Escalations:</strong> 3</p>
            <p><strong>Resolved Today:</strong> 7</p>
          </div>
          <button className="btn btn-warning" onClick={handleEscalations}>Manage Escalations</button>
        </div>
      </PermissionGuard>
      
      <PermissionGuard requiredPermissions={['reports.read']}>
        <div className="card">
          <h2>📊 Reports & Analytics</h2>
          <p>View system reports and analytics.</p>
          <button className="btn" onClick={handleViewReports}>View Reports</button>
        </div>
      </PermissionGuard>
      
      <PermissionGuard requiredPermissions={['audit.read']}>
        <div className="card">
          <h2>📋 Audit Logs</h2>
          <p>View system audit logs.</p>
          <button className="btn" onClick={handleViewAuditLogs}>View Audit Logs</button>
        </div>
      </PermissionGuard>
      
      {/* Adding Role Management Card */}
      <PermissionGuard requiredPermissions={['roles.manage']}>
        <div className="card">
          <h2>🔐 Role Management</h2>
          <p>Manage user roles and permissions.</p>
          <button className="btn" onClick={handleRoleManagement}>Manage Roles</button>
        </div>
      </PermissionGuard>
      
      {/* Modals */}
      <PermissionGuard requiredPermissions={['users.manage']}>
        <UserManagement 
          isOpen={showUserManagement} 
          onClose={() => setShowUserManagement(false)} 
        />
      </PermissionGuard>
      
      <PermissionGuard requiredPermissions={['escalations.manage']}>
        <EscalationManagement 
          isOpen={showEscalationManagement} 
          onClose={() => setShowEscalationManagement(false)} 
        />
      </PermissionGuard>
      
      <PermissionGuard requiredPermissions={['system.settings']}>
        {showSystemSettings && (
          <div className="modal-backdrop" onClick={() => setShowSystemSettings(false)}>
            <div className="modal-content" onClick={(e) => e.stopPropagation()}>
              <div className="modal-header">
                <h2>⚙️ System Settings</h2>
                <button className="modal-close" onClick={() => setShowSystemSettings(false)}>&times;</button>
              </div>
              <div className="modal-body">
                <div className="settings-section">
                  <h3>General Settings</h3>
                  <div className="setting-item">
                    <label>System Name:</label>
                    <input type="text" defaultValue="Healthcare Management System" />
                  </div>
                  <div className="setting-item">
                    <label>Max Appointments per Day:</label>
                    <input type="number" defaultValue="50" />
                  </div>
                  <div className="setting-item">
                    <label>Appointment Duration (minutes):</label>
                    <input type="number" defaultValue="30" />
                  </div>
                </div>
                
                <div className="settings-section">
                  <h3>Notification Settings</h3>
                  <div className="setting-item">
                    <label>
                      <input type="checkbox" defaultChecked /> Email Notifications
                    </label>
                  </div>
                  <div className="setting-item">
                    <label>
                      <input type="checkbox" defaultChecked /> SMS Notifications
                    </label>
                  </div>
                </div>
                
                <div className="form-actions">
                  <button className="btn btn-primary">Save Settings</button>
                  <button className="btn btn-secondary" onClick={() => setShowSystemSettings(false)}>Cancel</button>
                </div>
              </div>
            </div>
          </div>
        )}
      </PermissionGuard>
      
      {showReports && (
        <div className="modal-backdrop" onClick={() => setShowReports(false)}>
          <div className="modal-content modal-large" onClick={(e) => e.stopPropagation()}>
            <div className="modal-header">
              <h2>📊 Reports & Analytics</h2>
              <button className="modal-close" onClick={() => setShowReports(false)}>&times;</button>
            </div>
            <div className="modal-body">
              <div className="reports-grid">
                <div className="report-card">
                  <h3>User Statistics</h3>
                  <div className="chart-placeholder">
                    <p>Total Users: {systemStats.totalUsers}</p>
                    <p>Active Users: {systemStats.activeUsers}</p>
                    <p>New This Month: 12</p>
                  </div>
                </div>
                
                <div className="report-card">
                  <h3>Appointment Analytics</h3>
                  <div className="chart-placeholder">
                    <p>Total Appointments: {systemStats.totalAppointments}</p>
                    <p>This Week: 15</p>
                    <p>Completion Rate: 92%</p>
                  </div>
                </div>
                
                <div className="report-card">
                  <h3>System Performance</h3>
                  <div className="chart-placeholder">
                    <p>Uptime: 99.8%</p>
                    <p>Avg Response Time: 120ms</p>
                    <p>Error Rate: 0.2%</p>
                  </div>
                </div>
                
                <div className="report-card">
                  <h3>Revenue Analytics</h3>
                  <div className="chart-placeholder">
                    <p>This Month: $12,450</p>
                    <p>Last Month: $11,230</p>
                    <p>Growth: +10.9%</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      )}
      
      {showAuditLogs && (
        <div className="modal-backdrop" onClick={() => setShowAuditLogs(false)}>
          <div className="modal-content modal-large" onClick={(e) => e.stopPropagation()}>
            <div className="modal-header">
              <h2>📋 Audit Logs</h2>
              <button className="modal-close" onClick={() => setShowAuditLogs(false)}>&times;</button>
            </div>
            <div className="modal-body">
              <div className="audit-logs">
                <div className="log-filters">
                  <select>
                    <option>All Users</option>
                    <option>Admin Users</option>
                    <option>Doctors</option>
                    <option>Patients</option>
                    <option>Receptionists</option>
                  </select>
                  <select>
                    <option>All Actions</option>
                    <option>Login/Logout</option>
                    <option>User Management</option>
                    <option>Data Changes</option>
                    <option>Permission Changes</option>
                    <option>System Configuration</option>
                  </select>
                  <input type="date" />
                  <input type="date" />
                  <button className="btn btn-primary">Filter</button>
                </div>
                
                <div className="logs-table">
                  <table>
                    <thead>
                      <tr>
                        <th>Timestamp</th>
                        <th>User</th>
                        <th>Role</th>
                        <th>Action</th>
                        <th>Details</th>
                        <th>IP Address</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td>2023-08-25 14:30:15</td>
                        <td>admin@example.com</td>
                        <td>Admin</td>
                        <td>User Created</td>
                        <td>Created new doctor account: jane.smith@example.com</td>
                        <td>192.168.1.100</td>
                      </tr>
                      <tr>
                        <td>2023-08-25 14:25:42</td>
                        <td>jane.smith@example.com</td>
                        <td>Doctor</td>
                        <td>Login</td>
                        <td>Successful login</td>
                        <td>10.0.0.25</td>
                      </tr>
                      <tr>
                        <td>2023-08-25 14:20:18</td>
                        <td>admin@example.com</td>
                        <td>Admin</td>
                        <td>Settings Changed</td>
                        <td>Updated appointment duration to 45 minutes</td>
                        <td>192.168.1.100</td>
                      </tr>
                      <tr>
                        <td>2023-08-25 14:15:33</td>
                        <td>admin@example.com</td>
                        <td>Admin</td>
                        <td>Permission Changed</td>
                        <td>Granted 'view_all_appointments' to Receptionist role</td>
                        <td>192.168.1.100</td>
                      </tr>
                      <tr>
                        <td>2023-08-25 14:10:05</td>
                        <td>john.doe@example.com</td>
                        <td>Patient</td>
                        <td>Appointment Booked</td>
                        <td>Booked appointment with Dr. Smith for 2023-08-26 10:00</td>
                        <td>172.16.0.50</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      )}
      
      {showRoleManagement && (
        <div className="modal-backdrop" onClick={() => setShowRoleManagement(false)}>
          <div className="modal-content modal-large" onClick={(e) => e.stopPropagation()}>
            <div className="modal-header">
              <h2>🔐 Role & Permission Management</h2>
              <button className="modal-close" onClick={() => setShowRoleManagement(false)}>&times;</button>
            </div>
            <div className="modal-body">
              <div className="role-management">
                {showCreateRoleForm ? (
                  <div className="create-role-form">
                    <h3>Create New Role</h3>
                    <form onSubmit={handleCreateRole} className="modal-form">
                      <div className="form-group">
                        <label>Role Name:</label>
                        <input
                          type="text"
                          name="name"
                          value={newRoleData.name}
                          onChange={handleRoleInputChange}
                          placeholder="e.g., medical-coordinator"
                          required
                        />
                        <small>Internal name for the role (must be unique)</small>
                      </div>
                      <div className="form-group">
                        <label>Display Name:</label>
                        <input
                          type="text"
                          name="display_name"
                          value={newRoleData.display_name}
                          onChange={handleRoleInputChange}
                          placeholder="e.g., Medical Coordinator"
                          required
                        />
                        <small>User-friendly name for the role</small>
                      </div>
                      <div className="form-group">
                        <label>Description:</label>
                        <textarea
                          name="description"
                          value={newRoleData.description}
                          onChange={handleRoleInputChange}
                          placeholder="Describe the role's purpose and responsibilities"
                        />
                      </div>
                      <div className="form-group">
                        <label>Color:</label>
                        <input
                          type="color"
                          name="color"
                          value={newRoleData.color}
                          onChange={handleRoleInputChange}
                        />
                      </div>
                      <div className="form-group">
                        <label>Icon:</label>
                        <select
                          name="icon"
                          value={newRoleData.icon}
                          onChange={handleRoleInputChange}
                        >
                          <option value="user">User</option>
                          <option value="user-md">Medical User</option>
                          <option value="users">Users</option>
                          <option value="user-cog">User Settings</option>
                          <option value="user-shield">User Security</option>
                          <option value="user-check">Verified User</option>
                          <option value="user-plus">Add User</option>
                          <option value="user-minus">Remove User</option>
                          <option value="user-clock">User Schedule</option>
                          <option value="user-friends">User Group</option>
                        </select>
                      </div>
                      <div className="form-actions">
                        <button type="button" className="btn btn-secondary" onClick={() => setShowCreateRoleForm(false)}>
                          Cancel
                        </button>
                        <button type="submit" className="btn btn-primary">
                          Create Role
                        </button>
                      </div>
                    </form>
                  </div>
                ) : showEditRoleForm ? (
                  <div className="edit-role-form">
                    <h3>Edit Role</h3>
                    <form onSubmit={handleUpdateRole} className="modal-form">
                      <div className="form-group">
                        <label>Role Name:</label>
                        <input
                          type="text"
                          name="name"
                          value={newRoleData.name}
                          onChange={handleRoleInputChange}
                          required
                        />
                        <small>Internal name for the role (must be unique)</small>
                      </div>
                      <div className="form-group">
                        <label>Display Name:</label>
                        <input
                          type="text"
                          name="display_name"
                          value={newRoleData.display_name}
                          onChange={handleRoleInputChange}
                          required
                        />
                        <small>User-friendly name for the role</small>
                      </div>
                      <div className="form-group">
                        <label>Description:</label>
                        <textarea
                          name="description"
                          value={newRoleData.description}
                          onChange={handleRoleInputChange}
                          placeholder="Describe the role's purpose and responsibilities"
                        />
                      </div>
                      <div className="form-group">
                        <label>Color:</label>
                        <input
                          type="color"
                          name="color"
                          value={newRoleData.color}
                          onChange={handleRoleInputChange}
                        />
                      </div>
                      <div className="form-group">
                        <label>Icon:</label>
                        <select
                          name="icon"
                          value={newRoleData.icon}
                          onChange={handleRoleInputChange}
                        >
                          <option value="user">User</option>
                          <option value="user-md">Medical User</option>
                          <option value="users">Users</option>
                          <option value="user-cog">User Settings</option>
                          <option value="user-shield">User Security</option>
                          <option value="user-check">Verified User</option>
                          <option value="user-plus">Add User</option>
                          <option value="user-minus">Remove User</option>
                          <option value="user-clock">User Schedule</option>
                          <option value="user-friends">User Group</option>
                        </select>
                      </div>
                      <div className="form-actions">
                        <button type="button" className="btn btn-secondary" onClick={() => {setShowEditRoleForm(false); setSelectedRole(null);}}>
                          Cancel
                        </button>
                        <button type="submit" className="btn btn-primary">
                          Update Role
                        </button>
                      </div>
                    </form>
                  </div>
                ) : showEditPermissionsForm ? (
                  <div className="edit-permissions-form">
                    <h3>Edit Permissions for {selectedRole?.display_name}</h3>
                    <div className="permissions-list">
                      {permissions.map((permission) => {
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
                              <span className="permission-description">{permission.description}</span>
                            </label>
                          </div>
                        );
                      })}
                    </div>
                    <div className="form-actions">
                      <button 
                        className="btn btn-secondary" 
                        onClick={() => {
                          setShowEditPermissionsForm(false);
                          setSelectedRole(null);
                          setRolePermissions([]);
                        }}
                      >
                        Back to Roles
                      </button>
                    </div>
                  </div>
                ) : (
                  <>
                    <div className="roles-section">
                      <div className="section-header">
                        <h3>User Roles</h3>
                        <button className="btn btn-primary" onClick={() => setShowCreateRoleForm(true)}>
                          Create New Role
                        </button>
                      </div>
                      <div className="roles-grid">
                        {roles.map((role) => (
                          <div className="role-card" key={role.id}>
                            <h4>{role.display_name}</h4>
                            <p>{role.description}</p>
                            <span className="user-count">0 users</span>
                            <div className="role-actions">
                              <button 
                                className="btn btn-sm btn-secondary"
                                onClick={() => handleEditRole(role)}
                              >
                                Edit
                              </button>
                              <button 
                                className="btn btn-sm btn-warning"
                                onClick={() => handleEditPermissions(role)}
                              >
                                Edit Permissions
                              </button>
                              {role.is_system_role !== 1 && (
                                <button 
                                  className="btn btn-sm btn-danger"
                                  onClick={() => handleDeleteRole(role.id)}
                                >
                                  Delete
                                </button>
                              )}
                            </div>
                          </div>
                        ))}
                      </div>
                    </div>
                    
                    <div className="permissions-section">
                      <h3>Permission Matrix</h3>
                      <div className="permission-matrix">
                        <table>
                          <thead>
                            <tr>
                              <th>Permission</th>
                              {roles.map(role => (
                                <th key={role.id}>{role.display_name}</th>
                              ))}
                            </tr>
                          </thead>
                          <tbody>
                            {permissions.map(permission => (
                              <tr key={permission.id}>
                                <td>{permission.display_name}</td>
                                {roles.map(role => (
                                  <td key={`${permission.id}-${role.id}`}>
                                    <input 
                                      type="checkbox" 
                                      checked={permissionMatrix[permission.id]?.[role.id] || false} 
                                      onChange={() => handleMatrixPermissionToggle(permission.id, role.id)} 
                                    />
                                  </td>
                                ))}
                              </tr>
                            ))}
                          </tbody>
                        </table>
                      </div>
                      <div className="form-actions">
                        <button className="btn btn-primary" onClick={handleSavePermissionMatrix}>Save Permission Changes</button>
                        <button className="btn btn-secondary" onClick={() => setShowRoleManagement(false)}>Cancel</button>
                      </div>
                    </div>
                  </>
                )}
              </div>
            </div>
          </div>
        </div>
      )}
      
      {showSystemMonitoring && (
        <div className="modal-backdrop" onClick={() => setShowSystemMonitoring(false)}>
          <div className="modal-content modal-large" onClick={(e) => e.stopPropagation()}>
            <div className="modal-header">
              <h2>📊 System Monitoring</h2>
              <button className="modal-close" onClick={() => setShowSystemMonitoring(false)}>&times;</button>
            </div>
            <div className="modal-body">
              <div className="monitoring-dashboard">
                <div className="system-health">
                  <h3>System Health Overview</h3>
                  <div className="health-metrics">
                    <div className="metric-card">
                      <div className="metric-value">99.8%</div>
                      <div className="metric-label">System Uptime</div>
                      <div className="metric-status healthy">Healthy</div>
                    </div>
                    <div className="metric-card">
                      <div className="metric-value">125ms</div>
                      <div className="metric-label">Avg Response Time</div>
                      <div className="metric-status healthy">Good</div>
                    </div>
                    <div className="metric-card">
                      <div className="metric-value">0.2%</div>
                      <div className="metric-label">Error Rate</div>
                      <div className="metric-status healthy">Low</div>
                    </div>
                    <div className="metric-card">
                      <div className="metric-value">45</div>
                      <div className="metric-label">Active Users</div>
                      <div className="metric-status healthy">Normal</div>
                    </div>
                  </div>
                </div>
                
                <div className="service-status">
                  <h3>Service Status</h3>
                  <div className="services-grid">
                    <div className="service-item">
                      <span className="service-name">User Service</span>
                      <span className="status-indicator online"></span>
                      <span className="service-status">Online</span>
                    </div>
                    <div className="service-item">
                      <span className="service-name">Appointment Service</span>
                      <span className="status-indicator online"></span>
                      <span className="service-status">Online</span>
                    </div>
                    <div className="service-item">
                      <span className="service-name">Clinical Service</span>
                      <span className="status-indicator online"></span>
                      <span className="service-status">Online</span>
                    </div>
                    <div className="service-item">
                      <span className="service-name">Billing Service</span>
                      <span className="status-indicator warning"></span>
                      <span className="service-status">Warning</span>
                    </div>
                    <div className="service-item">
                      <span className="service-name">Notification Service</span>
                      <span className="status-indicator online"></span>
                      <span className="service-status">Online</span>
                    </div>
                    <div className="service-item">
                      <span className="service-name">Storage Service</span>
                      <span className="status-indicator online"></span>
                      <span className="service-status">Online</span>
                    </div>
                  </div>
                </div>
                
                <div className="database-status">
                  <h3>Database Performance</h3>
                  <div className="db-metrics">
                    <div className="db-metric">
                      <span className="metric-name">Connection Pool:</span>
                      <span className="metric-value">8/20 active</span>
                    </div>
                    <div className="db-metric">
                      <span className="metric-name">Query Time (avg):</span>
                      <span className="metric-value">15ms</span>
                    </div>
                    <div className="db-metric">
                      <span className="metric-name">Storage Used:</span>
                      <span className="metric-value">2.3GB / 10GB</span>
                    </div>
                    <div className="db-metric">
                      <span className="metric-name">Backup Status:</span>
                      <span className="metric-value">Last: 2 hours ago</span>
                    </div>
                  </div>
                </div>
                
                <div className="recent-alerts">
                  <h3>Recent System Alerts</h3>
                  <div className="alerts-list">
                    <div className="alert-item warning">
                      <span className="alert-time">14:25</span>
                      <span className="alert-message">Billing Service: High memory usage detected</span>
                    </div>
                    <div className="alert-item info">
                      <span className="alert-time">13:45</span>
                      <span className="alert-message">Database backup completed successfully</span>
                    </div>
                    <div className="alert-item success">
                      <span className="alert-time">12:30</span>
                      <span className="alert-message">System update deployed successfully</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      )}
      
      {showAdvancedSettings && (
        <div className="modal-backdrop" onClick={() => setShowAdvancedSettings(false)}>
          <div className="modal-content modal-large" onClick={(e) => e.stopPropagation()}>
            <div className="modal-header">
              <h2>⚙️ Advanced System Settings</h2>
              <button className="modal-close" onClick={() => setShowAdvancedSettings(false)}>&times;</button>
            </div>
            <div className="modal-body">
              <div className="advanced-settings">
                <div className="settings-tabs">
                  <button className="tab-button active">Security</button>
                  <button className="tab-button">Performance</button>
                  <button className="tab-button">Notifications</button>
                  <button className="tab-button">Integration</button>
                </div>
                
                <div className="settings-content">
                  <div className="settings-section">
                    <h3>Security Configuration</h3>
                    <div className="setting-item">
                      <label>Session Timeout (minutes):</label>
                      <input type="number" defaultValue="60" />
                    </div>
                    <div className="setting-item">
                      <label>Maximum Login Attempts:</label>
                      <input type="number" defaultValue="3" />
                    </div>
                    <div className="setting-item">
                      <label>Password Policy:</label>
                      <select defaultValue="strong">
                        <option value="basic">Basic (8+ characters)</option>
                        <option value="medium">Medium (8+ chars, mixed case)</option>
                        <option value="strong">Strong (8+ chars, mixed case, numbers, symbols)</option>
                      </select>
                    </div>
                  </div>
                  
                  <div className="settings-section">
                    <h3>Data Retention</h3>
                    <div className="setting-item">
                      <label>Audit Log Retention (days):</label>
                      <input type="number" defaultValue="365" />
                    </div>
                    <div className="setting-item">
                      <label>Session Log Retention (days):</label>
                      <input type="number" defaultValue="90" />
                    </div>
                    <div className="setting-item">
                      <label>Medical Records Retention (years):</label>
                      <input type="number" defaultValue="7" />
                    </div>
                  </div>
                  
                  <div className="settings-section">
                    <h3>System Limits</h3>
                    <div className="setting-item">
                      <label>Maximum Concurrent Users:</label>
                      <input type="number" defaultValue="100" />
                    </div>
                    <div className="setting-item">
                      <label>API Request Rate Limit (per minute):</label>
                      <input type="number" defaultValue="1000" />
                    </div>
                    <div className="setting-item">
                      <label>File Upload Size Limit (MB):</label>
                      <input type="number" defaultValue="50" />
                    </div>
                  </div>
                </div>
                
                <div className="form-actions">
                  <button className="btn btn-primary">Save Advanced Settings</button>
                  <button className="btn btn-secondary" onClick={() => setShowAdvancedSettings(false)}>Cancel</button>
                  <button className="btn btn-warning">Reset to Defaults</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      )}
      
      <style jsx>{`
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
          max-width: 600px;
          max-height: 90vh;
          overflow-y: auto;
        }
        
        .modal-large {
          max-width: 900px;
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
        
        .settings-section {
          margin-bottom: 24px;
        }
        
        .setting-item {
          margin-bottom: 16px;
        }
        
        .setting-item label {
          display: block;
          margin-bottom: 4px;
          font-weight: 500;
        }
        
        .setting-item input {
          width: 100%;
          padding: 8px 12px;
          border: 1px solid #ddd;
          border-radius: 4px;
        }
        
        .reports-grid {
          display: grid;
          grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
          gap: 20px;
        }
        
        .report-card {
          background: #f8f9fa;
          padding: 20px;
          border-radius: 8px;
          border: 1px solid #e5e5e5;
        }
        
        .chart-placeholder {
          margin-top: 12px;
        }
        
        .chart-placeholder p {
          margin: 8px 0;
          font-size: 14px;
        }
        
        .log-filters {
          display: flex;
          gap: 12px;
          margin-bottom: 20px;
        }
        
        .log-filters select,
        .log-filters input {
          padding: 8px 12px;
          border: 1px solid #ddd;
          border-radius: 4px;
        }
        
        .logs-table table {
          width: 100%;
          border-collapse: collapse;
        }
        
        .logs-table th,
        .logs-table td {
          padding: 12px;
          text-align: left;
          border-bottom: 1px solid #ddd;
        }
        
        .logs-table th {
          background-color: #f8f9fa;
          font-weight: 600;
        }
        
        .form-actions {
          display: flex;
          gap: 12px;
          justify-content: flex-end;
          margin-top: 20px;
        }
        
        .btn {
          padding: 8px 16px;
          border: none;
          border-radius: 4px;
          cursor: pointer;
          font-size: 14px;
          font-weight: 500;
        }
        
        .btn-primary { background: #007bff; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-info { background: #17a2b8; color: white; }
        .btn-warning { background: #ffc107; color: #212529; }
        
        .card-actions {
          display: flex;
          gap: 12px;
          margin-top: 16px;
        }
        
        /* Role Management Styles */
        .roles-grid {
          display: grid;
          grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
          gap: 16px;
          margin-bottom: 24px;
        }
        
        .role-card {
          background: #f8f9fa;
          padding: 16px;
          border-radius: 8px;
          border: 1px solid #e5e5e5;
          text-align: center;
        }
        
        .role-card h4 {
          margin: 0 0 8px 0;
          color: #333;
        }
        
        .user-count {
          display: block;
          color: #666;
          font-size: 12px;
          margin: 8px 0 12px 0;
        }
        
        .permission-matrix table {
          width: 100%;
          margin-bottom: 20px;
        }
        
        .permission-matrix th:first-child {
          text-align: left;
          width: 200px;
        }
        
        .permission-matrix th:not(:first-child) {
          text-align: center;
          width: 100px;
        }
        
        .permission-matrix td {
          text-align: center;
        }
        
        .permission-matrix td:first-child {
          text-align: left;
          font-weight: 500;
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
        
        .permission-description {
          color: #666;
          font-size: 14px;
        }
        
        .permissions-list {
          max-height: 400px;
          overflow-y: auto;
          border: 1px solid #ddd;
          border-radius: 4px;
          margin: 16px 0;
        }
        
        /* System Monitoring Styles */
        .health-metrics {
          display: grid;
          grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
          gap: 16px;
          margin-bottom: 24px;
        }
        
        .metric-card {
          background: #f8f9fa;
          padding: 20px;
          border-radius: 8px;
          border: 1px solid #e5e5e5;
          text-align: center;
        }
        
        .metric-value {
          font-size: 2rem;
          font-weight: bold;
          color: #333;
          display: block;
        }
        
        .metric-label {
          display: block;
          color: #666;
          margin: 8px 0;
        }
        
        .metric-status {
          padding: 4px 8px;
          border-radius: 12px;
          font-size: 12px;
          font-weight: 500;
        }
        
        .metric-status.healthy {
          background: #d4edda;
          color: #155724;
        }
        
        .services-grid {
          display: grid;
          grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
          gap: 12px;
          margin-bottom: 24px;
        }
        
        .service-item {
          display: flex;
          align-items: center;
          padding: 12px;
          background: #f8f9fa;
          border-radius: 6px;
          border: 1px solid #e5e5e5;
        }
        
        .service-name {
          flex: 1;
          font-weight: 500;
        }
        
        .status-indicator {
          width: 12px;
          height: 12px;
          border-radius: 50%;
          margin-right: 8px;
        }
        
        .status-indicator.online {
          background: #28a745;
        }
        
        .status-indicator.warning {
          background: #ffc107;
        }
        
        .status-indicator.offline {
          background: #dc3545;
        }
        
        .db-metrics {
          display: grid;
          grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
          gap: 12px;
          margin-bottom: 24px;
        }
        
        .db-metric {
          display: flex;
          justify-content: space-between;
          padding: 8px 12px;
          background: #f8f9fa;
          border-radius: 4px;
        }
        
        .metric-name {
          font-weight: 500;
        }
        
        .alerts-list {
          max-height: 200px;
          overflow-y: auto;
        }
        
        .alert-item {
          display: flex;
          align-items: center;
          padding: 8px 12px;
          margin-bottom: 8px;
          border-radius: 4px;
          border-left: 4px solid #ddd;
        }
        
        .alert-item.warning {
          background: #fff3cd;
          border-left-color: #ffc107;
        }
        
        .alert-item.info {
          background: #d1ecf1;
          border-left-color: #17a2b8;
        }
        
        .alert-item.success {
          background: #d4edda;
          border-left-color: #28a745;
        }
        
        .alert-time {
          font-weight: 500;
          margin-right: 12px;
          min-width: 60px;
        }
        
        /* Advanced Settings Styles */
        .settings-tabs {
          display: flex;
          gap: 8px;
          margin-bottom: 20px;
          border-bottom: 1px solid #e5e5e5;
        }
        
        .tab-button {
          padding: 8px 16px;
          border: none;
          background: none;
          cursor: pointer;
          border-bottom: 2px solid transparent;
        }
        
        .tab-button.active {
          border-bottom-color: #007bff;
          color: #007bff;
          font-weight: 500;
        }
        
        /* Create Role Form Styles */
        .create-role-form {
          padding: 20px;
        }
        
        .create-role-form h3 {
          margin-top: 0;
          margin-bottom: 20px;
        }
        
        .section-header {
          display: flex;
          justify-content: space-between;
          align-items: center;
          margin-bottom: 20px;
        }
        
        .form-group {
          margin-bottom: 16px;
        }
        
        .form-group label {
          display: block;
          margin-bottom: 4px;
          font-weight: 500;
        }
        
        .form-group input,
        .form-group textarea,
        .form-group select {
          width: 100%;
          padding: 8px 12px;
          border: 1px solid #ddd;
          border-radius: 4px;
          font-family: inherit;
        }
        
        .form-group textarea {
          min-height: 80px;
          resize: vertical;
        }
        
        .form-group small {
          display: block;
          color: #666;
          font-size: 12px;
          margin-top: 4px;
        }
        
        .form-actions {
          display: flex;
          gap: 12px;
          justify-content: flex-end;
          margin-top: 20px;
        }
      `}
      </style>
    </div>
  );
};

export default AdminDashboard;
