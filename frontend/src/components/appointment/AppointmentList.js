import React, { useState, useEffect } from 'react';
import './Appointment.css';

const AppointmentList = ({ userType }) => {
  const [appointments, setAppointments] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    fetchAppointments();
  }, []);

  const fetchAppointments = async () => {
    setLoading(true);
    setError('');
    
    try {
      // In a real app, this would be an API call
      // For now, we'll simulate with mock data
      const mockAppointments = [
        {
          id: 1,
          doctor: 'Dr. Smith',
          patient: 'John Doe',
          date: '2023-08-25',
          timeSlot: '10:00',
          status: 'confirmed'
        },
        {
          id: 2,
          doctor: 'Dr. Johnson',
          patient: 'Jane Roe',
          date: '2023-08-26',
          timeSlot: '14:30',
          status: 'pending'
        },
        {
          id: 3,
          doctor: 'Dr. Williams',
          patient: 'Bob Smith',
          date: '2023-08-27',
          timeSlot: '11:00',
          status: 'completed'
        }
      ];
      
      setAppointments(mockAppointments);
    } catch (err) {
      setError('Failed to fetch appointments');
    } finally {
      setLoading(false);
    }
  };

  const handleStatusChange = async (appointmentId, newStatus) => {
    // In a real app, this would be an API call to update the appointment status
    // For now, we'll just update the local state
    setAppointments(prevAppointments => 
      prevAppointments.map(appt => 
        appt.id === appointmentId ? { ...appt, status: newStatus } : appt
      )
    );
  };

  if (loading) {
    return <div className="appointments-list">Loading appointments...</div>;
  }

  if (error) {
    return <div className="appointments-list error-message">{error}</div>;
  }

  return (
    <div className="appointments-list">
      <h2>{userType === 'doctor' ? 'My Appointments' : 'My Appointments'}</h2>
      
      {appointments.length === 0 ? (
        <p>No appointments found.</p>
      ) : (
        <div>
          {appointments.map(appointment => (
            <div key={appointment.id} className="appointment-card">
              <h3>Appointment #{appointment.id}</h3>
              
              <div className="appointment-details">
                {userType === 'doctor' ? (
                  <div className="detail-item">
                    <span className="detail-label">Patient</span>
                    <span className="detail-value">{appointment.patient}</span>
                  </div>
                ) : (
                  <div className="detail-item">
                    <span className="detail-label">Doctor</span>
                    <span className="detail-value">{appointment.doctor}</span>
                  </div>
                )}
                
                <div className="detail-item">
                  <span className="detail-label">Date</span>
                  <span className="detail-value">{appointment.date}</span>
                </div>
                
                <div className="detail-item">
                  <span className="detail-label">Time</span>
                  <span className="detail-value">{appointment.timeSlot}</span>
                </div>
                
                <div className="detail-item">
                  <span className="detail-label">Status</span>
                  <span className="detail-value">
                    <span className={`appointment-status status-${appointment.status}`}>
                      {appointment.status}
                    </span>
                  </span>
                </div>
              </div>
              
              {userType === 'doctor' && (
                <div className="appointment-actions">
                  <select 
                    value={appointment.status} 
                    onChange={(e) => handleStatusChange(appointment.id, e.target.value)}
                  >
                    <option value="pending">Pending</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="cancelled">Cancelled</option>
                    <option value="completed">Completed</option>
                  </select>
                </div>
              )}
            </div>
          ))}
        </div>
      )}
    </div>
  );
};

export default AppointmentList;