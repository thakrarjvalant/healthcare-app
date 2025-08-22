# Appointment Service Developer Guide

This guide provides information for developers working with the Appointment Service of the Healthcare Management System.

## Overview

The Appointment Service handles appointment booking, scheduling, and management. It provides APIs for creating, updating, and retrieving appointments, as well as managing availability and time slots.

## Project Structure

```
backend/
└── appointment-service/
    ├── controllers/
    │   └── AppointmentController.php
    ├── models/
    │   ├── Appointment.php
    │   └── Availability.php
    ├── AppointmentService.php
    ├── api.php
    └── config/
        └── database.php
```

## Key Components

### Appointment Model

The `Appointment` model represents an appointment in the system.

#### Properties
- `id`: Unique identifier for the appointment
- `patient_id`: ID of the patient
- `doctor_id`: ID of the doctor
- `date`: Date of the appointment (YYYY-MM-DD)
- `time_slot`: Time slot for the appointment (HH:MM)
- `status`: Status of the appointment (pending, confirmed, cancelled, rescheduled, completed)
- `created_at`: Timestamp when the appointment was created
- `updated_at`: Timestamp when the appointment was last updated

#### Methods
- `__construct($data = [])`: Constructor to initialize the appointment with data
- Getters and setters for all properties
- `toArray()`: Convert the appointment object to an array

### Availability Model

The `Availability` model represents pregenerated time slots for doctors.

#### Properties
- `id`: Unique identifier for the availability record
- `doctor_id`: ID of the doctor
- `date`: Date of availability (YYYY-MM-DD)
- `time_slot`: Time slot (HH:MM)
- `created_at`: Timestamp when the availability was created
- `updated_at`: Timestamp when the availability was last updated

#### Methods
- `__construct($data = [])`: Constructor to initialize the availability with data
- Getters and setters for all properties
- `toArray()`: Convert the availability object to an array

### AppointmentService Class

The `AppointmentService` class contains the business logic for appointment management.

#### Methods
- `bookAppointment($appointmentData)`: Book a new appointment
- `getAvailableSlots($doctorId, $date)`: Get available time slots for a doctor on a specific date
- `getPatientAppointments($patientId)`: Get appointments for a patient
- `getDoctorAppointments($doctorId)`: Get appointments for a doctor
- `updateAppointmentStatus($appointmentId, $status)`: Update appointment status
- `validateAppointmentData($appointmentData)`: Validate appointment data
- `isSlotAvailable($doctorId, $date, $timeSlot)`: Check if a time slot is available

### AppointmentController Class

The `AppointmentController` class handles HTTP requests for appointment management.

#### Methods
- `bookAppointment($request)`: Handle appointment booking request
- `getAvailableSlots($request)`: Handle request for available time slots
- `getUserAppointments($request)`: Handle request for user appointments
- `updateAppointmentStatus($request)`: Handle request to update appointment status

## API Endpoints

### Book Appointment
- **URL**: `/api/appointments`
- **Method**: `POST`
- **Auth Required**: Yes
- **Data Params**:
  ```json
  {
    "doctor_id": "[integer]",
    "date": "[string] (YYYY-MM-DD)",
    "time_slot": "[string] (HH:MM)"
  }
  ```

### Get Available Slots
- **URL**: `/api/appointments/availability`
- **Method**: `GET`
- **Auth Required**: Yes
- **Query Params**:
  - `doctor_id=[integer]`
  - `date=[string] (YYYY-MM-DD)`

### Get User Appointments
- **URL**: `/api/appointments`
- **Method**: `GET`
- **Auth Required**: Yes

### Update Appointment Status
- **URL**: `/api/appointments/:id/status`
- **Method**: `PUT`
- **Auth Required**: Yes
- **URL Params**:
  - `id=[integer]`
- **Data Params**:
  ```json
  {
    "status": "[string] (pending|confirmed|cancelled|rescheduled|completed)"
  }
  ```

## Database Schema

### Appointments Table
```sql
CREATE TABLE appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    doctor_id INT NOT NULL,
    date DATE NOT NULL,
    time_slot TIME NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled', 'rescheduled', 'completed') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES users(id),
    FOREIGN KEY (doctor_id) REFERENCES users(id)
);
```

### Availability Table
```sql
CREATE TABLE availability (
    id INT AUTO_INCREMENT PRIMARY KEY,
    doctor_id INT NOT NULL,
    date DATE NOT NULL,
    time_slot TIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (doctor_id) REFERENCES users(id)
);
```

## Error Handling

The Appointment Service uses standard HTTP status codes for error responses:

- 200: Success
- 201: Created
- 400: Bad Request
- 401: Unauthorized
- 403: Forbidden
- 404: Not Found
- 409: Conflict
- 500: Internal Server Error

## Testing

### Unit Tests

Unit tests for the Appointment Service should cover:

1. Appointment booking functionality
2. Availability checking
3. Appointment retrieval
4. Appointment status updates
5. Data validation

### Integration Tests

Integration tests should cover:

1. API endpoint responses
2. Database interactions
3. Authentication and authorization
4. Error handling

## Deployment

### Environment Variables

The Appointment Service requires the following environment variables:

- `DB_HOST`: Database host
- `DB_PORT`: Database port
- `DB_NAME`: Database name
- `DB_USER`: Database username
- `DB_PASS`: Database password

### Dependencies

The Appointment Service requires:

- PHP 8.0 or higher
- Composer
- MySQL 5.7 or higher

### Installation

1. Navigate to the appointment-service directory
2. Run `composer install` to install dependencies
3. Set up the database and run migrations
4. Configure environment variables
5. Start the service

## Security Considerations

1. All API endpoints require authentication
2. Role-based access control is implemented
3. Input validation is performed on all data
4. SQL injection prevention through prepared statements
5. Cross-site scripting (XSS) prevention through output escaping

## Performance Considerations

1. Database indexes on frequently queried columns
2. Caching of availability data
3. Pagination for large result sets
4. Connection pooling for database connections

## Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Check database credentials in environment variables
   - Verify database server is running
   - Check database permissions

2. **Appointment Booking Fails**
   - Check if time slot is available
   - Verify patient and doctor IDs exist
   - Check for data validation errors

3. **Availability Not Showing**
   - Check if availability records exist for the date
   - Verify doctor ID is correct
   - Check for timezone issues

### Getting Help

If you encounter issues not covered in this guide:

1. Check the service logs for error messages
2. Review the API documentation
3. Contact the development team