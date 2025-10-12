import React, { useState, useEffect, useContext } from 'react';
import { AuthContext } from '../../context/AuthContext';
import ApiService from '../../services/api';
import AppointmentBooking from '../../components/patient/AppointmentBooking';
import PermissionGuard from '../../components/common/PermissionGuard';
import '../../App.css';

const PatientDashboard = ({ user }) => {
  const { logout, hasPermission, refreshPermissions } = useContext(AuthContext);
  const [appointments, setAppointments] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [showBookingModal, setShowBookingModal] = useState(false);
  const [showRecordsModal, setShowRecordsModal] = useState(false);
  const [showReportsModal, setShowReportsModal] = useState(false);
  const [showPrescriptionsModal, setShowPrescriptionsModal] = useState(false);
  const [showHealthTrackingModal, setShowHealthTrackingModal] = useState(false);
  const [showDocumentsModal, setShowDocumentsModal] = useState(false);
  
  useEffect(() => {
    fetchUserAppointments();
  }, []);
  
  const fetchUserAppointments = async () => {
    try {
      const response = await ApiService.getUserAppointments(user.id);
      setAppointments(response.appointments || []);
    } catch (err) {
      console.log('Failed to fetch appointments, using mock data:', err.message);
      // Mock appointments for testing
      setAppointments([
        {
          id: 1,
          doctor: 'Dr. Smith',
          date: '2023-08-25',
          timeSlot: '10:00',
          status: 'confirmed'
        }
      ]);
    } finally {
      setLoading(false);
    }
  };
  
  const handleBookAppointment = () => {
    setShowBookingModal(true);
  };
  
  const handleViewRecords = () => {
    setShowRecordsModal(true);
  };
  
  const handleViewReports = () => {
    setShowReportsModal(true);
  };
  
  const handleViewPrescriptions = () => {
    setShowPrescriptionsModal(true);
  };
  
  const handleHealthTracking = () => {
    setShowHealthTrackingModal(true);
  };
  
  const handleViewDocuments = () => {
    setShowDocumentsModal(true);
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
      <h1>Patient Dashboard</h1>
      <div className="card">
        <h2>Welcome, {user.name}!</h2>
        <p>Email: {user.email}</p>
        <p>Role: {user.role}</p>
        <button className="btn btn-secondary" onClick={handleRefreshPermissions} style={{marginTop: '10px'}}>
          Refresh Permissions
        </button>
      </div>
      
      <PermissionGuard requiredPermissions={['appointments.read']}>
        <div className="card">
          <h2>Upcoming Appointments</h2>
          {loading ? (
            <p>Loading appointments...</p>
          ) : error ? (
            <p className="error-message">{error}</p>
          ) : appointments.length > 0 ? (
            <div>
              {appointments.map(appointment => (
                <div key={appointment.id} className="appointment-item">
                  <p><strong>Doctor:</strong> {appointment.doctor}</p>
                  <p><strong>Date:</strong> {appointment.date}</p>
                  <p><strong>Time:</strong> {appointment.timeSlot}</p>
                  <p><strong>Status:</strong> {appointment.status}</p>
                </div>
              ))}
            </div>
          ) : (
            <p>You have no upcoming appointments.</p>
          )}
          {hasPermission('appointments.create') && (
            <button className="btn" onClick={handleBookAppointment}>Book Appointment</button>
          )}
        </div>
      </PermissionGuard>
      
      <PermissionGuard requiredPermissions={['health_metrics.read']}>
        <div className="card">
          <h2>📊 Health Overview</h2>
          <div className="health-metrics">
            <div className="metric">
              <span className="metric-value">120/80</span>
              <span className="metric-label">Blood Pressure</span>
            </div>
            <div className="metric">
              <span className="metric-value">98.6°F</span>
              <span className="metric-label">Temperature</span>
            </div>
            <div className="metric">
              <span className="metric-value">72 bpm</span>
              <span className="metric-label">Heart Rate</span>
            </div>
          </div>
          {hasPermission('health_tracking.create') && (
            <button className="btn" onClick={handleHealthTracking}>Health Tracking</button>
          )}
        </div>
      </PermissionGuard>
      
      <PermissionGuard requiredPermissions={['medical_records.read']}>
        <div className="card">
          <h2>📊 Medical History</h2>
          <p>Complete medical history and treatment records.</p>
          <button className="btn" onClick={handleViewRecords}>View Medical Records</button>
        </div>
      </PermissionGuard>
      
      <PermissionGuard requiredPermissions={['prescriptions.read']}>
        <div className="card">
          <h2>📊 Prescriptions & Medications</h2>
          <div className="prescription-summary">
            <p><strong>Active Prescriptions:</strong> 2</p>
            <p><strong>Next Refill Due:</strong> Aug 30, 2023</p>
          </div>
          <button className="btn" onClick={handleViewPrescriptions}>Manage Prescriptions</button>
        </div>
      </PermissionGuard>
      
      <PermissionGuard requiredPermissions={['documents.read']}>
        <div className="card">
          <h2>📄 Documents & Reports</h2>
          <p>Lab results, imaging reports, and medical documents.</p>
          <div className="card-actions">
            <button className="btn" onClick={handleViewReports}>View Reports</button>
            <button className="btn btn-secondary" onClick={handleViewDocuments}>My Documents</button>
          </div>
        </div>
      </PermissionGuard>
      
      {/* Modals */}
      <PermissionGuard requiredPermissions={['appointments.create']}>
        <AppointmentBooking 
          isOpen={showBookingModal} 
          onClose={() => setShowBookingModal(false)} 
          user={user}
        />
      </PermissionGuard>
      
      <PermissionGuard requiredPermissions={['medical_records.read']}>
        {showRecordsModal && (
          <div className="modal-backdrop" onClick={() => setShowRecordsModal(false)}>
            <div className="modal-content" onClick={(e) => e.stopPropagation()}>
              <div className="modal-header">
                <h2>Medical Records</h2>
                <button className="modal-close" onClick={() => setShowRecordsModal(false)}>&times;</button>
              </div>
              <div className="modal-body">
                <div className="records-list">
                  <div className="record-item">
                    <h3>Blood Test Results</h3>
                    <p><strong>Date:</strong> August 15, 2023</p>
                    <p><strong>Doctor:</strong> Dr. Jane Smith</p>
                    <p><strong>Results:</strong> All values within normal range</p>
                  </div>
                  <div className="record-item">
                    <h3>X-Ray Report</h3>
                    <p><strong>Date:</strong> July 22, 2023</p>
                    <p><strong>Doctor:</strong> Dr. Robert Brown</p>
                    <p><strong>Results:</strong> No abnormalities detected</p>
                  </div>
                  <div className="record-item">
                    <h3>Annual Checkup</h3>
                    <p><strong>Date:</strong> January 10, 2023</p>
                    <p><strong>Doctor:</strong> Dr. Jane Smith</p>
                    <p><strong>Results:</strong> Overall health excellent</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        )}
      </PermissionGuard>
      
      <PermissionGuard requiredPermissions={['reports.read']}>
        {showReportsModal && (
          <div className="modal-backdrop" onClick={() => setShowReportsModal(false)}>
            <div className="modal-content modal-large" onClick={(e) => e.stopPropagation()}>
              <div className="modal-header">
                <h2>📊 Medical Reports & Lab Results</h2>
                <button className="modal-close" onClick={() => setShowReportsModal(false)}>&times;</button>
              </div>
              <div className="modal-body">
                <div className="reports-section">
                  <div className="report-filters">
                    <select>
                      <option>All Report Types</option>
                      <option>Lab Results</option>
                      <option>Imaging Reports</option>
                      <option>Test Results</option>
                      <option>Pathology</option>
                    </select>
                    <input type="date" placeholder="From Date" />
                    <input type="date" placeholder="To Date" />
                    <button className="btn btn-primary">Filter</button>
                  </div>
                  
                  <div className="reports-list">
                    <div className="report-card">
                      <div className="report-header">
                        <div className="report-icon">🩺</div>
                        <div className="report-info">
                          <h3>Complete Blood Count (CBC)</h3>
                          <p className="report-date">August 15, 2023</p>
                          <p className="report-doctor">Ordered by: Dr. Jane Smith</p>
                        </div>
                        <div className="report-status normal">Normal</div>
                      </div>
                      <div className="report-summary">
                        <div className="result-grid">
                          <div className="result-item">
                            <span className="result-name">WBC:</span>
                            <span className="result-value">7.2 K/μL</span>
                            <span className="result-range">(4.0-11.0)</span>
                          </div>
                          <div className="result-item">
                            <span className="result-name">RBC:</span>
                            <span className="result-value">4.8 M/μL</span>
                            <span className="result-range">(4.2-5.4)</span>
                          </div>
                          <div className="result-item">
                            <span className="result-name">Hemoglobin:</span>
                            <span className="result-value">14.2 g/dL</span>
                            <span className="result-range">(12.0-16.0)</span>
                          </div>
                        </div>
                      </div>
                      <div className="report-actions">
                        <button className="btn btn-sm btn-primary">View Full Report</button>
                        <button className="btn btn-sm btn-secondary">Download PDF</button>
                        <button className="btn btn-sm btn-info">Share with Doctor</button>
                      </div>
                    </div>
                    
                    <div className="report-card">
                      <div className="report-header">
                        <div className="report-icon">📷</div>
                        <div className="report-info">
                          <h3>Chest X-Ray</h3>
                          <p className="report-date">August 10, 2023</p>
                          <p className="report-doctor">Ordered by: Dr. Michael Johnson</p>
                        </div>
                        <div className="report-status normal">Normal</div>
                      </div>
                      <div className="report-summary">
                        <p><strong>Impression:</strong> Normal chest X-ray. No acute cardiopulmonary abnormalities.</p>
                        <p><strong>Technique:</strong> PA and lateral chest radiographs</p>
                      </div>
                      <div className="report-actions">
                        <button className="btn btn-sm btn-primary">View Images</button>
                        <button className="btn btn-sm btn-secondary">Download Report</button>
                        <button className="btn btn-sm btn-info">Compare Previous</button>
                      </div>
                    </div>
                    
                    <div className="report-card">
                      <div className="report-header">
                        <div className="report-icon">⚡</div>
                        <div className="report-info">
                          <h3>HbA1c (Diabetes Check)</h3>
                          <p className="report-date">August 5, 2023</p>
                          <p className="report-doctor">Ordered by: Dr. Jane Smith</p>
                        </div>
                        <div className="report-status attention">Attention</div>
                      </div>
                      <div className="report-summary">
                        <div className="result-grid">
                          <div className="result-item important">
                            <span className="result-name">HbA1c:</span>
                            <span className="result-value">7.2%</span>
                            <span className="result-range">(&lt;7.0%)</span>
                          </div>
                        </div>
                        <p className="result-note">Slightly elevated. Follow-up recommended.</p>
                      </div>
                      <div className="report-actions">
                        <button className="btn btn-sm btn-warning">Schedule Follow-up</button>
                        <button className="btn btn-sm btn-primary">View Full Report</button>
                        <button className="btn btn-sm btn-secondary">Download PDF</button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        )}
      </PermissionGuard>
      
      {showPrescriptionsModal && (
        <div className="modal-backdrop" onClick={() => setShowPrescriptionsModal(false)}>
          <div className="modal-content modal-large" onClick={(e) => e.stopPropagation()}>
            <div className="modal-header">
              <h2>📊 Prescriptions & Medications</h2>
              <button className="modal-close" onClick={() => setShowPrescriptionsModal(false)}>&times;</button>
            </div>
            <div className="modal-body">
              <div className="prescriptions-section">
                <div className="prescription-tabs">
                  <button className="tab-button active">Active Prescriptions</button>
                  <button className="tab-button">Prescription History</button>
                  <button className="tab-button">Allergies & Interactions</button>
                </div>
                
                <div className="prescriptions-list">
                  <div className="prescription-card active">
                    <div className="prescription-header">
                      <h3>Metformin 500mg</h3>
                      <span className="prescription-status active">Active</span>
                    </div>
                    <div className="prescription-details">
                      <p><strong>Prescribed by:</strong> Dr. Jane Smith</p>
                      <p><strong>Date Prescribed:</strong> July 15, 2023</p>
                      <p><strong>Dosage:</strong> 500mg twice daily with meals</p>
                      <p><strong>Quantity:</strong> 60 tablets</p>
                      <p><strong>Refills Remaining:</strong> 2 of 3</p>
                      <p><strong>Next Refill Due:</strong> August 30, 2023</p>
                    </div>
                    <div className="prescription-actions">
                      <button className="btn btn-sm btn-primary">Request Refill</button>
                      <button className="btn btn-sm btn-info">Set Reminder</button>
                      <button className="btn btn-sm btn-secondary">View Details</button>
                    </div>
                  </div>
                  
                  <div className="prescription-card active">
                    <div className="prescription-header">
                      <h3>Lisinopril 10mg</h3>
                      <span className="prescription-status active">Active</span>
                    </div>
                    <div className="prescription-details">
                      <p><strong>Prescribed by:</strong> Dr. Jane Smith</p>
                      <p><strong>Date Prescribed:</strong> June 20, 2023</p>
                      <p><strong>Dosage:</strong> 10mg once daily in the morning</p>
                      <p><strong>Quantity:</strong> 30 tablets</p>
                      <p><strong>Refills Remaining:</strong> 1 of 5</p>
                      <p><strong>Next Refill Due:</strong> September 5, 2023</p>
                    </div>
                    <div className="prescription-actions">
                      <button className="btn btn-sm btn-primary">Request Refill</button>
                      <button className="btn btn-sm btn-info">Set Reminder</button>
                      <button className="btn btn-sm btn-secondary">View Details</button>
                    </div>
                  </div>
                </div>
                
                <div className="medication-reminders">
                  <h3>Medication Reminders</h3>
                  <div className="reminder-settings">
                    <div className="reminder-item">
                      <span className="medication-name">Metformin 500mg</span>
                      <span className="reminder-time">8:00 AM, 6:00 PM</span>
                      <button className="btn btn-sm btn-secondary">Edit</button>
                    </div>
                    <div className="reminder-item">
                      <span className="medication-name">Lisinopril 10mg</span>
                      <span className="reminder-time">8:00 AM</span>
                      <button className="btn btn-sm btn-secondary">Edit</button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      )}
      
      {showHealthTrackingModal && (
        <div className="modal-backdrop" onClick={() => setShowHealthTrackingModal(false)}>
          <div className="modal-content modal-large" onClick={(e) => e.stopPropagation()}>
            <div className="modal-header">
              <h2>📊 Health Tracking & Monitoring</h2>
              <button className="modal-close" onClick={() => setShowHealthTrackingModal(false)}>&times;</button>
            </div>
            <div className="modal-body">
              <div className="health-tracking">
                <div className="tracking-tabs">
                  <button className="tab-button active">Vital Signs</button>
                  <button className="tab-button">Weight & BMI</button>
                  <button className="tab-button">Blood Sugar</button>
                  <button className="tab-button">Blood Pressure</button>
                </div>
                
                <div className="current-vitals">
                  <h3>Current Vitals (Last Recorded)</h3>
                  <div className="vitals-grid">
                    <div className="vital-card">
                      <div className="vital-icon">❤️</div>
                      <div className="vital-info">
                        <span className="vital-value">72 bpm</span>
                        <span className="vital-label">Heart Rate</span>
                        <span className="vital-date">Aug 20, 2023</span>
                      </div>
                    </div>
                    <div className="vital-card">
                      <div className="vital-icon">🌡️</div>
                      <div className="vital-info">
                        <span className="vital-value">98.6°F</span>
                        <span className="vital-label">Temperature</span>
                        <span className="vital-date">Aug 20, 2023</span>
                      </div>
                    </div>
                  </div>
                </div>
                
                <div className="add-measurement">
                  <h3>Record New Measurement</h3>
                  <div className="measurement-form">
                    <div className="form-row">
                      <select>
                        <option>Select Measurement Type</option>
                        <option>Blood Pressure</option>
                        <option>Weight</option>
                        <option>Blood Sugar</option>
                      </select>
                      <input type="number" placeholder="Value" />
                      <button className="btn btn-primary">Add Measurement</button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      )}
      
      {showDocumentsModal && (
        <div className="modal-backdrop" onClick={() => setShowDocumentsModal(false)}>
          <div className="modal-content modal-large" onClick={(e) => e.stopPropagation()}>
            <div className="modal-header">
              <h2>📄 My Documents & Records</h2>
              <button className="modal-close" onClick={() => setShowDocumentsModal(false)}>&times;</button>
            </div>
            <div className="modal-body">
              <div className="documents-section">
                <div className="upload-section">
                  <h3>Upload New Document</h3>
                  <div className="upload-form">
                    <input type="file" accept=".pdf,.jpg,.png,.doc,.docx" />
                    <select>
                      <option>Select Document Type</option>
                      <option>Lab Results</option>
                      <option>Insurance Card</option>
                      <option>Prescription</option>
                    </select>
                    <button className="btn btn-primary">Upload Document</button>
                  </div>
                </div>
                
                <div className="documents-list">
                  <h3>My Document Library</h3>
                  <div className="document-grid">
                    <div className="document-card">
                      <div className="document-icon">📷</div>
                      <div className="document-info">
                        <h4>Insurance Card - Front</h4>
                        <p className="document-type">Insurance Document</p>
                        <p className="document-date">Uploaded: Aug 10, 2023</p>
                      </div>
                      <div className="document-actions">
                        <button className="btn btn-sm btn-primary">View</button>
                        <button className="btn btn-sm btn-secondary">Download</button>
                      </div>
                    </div>
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
        
        .record-item, .report-item {
          background: #f8f9fa;
          padding: 16px;
          border-radius: 8px;
          margin-bottom: 16px;
        }
        
        .record-item h3, .report-item h3 {
          margin-top: 0;
          color: #333;
        }
        
        .btn-sm {
          padding: 4px 8px;
          font-size: 12px;
        }
        
        .card-actions {
          display: flex;
          gap: 12px;
          margin-top: 16px;
        }
        
        .health-metrics {
          display: grid;
          grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
          gap: 16px;
          margin: 16px 0;
        }
        
        .metric {
          text-align: center;
          background: #f8f9fa;
          padding: 16px;
          border-radius: 8px;
          border: 1px solid #e5e5e5;
        }
        
        .metric-value {
          display: block;
          font-size: 1.2rem;
          font-weight: bold;
          color: #333;
        }
        
        .metric-label {
          display: block;
          font-size: 0.9rem;
          color: #666;
          margin-top: 4px;
        }
        
        .prescription-summary {
          background: #f8f9fa;
          padding: 12px;
          border-radius: 6px;
          margin: 12px 0;
        }
        
        /* Report Styles */
        .report-filters {
          display: flex;
          gap: 12px;
          margin-bottom: 20px;
          align-items: center;
        }
        
        .report-card {
          border: 1px solid #e5e5e5;
          border-radius: 8px;
          padding: 20px;
          margin-bottom: 20px;
          background: #f8f9fa;
        }
        
        .report-header {
          display: flex;
          align-items: center;
          margin-bottom: 16px;
          gap: 16px;
        }
        
        .report-icon {
          font-size: 2rem;
        }
        
        .report-info h3 {
          margin: 0 0 4px 0;
          color: #333;
        }
        
        .report-date, .report-doctor {
          color: #666;
          font-size: 0.9rem;
          margin: 2px 0;
        }
        
        .report-status {
          padding: 4px 12px;
          border-radius: 20px;
          font-size: 0.8rem;
          font-weight: 500;
          margin-left: auto;
        }
        
        .report-status.normal {
          background: #d4edda;
          color: #155724;
        }
        
        .report-status.attention {
          background: #fff3cd;
          color: #856404;
        }
        
        .result-grid {
          display: grid;
          grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
          gap: 12px;
          margin: 12px 0;
        }
        
        .result-item {
          display: grid;
          grid-template-columns: 1fr 1fr 1fr;
          gap: 8px;
          padding: 8px;
          background: white;
          border-radius: 4px;
          border: 1px solid #e5e5e5;
        }
        
        .result-name {
          font-weight: 500;
        }
        
        .result-value {
          font-weight: bold;
          color: #333;
        }
        
        .result-range {
          color: #666;
          font-size: 0.9rem;
        }
        
        .result-note {
          color: #856404;
          font-style: italic;
          margin-top: 8px;
        }
        
        /* Health Tracking Styles */
        .tracking-tabs {
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
        
        .vitals-grid {
          display: grid;
          grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
          gap: 16px;
          margin: 16px 0;
        }
        
        .vital-card {
          display: flex;
          align-items: center;
          background: white;
          padding: 16px;
          border-radius: 8px;
          border: 1px solid #e5e5e5;
          gap: 12px;
        }
        
        .vital-icon {
          font-size: 1.5rem;
        }
        
        .vital-value {
          display: block;
          font-size: 1.1rem;
          font-weight: bold;
          color: #333;
        }
        
        .vital-label {
          display: block;
          color: #666;
        }
        
        .vital-date {
          display: block;
          color: #999;
          font-size: 0.8rem;
        }
        
        .measurement-form .form-row {
          display: flex;
          gap: 12px;
          align-items: center;
        }
        
        /* Document Styles */
        .upload-form {
          display: flex;
          gap: 12px;
          align-items: center;
          margin-bottom: 20px;
        }
        
        .document-grid {
          display: grid;
          grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
          gap: 16px;
        }
        
        .document-card {
          display: flex;
          align-items: center;
          background: white;
          padding: 16px;
          border-radius: 8px;
          border: 1px solid #e5e5e5;
          gap: 16px;
        }
        
        .document-icon {
          font-size: 2rem;
        }
        
        .document-info {
          flex: 1;
        }
        
        .document-info h4 {
          margin: 0 0 4px 0;
          color: #333;
        }
        
        .document-type, .document-date {
          color: #666;
          font-size: 0.9rem;
          margin: 2px 0;
        }
        
        .document-actions {
          display: flex;
          gap: 8px;
        }
      `}
      </style>
    </div>
  );
};

export default PatientDashboard;