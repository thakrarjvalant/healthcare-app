import React, { useState, useEffect, useContext } from 'react';
import { AuthContext } from '../../context/AuthContext';
import ApiService from '../../services/api';
import PermissionGuard from '../../components/common/PermissionGuard';
import '../../App.css';

const ReceptionistDashboard = ({ user }) => {
  const { logout, hasPermission, refreshPermissions } = useContext(AuthContext);
  const [appointments, setAppointments] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [showPatientRegistration, setShowPatientRegistration] = useState(false);
  const [showCheckIn, setShowCheckIn] = useState(false);
  const [showPaymentProcessing, setShowPaymentProcessing] = useState(false);
  const [showReports, setShowReports] = useState(false);
  const [showCreateForm, setShowCreateForm] = useState(false);
  const [showConflictResolver, setShowConflictResolver] = useState(false);
  const [conflicts, setConflicts] = useState([]);
  const [newAppointmentData, setNewAppointmentData] = useState({
    patient_id: '',
    doctor_id: '',
    date: '',
    time: '',
    type: 'consultation',
    notes: ''
  });
  
  useEffect(() => {
    fetchTodaysAppointments();
  }, []);
  
  const fetchTodaysAppointments = async () => {
    try {
      // In a real app, this would fetch today's appointments
      const response = await ApiService.getUserAppointments('all');
      setAppointments(response.appointments || []);
    } catch (err) {
      console.log('Failed to fetch appointments, using mock data:', err.message);
      // Mock appointments for testing
      setAppointments([
        {
          id: 1,
          patient: 'John Smith',
          doctor: 'Dr. Williams',
          date: '2023-08-25',
          timeSlot: '9:00',
          status: 'confirmed'
        },
        {
          id: 2,
          patient: 'Sarah Johnson',
          doctor: 'Dr. Brown',
          date: '2023-08-25',
          timeSlot: '10:30',
          status: 'pending'
        }
      ]);
    } finally {
      setLoading(false);
    }
  };
  
  const fetchAppointments = async () => {
    try {
      // In a real implementation, this would fetch appointments from the API
      // For now, we'll use mock data
      const mockAppointments = [
        {
          id: 1,
          patient_name: 'John Doe',
          doctor_name: 'Dr. Smith',
          date: '2023-09-15',
          time: '10:00',
          type: 'Consultation',
          status: 'confirmed'
        },
        {
          id: 2,
          patient_name: 'Jane Roe',
          doctor_name: 'Dr. Johnson',
          date: '2023-09-15',
          time: '11:30',
          type: 'Follow-up',
          status: 'pending'
        }
      ];
      setAppointments(mockAppointments);
    } catch (err) {
      console.error('Failed to fetch appointments:', err);
    } finally {
      setLoading(false);
    }
  };

  const fetchConflicts = async () => {
    try {
      // In a real implementation, this would fetch scheduling conflicts from the API
      // For now, we'll use mock data
      const mockConflicts = [
        {
          id: 1,
          patient_name: 'John Doe',
          doctor_name: 'Dr. Smith',
          date: '2023-09-15',
          time: '10:00',
          conflict_with: 'Another appointment',
          severity: 'high'
        }
      ];
      setConflicts(mockConflicts);
    } catch (err) {
      console.error('Failed to fetch conflicts:', err);
    }
  };

  const handleCreateAppointment = async (e) => {
    e.preventDefault();
    try {
      // In a real implementation, this would create an appointment via the API
      alert('Appointment created successfully!');
      setShowCreateForm(false);
      setNewAppointmentData({
        patient_id: '',
        doctor_id: '',
        date: '',
        time: '',
        type: 'consultation',
        notes: ''
      });
      // Refresh appointments
      fetchAppointments();
    } catch (error) {
      console.error('Failed to create appointment:', error);
      alert('Failed to create appointment: ' + error.message);
    }
  };

  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setNewAppointmentData(prev => ({
      ...prev,
      [name]: value
    }));
  };

  const handleResolveConflict = (conflictId) => {
    // In a real implementation, this would resolve a scheduling conflict via the API
    alert(`Conflict ${conflictId} resolved!`);
    // Refresh conflicts
    fetchConflicts();
  };
  
  const handleRegisterPatient = () => {
    setShowPatientRegistration(true);
  };
  
  const handleCheckInPatient = () => {
    setShowCheckIn(true);
  };
  
  const handlePaymentProcessing = () => {
    setShowPaymentProcessing(true);
  };
  
  const handleViewReports = () => {
    setShowReports(true);
  };
  
  const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString();
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
      <h1>Receptionist Dashboard</h1>
      <div className="card">
        <h2>Welcome, {user.name}!</h2>
        <p>Email: {user.email}</p>
        <p>Role: {user.role}</p>
        <p className="role-description">
          Front desk operations specialist - Patient registration, appointment scheduling, and check-in management
        </p>
        <button className="btn btn-secondary" onClick={handleRefreshPermissions} style={{marginTop: '10px'}}>
          Refresh Permissions
        </button>
      </div>
      
      {/* Dynamic Appointment Management Section */}
      <PermissionGuard requiredPermissions={['appointments.create', 'appointments.read', 'appointments.update', 'appointments.delete']}>
        <div className="card">
          <h2>📅 Appointment Management</h2>
          <p>Manage all appointment scheduling, rescheduling, and cancellations.</p>
          <div className="card-actions">
            {hasPermission('appointments.create') && (
              <button className="btn" onClick={() => setShowCreateForm(true)}>Create Appointment</button>
            )}
            {hasPermission('appointments.read') && (
              <button className="btn btn-secondary" onClick={fetchAppointments}>Refresh Appointments</button>
            )}
          </div>
        </div>
      </PermissionGuard>
      
      {/* Dynamic Scheduling Conflicts Section */}
      <PermissionGuard requiredPermissions={['appointments.resolve_conflicts']}>
        <div className="card">
          <h2>⚠️ Scheduling Conflicts</h2>
          <p>Resolve appointment scheduling conflicts.</p>
          <button className="btn btn-warning" onClick={() => setShowConflictResolver(true)}>Resolve Conflicts</button>
        </div>
      </PermissionGuard>
      
      <div className="card">
        <h2>📋 Front Desk Overview</h2>
        <p>Today's front desk activity and patient flow</p>
        <div className="front-desk-stats">
          <div className="stat-item">
            <span className="stat-number">23</span>
            <span className="stat-label">Patients Registered</span>
          </div>
          <div className="stat-item">
            <span className="stat-number">18</span>
            <span className="stat-label">Check-ins Completed</span>
          </div>
          <div className="stat-item">
            <span className="stat-number">5</span>
            <span className="stat-label">Queue Waiting</span>
          </div>
        </div>
      </div>
      
      {/* Front Desk Core Functions */}
      <div className="front-desk-grid">
        <PermissionGuard requiredPermissions={['front_desk.registration', 'patients.basic_create', 'patients.basic_read']}>
          <div className="card">
            <h2>📋 Patient Registration</h2>
            <p>Register new patients and update patient information</p>
            <div className="registration-stats">
              <p><strong>New Registrations Today:</strong> 8</p>
              <p><strong>Updates Processed:</strong> 15</p>
            </div>
            <button className="btn btn-primary" onClick={handleRegisterPatient}>Register New Patient</button>
          </div>
        </PermissionGuard>

        <PermissionGuard requiredPermissions={['front_desk.checkin', 'front_desk.queue_management']}>
          <div className="card">
            <h2>✅ Patient Check-in</h2>
            <p>Process patient arrivals and manage check-in queue</p>
            <div className="checkin-stats">
              <p><strong>Checked In Today:</strong> 18</p>
              <p><strong>Currently Waiting:</strong> 5</p>
            </div>
            <button className="btn btn-success" onClick={handleCheckInPatient}>Manage Check-ins</button>
          </div>
        </PermissionGuard>

        <PermissionGuard requiredPermissions={['billing.create', 'billing.read', 'billing.update', 'billing.delete', 'payments.process']}>
          <div className="card">
            <h2>💳 Payment Processing</h2>
            <p>Process co-pays and handle basic billing inquiries</p>
            <div className="payment-summary">
              <p><strong>Co-pays Collected:</strong> $480</p>
              <p><strong>Pending Payments:</strong> $150</p>
            </div>
            <button className="btn btn-info" onClick={handlePaymentProcessing}>Process Payments</button>
          </div>
        </PermissionGuard>

        <div className="card">
          <h2>📋 Daily Reports</h2>
          <p>Front desk activity reports and patient flow metrics</p>
          <div className="report-preview">
            <p><strong>Patient Throughput:</strong> 92%</p>
            <p><strong>Avg Check-in Time:</strong> 3.2 mins</p>
          </div>
          <button className="btn btn-secondary" onClick={handleViewReports}>View Reports</button>
        </div>
      </div>
      
      {/* Appointment Management Modals */}
      <PermissionGuard requiredPermissions={['appointments.create', 'appointments.read', 'appointments.update']}>
        {showCreateForm && (
          <div className="modal-backdrop" onClick={() => setShowCreateForm(false)}>
            <div className="modal-content" onClick={(e) => e.stopPropagation()}>
              <div className="modal-header">
                <h2>📅 Create New Appointment</h2>
                <button className="modal-close" onClick={() => setShowCreateForm(false)}>&times;</button>
              </div>
              <div className="modal-body">
                <form onSubmit={handleCreateAppointment} className="modal-form">
                  <div className="form-group">
                    <label>Patient:</label>
                    <select
                      name="patient_id"
                      value={newAppointmentData.patient_id}
                      onChange={handleInputChange}
                      required
                    >
                      <option value="">Select Patient</option>
                      <option value="1">John Doe</option>
                      <option value="2">Jane Roe</option>
                    </select>
                  </div>
                  <div className="form-group">
                    <label>Doctor:</label>
                    <select
                      name="doctor_id"
                      value={newAppointmentData.doctor_id}
                      onChange={handleInputChange}
                      required
                    >
                      <option value="">Select Doctor</option>
                      <option value="1">Dr. Smith</option>
                      <option value="2">Dr. Johnson</option>
                    </select>
                  </div>
                  <div className="form-group">
                    <label>Date:</label>
                    <input
                      type="date"
                      name="date"
                      value={newAppointmentData.date}
                      onChange={handleInputChange}
                      required
                    />
                  </div>
                  <div className="form-group">
                    <label>Time:</label>
                    <input
                      type="time"
                      name="time"
                      value={newAppointmentData.time}
                      onChange={handleInputChange}
                      required
                    />
                  </div>
                  <div className="form-group">
                    <label>Appointment Type:</label>
                    <select
                      name="type"
                      value={newAppointmentData.type}
                      onChange={handleInputChange}
                    >
                      <option value="consultation">Consultation</option>
                      <option value="follow-up">Follow-up</option>
                      <option value="checkup">Checkup</option>
                      <option value="emergency">Emergency</option>
                    </select>
                  </div>
                  <div className="form-group">
                    <label>Notes:</label>
                    <textarea
                      name="notes"
                      value={newAppointmentData.notes}
                      onChange={handleInputChange}
                      placeholder="Additional notes for this appointment"
                    />
                  </div>
                  <div className="form-actions">
                    <button type="button" className="btn btn-secondary" onClick={() => setShowCreateForm(false)}>
                      Cancel
                    </button>
                    <button type="submit" className="btn btn-primary">
                      Create Appointment
                    </button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        )}
      </PermissionGuard>
      
      <PermissionGuard requiredPermissions={['appointments.resolve_conflicts']}>
        {showConflictResolver && (
          <div className="modal-backdrop" onClick={() => setShowConflictResolver(false)}>
            <div className="modal-content modal-large" onClick={(e) => e.stopPropagation()}>
              <div className="modal-header">
                <h2>⚠️ Scheduling Conflicts</h2>
                <button className="modal-close" onClick={() => setShowConflictResolver(false)}>&times;</button>
              </div>
              <div className="modal-body">
                <div className="conflicts-list">
                  {conflicts.length > 0 ? (
                    <table>
                      <thead>
                        <tr>
                          <th>Patient</th>
                          <th>Doctor</th>
                          <th>Date & Time</th>
                          <th>Conflict With</th>
                          <th>Severity</th>
                          <th>Actions</th>
                        </tr>
                      </thead>
                      <tbody>
                        {conflicts.map(conflict => (
                          <tr key={conflict.id}>
                            <td>{conflict.patient_name}</td>
                            <td>{conflict.doctor_name}</td>
                            <td>{formatDate(conflict.date)} at {conflict.time}</td>
                            <td>{conflict.conflict_with}</td>
                            <td>
                              <span className={`severity-${conflict.severity}`}>
                                {conflict.severity}
                              </span>
                            </td>
                            <td>
                              <button 
                                className="btn btn-sm btn-primary"
                                onClick={() => handleResolveConflict(conflict.id)}
                              >
                                Resolve
                              </button>
                            </td>
                          </tr>
                        ))}
                      </tbody>
                    </table>
                  ) : (
                    <p>No scheduling conflicts found.</p>
                  )}
                </div>
                <div className="form-actions">
                  <button 
                    className="btn btn-secondary" 
                    onClick={() => setShowConflictResolver(false)}
                  >
                    Close
                  </button>
                </div>
              </div>
            </div>
          </div>
        )}
      </PermissionGuard>
      
      {/* Modals */}
      <PermissionGuard requiredPermissions={['front_desk.registration', 'patients.basic_create']}>
        {showPatientRegistration && (
          <div className="modal-backdrop" onClick={() => setShowPatientRegistration(false)}>
            <div className="modal-content" onClick={(e) => e.stopPropagation()}>
              <div className="modal-header">
                <h2>Register New Patient</h2>
                <button className="modal-close" onClick={() => setShowPatientRegistration(false)}>&times;</button>
              </div>
              <div className="modal-body">
                <form className="patient-form">
                  <div className="form-section">
                    <h3>Personal Information</h3>
                    <div className="form-row">
                      <div className="form-group">
                        <label>First Name:</label>
                        <input type="text" required />
                      </div>
                      <div className="form-group">
                        <label>Last Name:</label>
                        <input type="text" required />
                      </div>
                    </div>
                    <div className="form-row">
                      <div className="form-group">
                        <label>Date of Birth:</label>
                        <input type="date" required />
                      </div>
                      <div className="form-group">
                        <label>Gender:</label>
                        <select required>
                          <option value="">Select...</option>
                          <option value="male">Male</option>
                          <option value="female">Female</option>
                          <option value="other">Other</option>
                        </select>
                      </div>
                    </div>
                  </div>
                  
                  <div className="form-section">
                    <h3>Contact Information</h3>
                    <div className="form-group">
                      <label>Email:</label>
                      <input type="email" required />
                    </div>
                    <div className="form-group">
                      <label>Phone:</label>
                      <input type="tel" required />
                    </div>
                    <div className="form-group">
                      <label>Address:</label>
                      <textarea rows="3"></textarea>
                    </div>
                  </div>
                  
                  <div className="form-section">
                    <h3>Emergency Contact</h3>
                    <div className="form-row">
                      <div className="form-group">
                        <label>Contact Name:</label>
                        <input type="text" />
                      </div>
                      <div className="form-group">
                        <label>Relationship:</label>
                        <input type="text" />
                      </div>
                    </div>
                    <div className="form-group">
                      <label>Emergency Phone:</label>
                      <input type="tel" />
                    </div>
                  </div>
                  
                  <div className="form-actions">
                    <button type="button" className="btn btn-secondary" onClick={() => setShowPatientRegistration(false)}>Cancel</button>
                    <button type="submit" className="btn btn-primary">Register Patient</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        )}
      </PermissionGuard>
      
      <PermissionGuard requiredPermissions={['front_desk.checkin']}>
        {showCheckIn && (
          <div className="modal-backdrop" onClick={() => setShowCheckIn(false)}>
            <div className="modal-content" onClick={(e) => e.stopPropagation()}>
              <div className="modal-header">
                <h2>Patient Check-in</h2>
                <button className="modal-close" onClick={() => setShowCheckIn(false)}>&times;</button>
              </div>
              <div className="modal-body">
                <div className="checkin-search">
                  <div className="search-options">
                    <div className="search-method">
                      <input type="radio" name="searchMethod" value="appointment" defaultChecked />
                      <label>Search by Appointment</label>
                    </div>
                    <div className="search-method">
                      <input type="radio" name="searchMethod" value="patient" />
                      <label>Search by Patient Name</label>
                    </div>
                  </div>
                  
                  <div className="search-input">
                    <input type="text" placeholder="Enter patient name or appointment ID" />
                    <button className="btn btn-primary">Search</button>
                  </div>
                </div>
                
                <div className="checkin-results">
                  <div className="appointment-card">
                    <div className="appointment-info">
                      <h3>John Smith</h3>
                      <p><strong>Appointment:</strong> 09:00 AM with Dr. Jane Smith</p>
                      <p><strong>Type:</strong> Regular Consultation</p>
                      <p><strong>Status:</strong> <span className="status confirmed">Confirmed</span></p>
                    </div>
                    <div className="checkin-actions">
                      <button className="btn btn-success">Check-in Patient</button>
                      <button className="btn btn-secondary">Update Info</button>
                    </div>
                  </div>
                </div>
                
                <div className="checkin-queue">
                  <h3>Today's Check-in Queue</h3>
                  <div className="queue-list">
                    <div className="queue-item checked-in">
                      <span className="patient-name">Sarah Johnson</span>
                      <span className="appointment-time">08:30</span>
                      <span className="status">Checked-in</span>
                    </div>
                    <div className="queue-item waiting">
                      <span className="patient-name">Michael Brown</span>
                      <span className="appointment-time">09:30</span>
                      <span className="status">Waiting</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        )}
      </PermissionGuard>
      
      <PermissionGuard requiredPermissions={['billing.create', 'billing.read', 'billing.update', 'billing.delete', 'payments.process']}>
        {showPaymentProcessing && (
          <div className="modal-backdrop" onClick={() => setShowPaymentProcessing(false)}>
            <div className="modal-content modal-large" onClick={(e) => e.stopPropagation()}>
              <div className="modal-header">
                <h2>💳 Payment Processing Center</h2>
                <button className="modal-close" onClick={() => setShowPaymentProcessing(false)}>&times;</button>
              </div>
              <div className="modal-body">
                <div className="payment-processing">
                  <div className="payment-summary-dashboard">
                    <h3>Today's Payment Summary</h3>
                    <div className="payment-stats">
                      <div className="stat-card">
                        <span className="stat-value">$1,280</span>
                        <span className="stat-label">Collected Today</span>
                      </div>
                      <div className="stat-card">
                        <span className="stat-value">$2,450</span>
                        <span className="stat-label">Pending Payments</span>
                      </div>
                      <div className="stat-card">
                        <span className="stat-value">23</span>
                        <span className="stat-label">Transactions</span>
                      </div>
                    </div>
                  </div>
                  
                  <div className="pending-payments">
                    <h3>Pending Payments</h3>
                    <div className="payments-table">
                      <table>
                        <thead>
                          <tr>
                            <th>Patient</th>
                            <th>Service</th>
                            <th>Amount</th>
                            <th>Insurance</th>
                            <th>Actions</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr>
                            <td>John Smith</td>
                            <td>Consultation</td>
                            <td>$250.00</td>
                            <td>BlueCross - 80%</td>
                            <td>
                              <button className="btn btn-sm btn-success">Collect Payment</button>
                              <button className="btn btn-sm btn-info">Insurance Claim</button>
                            </td>
                          </tr>
                          <tr>
                            <td>Sarah Johnson</td>
                            <td>Lab Tests</td>
                            <td>$180.00</td>
                            <td>Aetna - 70%</td>
                            <td>
                              <button className="btn btn-sm btn-success">Collect Payment</button>
                              <button className="btn btn-sm btn-info">Insurance Claim</button>
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </div>
                  
                  <div className="payment-methods">
                    <h3>Payment Methods</h3>
                    <div className="payment-options">
                      <button className="btn btn-primary">Cash Payment</button>
                      <button className="btn btn-info">Credit Card</button>
                      <button className="btn btn-secondary">Insurance</button>
                      <button className="btn btn-warning">Payment Plan</button>
                    </div>
                  </div>
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
              <h2>📋 Daily Reports & Analytics</h2>
              <button className="modal-close" onClick={() => setShowReports(false)}>&times;</button>
            </div>
            <div className="modal-body">
              <div className="reports-dashboard">
                <div className="report-filters">
                  <input type="date" defaultValue={new Date().toISOString().split('T')[0]} />
                  <select>
                    <option>All Reports</option>
                    <option>Appointment Summary</option>
                    <option>Payment Report</option>
                    <option>Patient Activity</option>
                  </select>
                  <button className="btn btn-primary">Generate Report</button>
                </div>
                
                <div className="reports-grid">
                  <div className="report-card">
                    <h3>Appointment Summary</h3>
                    <div className="report-metrics">
                      <div className="metric-item">
                        <span className="metric-number">28</span>
                        <span className="metric-label">Total Appointments</span>
                      </div>
                      <div className="metric-item">
                        <span className="metric-number">25</span>
                        <span className="metric-label">Completed</span>
                      </div>
                      <div className="metric-item">
                        <span className="metric-number">2</span>
                        <span className="metric-label">No Shows</span>
                      </div>
                    </div>
                    <button className="btn btn-sm btn-info">View Details</button>
                  </div>
                  
                  <div className="report-card">
                    <h3>Revenue Summary</h3>
                    <div className="report-metrics">
                      <div className="metric-item">
                        <span className="metric-number">$1,280</span>
                        <span className="metric-label">Cash Collected</span>
                      </div>
                      <div className="metric-item">
                        <span className="metric-number">$850</span>
                        <span className="metric-label">Insurance Claims</span>
                      </div>
                    </div>
                    <button className="btn btn-sm btn-info">View Details</button>
                  </div>
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
        
        .appointment-filters {
          display: flex;
          gap: 12px;
          margin-bottom: 20px;
          align-items: center;
        }
        
        .appointment-filters input,
        .appointment-filters select {
          padding: 8px 12px;
          border: 1px solid #ddd;
          border-radius: 4px;
        }
        
        .appointments-table table {
          width: 100%;
          border-collapse: collapse;
        }
        
        .appointments-table th,
        .appointments-table td {
          padding: 12px;
          text-align: left;
          border-bottom: 1px solid #ddd;
        }
        
        .appointments-table th {
          background-color: #f8f9fa;
          font-weight: 600;
        }
        
        .status {
          padding: 4px 8px;
          border-radius: 12px;
          font-size: 12px;
          font-weight: 500;
          color: white;
        }
        
        .status.confirmed { background: #28a745; }
        .status.pending { background: #ffc107; color: #000; }
        .status.cancelled { background: #dc3545; }
        
        .patient-form {
          max-height: 60vh;
          overflow-y: auto;
        }
        
        .form-section {
          margin-bottom: 24px;
          padding-bottom: 16px;
          border-bottom: 1px solid #e5e5e5;
        }
        
        .form-section:last-of-type {
          border-bottom: none;
        }
        
        .form-section h3 {
          margin-top: 0;
          margin-bottom: 16px;
          color: #333;
        }
        
        .form-row {
          display: flex;
          gap: 16px;
        }
        
        .form-group {
          flex: 1;
          margin-bottom: 16px;
        }
        
        .form-group label {
          display: block;
          margin-bottom: 4px;
          font-weight: 500;
          color: #555;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
          width: 100%;
          padding: 8px 12px;
          border: 1px solid #ddd;
          border-radius: 4px;
        }
        
        .search-options {
          display: flex;
          gap: 20px;
          margin-bottom: 16px;
        }
        
        .search-method {
          display: flex;
          align-items: center;
          gap: 8px;
        }
        
        .search-input {
          display: flex;
          gap: 12px;
          margin-bottom: 20px;
        }
        
        .search-input input {
          flex: 1;
          padding: 8px 12px;
          border: 1px solid #ddd;
          border-radius: 4px;
        }
        
        .appointment-card {
          border: 1px solid #e5e5e5;
          border-radius: 8px;
          padding: 20px;
          background: #f8f9fa;
          margin-bottom: 20px;
        }
        
        .appointment-info h3 {
          margin-top: 0;
          color: #333;
        }
        
        .checkin-actions {
          margin-top: 16px;
          display: flex;
          gap: 12px;
        }
        
        .checkin-queue h3 {
          margin-bottom: 12px;
          color: #333;
        }
        
        .queue-item {
          display: flex;
          justify-content: space-between;
          align-items: center;
          padding: 12px;
          border: 1px solid #e5e5e5;
          border-radius: 4px;
          margin-bottom: 8px;
        }
        
        .queue-item.checked-in {
          background: #d4edda;
        }
        
        .queue-item.waiting {
          background: #fff3cd;
        }
        
        .form-actions {
          display: flex;
          gap: 12px;
          justify-content: flex-end;
          margin-top: 20px;
          padding-top: 16px;
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
        
        .card-actions {
          display: flex;
          gap: 12px;
          margin-top: 16px;
        }
        
        .payment-summary {
          background: #f8f9fa;
          padding: 12px;
          border-radius: 6px;
          margin: 12px 0;
        }

        /* Front Desk Specific Styles */
        .role-description {
          font-style: italic;
          color: #666;
          margin-top: 8px;
        }

        .front-desk-stats {
          display: grid;
          grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
          gap: 16px;
          margin-top: 16px;
        }

        .stat-item {
          background: #f8f9fa;
          padding: 12px;
          border-radius: 8px;
          text-align: center;
          border-left: 4px solid #ffc107;
        }

        .stat-number {
          display: block;
          font-size: 1.5rem;
          font-weight: bold;
          color: #333;
          margin-bottom: 4px;
        }

        .stat-label {
          color: #666;
          font-size: 0.9rem;
        }

        .front-desk-grid {
          display: grid;
          grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
          gap: 20px;
          margin-top: 20px;
        }

        .registration-stats, .checkin-stats, .report-preview {
          background: #f8f9fa;
          padding: 12px;
          border-radius: 6px;
          margin: 12px 0;
        }}
        
        /* Payment Processing Styles */
        .payment-stats {
          display: grid;
          grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
          gap: 16px;
          margin-bottom: 24px;
        }
        
        .stat-card {
          background: white;
          padding: 16px;
          border-radius: 8px;
          border: 1px solid #e5e5e5;
          text-align: center;
        }
        
        .stat-value {
          display: block;
          font-size: 1.5rem;
          font-weight: bold;
          color: #333;
          margin-bottom: 4px;
        }
        
        .stat-label {
          color: #666;
          font-size: 0.9rem;
        }
        
        .payments-table table {
          width: 100%;
          margin-bottom: 20px;
        }
        
        .payment-options {
          display: flex;
          gap: 12px;
          flex-wrap: wrap;
        }
        
        /* Reports Styles */
        .report-filters {
          display: flex;
          gap: 12px;
          margin-bottom: 20px;
          align-items: center;
        }
        
        .reports-grid {
          display: grid;
          grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
          gap: 20px;
        }
        
        .report-card {
          background: white;
          padding: 20px;
          border-radius: 8px;
          border: 1px solid #e5e5e5;
        }
        
        .report-metrics {
          margin: 16px 0;
        }
        
        .metric-item {
          display: flex;
          justify-content: space-between;
          align-items: center;
          padding: 8px 0;
          border-bottom: 1px solid #f0f0f0;
        }
        
        .metric-number {
          font-weight: bold;
          color: #333;
        }
        
        .metric-label {
          color: #666;
        }
        
        /* Calendar Styles */
        .calendar-controls {
          display: flex;
          justify-content: space-between;
          align-items: center;
          margin-bottom: 20px;
        }
        
        .date-navigation, .view-options {
          display: flex;
          gap: 8px;
          align-items: center;
        }
        
        .current-date {
          font-weight: 500;
          margin: 0 12px;
        }
        
        .time-slots {
          display: flex;
          flex-direction: column;
          gap: 8px;
        }
        
        .time-slot {
          display: flex;
          align-items: center;
          gap: 16px;
        }
        
        .time-label {
          width: 80px;
          font-weight: 500;
          color: #666;
        }
        
        .appointment-slot {
          flex: 1;
          padding: 12px;
          border-radius: 6px;
          border: 1px solid #e5e5e5;
        }
        
        .appointment-slot.occupied {
          background: #e3f2fd;
          border-color: #2196f3;
        }
        
        .appointment-slot.available {
          background: #f8f9fa;
          border-style: dashed;
          text-align: center;
          color: #999;
        }
        
        .appointment-info {
          font-size: 0.9rem;
        }
        
        .slot-placeholder {
          color: #999;
        }
        
        .action-buttons {
          display: flex;
          gap: 12px;
          flex-wrap: wrap;
        }
      `}
      </style>
    </div>
  );
};

export default ReceptionistDashboard;