import React, { useState, useEffect, useContext } from 'react';
import { AuthContext } from '../../context/AuthContext';
import ApiService from '../../services/api';
import PermissionGuard from '../../components/common/PermissionGuard';
import '../../App.css';

const MedicalCoordinatorDashboard = ({ user }) => {
  const { logout, hasPermission, refreshPermissions } = useContext(AuthContext);
  const [showPatientAssignment, setShowPatientAssignment] = useState(false);
  const [showPatientHistory, setShowPatientHistory] = useState(false);
  const [patients, setPatients] = useState([]);
  const [doctors, setDoctors] = useState([]);
  const [selectedPatient, setSelectedPatient] = useState('');
  const [selectedDoctor, setSelectedDoctor] = useState('');
  const [assignmentNotes, setAssignmentNotes] = useState('');
  const [patientHistory, setPatientHistory] = useState(null);
  const [assignmentHistory, setAssignmentHistory] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  const handleRefreshPermissions = async () => {
    try {
      await refreshPermissions();
      alert('Permissions refreshed successfully!');
    } catch (error) {
      console.error('Failed to refresh permissions:', error);
      alert('Failed to refresh permissions: ' + error.message);
    }
  };

  // Fetch patients and doctors when patient assignment modal is opened
  useEffect(() => {
    if (showPatientAssignment) {
      fetchPatientsAndDoctors();
    }
  }, [showPatientAssignment]);

  const fetchPatientsAndDoctors = async () => {
    setLoading(true);
    setError('');
    
    try {
      // Fetch patients
      const patientsResponse = await ApiService.getPatientsForAssignment();
      if (patientsResponse.data && patientsResponse.data.patients) {
        setPatients(patientsResponse.data.patients);
      }
      
      // Fetch doctors
      const doctorsResponse = await ApiService.getDoctorsForAssignment();
      if (doctorsResponse.data && doctorsResponse.data.doctors) {
        setDoctors(doctorsResponse.data.doctors);
      }
    } catch (error) {
      console.error('Failed to fetch data:', error);
      setError('Failed to load patients and doctors: ' + error.message);
    } finally {
      setLoading(false);
    }
  };

  const handleAssignPatient = async (e) => {
    e.preventDefault();
    
    if (!selectedPatient || !selectedDoctor) {
      alert('Please select both a patient and a doctor');
      return;
    }
    
    setLoading(true);
    setError('');
    
    try {
      const assignmentData = {
        patient_id: parseInt(selectedPatient),
        doctor_id: parseInt(selectedDoctor),
        notes: assignmentNotes
      };
      
      const response = await ApiService.assignPatientToDoctor(assignmentData);
      
      if (response.status === 201) {
        alert('Patient assigned successfully!');
        // Reset form
        setSelectedPatient('');
        setSelectedDoctor('');
        setAssignmentNotes('');
        // Refresh patient list
        fetchPatientsAndDoctors();
      } else {
        throw new Error(response.data?.message || 'Failed to assign patient');
      }
    } catch (error) {
      console.error('Failed to assign patient:', error);
      setError('Failed to assign patient: ' + error.message);
    } finally {
      setLoading(false);
    }
  };

  const handleViewPatientHistory = async (patientId) => {
    setLoading(true);
    setError('');
    
    try {
      // Fetch limited patient history
      const historyResponse = await ApiService.getPatientLimitedHistory(patientId);
      if (historyResponse.data) {
        setPatientHistory(historyResponse.data);
      }
      
      // Fetch assignment history
      const assignmentResponse = await ApiService.getPatientAssignmentHistory(patientId);
      if (assignmentResponse.data && assignmentResponse.data.history) {
        setAssignmentHistory(assignmentResponse.data.history);
      }
      
      setShowPatientHistory(true);
    } catch (error) {
      console.error('Failed to fetch patient history:', error);
      setError('Failed to fetch patient history: ' + error.message);
    } finally {
      setLoading(false);
    }
  };

  const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString();
  };

  return (
    <div className="dashboard">
      <h1>Medical Coordinator Dashboard</h1>
      <div className="card">
        <h2>Welcome, {user.name}!</h2>
        <p>Email: {user.email}</p>
        <p>Role: {user.role}</p>
        <p className="role-description">
          Medical Coordinator - Assign patients to clinicians and access limited patient histories
        </p>
        <button className="btn btn-secondary" onClick={handleRefreshPermissions} style={{marginTop: '10px'}}>
          Refresh Permissions
        </button>
      </div>
      
      <PermissionGuard requiredPermissions={['patients.assign_clinician']}>
        <div className="card">
          <h2>👥 Patient Assignment</h2>
          <p>Assign patients to appropriate clinicians based on specialization.</p>
          <button className="btn" onClick={() => setShowPatientAssignment(true)}>Assign Patients</button>
        </div>
      </PermissionGuard>
      
      <PermissionGuard requiredPermissions={['patients.limited_history']}>
        <div className="card">
          <h2>📋 Limited Patient History</h2>
          <p>Access essential patient information for coordination purposes.</p>
          <button className="btn" onClick={() => handleViewPatientHistory(1)}>View Patient Records</button>
        </div>
      </PermissionGuard>
      
      {/* Patient Assignment Modal */}
      <PermissionGuard requiredPermissions={['patients.assign_clinician']}>
        {showPatientAssignment && (
          <div className="modal-backdrop" onClick={() => setShowPatientAssignment(false)}>
            <div className="modal-content modal-large" onClick={(e) => e.stopPropagation()}>
              <div className="modal-header">
                <h2>👥 Assign Patients to Clinicians</h2>
                <button className="modal-close" onClick={() => setShowPatientAssignment(false)}>&times;</button>
              </div>
              <div className="modal-body">
                {error && <div className="error-message">{error}</div>}
                
                <form onSubmit={handleAssignPatient}>
                  <div className="form-group">
                    <label htmlFor="patientSelect">Select Patient:</label>
                    <select
                      id="patientSelect"
                      value={selectedPatient}
                      onChange={(e) => setSelectedPatient(e.target.value)}
                      required
                    >
                      <option value="">Choose a patient...</option>
                      {patients.map(patient => (
                        <option key={patient.id} value={patient.id}>
                          {patient.name} {patient.assigned_doctor && `(Assigned to ${patient.doctor_name})`}
                        </option>
                      ))}
                    </select>
                  </div>
                  
                  <div className="form-group">
                    <label htmlFor="doctorSelect">Select Doctor:</label>
                    <select
                      id="doctorSelect"
                      value={selectedDoctor}
                      onChange={(e) => setSelectedDoctor(e.target.value)}
                      required
                    >
                      <option value="">Choose a doctor...</option>
                      {doctors.map(doctor => (
                        <option key={doctor.id} value={doctor.id}>
                          {doctor.name}
                        </option>
                      ))}
                    </select>
                  </div>
                  
                  <div className="form-group">
                    <label htmlFor="assignmentNotes">Notes:</label>
                    <textarea
                      id="assignmentNotes"
                      value={assignmentNotes}
                      onChange={(e) => setAssignmentNotes(e.target.value)}
                      placeholder="Add any notes about this assignment..."
                      rows="3"
                    />
                  </div>
                  
                  <div className="form-actions">
                    <button 
                      type="button" 
                      className="btn btn-secondary" 
                      onClick={() => setShowPatientAssignment(false)}
                      disabled={loading}
                    >
                      Cancel
                    </button>
                    <button 
                      type="submit" 
                      className="btn btn-primary" 
                      disabled={loading}
                    >
                      {loading ? 'Assigning...' : 'Assign Patient'}
                    </button>
                  </div>
                </form>
                
                {loading && <div className="loading">Loading...</div>}
              </div>
            </div>
          </div>
        )}
      </PermissionGuard>
      
      {/* Patient History Modal */}
      <PermissionGuard requiredPermissions={['patients.limited_history']}>
        {showPatientHistory && (
          <div className="modal-backdrop" onClick={() => setShowPatientHistory(false)}>
            <div className="modal-content modal-large" onClick={(e) => e.stopPropagation()}>
              <div className="modal-header">
                <h2>📋 Limited Patient History</h2>
                <button className="modal-close" onClick={() => setShowPatientHistory(false)}>&times;</button>
              </div>
              <div className="modal-body">
                {error && <div className="error-message">{error}</div>}
                
                {patientHistory ? (
                  <div className="patient-history">
                    <div className="patient-info-section">
                      <h3>Patient Information</h3>
                      <div className="info-grid">
                        <div className="info-item">
                          <label>Name:</label>
                          <span>{patientHistory.patient?.name}</span>
                        </div>
                        <div className="info-item">
                          <label>Email:</label>
                          <span>{patientHistory.patient?.email}</span>
                        </div>
                        <div className="info-item">
                          <label>Assigned Doctor:</label>
                          <span>{patientHistory.patient?.assigned_doctor_name || 'Not assigned'}</span>
                        </div>
                        <div className="info-item">
                          <label>Member Since:</label>
                          <span>{formatDate(patientHistory.patient?.created_at)}</span>
                        </div>
                      </div>
                    </div>
                    
                    <div className="history-section">
                      <h3>Recent Appointments</h3>
                      {patientHistory.recent_appointments && patientHistory.recent_appointments.length > 0 ? (
                        <table className="history-table">
                          <thead>
                            <tr>
                              <th>Date</th>
                              <th>Time</th>
                              <th>Doctor</th>
                              <th>Status</th>
                            </tr>
                          </thead>
                          <tbody>
                            {patientHistory.recent_appointments.map(appointment => (
                              <tr key={appointment.id}>
                                <td>{formatDate(appointment.date)}</td>
                                <td>{appointment.time_slot}</td>
                                <td>{appointment.doctor_name}</td>
                                <td>
                                  <span className={`status-badge status-${appointment.status}`}>
                                    {appointment.status}
                                  </span>
                                </td>
                              </tr>
                            ))}
                          </tbody>
                        </table>
                      ) : (
                        <p>No recent appointments found.</p>
                      )}
                    </div>
                    
                    <div className="history-section">
                      <h3>Recent Prescriptions</h3>
                      {patientHistory.recent_prescriptions && patientHistory.recent_prescriptions.length > 0 ? (
                        <table className="history-table">
                          <thead>
                            <tr>
                              <th>Medication</th>
                              <th>Dosage</th>
                              <th>Frequency</th>
                              <th>Status</th>
                              <th>Prescribed Date</th>
                            </tr>
                          </thead>
                          <tbody>
                            {patientHistory.recent_prescriptions.map(prescription => (
                              <tr key={prescription.id}>
                                <td>{prescription.medication_name}</td>
                                <td>{prescription.dosage}</td>
                                <td>{prescription.frequency}</td>
                                <td>
                                  <span className={`status-badge status-${prescription.status}`}>
                                    {prescription.status}
                                  </span>
                                </td>
                                <td>{formatDate(prescription.prescribed_date)}</td>
                              </tr>
                            ))}
                          </tbody>
                        </table>
                      ) : (
                        <p>No recent prescriptions found.</p>
                      )}
                    </div>
                    
                    <div className="history-section">
                      <h3>Assignment History</h3>
                      {assignmentHistory && assignmentHistory.length > 0 ? (
                        <table className="history-table">
                          <thead>
                            <tr>
                              <th>Doctor</th>
                              <th>Assigned By</th>
                              <th>Assignment Date</th>
                              <th>Status</th>
                            </tr>
                          </thead>
                          <tbody>
                            {assignmentHistory.map(assignment => (
                              <tr key={assignment.id}>
                                <td>{assignment.doctor_name}</td>
                                <td>{assignment.assigned_by_name}</td>
                                <td>{formatDate(assignment.assignment_date)}</td>
                                <td>
                                  <span className={`status-badge ${assignment.is_active ? 'status-active' : 'status-inactive'}`}>
                                    {assignment.is_active ? 'Active' : 'Inactive'}
                                  </span>
                                </td>
                              </tr>
                            ))}
                          </tbody>
                        </table>
                      ) : (
                        <p>No assignment history found.</p>
                      )}
                    </div>
                  </div>
                ) : (
                  <div className="loading">Loading patient history...</div>
                )}
                
                <div className="form-actions">
                  <button 
                    className="btn btn-secondary" 
                    onClick={() => setShowPatientHistory(false)}
                  >
                    Close
                  </button>
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
        
        .form-actions {
          display: flex;
          gap: 12px;
          justify-content: flex-end;
          margin-top: 20px;
        }
        
        .btn {
          padding: 8px 16px;
          border: none;
          border-radius: 4px;
          cursor: pointer;
          font-size: 14px;
          font-weight: 500;
        }
        
        .btn-primary {
          background: #007bff;
          color: white;
        }
        
        .btn-secondary {
          background: #6c757d;
          color: white;
        }
        
        .role-description {
          font-style: italic;
          color: #666;
          margin-top: 8px;
        }
        
        .form-group {
          margin-bottom: 20px;
        }
        
        .form-group label {
          display: block;
          margin-bottom: 8px;
          font-weight: 500;
        }
        
        .form-group select,
        .form-group textarea {
          width: 100%;
          padding: 8px 12px;
          border: 1px solid #ddd;
          border-radius: 4px;
          font-size: 14px;
        }
        
        .form-group textarea {
          resize: vertical;
          min-height: 80px;
        }
        
        .error-message {
          color: #dc3545;
          background-color: #f8d7da;
          border: 1px solid #f5c6cb;
          padding: 10px;
          border-radius: 4px;
          margin-bottom: 20px;
        }
        
        .loading {
          text-align: center;
          padding: 20px;
          color: #666;
        }
        
        .patient-history {
          margin-bottom: 20px;
        }
        
        .info-grid {
          display: grid;
          grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
          gap: 16px;
          margin-bottom: 20px;
        }
        
        .info-item {
          display: flex;
          flex-direction: column;
        }
        
        .info-item label {
          font-weight: 500;
          margin-bottom: 4px;
          color: #555;
        }
        
        .info-item span {
          padding: 8px;
          background-color: #f8f9fa;
          border-radius: 4px;
        }
        
        .history-section {
          margin-bottom: 30px;
        }
        
        .history-section h3 {
          margin-bottom: 15px;
          padding-bottom: 8px;
          border-bottom: 1px solid #eee;
        }
        
        .history-table {
          width: 100%;
          border-collapse: collapse;
        }
        
        .history-table th,
        .history-table td {
          padding: 12px;
          text-align: left;
          border-bottom: 1px solid #eee;
        }
        
        .history-table th {
          background-color: #f8f9fa;
          font-weight: 500;
        }
        
        .status-badge {
          padding: 4px 8px;
          border-radius: 12px;
          font-size: 12px;
          font-weight: 500;
        }
        
        .status-badge.status-active {
          background-color: #d4edda;
          color: #155724;
        }
        
        .status-badge.status-inactive {
          background-color: #f8d7da;
          color: #721c24;
        }
        
        .status-badge.status-confirmed {
          background-color: #d4edda;
          color: #155724;
        }
        
        .status-badge.status-pending {
          background-color: #fff3cd;
          color: #856404;
        }
        
        .status-badge.status-active {
          background-color: #d4edda;
          color: #155724;
        }
      `}</style>
    </div>
  );
};

export default MedicalCoordinatorDashboard;