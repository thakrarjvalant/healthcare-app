# Storage Service API Documentation

## Overview
The Storage Service handles document storage, retrieval, and management for the Healthcare Management System.

## Base URL
`http://localhost:8000/api/storage`

## Authentication
Most endpoints require authentication via a JWT token. Include the token in the Authorization header:
```
Authorization: Bearer <token>
```

## Endpoints

### Upload Document
- **URL**: `/documents`
- **Method**: `POST`
- **Auth Required**: Yes
- **Data Params**:
  - `file=[file] (multipart/form-data)`
- **Success Response**:
  - **Code**: 201
  - **Content**:
    ```json
    {
      "message": "Document uploaded successfully",
      "document": {
        "id": "[integer]",
        "filename": "[string]",
        "original_filename": "[string]",
        "file_size": "[integer]",
        "file_type": "[string]",
        "uploaded_at": "[string] (ISO 8601)"
      }
    }
    ```
- **Error Response**:
  - **Code**: 400
  - **Content**:
    ```json
    {
      "message": "Invalid file"
    }
    ```
  - **Code**: 403
  - **Content**:
    ```json
    {
      "message": "Insufficient permissions"
    }
    ```

### Get User Documents
- **URL**: `/documents`
- **Method**: `GET`
- **Auth Required**: Yes
- **Success Response**:
  - **Code**: 200
  - **Content**:
    ```json
    {
      "documents": [
        {
          "id": "[integer]",
          "filename": "[string]",
          "original_filename": "[string]",
          "file_size": "[integer]",
          "file_type": "[string]",
          "uploaded_at": "[string] (ISO 8601)"
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
      "message": "Failed to fetch documents"
    }
    ```

### Get Document by ID
- **URL**: `/documents/:id`
- **Method**: `GET`
- **Auth Required**: Yes
- **URL Params**:
  - `id=[integer]`
- **Success Response**:
  - **Code**: 200
  - **Content**:
    ```json
    {
      "document": {
        "id": "[integer]",
        "filename": "[string]",
        "original_filename": "[string]",
        "file_size": "[integer]",
        "file_type": "[string]",
        "uploaded_at": "[string] (ISO 8601)"
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
      "message": "Document not found"
    }
    ```

### Download Document
- **URL**: `/documents/:id/download`
- **Method**: `GET`
- **Auth Required**: Yes
- **URL Params**:
  - `id=[integer]`
- **Success Response**:
  - **Code**: 200
  - **Content**: Binary file data
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
      "message": "Document not found"
    }
    ```

### Delete Document
- **URL**: `/documents/:id`
- **Method**: `DELETE`
- **Auth Required**: Yes
- **URL Params**:
  - `id=[integer]`
- **Success Response**:
  - **Code**: 200
  - **Content**:
    ```json
    {
      "message": "Document deleted successfully"
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
      "message": "Document not found"
    }
    ```

### Get Document Metadata
- **URL**: `/documents/:id/metadata`
- **Method**: `GET`
- **Auth Required**: Yes
- **URL Params**:
  - `id=[integer]`
- **Success Response**:
  - **Code**: 200
  - **Content**:
    ```json
    {
      "metadata": {
        "id": "[integer]",
        "filename": "[string]",
        "original_filename": "[string]",
        "file_size": "[integer]",
        "file_type": "[string]",
        "uploaded_at": "[string] (ISO 8601)",
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
      "message": "Document not found"
    }
    ```

### Update Document Metadata
- **URL**: `/documents/:id/metadata`
- **Method**: `PUT`
- **Auth Required**: Yes
- **URL Params**:
  - `id=[integer]`
- **Data Params**:
  ```json
  {
    "filename": "[string] (optional)",
    "original_filename": "[string] (optional)"
  }
  ```
- **Success Response**:
  - **Code**: 200
  - **Content**:
    ```json
    {
      "message": "Document metadata updated successfully"
    }
    ```
- **Error Response**:
  - **Code**: 400
  - **Content**:
    ```json
    {
      "message": "Invalid metadata"
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
      "message": "Document not found"
    }
    ```

### Search Documents
- **URL**: `/documents/search`
- **Method**: `GET`
- **Auth Required**: Yes
- **Query Params**:
  - `query=[string]`
  - `type=[string] (optional)`
  - `date_from=[string] (YYYY-MM-DD) (optional)`
  - `date_to=[string] (YYYY-MM-DD) (optional)`
- **Success Response**:
  - **Code**: 200
  - **Content**:
    ```json
    {
      "documents": [
        {
          "id": "[integer]",
          "filename": "[string]",
          "original_filename": "[string]",
          "file_size": "[integer]",
          "file_type": "[string]",
          "uploaded_at": "[string] (ISO 8601)"
        },
        ...
      ]
    }
    ```
- **Error Response**:
  - **Code**: 400
  - **Content**:
    ```json
    {
      "message": "Invalid search parameters"
    }
    ```

### Get Storage Usage
- **URL**: `/usage`
- **Method**: `GET`
- **Auth Required**: Yes
- **Success Response**:
  - **Code**: 200
  - **Content**:
    ```json
    {
      "usage": {
        "total_files": "[integer]",
        "total_size": "[integer] (bytes)",
        "quota": "[integer] (bytes)",
        "used_percentage": "[number] (0-100)"
      }
    }
    ```
- **Error Response**:
  - **Code**: 500
  - **Content**:
    ```json
    {
      "message": "Failed to fetch storage usage"
    }
    ```

### Generate Document Preview
- **URL**: `/documents/:id/preview`
- **Method**: `GET`
- **Auth Required**: Yes
- **URL Params**:
  - `id=[integer]`
- **Success Response**:
  - **Code**: 200
  - **Content**: Binary preview image data
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
      "message": "Document not found"
    }
    ```
  - **Code**: 415
  - **Content**:
    ```json
    {
      "message": "Preview not available for this file type"
    }
    ```

### Share Document
- **URL**: `/documents/:id/share`
- **Method**: `POST`
- **Auth Required**: Yes
- **URL Params**:
  - `id=[integer]`
- **Data Params**:
  ```json
  {
    "recipient_email": "[string]",
    "message": "[string] (optional)",
    "expires_at": "[string] (ISO 8601) (optional)"
  }
  ```
- **Success Response**:
  - **Code**: 200
  - **Content**:
    ```json
    {
      "message": "Document shared successfully",
      "share_link": "[string]"
    }
    ```
- **Error Response**:
  - **Code**: 400
  - **Content**:
    ```json
    {
      "message": "Invalid share data"
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
      "message": "Document not found"
    }
    ```

## Supported File Types
- Images: JPEG, PNG, GIF
- Documents: PDF, DOC, DOCX, TXT
- Spreadsheets: XLS, XLSX
- Presentations: PPT, PPTX

## Error Codes
- 200: Success
- 201: Created
- 400: Bad Request
- 401: Unauthorized
- 403: Forbidden
- 404: Not Found
- 413: Payload Too Large
- 415: Unsupported Media Type
- 500: Internal Server Error