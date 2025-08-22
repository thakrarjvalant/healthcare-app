import React, { useState } from 'react';
import './Appointment.css';

const BookingForm = ({ onBook }) => {
  const [formData, setFormData] = useState({
    doctorId: '',
    date: '',
    timeSlot: ''
  });
  const [availableSlots, setAvailableSlots] = useState([]);
  const [doctors, setDoctors] = useState([
    { id: 1, name: 'Dr. Smith' },
    { id: 2, name: 'Dr. Johnson' },
    { id: 3, name: 'Dr. Williams' }
  ]);
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);

  const handleDoctorChange = async (e) => {
    const doctorId = e.target.value;
    setFormData({ ...formData, doctorId });
    
    if (formData.date) {
      await fetchAvailableSlots(doctorId, formData.date);
    }
  };

  const handleDateChange = async (e) => {
    const date = e.target.value;
    setFormData({ ...formData, date });
    
    if (formData.doctorId) {
      await fetchAvailableSlots(formData.doctorId, date);
    }
  };

  const fetchAvailableSlots = async (doctorId, date) => {
    setLoading(true);
    setError('');
    
    try {
      // In a real app, this would be an API call
      // For now, we'll simulate with mock data
      const mockSlots = [
        '09:00',
        '09:30',
        '10:00',
        '10:30',
        '11:00',
        '11:30',
        '14:00',
        '14:30',
        '15:00',
        '15:30'
      ];
      
      // Filter out some slots to simulate booked appointments
      const available = mockSlots.filter((slot, index) => index % 3 !== 0);
      
      setAvailableSlots(available);
    } catch (err) {
      setError('Failed to fetch available slots');
    } finally {
      setLoading(false);
    }
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    
    if (!formData.doctorId || !formData.date || !formData.timeSlot) {
      setError('Please fill in all fields');
      return;
    }
    
    onBook(formData);
  };

  return (
    <div className="booking-form">
      <h2>Book Appointment</h2>
      {error && <div className="error-message">{error}</div>}
      
      <form onSubmit={handleSubmit}>
        <div className="form-group">
          <label htmlFor="doctor">Select Doctor:</label>
          <select
            id="doctor"
            value={formData.doctorId}
            onChange={handleDoctorChange}
            required
          >
            <option value="">Select a doctor</option>
            {doctors.map(doctor => (
              <option key={doctor.id} value={doctor.id}>
                {doctor.name}
              </option>
            ))}
          </select>
        </div>
        
        <div className="form-group">
          <label htmlFor="date">Select Date:</label>
          <input
            type="date"
            id="date"
            value={formData.date}
            onChange={handleDateChange}
            min={new Date().toISOString().split('T')[0]}
            required
          />
        </div>
        
        <div className="form-group">
          <label htmlFor="timeSlot">Select Time Slot:</label>
          <select
            id="timeSlot"
            value={formData.timeSlot}
            onChange={(e) => setFormData({ ...formData, timeSlot: e.target.value })}
            disabled={!formData.doctorId || !formData.date || loading}
            required
          >
            <option value="">Select a time slot</option>
            {availableSlots.map((slot, index) => (
              <option key={index} value={slot}>
                {slot}
              </option>
            ))}
          </select>
          {loading && <span className="loading">Loading slots...</span>}
        </div>
        
        <div className="form-group">
          <button type="submit" className="btn btn-primary">
            Book Appointment
          </button>
        </div>
      </form>
    </div>
  );
};

export default BookingForm;