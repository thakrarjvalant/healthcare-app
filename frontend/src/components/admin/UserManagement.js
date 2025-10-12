import React, { useState, useEffect } from 'react';
import Modal from '../common/Modal';
import ApiService from '../../services/api';

const UserManagement = ({ isOpen, onClose }) => {
  const [users, setUsers] = useState([]);
  const [roles, setRoles] = useState([]);
  const [loading, setLoading] = useState(true);
  const [selectedUser, setSelectedUser] = useState(null);
  const [showCreateForm, setShowCreateForm] = useState(false);
  const [formData, setFormData] = useState({
    name: '',
    email: '',
    password: '',
    role: 'patient'
  });

  useEffect(() => {
    if (isOpen) {
      fetchUsers();
      fetchRoles();
    }
  }, [isOpen]);

  const fetchUsers = async () => {
    setLoading(true);
    try {
      const response = await ApiService.getUsers();
      // The API returns data in { status: 200, data: { users: [...] } } format
      setUsers(response.data?.users || []);
    } catch (error) {
      console.error('Failed to fetch users:', error);
      setUsers([]);
    } finally {
      setLoading(false);
    }
  };

  const fetchRoles = async () => {
    try {
      const response = await ApiService.getDynamicRoles();
      console.log('Roles API response:', response); // Debug log
      if (response.data && response.data.roles) {
        // Check if medical_coordinator role exists in the response
        const hasMedicalCoordinator = response.data.roles.some(role => role.name === 'medical_coordinator');
        console.log('Has medical coordinator role:', hasMedicalCoordinator);
        setRoles(response.data.roles);
      } else {
        // Fallback to default roles if API doesn't return roles
        const defaultRoles = [
          { name: 'patient', display_name: 'Patient' },
          { name: 'doctor', display_name: 'Doctor' },
          { name: 'receptionist', display_name: 'Receptionist' },
          { name: 'admin', display_name: 'Admin' },
          { name: 'medical_coordinator', display_name: 'Medical Coordinator' }
        ];
        console.log('Using fallback roles');
        setRoles(defaultRoles);
      }
    } catch (error) {
      console.error('Failed to fetch roles:', error);
      // Fallback to default roles
      const defaultRoles = [
        { name: 'patient', display_name: 'Patient' },
        { name: 'doctor', display_name: 'Doctor' },
        { name: 'receptionist', display_name: 'Receptionist' },
        { name: 'admin', display_name: 'Admin' },
        { name: 'medical_coordinator', display_name: 'Medical Coordinator' }
      ];
      console.log('Using fallback roles due to error');
      setRoles(defaultRoles);
    }
  };

  const handleCreateUser = async (e) => {
    e.preventDefault();
    try {
      const response = await ApiService.createUser(formData);
      if (response.success) {
        // Refresh the user list to include the new user
        fetchUsers();
        setFormData({ name: '', email: '', password: '', role: 'patient' });
        setShowCreateForm(false);
        alert('User created successfully!');
      }
    } catch (error) {
      console.error('Failed to create user:', error);
      alert('Failed to create user: ' + error.message);
    }
  };

  const handleDeleteUser = async (userId) => {
    if (window.confirm('Are you sure you want to delete this user?')) {
      try {
        await ApiService.deleteUser(userId);
        // Refresh the user list to remove the deleted user
        fetchUsers();
        alert('User deleted successfully!');
      } catch (error) {
        console.error('Failed to delete user:', error);
        alert('Failed to delete user: ' + error.message);
      }
    }
  };

  const handleInputChange = (e) => {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value
    });
  };

  return (
    <Modal isOpen={isOpen} onClose={onClose} title="User Management" size="large">
      <div className="user-management">
        <div className="notification-banner warning">
          <p><strong>⚠️ Note:</strong> Billing and payment processing permissions have been transferred to the Finance Department.</p>
        </div>
        
        <div className="actions-bar">
          <button 
            className="btn btn-primary" 
            onClick={() => setShowCreateForm(true)}
          >
            Add New User
          </button>
          <button 
            className="btn btn-secondary" 
            onClick={fetchUsers}
          >
            Refresh
          </button>
        </div>

        {showCreateForm && (
          <div className="create-user-form">
            <h3>Create New User</h3>
            <form onSubmit={handleCreateUser} className="modal-form">
              <div className="form-group">
                <label>Name:</label>
                <input
                  type="text"
                  name="name"
                  value={formData.name}
                  onChange={handleInputChange}
                  required
                />
              </div>
              <div className="form-group">
                <label>Email:</label>
                <input
                  type="email"
                  name="email"
                  value={formData.email}
                  onChange={handleInputChange}
                  required
                />
              </div>
              <div className="form-group">
                <label>Password:</label>
                <input
                  type="password"
                  name="password"
                  value={formData.password}
                  onChange={handleInputChange}
                  required
                />
              </div>
              <div className="form-group">
                <label>Role:</label>
                <select
                  name="role"
                  value={formData.role}
                  onChange={handleInputChange}
                >
                  {roles.map((role) => (
                    <option key={role.name} value={role.name}>
                      {role.display_name || role.name}
                    </option>
                  ))}
                </select>
                {/* Debug info */}
                <div style={{ fontSize: '12px', color: '#666', marginTop: '5px' }}>
                  Available roles: {roles.map(r => r.name).join(', ')}
                </div>
              </div>
              <div className="form-actions">
                <button type="button" className="btn btn-secondary" onClick={() => setShowCreateForm(false)}>
                  Cancel
                </button>
                <button type="submit" className="btn btn-primary">
                  Create User
                </button>
              </div>
            </form>
          </div>
        )}

        {loading ? (
          <p>Loading users...</p>
        ) : (
          <div className="users-table">
            <table>
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Role</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                {users.map(user => (
                  <tr key={user.id}>
                    <td>{user.id}</td>
                    <td>{user.name}</td>
                    <td>{user.email}</td>
                    <td>
                      <span className={`role-badge role-${user.role}`}>
                        {user.role}
                      </span>
                    </td>
                    <td>
                      <span className={`status-badge status-${user.status}`}>
                        {user.status}
                      </span>
                    </td>
                    <td>
                      <button 
                        className="btn btn-sm btn-danger"
                        onClick={() => handleDeleteUser(user.id)}
                        disabled={user.role === 'admin' && user.email === 'admin@example.com'} // Prevent deleting main admin
                      >
                        Delete
                      </button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>

      <style jsx>{`
        .user-management {
          min-height: 400px;
        }
        
        .notification-banner {
          padding: 12px 16px;
          border-radius: 4px;
          margin-bottom: 20px;
          border: 1px solid;
        }
        
        .notification-banner.warning {
          background-color: #fff3cd;
          border-color: #ffeaa7;
          color: #856404;
        }
        
        .actions-bar {
          display: flex;
          gap: 12px;
          margin-bottom: 20px;
        }
        
        .create-user-form {
          background: #f8f9fa;
          padding: 20px;
          border-radius: 8px;
          margin-bottom: 20px;
        }
        
        .users-table {
          overflow-x: auto;
        }
        
        .users-table table {
          width: 100%;
          border-collapse: collapse;
          margin-top: 16px;
        }
        
        .users-table th,
        .users-table td {
          padding: 12px;
          text-align: left;
          border-bottom: 1px solid #ddd;
        }
        
        .users-table th {
          background-color: #f8f9fa;
          font-weight: 600;
        }
        
        .role-badge {
          padding: 4px 8px;
          border-radius: 12px;
          font-size: 12px;
          font-weight: 500;
        }
        
        .role-admin { background: #dc3545; color: white; }
        .role-doctor { background: #28a745; color: white; }
        .role-receptionist { background: #007bff; color: white; }
        .role-patient { background: #6c757d; color: white; }
        .role-medical_coordinator { background: #20c997; color: white; }
        .role-super_admin { background: #6f42c1; color: white; }
        
        .status-badge {
          padding: 4px 8px;
          border-radius: 12px;
          font-size: 12px;
          font-weight: 500;
        }
        
        .status-active { background: #28a745; color: white; }
        .status-inactive { background: #dc3545; color: white; }
        
        .btn {
          padding: 8px 16px;
          border: none;
          border-radius: 4px;
          cursor: pointer;
          font-size: 14px;
          font-weight: 500;
        }
        
        .btn-primary {
          background-color: #007bff;
          color: white;
        }
        
        .btn-secondary {
          background-color: #6c757d;
          color: white;
        }
        
        .btn-danger {
          background-color: #dc3545;
          color: white;
        }
        
        .btn-sm {
          padding: 4px 8px;
          font-size: 12px;
        }
      `}</style>
    </Modal>
  );
};

export default UserManagement;