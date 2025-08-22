import React from 'react';
import '../../App.css';

const ReceptionistDashboard = ({ user }) => {
  return (
    <div className="dashboard">
      <h1>Receptionist Dashboard</h1>
      <div className="card">
        <h2>Welcome, {user.name}!</h2>
        <p>Email: {user.email}</p>
        <p>Role: {user.role}</p>
      </div>
      
      <div className="card">
        <h2>Today's Appointments</h2>
        <ul>
          <li>9:00 AM - John Smith with Dr. Williams</li>
          <li>10:30 AM - Sarah Johnson with Dr. Brown</li>
          <li>1:00 PM - Michael Brown with Dr. Davis</li>
        </ul>
        <button className="btn">Manage Appointments</button>
      </div>
      
      <div className="card">
        <h2>Patient Registration</h2>
        <p>Register new patients in the system.</p>
        <button className="btn">Register New Patient</button>
      </div>
      
      <div className="card">
        <h2>Patient Check-in</h2>
        <p>Check in patients for their appointments.</p>
        <button className="btn">Check-in Patient</button>
      </div>
    </div>
  );
};

export default ReceptionistDashboard;