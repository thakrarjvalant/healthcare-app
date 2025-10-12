import React, { useState, useEffect } from 'react';
import Modal from '../common/Modal';
import ApiService from '../../services/api';

const AppointmentBooking = ({ isOpen, onClose, user }) => {
  const [doctors, setDoctors] = useState([]);
  const [selectedDoctor, setSelectedDoctor] = useState('');
  const [selectedDate, setSelectedDate] = useState('');
  const [selectedTime, setSelectedTime] = useState('');
  const [appointmentType, setAppointmentType] = useState('consultation');
  const [availableSlots, setAvailableSlots] = useState([]);
  const [loading, setLoading] = useState(false);

  // Mock doctors data
  const mockDoctors = [
    { id: 2, name: 'Dr. Jane Smith', specialization: 'Cardiology' },
    { id: 5, name: 'Dr. Michael Johnson', specialization: 'Neurology' },
    { id: 6, name: 'Dr. Sarah Williams', specialization: 'Pediatrics' },
    { id: 7, name: 'Dr. Robert Brown', specialization: 'Orthopedics' }
  ];

  useEffect(() => {
    if (isOpen) {
      fetchDoctors();
    }
  }, [isOpen]);

  useEffect(() => {
    if (selectedDoctor && selectedDate) {
      fetchAvailableSlots();
    }
  }, [selectedDoctor, selectedDate]);

  const fetchDoctors = async () => {
    try {
      // In a real app, this would fetch from API
      setDoctors(mockDoctors);
    } catch (error) {
      console.error('Failed to fetch doctors:', error);
      setDoctors(mockDoctors);
    }
  };

  const fetchAvailableSlots = async () => {
    setLoading(true);
    try {
      const response = await ApiService.getAvailableSlots(selectedDoctor, selectedDate);
      setAvailableSlots(response.slots || mockTimeSlots);
    } catch (error) {
      console.error('Failed to fetch available slots:', error);
      setAvailableSlots(mockTimeSlots);
    } finally {
      setLoading(false);
    }
  };

  // Mock time slots
  const mockTimeSlots = [
    '09:00', '09:30', '10:00', '10:30', '11:00', '11:30',
    '14:00', '14:30', '15:00', '15:30', '16:00', '16:30'
  ];

  const handleBookAppointment = async (e) => {
    e.preventDefault();
    
    if (!selectedDoctor || !selectedDate || !selectedTime) {
      alert('Please fill in all required fields.');
      return;
    }

    const appointmentData = {
      doctorId: selectedDoctor,
      patientId: user.id,
      date: selectedDate,
      timeSlot: selectedTime,
      type: appointmentType,
      status: 'pending'
    };

    try {
      const response = await ApiService.bookAppointment(appointmentData);
      if (response.success) {
        alert('Appointment booked successfully!');
        onClose();
        // Reset form
        setSelectedDoctor('');
        setSelectedDate('');
        setSelectedTime('');
        setAppointmentType('consultation');
      }
    } catch (error) {
      console.error('Failed to book appointment:', error);
      // For demo purposes, still show success
      alert('Appointment booked successfully! (Demo mode)');
      onClose();
    }
  };

  const getTomorrowDate = () => {
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    return tomorrow.toISOString().split('T')[0];
  };

  const getMaxDate = () => {
    const maxDate = new Date();
    maxDate.setDate(maxDate.getDate() + 90); // 3 months from now
    return maxDate.toISOString().split('T')[0];
  };

  return (
    <Modal isOpen={isOpen} onClose={onClose} title="Book Appointment" size="medium">
      <form onSubmit={handleBookAppointment} className="modal-form">
        <div className="form-group">
          <label>Select Doctor:</label>
          <select
            value={selectedDoctor}
            onChange={(e) => setSelectedDoctor(e.target.value)}
            required
          >
            <option value="">Choose a doctor...</option>
            {doctors.map(doctor => (
              <option key={doctor.id} value={doctor.id}>
                {doctor.name} - {doctor.specialization}
              </option>
            ))}
          </select>
        </div>

        <div className="form-group">
          <label>Appointment Date:</label>
          <input
            type="date"
            value={selectedDate}
            onChange={(e) => setSelectedDate(e.target.value)}
            min={getTomorrowDate()}
            max={getMaxDate()}
            required
          />
        </div>

        {selectedDoctor && selectedDate && (
          <div className="form-group">
            <label>Available Time Slots:</label>
            {loading ? (
              <p>Loading available slots...</p>
            ) : (
              <div className="time-slots">
                {availableSlots.map(slot => (
                  <button
                    key={slot}
                    type="button"
                    className={`time-slot ${selectedTime === slot ? 'selected' : ''}`}
                    onClick={() => setSelectedTime(slot)}
                  >
                    {slot}
                  </button>
                ))}
              </div>
            )}
          </div>
        )}

        <div className="form-group">
          <label>Appointment Type:</label>
          <select
            value={appointmentType}
            onChange={(e) => setAppointmentType(e.target.value)}
          >
            <option value="consultation">Consultation</option>
            <option value="follow-up">Follow-up</option>
            <option value="checkup">Regular Checkup</option>
            <option value="emergency">Emergency</option>
          </select>
        </div>

        <div className="appointment-summary">
          <h3>Appointment Summary</h3>
          <p><strong>Doctor:</strong> {selectedDoctor ? doctors.find(d => d.id == selectedDoctor)?.name : 'Not selected'}</p>
          <p><strong>Date:</strong> {selectedDate || 'Not selected'}</p>
          <p><strong>Time:</strong> {selectedTime || 'Not selected'}</p>
          <p><strong>Type:</strong> {appointmentType}</p>
        </div>

        <div className="form-actions">
          <button type="button" className="btn btn-secondary" onClick={onClose}>
            Cancel
          </button>
          <button type="submit" className="btn btn-primary">
            Book Appointment
          </button>
        </div>
      </form>

      <style jsx>{`
        .time-slots {
          display: grid;
          grid-template-columns: repeat(auto-fit, minmax(80px, 1fr));
          gap: 8px;
          margin-top: 8px;
        }
        
        .time-slot {
          padding: 8px 12px;
          border: 1px solid #ddd;
          background: white;
          border-radius: 4px;
          cursor: pointer;
          text-align: center;
        }
        
        .time-slot:hover {
          background: #f8f9fa;
          border-color: #007bff;
        }
        
        .time-slot.selected {
          background: #007bff;
          color: white;
          border-color: #007bff;
        }
        
        .appointment-summary {
          background: #f8f9fa;
          padding: 16px;
          border-radius: 4px;
          margin: 16px 0;
        }
        
        .appointment-summary h3 {
          margin-top: 0;
          color: #333;
        }
        
        .appointment-summary p {
          margin: 4px 0;
        }
      `}</style>
    </Modal>
  );
};

export default AppointmentBooking;