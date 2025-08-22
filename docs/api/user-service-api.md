# User Service API Documentation

## Overview
The User Service handles user registration, authentication, and profile management for the Healthcare Management System.

## Base URL
`http://localhost:8000/api/users`

## Authentication
Most endpoints require authentication via a JWT token. Include the token in the Authorization header:
```
Authorization: Bearer <token>
```

## Endpoints

### Register a New User
- **URL**: `/register`
- **Method**: `POST`
- **Auth Required**: No
- **Data Params**:
  ```json
  {
    "name": "[string]",
    "email": "[string]",
    "password": "[string]",
    "role": "[string] (optional, defaults to 'patient')"
  }
  ```
- **Success Response**:
  - **Code**: 201
  - **Content**:
    ```json
    {
      "message": "User registered successfully",
      "user_id": "[integer]"
    }
    ```
- **Error Response**:
  - **Code**: 400
  - **Content**:
    ```json
    {
      "message": "Invalid user data"
    }
    ```

### User Login
- **URL**: `/login`
- **Method**: `POST`
- **Auth Required**: No
- **Data Params**:
  ```json
  {
    "email": "[string]",
    "password": "[string]"
  }
  ```
- **Success Response**:
  - **Code**: 200
  - **Content**:
    ```json
    {
      "message": "Login successful",
      "token": "[JWT token]",
      "user": {
        "id": "[integer]",
        "name": "[string]",
        "email": "[string]",
        "role": "[string]"
      }
    }
    ```
- **Error Response**:
  - **Code**: 401
  - **Content**:
    ```json
    {
      "message": "Invalid credentials"
    }
    ```

### Get User Profile
- **URL**: `/profile`
- **Method**: `GET`
- **Auth Required**: Yes
- **Data Params**: None
- **Success Response**:
  - **Code**: 200
  - **Content**:
    ```json
    {
      "user": {
        "id": "[integer]",
        "name": "[string]",
        "email": "[string]",
        "role": "[string]"
      }
    }
    ```
- **Error Response**:
  - **Code**: 401
  - **Content**:
    ```json
    {
      "message": "Unauthorized"
    }
    ```

### Update User Profile
- **URL**: `/profile`
- **Method**: `PUT`
- **Auth Required**: Yes
- **Data Params**:
  ```json
  {
    "name": "[string] (optional)",
    "email": "[string] (optional)",
    "password": "[string] (optional)"
  }
  ```
- **Success Response**:
  - **Code**: 200
  - **Content**:
    ```json
    {
      "message": "Profile updated successfully",
      "user": {
        "id": "[integer]",
        "name": "[string]",
        "email": "[string]",
        "role": "[string]"
      }
    }
    ```
- **Error Response**:
  - **Code**: 400
  - **Content**:
    ```json
    {
      "message": "Invalid user data"
    }
    ```
  - **Code**: 401
  - **Content**:
    ```json
    {
      "message": "Unauthorized"
    }
    ```

### Delete User
- **URL**: `/profile`
- **Method**: `DELETE`
- **Auth Required**: Yes
- **Data Params**: None
- **Success Response**:
  - **Code**: 200
  - **Content**:
    ```json
    {
      "message": "User deleted successfully"
    }
    ```
- **Error Response**:
  - **Code**: 401
  - **Content**:
    ```json
    {
      "message": "Unauthorized"
    }
    ```
  - **Code**: 403
  - **Content**:
    ```json
    {
      "message": "Forbidden"
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

## User Roles
- patient
- doctor
- receptionist
- admin