# Notification Service API Documentation

## Overview
The Notification Service handles sending and managing notifications for the Healthcare Management System.

## Base URL
`http://localhost:8000/api/notifications`

## Authentication
Most endpoints require authentication via a JWT token. Include the token in the Authorization header:
```
Authorization: Bearer <token>
```

## Endpoints

### Get User Notifications
- **URL**: `/`
- **Method**: `GET`
- **Auth Required**: Yes
- **Query Params**:
  - `unread=[boolean] (optional)`
- **Success Response**:
  - **Code**: 200
  - **Content**:
    ```json
    {
      "notifications": [
        {
          "id": "[integer]",
          "user_id": "[integer]",
          "type": "[string]",
          "title": "[string]",
          "message": "[string]",
          "is_read": "[boolean]",
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
      "message": "Failed to fetch notifications"
    }
    ```

### Mark Notification as Read
- **URL**: `/:id/read`
- **Method**: `PUT`
- **Auth Required**: Yes
- **URL Params**:
  - `id=[integer]`
- **Success Response**:
  - **Code**: 200
  - **Content**:
    ```json
    {
      "message": "Notification marked as read"
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
      "message": "Notification not found"
    }
    ```

### Mark All Notifications as Read
- **URL**: `/read-all`
- **Method**: `PUT`
- **Auth Required**: Yes
- **Success Response**:
  - **Code**: 200
  - **Content**:
    ```json
    {
      "message": "All notifications marked as read"
    }
    ```
- **Error Response**:
  - **Code**: 400
  - **Content**:
    ```json
    {
      "message": "Failed to mark notifications as read"
    }
    ```

### Delete Notification
- **URL**: `/:id`
- **Method**: `DELETE`
- **Auth Required**: Yes
- **URL Params**:
  - `id=[integer]`
- **Success Response**:
  - **Code**: 200
  - **Content**:
    ```json
    {
      "message": "Notification deleted"
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
      "message": "Notification not found"
    }
    ```

### Send Email Notification
- **URL**: `/email`
- **Method**: `POST`
- **Auth Required**: Yes (Admin/Service)
- **Data Params**:
  ```json
  {
    "to": "[string] (email address)",
    "subject": "[string]",
    "message": "[string]"
  }
  ```
- **Success Response**:
  - **Code**: 200
  - **Content**:
    ```json
    {
      "message": "Email sent successfully"
    }
    ```
- **Error Response**:
  - **Code**: 400
  - **Content**:
    ```json
    {
      "message": "Invalid email data"
    }
    ```
  - **Code**: 403
  - **Content**:
    ```json
    {
      "message": "Insufficient permissions"
    }
    ```

### Send SMS Notification
- **URL**: `/sms`
- **Method**: `POST`
- **Auth Required**: Yes (Admin/Service)
- **Data Params**:
  ```json
  {
    "to": "[string] (phone number)",
    "message": "[string]"
  }
  ```
- **Success Response**:
  - **Code**: 200
  - **Content**:
    ```json
    {
      "message": "SMS sent successfully"
    }
    ```
- **Error Response**:
  - **Code**: 400
  - **Content**:
    ```json
    {
      "message": "Invalid SMS data"
    }
    ```
  - **Code**: 403
  - **Content**:
    ```json
    {
      "message": "Insufficient permissions"
    }
    ```

### Get Notification Preferences
- **URL**: `/preferences`
- **Method**: `GET`
- **Auth Required**: Yes
- **Success Response**:
  - **Code**: 200
  - **Content**:
    ```json
    {
      "preferences": {
        "email_notifications": "[boolean]",
        "sms_notifications": "[boolean]",
        "push_notifications": "[boolean]",
        "whatsapp_notifications": "[boolean]"
      }
    }
    ```
- **Error Response**:
  - **Code**: 500
  - **Content**:
    ```json
    {
      "message": "Failed to fetch preferences"
    }
    ```

### Update Notification Preferences
- **URL**: `/preferences`
- **Method**: `PUT`
- **Auth Required**: Yes
- **Data Params**:
  ```json
  {
    "email_notifications": "[boolean] (optional)",
    "sms_notifications": "[boolean] (optional)",
    "push_notifications": "[boolean] (optional)",
    "whatsapp_notifications": "[boolean] (optional)"
  }
  ```
- **Success Response**:
  - **Code**: 200
  - **Content**:
    ```json
    {
      "message": "Preferences updated successfully"
    }
    ```
- **Error Response**:
  - **Code**: 400
  - **Content**:
    ```json
    {
      "message": "Invalid preferences data"
    }
    ```

### Get Notification Templates
- **URL**: `/templates`
- **Method**: `GET`
- **Auth Required**: Yes (Admin)
- **Success Response**:
  - **Code**: 200
  - **Content**:
    ```json
    {
      "templates": [
        {
          "id": "[integer]",
          "name": "[string]",
          "type": "[string] (email|sms|push)",
          "subject": "[string] (for email)",
          "content": "[string]",
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

### Create Notification Template
- **URL**: `/templates`
- **Method**: `POST`
- **Auth Required**: Yes (Admin)
- **Data Params**:
  ```json
  {
    "name": "[string]",
    "type": "[string] (email|sms|push)",
    "subject": "[string] (for email)",
    "content": "[string]"
  }
  ```
- **Success Response**:
  - **Code**: 201
  - **Content**:
    ```json
    {
      "message": "Template created successfully",
      "template_id": "[integer]"
    }
    ```
- **Error Response**:
  - **Code**: 400
  - **Content**:
    ```json
    {
      "message": "Invalid template data"
    }
    ```
  - **Code**: 403
  - **Content**:
    ```json
    {
      "message": "Insufficient permissions"
    }
    ```

### Update Notification Template
- **URL**: `/templates/:id`
- **Method**: `PUT`
- **Auth Required**: Yes (Admin)
- **URL Params**:
  - `id=[integer]`
- **Data Params**:
  ```json
  {
    "name": "[string] (optional)",
    "type": "[string] (email|sms|push) (optional)",
    "subject": "[string] (for email) (optional)",
    "content": "[string] (optional)"
  }
  ```
- **Success Response**:
  - **Code**: 200
  - **Content**:
    ```json
    {
      "message": "Template updated successfully"
    }
    ```
- **Error Response**:
  - **Code**: 400
  - **Content**:
    ```json
    {
      "message": "Invalid template data"
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
      "message": "Template not found"
    }
    ```

### Delete Notification Template
- **URL**: `/templates/:id`
- **Method**: `DELETE`
- **Auth Required**: Yes (Admin)
- **URL Params**:
  - `id=[integer]`
- **Success Response**:
  - **Code**: 200
  - **Content**:
    ```json
    {
      "message": "Template deleted successfully"
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
      "message": "Template not found"
    }
    ```

## Notification Types
- appointment: Appointment reminders and updates
- billing: Invoice and payment notifications
- medical: Medical record updates and test results
- system: System maintenance and updates
- security: Security alerts and login notifications

## Error Codes
- 200: Success
- 201: Created
- 400: Bad Request
- 401: Unauthorized
- 403: Forbidden
- 404: Not Found
- 500: Internal Server Error