import React from 'react';
import '../../App.css';

const DoctorDashboard = ({ user }) => {
  return (
    <div className="dashboard">
      <h1>Doctor Dashboard</h1>
      <div className="card">
        <h2>Welcome, Dr. {user.name}!</h2>
        <p>Email: {user.email}</p>
        <p>Specialization: Cardiology</p>
      </div>
      
      <div className="card">
        <h2>Today's Appointments</h2>
        <ul>
          <li>10:00 AM - John Smith (Follow-up)</li>
          <li>11:30 AM - Sarah Johnson (Initial Consultation)</li>
          <li>2:00 PM - Michael Brown (Check-up)</li>
        </ul>
        <button className="btn">View All Appointments</button>
      </div>
      
      <div className="card">
        <h2>Pending Treatment Plans</h2>
        <p>No pending treatment plans.</p>
        <button className="btn">Create Treatment Plan</button>
      </div>
      
      <div className="card">
        <h2>Recent Patient Reports</h2>
        <ul>
          <li>John Smith - Blood Test Results (Aug 15, 2023)</li>
          <li>Sarah Johnson - X-Ray Report (Aug 14, 2023)</li>
        </ul>
        <button className="btn">View All Reports</button>
      </div>
    </div>
  );
};

export default DoctorDashboard;