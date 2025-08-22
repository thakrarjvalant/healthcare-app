# Clinical Service API Documentation

## Overview
The Clinical Service handles medical records, treatment plans, and clinical data management for the Healthcare Management System.

## Base URL
`http://localhost:8000/api/clinical`

## Authentication
Most endpoints require authentication via a JWT token. Include the token in the Authorization header:
```
Authorization: Bearer <token>
```

## Endpoints

### Create Medical Record
- **URL**: `/records`
- **Method**: `POST`
- **Auth Required**: Yes (Doctor)
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
- **Success Response**:
  - **Code**: 201
  - **Content**:
    ```json
    {
      "message": "Medical record created successfully",
      "record_id": "[integer]"
    }
    ```
- **Error Response**:
  - **Code**: 400
  - **Content**:
    ```json
    {
      "message": "Invalid medical record data"
    }
    ```
  - **Code**: 403
  - **Content**:
    ```json
    {
      "message": "Insufficient permissions"
    }
    ```

### Get Patient Medical Records
- **URL**: `/records/patient/:patient_id`
- **Method**: `GET`
- **Auth Required**: Yes
- **URL Params**:
  - `patient_id=[integer]`
- **Success Response**:
  - **Code**: 200
  - **Content**:
    ```json
    {
      "records": [
        {
          "id": "[integer]",
          "patient_id": "[integer]",
          "doctor_id": "[integer]",
          "appointment_id": "[integer]",
          "diagnosis": "[string]",
          "prescription": "[string]",
          "notes": "[string]",
          "created_at": "[string] (ISO 8601)",
          "updated_at": "[string] (ISO 8601)"
        },
        ...
      ]
    }
    ```
- **Error Response**:
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
      "message": "Medical records not found"
    }
    ```

### Get Medical Record by ID
- **URL**: `/records/:id`
- **Method**: `GET`
- **Auth Required**: Yes
- **URL Params**:
  - `id=[integer]`
- **Success Response**:
  - **Code**: 200
  - **Content**:
    ```json
    {
      "record": {
        "id": "[integer]",
        "patient_id": "[integer]",
        "doctor_id": "[integer]",
        "appointment_id": "[integer]",
        "diagnosis": "[string]",
        "prescription": "[string]",
        "notes": "[string]",
        "created_at": "[string] (ISO 8601)",
        "updated_at": "[string] (ISO 8601)"
      }
    }
    ```
- **Error Response**:
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
      "message": "Medical record not found"
    }
    ```

### Update Medical Record
- **URL**: `/records/:id`
- **Method**: `PUT`
- **Auth Required**: Yes (Doctor)
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
- **Success Response**:
  - **Code**: 200
  - **Content**:
    ```json
    {
      "message": "Medical record updated"
    }
    ```
- **Error Response**:
  - **Code**: 400
  - **Content**:
    ```json
    {
      "message": "Invalid medical record data"
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
      "message": "Medical record not found"
    }
    ```

### Create Treatment Plan
- **URL**: `/treatment-plans`
- **Method**: `POST`
- **Auth Required**: Yes (Doctor)
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
- **Success Response**:
  - **Code**: 201
  - **Content**:
    ```json
    {
      "message": "Treatment plan created successfully",
      "plan_id": "[integer]"
    }
    ```
- **Error Response**:
  - **Code**: 400
  - **Content**:
    ```json
    {
      "message": "Invalid treatment plan data"
    }
    ```
  - **Code**: 403
  - **Content**:
    ```json
    {
      "message": "Insufficient permissions"
    }
    ```

### Get Patient Treatment Plans
- **URL**: `/treatment-plans/patient/:patient_id`
- **Method**: `GET`
- **Auth Required**: Yes
- **URL Params**:
  - `patient_id=[integer]`
- **Success Response**:
  - **Code**: 200
  - **Content**:
    ```json
    {
      "plans": [
        {
          "id": "[integer]",
          "patient_id": "[integer]",
          "doctor_id": "[integer]",
          "title": "[string]",
          "description": "[string]",
          "start_date": "[string] (YYYY-MM-DD)",
          "end_date": "[string] (YYYY-MM-DD)",
          "status": "[string]",
          "created_at": "[string] (ISO 8601)",
          "updated_at": "[string] (ISO 8601)"
        },
        ...
      ]
    }
    ```
- **Error Response**:
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
      "message": "Treatment plans not found"
    }
    ```

### Update Treatment Plan Status
- **URL**: `/treatment-plans/:id/status`
- **Method**: `PUT`
- **Auth Required**: Yes (Doctor)
- **URL Params**:
  - `id=[integer]`
- **Data Params**:
  ```json
  {
    "status": "[string] (active|completed|cancelled)"
  }
  ```
- **Success Response**:
  - **Code**: 200
  - **Content**:
    ```json
    {
      "message": "Treatment plan status updated"
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
      "message": "Treatment plan not found"
    }
    ```

## Error Codes
- 200: Success
- 201: Created
- 400: Bad Request
- 401: Unauthorized
- 403: Forbidden
- 404: Not Found
- 500: Internal Server Error

## Treatment Plan Statuses
- active: Currently ongoing treatment
- completed: Treatment finished
- cancelled: Treatment cancelled