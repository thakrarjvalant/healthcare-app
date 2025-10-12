import React, { useState, useEffect, useContext } from 'react';
import { AuthContext } from '../../context/AuthContext';
import ApiService from '../../services/api';
import AppointmentManagement from '../../components/doctor/AppointmentManagement';
import PermissionGuard from '../../components/common/PermissionGuard';
import '../../App.css';

const DoctorDashboard = ({ user }) => {
  const { logout, hasPermission, refreshPermissions } = useContext(AuthContext);
  const [appointments, setAppointments] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [showAppointmentManagement, setShowAppointmentManagement] = useState(false);
  const [showTreatmentPlans, setShowTreatmentPlans] = useState(false);
  const [showReports, setShowReports] = useState(false);
  const [showPatientManagement, setShowPatientManagement] = useState(false);
  const [showClinicalNotes, setShowClinicalNotes] = useState(false);
  const [showScheduleManagement, setShowScheduleManagement] = useState(false);
  
  useEffect(() => {
    fetchDoctorAppointments();
  }, []);
  
  const fetchDoctorAppointments = async () => {
    try {
      const response = await ApiService.getUserAppointments(user.id);
      setAppointments(response.appointments || []);
    } catch (err) {
      console.log('Failed to fetch appointments, using mock data:', err.message);
      // Mock appointments for testing
      setAppointments([
        {
          id: 1,
          patient: 'John Smith',
          date: '2023-08-25',
          timeSlot: '10:00',
          status: 'confirmed',
          type: 'Follow-up'
        },
        {
          id: 2,
          patient: 'Sarah Johnson',
          date: '2023-08-25',
          timeSlot: '11:30',
          status: 'confirmed',
          type: 'Initial Consultation'
        }
      ]);
    } finally {
      setLoading(false);
    }
  };
  
  const handleViewAllAppointments = () => {
    setShowAppointmentManagement(true);
  };
  
  const handleCreateTreatmentPlan = () => {
    setShowTreatmentPlans(true);
  };
  
  const handleViewReports = () => {
    setShowReports(true);
  };
  
  const handlePatientManagement = () => {
    setShowPatientManagement(true);
  };
  
  const handleClinicalNotes = () => {
    setShowClinicalNotes(true);
  };
  
  const handleScheduleManagement = () => {
    setShowScheduleManagement(true);
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
      <h1>Doctor Dashboard</h1>
      <div className="card">
        <h2>Welcome, Dr. {user.name}!</h2>
        <p>Email: {user.email}</p>
        <p>Specialization: Cardiology</p>
        <button className="btn btn-secondary" onClick={handleRefreshPermissions} style={{marginTop: '10px'}}>
          Refresh Permissions
        </button>
      </div>
      
      <PermissionGuard requiredPermissions={['appointments.read', 'appointments.update']}>
        <div className="card">
          <h2>Today's Appointments</h2>
          {loading ? (
            <p>Loading appointments...</p>
          ) : error ? (
            <p className="error-message">{error}</p>
          ) : appointments.length > 0 ? (
            <ul>
              {appointments.map(appointment => (
                <li key={appointment.id}>
                  {appointment.timeSlot} - {appointment.patient} ({appointment.type})
                  <span className={`status-badge status-${appointment.status}`}>
                    {appointment.status}
                  </span>
                </li>
              ))}
            </ul>
          ) : (
            <p>No appointments scheduled for today.</p>
          )}
          <div className="card-actions">
            <button className="btn" onClick={handleViewAllAppointments}>View All Appointments</button>
            {hasPermission('schedule.manage') && (
              <button className="btn btn-secondary" onClick={handleScheduleManagement}>Manage Schedule</button>
            )}
          </div>
        </div>
      </PermissionGuard>
      
      <PermissionGuard requiredPermissions={['treatment_plans.create', 'treatment_plans.read']}>
        <div className="card">
          <h2>🗃️ Clinical Management</h2>
          <p>Manage patient clinical data, notes, and treatment plans.</p>
          <div className="card-actions">
            <button className="btn" onClick={handleCreateTreatmentPlan}>Treatment Plans</button>
            {hasPermission('clinical_notes.create') && (
              <button className="btn btn-secondary" onClick={handleClinicalNotes}>Clinical Notes</button>
            )}
          </div>
        </div>
      </PermissionGuard>
      
      <PermissionGuard requiredPermissions={['patients.read', 'medical_records.read']}>
        <div className="card">
          <h2>👥 Patient Management</h2>
          <p>View and manage your patients' information and medical history.</p>
          <button className="btn" onClick={handlePatientManagement}>Manage Patients</button>
        </div>
      </PermissionGuard>
      
      <PermissionGuard requiredPermissions={['reports.read', 'analytics.view']}>
        <div className="card">
          <h2>Recent Patient Reports</h2>
          <ul>
            <li>John Smith - Blood Test Results (Aug 15, 2023)</li>
            <li>Sarah Johnson - X-Ray Report (Aug 14, 2023)</li>
          </ul>
          <button className="btn" onClick={handleViewReports}>View All Reports</button>
        </div>
      </PermissionGuard>
      
      {/* Modals */}
      <AppointmentManagement 
        isOpen={showAppointmentManagement} 
        onClose={() => setShowAppointmentManagement(false)} 
        user={user}
      />
      
      <PermissionGuard requiredPermissions={['treatment_plans.create', 'treatment_plans.update']}>
        {showTreatmentPlans && (
          <div className="modal-backdrop" onClick={() => setShowTreatmentPlans(false)}>
            <div className="modal-content modal-large" onClick={(e) => e.stopPropagation()}>
              <div className="modal-header">
                <h2>Treatment Plans</h2>
                <button className="modal-close" onClick={() => setShowTreatmentPlans(false)}>&times;</button>
              </div>
              <div className="modal-body">
                <div className="treatment-plans">
                  <div className="action-bar">
                    {hasPermission('treatment_plans.create') && (
                      <button className="btn btn-primary">Create New Treatment Plan</button>
                    )}
                  </div>
                  
                  <div className="plans-list">
                    <div className="plan-card">
                      <div className="plan-header">
                        <h3>Hypertension Management - John Smith</h3>
                        <span className="status-badge active">Active</span>
                      </div>
                      <div className="plan-details">
                        <p><strong>Start Date:</strong> August 1, 2023</p>
                        <p><strong>Duration:</strong> 3 months</p>
                        <p><strong>Medications:</strong> Lisinopril 10mg daily</p>
                        <p><strong>Instructions:</strong> Monitor blood pressure daily, low sodium diet</p>
                      </div>
                      <div className="plan-actions">
                        {hasPermission('treatment_plans.update') && (
                          <button className="btn btn-sm btn-secondary">Edit Plan</button>
                        )}
                        {hasPermission('treatment_plans.read') && (
                          <button className="btn btn-sm btn-info">View Progress</button>
                        )}
                      </div>
                    </div>
                    
                    <div className="plan-card">
                      <div className="plan-header">
                        <h3>Diabetes Management - Sarah Johnson</h3>
                        <span className="status-badge active">Active</span>
                      </div>
                      <div className="plan-details">
                        <p><strong>Start Date:</strong> July 15, 2023</p>
                        <p><strong>Duration:</strong> 6 months</p>
                        <p><strong>Medications:</strong> Metformin 500mg twice daily</p>
                        <p><strong>Instructions:</strong> Blood glucose monitoring, dietary counseling</p>
                      </div>
                      <div className="plan-actions">
                        {hasPermission('treatment_plans.update') && (
                          <button className="btn btn-sm btn-secondary">Edit Plan</button>
                        )}
                        {hasPermission('treatment_plans.read') && (
                          <button className="btn btn-sm btn-info">View Progress</button>
                        )}
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        )}
      </PermissionGuard>
      
      <PermissionGuard requiredPermissions={['reports.read']}>
        {showReports && (
          <div className="modal-backdrop" onClick={() => setShowReports(false)}>
            <div className="modal-content modal-large" onClick={(e) => e.stopPropagation()}>
              <div className="modal-header">
                <h2>📈 Patient Reports & Analytics</h2>
                <button className="modal-close" onClick={() => setShowReports(false)}>&times;</button>
              </div>
              <div className="modal-body">
                <div className="reports-section">
                  <div className="filters">
                    <select>
                      <option>All Patients</option>
                      <option>John Smith</option>
                      <option>Sarah Johnson</option>
                      <option>Michael Brown</option>
                    </select>
                    <select>
                      <option>All Report Types</option>
                      <option>Lab Results</option>
                      <option>Imaging</option>
                      <option>Progress Notes</option>
                      <option>Treatment Plans</option>
                    </select>
                    <input type="date" />
                    <input type="date" />
                    <button className="btn btn-primary">Filter</button>
                  </div>
                  
                  <div className="reports-list">
                    <div className="report-item">
                      <div className="report-header">
                        <h3>🩸 Blood Test Results - John Smith</h3>
                        <span className="report-date">August 15, 2023</span>
                      </div>
                      <div className="report-summary">
                        <p>Complete Blood Count, Lipid Panel, HbA1c</p>
                        <p><strong>Status:</strong> <span className="status normal">✓ Normal ranges</span></p>
                        <p><strong>Notes:</strong> Follow-up required in 3 months</p>
                      </div>
                      <div className="report-actions">
                        <button className="btn btn-sm btn-primary">View Full Report</button>
                        <button className="btn btn-sm btn-secondary">Add to Treatment Plan</button>
                        <button className="btn btn-sm btn-info">Download PDF</button>
                      </div>
                    </div>
                    
                    <div className="report-item">
                      <div className="report-header">
                        <h3>📷 Chest X-Ray - Sarah Johnson</h3>
                        <span className="report-date">August 12, 2023</span>
                      </div>
                      <div className="report-summary">
                        <p>Chest X-Ray - PA and Lateral views</p>
                        <p><strong>Status:</strong> <span className="status normal">✓ No abnormalities</span></p>
                        <p><strong>Radiologist:</strong> Dr. Patricia Wilson</p>
                      </div>
                      <div className="report-actions">
                        <button className="btn btn-sm btn-primary">View Images</button>
                        <button className="btn btn-sm btn-secondary">Compare Previous</button>
                        <button className="btn btn-sm btn-info">Download Report</button>
                      </div>
                    </div>
                    
                    <div className="report-item">
                      <div className="report-header">
                        <h3>🧬 MRI Brain - Michael Brown</h3>
                        <span className="report-date">August 10, 2023</span>
                      </div>
                      <div className="report-summary">
                        <p>MRI Brain with contrast</p>
                        <p><strong>Status:</strong> <span className="status attention">⚠ Requires attention</span></p>
                        <p><strong>Priority:</strong> High - Schedule follow-up</p>
                      </div>
                      <div className="report-actions">
                        <button className="btn btn-sm btn-primary">View Study</button>
                        <button className="btn btn-sm btn-warning">Schedule Follow-up</button>
                        <button className="btn btn-sm btn-info">Consult Specialist</button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        )}
      </PermissionGuard>
      
      <PermissionGuard requiredPermissions={['patients.read', 'patients.update']}>
        {showPatientManagement && (
          <div className="modal-backdrop" onClick={() => setShowPatientManagement(false)}>
            <div className="modal-content modal-large" onClick={(e) => e.stopPropagation()}>
              <div className="modal-header">
                <h2>👥 Patient Management</h2>
                <button className="modal-close" onClick={() => setShowPatientManagement(false)}>&times;</button>
              </div>
              <div className="modal-body">
                <div className="patient-management">
                  <div className="search-section">
                    <input type="text" placeholder="Search patients by name, ID, or condition..." className="search-input" />
                    <button className="btn btn-primary">Search</button>
                  </div>
                  
                  <div className="patients-grid">
                    <div className="patient-card">
                      <div className="patient-header">
                        <h3>John Smith</h3>
                        <span className="patient-id">#P001</span>
                      </div>
                      <div className="patient-info">
                        <p><strong>Age:</strong> 45</p>
                        <p><strong>Last Visit:</strong> Aug 15, 2023</p>
                        <p><strong>Condition:</strong> Hypertension</p>
                        <p><strong>Next Appointment:</strong> Aug 25, 10:00 AM</p>
                      </div>
                      <div className="patient-actions">
                        <button className="btn btn-sm btn-primary">View Records</button>
                        <button className="btn btn-sm btn-secondary">Update Plan</button>
                        <button className="btn btn-sm btn-info">Send Message</button>
                      </div>
                    </div>
                    
                    <div className="patient-card">
                      <div className="patient-header">
                        <h3>Sarah Johnson</h3>
                        <span className="patient-id">#P002</span>
                      </div>
                      <div className="patient-info">
                        <p><strong>Age:</strong> 32</p>
                        <p><strong>Last Visit:</strong> Aug 12, 2023</p>
                        <p><strong>Condition:</strong> Diabetes Type 2</p>
                        <p><strong>Next Appointment:</strong> Aug 26, 2:00 PM</p>
                      </div>
                      <div className="patient-actions">
                        <button className="btn btn-sm btn-primary">View Records</button>
                        <button className="btn btn-sm btn-secondary">Update Plan</button>
                        <button className="btn btn-sm btn-info">Send Message</button>
                      </div>
                    </div>
                    
                    <div className="patient-card priority">
                      <div className="patient-header">
                        <h3>Michael Brown</h3>
                        <span className="patient-id">#P003</span>
                        <span className="priority-badge">High Priority</span>
                      </div>
                      <div className="patient-info">
                        <p><strong>Age:</strong> 58</p>
                        <p><strong>Last Visit:</strong> Aug 10, 2023</p>
                        <p><strong>Condition:</strong> Post-surgical care</p>
                        <p><strong>Status:</strong> Requires immediate attention</p>
                      </div>
                      <div className="patient-actions">
                        <button className="btn btn-sm btn-warning">Urgent Review</button>
                        <button className="btn btn-sm btn-primary">View Records</button>
                        <button className="btn btn-sm btn-info">Call Patient</button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        )}
      </PermissionGuard>
      
      <PermissionGuard requiredPermissions={['clinical_notes.create', 'clinical_notes.read']}>
        {showClinicalNotes && (
          <div className="modal-backdrop" onClick={() => setShowClinicalNotes(false)}>
            <div className="modal-content modal-large" onClick={(e) => e.stopPropagation()}>
              <div className="modal-header">
                <h2>🗃️ Clinical Notes & Documentation</h2>
                <button className="modal-close" onClick={() => setShowClinicalNotes(false)}>&times;</button>
              </div>
              <div className="modal-body">
                <div className="clinical-notes">
                  <div className="notes-toolbar">
                    <button className="btn btn-primary">New Note</button>
                    <select>
                      <option>All Patients</option>
                      <option>John Smith</option>
                      <option>Sarah Johnson</option>
                    </select>
                    <select>
                      <option>All Note Types</option>
                      <option>Progress Notes</option>
                      <option>Assessment</option>
                      <option>Treatment Plan</option>
                      <option>Discharge Summary</option>
                    </select>
                  </div>
                  
                  <div className="notes-list">
                    <div className="note-item">
                      <div className="note-header">
                        <h3>Progress Note - John Smith</h3>
                        <span className="note-date">Aug 15, 2023 10:30 AM</span>
                      </div>
                      <div className="note-content">
                        <p><strong>Chief Complaint:</strong> Follow-up for hypertension management</p>
                        <p><strong>Assessment:</strong> Blood pressure well controlled on current medication regimen. Patient reports no side effects.</p>
                        <p><strong>Plan:</strong> Continue current medications. Recheck in 3 months. Patient advised on lifestyle modifications.</p>
                      </div>
                      <div className="note-actions">
                        <button className="btn btn-sm btn-secondary">Edit</button>
                        <button className="btn btn-sm btn-info">View Full</button>
                        <button className="btn btn-sm btn-warning">Add Addendum</button>
                      </div>
                    </div>
                    
                    <div className="note-item">
                      <div className="note-header">
                        <h3>Treatment Plan - Sarah Johnson</h3>
                        <span className="note-date">Aug 12, 2023 2:15 PM</span>
                      </div>
                      <div className="note-content">
                        <p><strong>Diagnosis:</strong> Type 2 Diabetes Mellitus, well controlled</p>
                        <p><strong>Current Medications:</strong> Metformin 500mg BID, Lisinopril 10mg daily</p>
                        <p><strong>Goals:</strong> HbA1c &lt;7%, BP &lt;130/80, weight loss 10 lbs</p>
                      </div>
                      <div className="note-actions">
                        <button className="btn btn-sm btn-secondary">Edit</button>
                        <button className="btn btn-sm btn-info">View Full</button>
                        <button className="btn btn-sm btn-success">Update Plan</button>
                      </div>
                    </div>
                  </div>
                  
                  <div className="note-templates">
                    <h3>Quick Templates</h3>
                    <div className="template-buttons">
                      <button className="btn btn-sm btn-outline">Progress Note</button>
                      <button className="btn btn-sm btn-outline">Assessment & Plan</button>
                      <button className="btn btn-sm btn-outline">Prescription</button>
                      <button className="btn btn-sm btn-outline">Referral</button>
                      <button className="btn btn-sm btn-outline">Discharge Summary</button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        )}
      </PermissionGuard>
      
      <PermissionGuard requiredPermissions={['schedule.manage']}>
        {showScheduleManagement && (
          <div className="modal-backdrop" onClick={() => setShowScheduleManagement(false)}>
            <div className="modal-content modal-large" onClick={(e) => e.stopPropagation()}>
              <div className="modal-header">
                <h2>📅 Schedule Management</h2>
                <button className="modal-close" onClick={() => setShowScheduleManagement(false)}>&times;</button>
              </div>
              <div className="modal-body">
                <div className="schedule-management">
                  <div className="schedule-controls">
                    <div className="date-controls">
                      <button className="btn btn-sm">&lt;</button>
                      <span className="current-date">August 2023</span>
                      <button className="btn btn-sm">&gt;</button>
                    </div>
                    <div className="view-controls">
                      <button className="btn btn-sm btn-primary">Day</button>
                      <button className="btn btn-sm">Week</button>
                      <button className="btn btn-sm">Month</button>
                    </div>
                  </div>
                  
                  <div className="availability-settings">
                    <h3>Availability Settings</h3>
                    <div className="availability-grid">
                      <div className="day-settings">
                        <h4>Monday</h4>
                        <label><input type="checkbox" checked /> Available</label>
                        <div className="time-slots">
                          <input type="time" defaultValue="09:00" /> - <input type="time" defaultValue="17:00" />
                        </div>
                        <div className="break-settings">
                          <label>Lunch Break:</label>
                          <input type="time" defaultValue="12:00" /> - <input type="time" defaultValue="13:00" />
                        </div>
                      </div>
                      
                      <div className="day-settings">
                        <h4>Tuesday</h4>
                        <label><input type="checkbox" checked /> Available</label>
                        <div className="time-slots">
                          <input type="time" defaultValue="09:00" /> - <input type="time" defaultValue="17:00" />
                        </div>
                        <div className="break-settings">
                          <label>Lunch Break:</label>
                          <input type="time" defaultValue="12:00" /> - <input type="time" defaultValue="13:00" />
                        </div>
                      </div>
                    </div>
                  </div>
                  
                  <div className="appointment-types">
                    <h3>Appointment Types & Duration</h3>
                    <div className="appointment-type-settings">
                      <div className="type-setting">
                        <label>Consultation:</label>
                        <input type="number" defaultValue="30" /> minutes
                      </div>
                      <div className="type-setting">
                        <label>Follow-up:</label>
                        <input type="number" defaultValue="15" /> minutes
                      </div>
                      <div className="type-setting">
                        <label>Physical Exam:</label>
                        <input type="number" defaultValue="45" /> minutes
                      </div>
                    </div>
                  </div>
                  
                  <div className="form-actions">
                    <button className="btn btn-primary">Save Schedule Settings</button>
                    <button className="btn btn-secondary" onClick={() => setShowScheduleManagement(false)}>Cancel</button>
                    <button className="btn btn-warning">Block Time Slot</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        )}
      </PermissionGuard>
      
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
        
        .action-bar {
          margin-bottom: 20px;
        }
        
        .plan-card, .report-item {
          border: 1px solid #e5e5e5;
          border-radius: 8px;
          padding: 20px;
          margin-bottom: 16px;
          background: #f8f9fa;
        }
        
        .plan-header, .report-header {
          display: flex;
          justify-content: space-between;
          align-items: center;
          margin-bottom: 12px;
        }
        
        .plan-header h3, .report-header h3 {
          margin: 0;
          color: #333;
        }
        
        .status-badge {
          padding: 4px 8px;
          border-radius: 12px;
          font-size: 12px;
          font-weight: 500;
          color: white;
        }
        
        .status-badge.active {
          background: #28a745;
        }
        
        .plan-details, .report-summary {
          margin-bottom: 16px;
        }
        
        .plan-details p, .report-summary p {
          margin: 8px 0;
          font-size: 14px;
        }
        
        .plan-actions, .report-actions {
          display: flex;
          gap: 8px;
        }
        
        .filters {
          display: flex;
          gap: 12px;
          margin-bottom: 20px;
        }
        
        .filters select, .filters input {
          padding: 8px 12px;
          border: 1px solid #ddd;
          border-radius: 4px;
        }
        
        .report-date {
          color: #666;
          font-size: 14px;
        }
        
        .status {
          padding: 2px 6px;
          border-radius: 4px;
          font-size: 12px;
        }
        
        .status.normal {
          background: #d4edda;
          color: #155724;
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
        .btn-info { background: #17a2b8; color: white; }
        
        /* Dashboard Styles */
        .dashboard {
          padding: 24px;
          background: #f8f9fa;
          min-height: 100vh;
        }
        
        .card {
          background: white;
          border-radius: 8px;
          box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
          padding: 24px;
          margin-bottom: 24px;
        }
        
        .card-actions {
          display: flex;
          gap: 12px;
          margin-top: 16px;
        }
        
        .status-badge {
          padding: 2px 6px;
          border-radius: 10px;
          font-size: 10px;
          font-weight: 500;
          margin-left: 8px;
        }
        
        .status-confirmed { background: #d4edda; color: #155724; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        
        /* Patient Management Styles */
        .search-input {
          flex: 1;
          padding: 8px 12px;
          border: 1px solid #ddd;
          border-radius: 4px;
        }
        
        .patients-grid {
          display: grid;
          grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
          gap: 16px;
          margin-top: 20px;
        }
        
        .patient-card {
          border: 1px solid #e5e5e5;
          border-radius: 8px;
          padding: 16px;
          background: #f8f9fa;
        }
        
        .patient-card.priority {
          border-left: 4px solid #dc3545;
          background: #fff5f5;
        }
        
        .patient-header {
          display: flex;
          justify-content: space-between;
          align-items: center;
          margin-bottom: 12px;
        }
        
        .patient-id {
          color: #666;
          font-size: 12px;
        }
        
        .priority-badge {
          background: #dc3545;
          color: white;
          padding: 2px 6px;
          border-radius: 10px;
          font-size: 10px;
        }
        
        .patient-actions {
          display: flex;
          gap: 8px;
          margin-top: 12px;
        }
        
        /* Clinical Notes Styles */
        .notes-toolbar {
          display: flex;
          gap: 12px;
          margin-bottom: 20px;
          align-items: center;
        }
        
        .note-item {
          border: 1px solid #e5e5e5;
          border-radius: 8px;
          padding: 16px;
          margin-bottom: 16px;
          background: #f8f9fa;
        }
        
        .note-header {
          display: flex;
          justify-content: space-between;
          align-items: center;
          margin-bottom: 12px;
        }
        
        .note-date {
          color: #666;
          font-size: 12px;
        }
        
        .note-content {
          margin-bottom: 12px;
        }
        
        .note-actions {
          display: flex;
          gap: 8px;
        }
        
        .template-buttons {
          display: flex;
          gap: 8px;
          flex-wrap: wrap;
        }
        
        .btn-outline {
          background: white;
          border: 1px solid #007bff;
          color: #007bff;
        }
        
        /* Schedule Management Styles */
        .schedule-controls {
          display: flex;
          justify-content: space-between;
          align-items: center;
          margin-bottom: 20px;
        }
        
        .date-controls, .view-controls {
          display: flex;
          gap: 8px;
          align-items: center;
        }
        
        .current-date {
          font-weight: 500;
          margin: 0 12px;
        }
        
        .availability-grid {
          display: grid;
          grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
          gap: 16px;
          margin-bottom: 20px;
        }
        
        .day-settings {
          background: #f8f9fa;
          padding: 16px;
          border-radius: 8px;
          border: 1px solid #e5e5e5;
        }
        
        .time-slots, .break-settings {
          margin: 8px 0;
        }
        
        .time-slots input, .break-settings input {
          margin: 0 4px;
        }
        
        .appointment-type-settings {
          display: grid;
          grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
          gap: 12px;
        }
        
        .type-setting {
          display: flex;
          align-items: center;
          gap: 8px;
        }
        
        .status.attention {
          background: #fff3cd;
          color: #856404;
        }
      `}</style>
    </div>
  );
};

export default DoctorDashboard;