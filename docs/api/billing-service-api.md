# Billing Service API Documentation

## Overview
The Billing Service handles invoicing, payments, and financial management for the Healthcare Management System.

## Base URL
`http://localhost:8000/api/billing`

## Authentication
Most endpoints require authentication via a JWT token. Include the token in the Authorization header:
```
Authorization: Bearer <token>
```

## Endpoints

### Create Invoice
- **URL**: `/invoices`
- **Method**: `POST`
- **Auth Required**: Yes (Admin/Receptionist)
- **Data Params**:
  ```json
  {
    "patient_id": "[integer]",
    "appointment_id": "[integer]",
    "amount": "[number] (decimal)",
    "description": "[string] (optional)"
  }
  ```
- **Success Response**:
  - **Code**: 201
  - **Content**:
    ```json
    {
      "message": "Invoice created successfully",
      "invoice_id": "[integer]"
    }
    ```
- **Error Response**:
  - **Code**: 400
  - **Content**:
    ```json
    {
      "message": "Invalid invoice data"
    }
    ```
  - **Code**: 403
  - **Content**:
    ```json
    {
      "message": "Insufficient permissions"
    }
    ```

### Get Patient Invoices
- **URL**: `/invoices/patient/:patient_id`
- **Method**: `GET`
- **Auth Required**: Yes
- **URL Params**:
  - `patient_id=[integer]`
- **Success Response**:
  - **Code**: 200
  - **Content**:
    ```json
    {
      "invoices": [
        {
          "id": "[integer]",
          "patient_id": "[integer]",
          "appointment_id": "[integer]",
          "amount": "[number] (decimal)",
          "status": "[string]",
          "issued_date": "[string] (YYYY-MM-DD)",
          "due_date": "[string] (YYYY-MM-DD)",
          "paid_date": "[string] (YYYY-MM-DD)",
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
      "message": "Invoices not found"
    }
    ```

### Get Invoice by ID
- **URL**: `/invoices/:id`
- **Method**: `GET`
- **Auth Required**: Yes
- **URL Params**:
  - `id=[integer]`
- **Success Response**:
  - **Code**: 200
  - **Content**:
    ```json
    {
      "invoice": {
        "id": "[integer]",
        "patient_id": "[integer]",
        "appointment_id": "[integer]",
        "amount": "[number] (decimal)",
        "status": "[string]",
        "issued_date": "[string] (YYYY-MM-DD)",
        "due_date": "[string] (YYYY-MM-DD)",
        "paid_date": "[string] (YYYY-MM-DD)",
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
      "message": "Invoice not found"
    }
    ```

### Update Invoice Status
- **URL**: `/invoices/:id/status`
- **Method**: `PUT`
- **Auth Required**: Yes (Admin/Receptionist)
- **URL Params**:
  - `id=[integer]`
- **Data Params**:
  ```json
  {
    "status": "[string] (pending|paid|overdue|cancelled)"
  }
  ```
- **Success Response**:
  - **Code**: 200
  - **Content**:
    ```json
    {
      "message": "Invoice status updated"
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
      "message": "Invoice not found"
    }
    ```

### Process Payment
- **URL**: `/invoices/:id/pay`
- **Method**: `POST`
- **Auth Required**: Yes
- **URL Params**:
  - `id=[integer]`
- **Data Params**:
  ```json
  {
    "payment_method": "[string] (credit_card|debit_card|bank_transfer|cash)",
    "amount": "[number] (decimal) (optional, defaults to invoice amount)"
  }
  ```
- **Success Response**:
  - **Code**: 200
  - **Content**:
    ```json
    {
      "message": "Payment processed successfully",
      "transaction_id": "[string]"
    }
    ```
- **Error Response**:
  - **Code**: 400
  - **Content**:
    ```json
    {
      "message": "Invalid payment data"
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
      "message": "Invoice not found"
    }
    ```

### Get Payment History
- **URL**: `/payments/patient/:patient_id`
- **Method**: `GET`
- **Auth Required**: Yes
- **URL Params**:
  - `patient_id=[integer]`
- **Success Response**:
  - **Code**: 200
  - **Content**:
    ```json
    {
      "payments": [
        {
          "id": "[integer]",
          "invoice_id": "[integer]",
          "amount": "[number] (decimal)",
          "payment_method": "[string]",
          "transaction_id": "[string]",
          "payment_date": "[string] (ISO 8601)",
          "created_at": "[string] (ISO 8601)"
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
      "message": "Payment history not found"
    }
    ```

### Generate Financial Report
- **URL**: `/reports/financial`
- **Method**: `GET`
- **Auth Required**: Yes (Admin)
- **Query Params**:
  - `start_date=[string] (YYYY-MM-DD) (optional)`
  - `end_date=[string] (YYYY-MM-DD) (optional)`
  - `status=[string] (pending|paid|overdue|cancelled) (optional)`
- **Success Response**:
  - **Code**: 200
  - **Content**:
    ```json
    {
      "report": {
        "total_invoices": "[integer]",
        "total_revenue": "[number] (decimal)",
        "paid_invoices": "[integer]",
        "pending_invoices": "[integer]",
        "overdue_invoices": "[integer]",
        "cancelled_invoices": "[integer]",
        "period": {
          "start_date": "[string] (YYYY-MM-DD)",
          "end_date": "[string] (YYYY-MM-DD)"
        }
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

## Invoice Statuses
- pending: Invoice issued but not yet paid
- paid: Invoice fully paid
- overdue: Invoice past due date
- cancelled: Invoice cancelled

## Payment Methods
- credit_card: Credit card payment
- debit_card: Debit card payment
- bank_transfer: Bank transfer
- cash: Cash payment

## Error Codes
- 200: Success
- 201: Created
- 400: Bad Request
- 401: Unauthorized
- 403: Forbidden
- 404: Not Found
- 500: Internal Server Error