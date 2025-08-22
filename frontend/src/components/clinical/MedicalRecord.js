import React, { useState, useEffect } from 'react';
import './Clinical.css';

const MedicalRecord = ({ record }) => {
  const [expanded, setExpanded] = useState(false);

  return (
    <div className="medical-record">
      <div className="record-header" onClick={() => setExpanded(!expanded)}>
        <div className="record-date">{record.date}</div>
        <div className="record-doctor">Dr. {record.doctor}</div>
        <div className="record-expand">
          {expanded ? '▲' : '▼'}
        </div>
      </div>
      
      {expanded && (
        <div className="record-details">
          <div className="detail-section">
            <h4>Diagnosis</h4>
            <p>{record.diagnosis}</p>
          </div>
          
          <div className="detail-section">
            <h4>Prescription</h4>
            <p>{record.prescription}</p>
          </div>
          
          {record.notes && (
            <div className="detail-section">
              <h4>Notes</h4>
              <p>{record.notes}</p>
            </div>
          )}
        </div>
      )}
    </div>
  );
};

const MedicalRecordsList = () => {
  const [records, setRecords] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    fetchMedicalRecords();
  }, []);

  const fetchMedicalRecords = async () => {
    setLoading(true);
    setError('');
    
    try {
      // In a real app, this would be an API call
      // For now, we'll simulate with mock data
      const mockRecords = [
        {
          id: 1,
          date: '2023-08-15',
          doctor: 'Smith',
          diagnosis: 'Common cold with mild fever',
          prescription: 'Rest, fluids, and over-the-counter cold medication',
          notes: 'Patient advised to return if symptoms worsen'
        },
        {
          id: 2,
          date: '2023-07-20',
          doctor: 'Johnson',
          diagnosis: 'Seasonal allergies',
          prescription: 'Antihistamine daily for 2 weeks',
          notes: 'Patient reports improvement with current treatment'
        },
        {
          id: 3,
          date: '2023-06-10',
          doctor: 'Williams',
          diagnosis: 'Routine checkup - all vitals normal',
          prescription: 'Continue current exercise and diet regimen',
          notes: 'Patient cleared for annual physical activities'
        }
      ];
      
      setRecords(mockRecords);
    } catch (err) {
      setError('Failed to fetch medical records');
    } finally {
      setLoading(false);
    }
  };

  if (loading) {
    return <div className="medical-records">Loading medical records...</div>;
  }

  if (error) {
    return <div className="medical-records error-message">{error}</div>;
  }

  return (
    <div className="medical-records">
      <h2>Medical Records</h2>
      
      {records.length === 0 ? (
        <p>No medical records found.</p>
      ) : (
        <div className="records-list">
          {records.map(record => (
            <MedicalRecord key={record.id} record={record} />
          ))}
        </div>
      )}
    </div>
  );
};

export default MedicalRecordsList;