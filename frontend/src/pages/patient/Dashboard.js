import React from 'react';
import '../../App.css';

const PatientDashboard = ({ user }) => {
  return (
    <div className="dashboard">
      <h1>Patient Dashboard</h1>
      <div className="card">
        <h2>Welcome, {user.name}!</h2>
        <p>Email: {user.email}</p>
        <p>Role: {user.role}</p>
      </div>
      
      <div className="card">
        <h2>Upcoming Appointments</h2>
        <p>You have no upcoming appointments.</p>
        <button className="btn">Book Appointment</button>
      </div>
      
      <div className="card">
        <h2>Medical History</h2>
        <p>No medical history available.</p>
        <button className="btn">View Medical Records</button>
      </div>
      
      <div className="card">
        <h2>Recent Reports</h2>
        <p>No recent reports available.</p>
        <button className="btn">View All Reports</button>
      </div>
    </div>
  );
};

export default PatientDashboard;