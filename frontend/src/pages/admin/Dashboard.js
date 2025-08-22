import React from 'react';
import '../../App.css';

const AdminDashboard = ({ user }) => {
  return (
    <div className="dashboard">
      <h1>Admin Dashboard</h1>
      <div className="card">
        <h2>Welcome, {user.name}!</h2>
        <p>Email: {user.email}</p>
        <p>Role: {user.role}</p>
      </div>
      
      <div className="card">
        <h2>User Management</h2>
        <p>Manage users, roles, and permissions.</p>
        <button className="btn">Manage Users</button>
      </div>
      
      <div className="card">
        <h2>System Configuration</h2>
        <p>Configure system settings and preferences.</p>
        <button className="btn">System Settings</button>
      </div>
      
      <div className="card">
        <h2>Reports & Analytics</h2>
        <p>View system reports and analytics.</p>
        <button className="btn">View Reports</button>
      </div>
      
      <div className="card">
        <h2>Audit Logs</h2>
        <p>View system audit logs.</p>
        <button className="btn">View Audit Logs</button>
      </div>
    </div>
  );
};

export default AdminDashboard;