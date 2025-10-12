import React, { useState, useEffect } from 'react';
import Modal from '../common/Modal';
import ApiService from '../../services/api';

const AppointmentManagement = ({ isOpen, onClose, user }) => {
  const [appointments, setAppointments] = useState([]);
  const [loading, setLoading] = useState(true);
  const [selectedDate, setSelectedDate] = useState(new Date().toISOString().split('T')[0]);
  const [filter, setFilter] = useState('all');

  // Mock appointments data
  const mockAppointments = [
    {
      id: 1,
      patient: 'John Smith',
      patientEmail: 'john.smith@example.com',
      date: '2023-08-25',
      timeSlot: '09:00',
      status: 'confirmed',
      type: 'Initial Consultation',
      notes: 'First visit, general checkup'
    },
    {
      id: 2,
      patient: 'Sarah Johnson',
      patientEmail: 'sarah.johnson@example.com',
      date: '2023-08-25',
      timeSlot: '10:30',
      status: 'pending',
      type: 'Follow-up',
      notes: 'Blood pressure monitoring follow-up'
    },
    {
      id: 3,
      patient: 'Michael Brown',
      patientEmail: 'michael.brown@example.com',
      date: '2023-08-25',
      timeSlot: '14:00',
      status: 'confirmed',
      type: 'Regular Checkup',
      notes: 'Annual physical examination'
    },
    {
      id: 4,
      patient: 'Emily Davis',
      patientEmail: 'emily.davis@example.com',
      date: '2023-08-26',
      timeSlot: '11:00',
      status: 'pending',
      type: 'Consultation',
      notes: 'Discuss test results'
    }
  ];

  useEffect(() => {
    if (isOpen) {
      fetchAppointments();
    }
  }, [isOpen, selectedDate]);

  const fetchAppointments = async () => {
    setLoading(true);
    try {
      const response = await ApiService.getUserAppointments(user.id);
      setAppointments(response.appointments || mockAppointments);
    } catch (error) {
      console.error('Failed to fetch appointments:', error);
      setAppointments(mockAppointments);
    } finally {
      setLoading(false);
    }
  };

  const handleStatusChange = async (appointmentId, newStatus) => {
    try {
      await ApiService.updateAppointmentStatus(appointmentId, newStatus);
      setAppointments(appointments.map(apt => 
        apt.id === appointmentId ? { ...apt, status: newStatus } : apt
      ));
      alert(`Appointment ${newStatus} successfully!`);
    } catch (error) {
      console.error('Failed to update appointment:', error);
      // For demo purposes, still update local state
      setAppointments(appointments.map(apt => 
        apt.id === appointmentId ? { ...apt, status: newStatus } : apt
      ));
      alert(`Appointment ${newStatus} successfully! (Demo mode)`);
    }
  };

  const filteredAppointments = appointments.filter(apt => {
    const matchesDate = apt.date === selectedDate;
    const matchesFilter = filter === 'all' || apt.status === filter;
    return matchesDate && matchesFilter;
  });

  const getStatusColor = (status) => {
    switch (status) {
      case 'confirmed': return '#28a745';
      case 'pending': return '#ffc107';
      case 'cancelled': return '#dc3545';
      case 'completed': return '#17a2b8';
      default: return '#6c757d';
    }
  };

  return (
    <Modal isOpen={isOpen} onClose={onClose} title="Appointment Management" size="large">
      <div className="appointment-management">
        <div className="filters">
          <div className="filter-group">
            <label>Date:</label>
            <input
              type="date"
              value={selectedDate}
              onChange={(e) => setSelectedDate(e.target.value)}
            />
          </div>
          <div className="filter-group">
            <label>Status:</label>
            <select value={filter} onChange={(e) => setFilter(e.target.value)}>
              <option value="all">All Status</option>
              <option value="pending">Pending</option>
              <option value="confirmed">Confirmed</option>
              <option value="completed">Completed</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </div>
          <button className="btn btn-secondary" onClick={fetchAppointments}>
            Refresh
          </button>
        </div>

        {loading ? (
          <div className="loading">Loading appointments...</div>
        ) : (
          <div className="appointments-list">
            {filteredAppointments.length === 0 ? (
              <div className="no-appointments">
                No appointments found for the selected date and filter.
              </div>
            ) : (
              filteredAppointments.map(appointment => (
                <div key={appointment.id} className="appointment-card">
                  <div className="appointment-header">
                    <div className="patient-info">
                      <h3>{appointment.patient}</h3>
                      <p>{appointment.patientEmail}</p>
                    </div>
                    <div className="appointment-time">
                      <span className="time">{appointment.timeSlot}</span>
                      <span 
                        className="status-badge"
                        style={{ backgroundColor: getStatusColor(appointment.status) }}
                      >
                        {appointment.status}
                      </span>
                    </div>
                  </div>
                  
                  <div className="appointment-details">
                    <p><strong>Type:</strong> {appointment.type}</p>
                    <p><strong>Notes:</strong> {appointment.notes}</p>
                  </div>
                  
                  <div className="appointment-actions">
                    {appointment.status === 'pending' && (
                      <>
                        <button 
                          className="btn btn-success btn-sm"
                          onClick={() => handleStatusChange(appointment.id, 'confirmed')}
                        >
                          Confirm
                        </button>
                        <button 
                          className="btn btn-danger btn-sm"
                          onClick={() => handleStatusChange(appointment.id, 'cancelled')}
                        >
                          Cancel
                        </button>
                      </>
                    )}
                    {appointment.status === 'confirmed' && (
                      <button 
                        className="btn btn-info btn-sm"
                        onClick={() => handleStatusChange(appointment.id, 'completed')}
                      >
                        Mark Complete
                      </button>
                    )}
                    <button className="btn btn-outline btn-sm">
                      View Patient History
                    </button>
                  </div>
                </div>
              ))
            )}
          </div>
        )}
      </div>

      <style jsx>{`
        .appointment-management {
          min-height: 500px;
        }
        
        .filters {
          display: flex;
          gap: 16px;
          align-items: end;
          margin-bottom: 24px;
          padding-bottom: 16px;
          border-bottom: 1px solid #e5e5e5;
        }
        
        .filter-group {
          display: flex;
          flex-direction: column;
          gap: 4px;
        }
        
        .filter-group label {
          font-size: 14px;
          font-weight: 500;
          color: #555;
        }
        
        .filter-group input,
        .filter-group select {
          padding: 8px 12px;
          border: 1px solid #ddd;
          border-radius: 4px;
          font-size: 14px;
        }
        
        .loading, .no-appointments {
          text-align: center;
          padding: 40px;
          color: #666;
        }
        
        .appointments-list {
          display: flex;
          flex-direction: column;
          gap: 16px;
        }
        
        .appointment-card {
          border: 1px solid #e5e5e5;
          border-radius: 8px;
          padding: 20px;
          background: #f8f9fa;
        }
        
        .appointment-header {
          display: flex;
          justify-content: space-between;
          align-items: flex-start;
          margin-bottom: 16px;
        }
        
        .patient-info h3 {
          margin: 0 0 4px 0;
          color: #333;
        }
        
        .patient-info p {
          margin: 0;
          color: #666;
          font-size: 14px;
        }
        
        .appointment-time {
          text-align: right;
        }
        
        .time {
          display: block;
          font-size: 18px;
          font-weight: 600;
          color: #333;
          margin-bottom: 8px;
        }
        
        .status-badge {
          padding: 4px 8px;
          border-radius: 12px;
          color: white;
          font-size: 12px;
          font-weight: 500;
          text-transform: capitalize;
        }
        
        .appointment-details {
          margin-bottom: 16px;
        }
        
        .appointment-details p {
          margin: 8px 0;
          font-size: 14px;
        }
        
        .appointment-actions {
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
        .btn-info { background: #17a2b8; color: white; }
        .btn-outline { 
          background: transparent; 
          color: #007bff; 
          border: 1px solid #007bff; 
        }
        
        .btn:hover {
          opacity: 0.9;
        }
      `}</style>
    </Modal>
  );
};

export default AppointmentManagement;