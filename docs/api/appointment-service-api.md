# Appointment Service API Documentation

## Overview
The Appointment Service handles appointment booking, scheduling, and management for the Healthcare Management System.

## Base URL
`http://localhost:8000/api/appointments`

## Authentication
Most endpoints require authentication via a JWT token. Include the token in the Authorization header:
```
Authorization: Bearer <token>
```

## Endpoints

### Book a New Appointment
- **URL**: `/`
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
- **Success Response**:
  - **Code**: 201
  - **Content**:
    ```json
    {
      "message": "Appointment booked successfully",
      "appointment_id": "[integer]"
    }
    ```
- **Error Response**:
  - **Code**: 400
  - **Content**:
    ```json
    {
      "message": "Invalid appointment data"
    }
    ```
  - **Code**: 409
  - **Content**:
    ```json
    {
      "message": "Time slot is not available"
    }
    ```

### Get Available Time Slots
- **URL**: `/availability`
- **Method**: `GET`
- **Auth Required**: Yes
- **Query Params**:
  - `doctor_id=[integer]`
  - `date=[string] (YYYY-MM-DD)`
- **Success Response**:
  - **Code**: 200
  - **Content**:
    ```json
    {
      "available_slots": ["[string] (HH:MM)", "..."]
    }
    ```
- **Error Response**:
  - **Code**: 400
  - **Content**:
    ```json
    {
      "message": "Doctor ID and date are required"
    }
    ```

### Get User Appointments
- **URL**: `/`
- **Method**: `GET`
- **Auth Required**: Yes
- **Success Response**:
  - **Code**: 200
  - **Content**:
    ```json
    {
      "appointments": [
        {
          "id": "[integer]",
          "patient_id": "[integer]",
          "doctor_id": "[integer]",
          "date": "[string] (YYYY-MM-DD)",
          "time_slot": "[string] (HH:MM)",
          "status": "[string]",
          "created_at": "[string] (ISO 8601)",
          "updated_at": "[string] (ISO 8601)"
        },
        ...
      ]
    }
    ```
- **Error Response**:
  - **Code**: 500
  - **Content**:
    ```json
    {
      "message": "Failed to fetch appointments"
    }
    ```

### Get Appointment by ID
- **URL**: `/:id`
- **Method**: `GET`
- **Auth Required**: Yes
- **URL Params**:
  - `id=[integer]`
- **Success Response**:
  - **Code**: 200
  - **Content**:
    ```json
    {
      "appointment": {
        "id": "[integer]",
        "patient_id": "[integer]",
        "doctor_id": "[integer]",
        "date": "[string] (YYYY-MM-DD)",
        "time_slot": "[string] (HH:MM)",
        "status": "[string]",
        "created_at": "[string] (ISO 8601)",
        "updated_at": "[string] (ISO 8601)"
      }
    }
    ```
- **Error Response**:
  - **Code**: 404
  - **Content**:
    ```json
    {
      "message": "Appointment not found"
    }
    ```

### Update Appointment Status
- **URL**: `/:id/status`
- **Method**: `PUT`
- **Auth Required**: Yes (Doctor or Receptionist)
- **URL Params**:
  - `id=[integer]`
- **Data Params**:
  ```json
  {
    "status": "[string] (pending|confirmed|cancelled|rescheduled|completed)"
  }
  ```
- **Success Response**:
  - **Code**: 200
  - **Content**:
    ```json
    {
      "message": "Appointment status updated"
    }
    ```
- **Error Response**:
  - **Code**: 400
  - **Content**:
    ```json
    {
      "message": "Invalid status"
    }
    ```
  - **Code**: 403
  - **Content**:
    ```json
    {
      "message": "Insufficient permissions"
    }
    ```
  - **Code**: 404
  - **Content**:
    ```json
    {
      "message": "Appointment not found"
    }
    ```

### Cancel Appointment
- **URL**: `/:id/cancel`
- **Method**: `PUT`
- **Auth Required**: Yes
- **URL Params**:
  - `id=[integer]`
- **Success Response**:
  - **Code**: 200
  - **Content**:
    ```json
    {
      "message": "Appointment cancelled successfully"
    }
    ```
- **Error Response**:
  - **Code**: 400
  - **Content**:
    ```json
    {
      "message": "Cannot cancel appointment"
    }
    ```
  - **Code**: 404
  - **Content**:
    ```json
    {
      "message": "Appointment not found"
    }
    ```

## Appointment Statuses
- pending: Appointment requested but not yet confirmed
- confirmed: Appointment confirmed by doctor/receptionist
- cancelled: Appointment cancelled by patient/doctor/receptionist
- rescheduled: Appointment moved to a different time/date
- completed: Appointment finished

## Error Codes
- 200: Success
- 201: Created
- 400: Bad Request
- 401: Unauthorized
- 403: Forbidden
- 404: Not Found
- 409: Conflict
- 500: Internal Server Error