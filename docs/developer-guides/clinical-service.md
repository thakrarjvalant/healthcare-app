# Clinical Service Developer Guide

This guide provides information for developers working with the Clinical Service of the Healthcare Management System.

## Overview

The Clinical Service handles medical records, treatment plans, and clinical data management. It provides APIs for creating, updating, and retrieving clinical information.

## Project Structure

```
backend/
└── clinical-service/
    ├── controllers/
    │   └── ClinicalController.php
    ├── models/
    │   ├── MedicalRecord.php
    │   └── TreatmentPlan.php
    ├── ClinicalService.php
    ├── api.php
    └── config/
        └── database.php
```

## Key Components

### MedicalRecord Model

The `MedicalRecord` model represents a patient's medical record.

#### Properties
- `id`: Unique identifier for the medical record
- `patient_id`: ID of the patient
- `doctor_id`: ID of the doctor who created the record
- `appointment_id`: ID of the appointment associated with the record
- `diagnosis`: Diagnosis information
- `prescription`: Prescription information
- `notes`: Additional notes
- `created_at`: Timestamp when the record was created
- `updated_at`: Timestamp when the record was last updated

#### Methods
- `__construct($data = [])`: Constructor to initialize the medical record with data
- Getters and setters for all properties
- `toArray()`: Convert the medical record object to an array

### TreatmentPlan Model

The `TreatmentPlan` model represents a patient's treatment plan.

#### Properties
- `id`: Unique identifier for the treatment plan
- `patient_id`: ID of the patient
- `doctor_id`: ID of the doctor who created the plan
- `title`: Title of the treatment plan
- `description`: Description of the treatment plan
- `start_date`: Start date of the treatment plan (YYYY-MM-DD)
- `end_date`: End date of the treatment plan (YYYY-MM-DD)
- `status`: Status of the treatment plan (active, completed, cancelled)
- `created_at`: Timestamp when the plan was created
- `updated_at`: Timestamp when the plan was last updated

#### Methods
- `__construct($data = [])`: Constructor to initialize the treatment plan with data
- Getters and setters for all properties
- `toArray()`: Convert the treatment plan object to an array

### ClinicalService Class

The `ClinicalService` class contains the business logic for clinical data management.

#### Methods
- `createMedicalRecord($recordData)`: Create a new medical record
- `getPatientMedicalRecords($patientId)`: Get medical records for a patient
- `getMedicalRecord($recordId)`: Get a specific medical record
- `updateMedicalRecord($recordId, $recordData)`: Update a medical record
- `createTreatmentPlan($planData)`: Create a new treatment plan
- `getPatientTreatmentPlans($patientId)`: Get treatment plans for a patient
- `updateTreatmentPlanStatus($planId, $status)`: Update treatment plan status
- `validateMedicalRecordData($recordData)`: Validate medical record data
- `validateTreatmentPlanData($planData)`: Validate treatment plan data

### ClinicalController Class

The `ClinicalController` class handles HTTP requests for clinical data management.

#### Methods
- `createMedicalRecord($request)`: Handle request to create a medical record
- `getPatientMedicalRecords($request)`: Handle request to get patient medical records
- `getMedicalRecord($request)`: Handle request to get a specific medical record
- `updateMedicalRecord($request)`: Handle request to update a medical record
- `createTreatmentPlan($request)`: Handle request to create a treatment plan
- `getPatientTreatmentPlans($request)`: Handle request to get patient treatment plans
- `updateTreatmentPlanStatus($request)`: Handle request to update treatment plan status

## API Endpoints

### Create Medical Record
- **URL**: `/api/clinical/records`
- **Method**: `POST`
- **Auth Required**: Yes
- **Data Params**:
  ```json
  {
    "patient_id": "[integer]",
    "appointment_id": "[integer]",
    "diagnosis": "[string]",
    "prescription": "[string]",
    "notes": "[string] (optional)"
  }
  ```

### Get Patient Medical Records
- **URL**: `/api/clinical/records/patient/:patient_id`
- **Method**: `GET`
- **Auth Required**: Yes
- **URL Params**:
  - `patient_id=[integer]`

### Get Medical Record
- **URL**: `/api/clinical/records/:id`
- **Method**: `GET`
- **Auth Required**: Yes
- **URL Params**:
  - `id=[integer]`

### Update Medical Record
- **URL**: `/api/clinical/records/:id`
- **Method**: `PUT`
- **Auth Required**: Yes
- **URL Params**:
  - `id=[integer]`
- **Data Params**:
  ```json
  {
    "diagnosis": "[string] (optional)",
    "prescription": "[string] (optional)",
    "notes": "[string] (optional)"
  }
  ```

### Create Treatment Plan
- **URL**: `/api/clinical/treatment-plans`
- **Method**: `POST`
- **Auth Required**: Yes
- **Data Params**:
  ```json
  {
    "patient_id": "[integer]",
    "title": "[string]",
    "description": "[string]",
    "start_date": "[string] (YYYY-MM-DD)",
    "end_date": "[string] (YYYY-MM-DD) (optional)",
    "status": "[string] (active|completed|cancelled) (default: active)"
  }
  ```

### Get Patient Treatment Plans
- **URL**: `/api/clinical/treatment-plans/patient/:patient_id`
- **Method**: `GET`
- **Auth Required**: Yes
- **URL Params**:
  - `patient_id=[integer]`

### Update Treatment Plan Status
- **URL**: `/api/clinical/treatment-plans/:id/status`
- **Method**: `PUT`
- **Auth Required**: Yes
- **URL Params**:
  - `id=[integer]`
- **Data Params**:
  ```json
  {
    "status": "[string] (active|completed|cancelled)"
  }
  ```

## Database Schema

### Medical Records Table
```sql
CREATE TABLE medical_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    doctor_id INT NOT NULL,
    appointment_id INT,
    diagnosis TEXT,
    prescription TEXT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES users(id),
    FOREIGN KEY (doctor_id) REFERENCES users(id),
    FOREIGN KEY (appointment_id) REFERENCES appointments(id)
);
```

### Treatment Plans Table
```sql
CREATE TABLE treatment_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    doctor_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    start_date DATE NOT NULL,
    end_date DATE,
    status ENUM('active', 'completed', 'cancelled') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES users(id),
    FOREIGN KEY (doctor_id) REFERENCES users(id)
);
```

## Error Handling

The Clinical Service uses standard HTTP status codes for error responses:

- 200: Success
- 201: Created
- 400: Bad Request
- 401: Unauthorized
- 403: Forbidden
- 404: Not Found
- 500: Internal Server Error

## Testing

### Unit Tests

Unit tests for the Clinical Service should cover:

1. Medical record creation and retrieval
2. Treatment plan creation and retrieval
3. Data validation
4. Status updates

### Integration Tests

Integration tests should cover:

1. API endpoint responses
2. Database interactions
3. Authentication and authorization
4. Error handling

## Deployment

### Environment Variables

The Clinical Service requires the following environment variables:

- `DB_HOST`: Database host
- `DB_PORT`: Database port
- `DB_NAME`: Database name
- `DB_USER`: Database username
- `DB_PASS`: Database password

### Dependencies

The Clinical Service requires:

- PHP 8.0 or higher
- Composer
- MySQL 5.7 or higher

### Installation

1. Navigate to the clinical-service directory
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
6. Patient data is only accessible to authorized users

## Performance Considerations

1. Database indexes on frequently queried columns
2. Pagination for large result sets
3. Connection pooling for database connections
4. Caching of frequently accessed data

## Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Check database credentials in environment variables
   - Verify database server is running
   - Check database permissions

2. **Medical Record Creation Fails**
   - Check if patient and doctor IDs exist
   - Verify appointment ID exists (if provided)
   - Check for data validation errors

3. **Treatment Plan Not Found**
   - Verify treatment plan ID is correct
   - Check if treatment plan exists for the patient

### Getting Help

If you encounter issues not covered in this guide:

1. Check the service logs for error messages
2. Review the API documentation
3. Contact the development team