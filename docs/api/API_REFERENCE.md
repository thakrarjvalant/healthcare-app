# Healthcare App - API Reference

## 🔗 API Gateway

**Base URL**: `http://localhost:8000`

The API Gateway routes all frontend requests to appropriate microservices.

### Routes

| Route | Service | Port | Description |
|-------|---------|------|-------------|
| `/api/users/*` | User Service | 8001 | Authentication & User Management |
| `/api/appointments/*` | Appointment Service | 8002 | Appointment Management |
| `/api/clinical/*` | Clinical Service | 8003 | Medical Records |
| `/api/notifications/*` | Notification Service | 8004 | Alerts & Messages |
| `/api/billing/*` | Billing Service | 8005 | Invoices & Payments |
| `/api/storage/*` | Storage Service | 8006 | Document Storage |
| `/api/admin/*` | Admin UI | 8007 | Administrative Interface |

### Health Check
```http
GET /health
```

**Response:**
```json
{
  "status": "ok",
  "timestamp": 1758372201
}
```

## 👤 User Service API

**Base URL**: `http://localhost:8000/api/users`

### Authentication

#### Login
```http
POST /login
Content-Type: application/json

{
  "email": "admin@example.com",
  "password": "password123"
}
```

**Success Response:**
```json
{
  "success": true,
  "user": {
    "id": 4,
    "name": "Admin User",
    "email": "admin@example.com",
    "role": "admin"
  },
  "token": "admin-jwt-token-1758372201"
}
```

**Error Response:**
```json
{
  "message": "Invalid email or password"
}
```

#### Register
```http
POST /register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "role": "patient"
}
```

### Valid User Credentials

| Role | Email | Password | Display Name |
|------|-------|----------|--------------|
| Admin | `admin@example.com` | `password123` | Admin User |
| Doctor | `jane.smith@example.com` | `password123` | Dr. Jane Smith |
| Receptionist | `bob.receptionist@example.com` | `password123` | Receptionist Bob |
| Patient | `john.doe@example.com` | `password123` | John Doe |

## 📅 Appointment Service API

**Base URL**: `http://localhost:8000/api/appointments`

### Endpoints

#### Get Appointments
```http
GET /
Authorization: Bearer {token}
```

#### Book Appointment
```http
POST /
Content-Type: application/json
Authorization: Bearer {token}

{
  "doctorId": 2,
  "date": "2023-08-25",
  "timeSlot": "10:00",
  "type": "consultation"
}
```

#### Update Appointment Status
```http
PUT /{id}/status
Content-Type: application/json
Authorization: Bearer {token}

{
  "status": "confirmed"
}
```

## 🏥 Clinical Service API

**Base URL**: `http://localhost:8000/api/clinical`

### Endpoints

#### Get Medical History
```http
GET /history/{patientId}
Authorization: Bearer {token}
```

#### Get Treatment Plans
```http
GET /treatment-plans/{patientId}
Authorization: Bearer {token}
```

## 🔔 Notification Service API

**Base URL**: `http://localhost:8000/api/notifications`

### Endpoints

#### Get User Notifications
```http
GET /user/{userId}
Authorization: Bearer {token}
```

## 💰 Billing Service API

**Base URL**: `http://localhost:8000/api/billing`

### Endpoints

#### Get Invoices
```http
GET /invoices/{patientId}
Authorization: Bearer {token}
```

## 📁 Storage Service API

**Base URL**: `http://localhost:8000/api/storage`

### Endpoints

#### Upload Document
```http
POST /upload
Content-Type: multipart/form-data
Authorization: Bearer {token}

file: {binary}
patientId: {id}
```

## 🛡️ Authentication & Authorization

### Headers Required
All protected endpoints require:
```http
Authorization: Bearer {token}
Content-Type: application/json
```

### Role-Based Access
- **Patient**: Can access their own data only
- **Doctor**: Can access their patients' data
- **Receptionist**: Can access appointment and patient data
- **Admin**: Can access all data and manage users

### Token Format
Tokens are generated on login:
```
{role}-jwt-token-{timestamp}
```

Example: `admin-jwt-token-1758372201`

## 🔧 Development Testing

### Test with cURL

**Login:**
```bash
curl -X POST -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password123"}' \
  http://localhost:8000/api/users/login
```

**Get Health:**
```bash
curl http://localhost:8000/health
```

### Test with Browser

Create an HTML file to test API connectivity:
```html
<!DOCTYPE html>
<html>
<head><title>API Test</title></head>
<body>
    <button onclick="testLogin()">Test Login</button>
    <div id="result"></div>
    <script>
        async function testLogin() {
            const response = await fetch('http://localhost:8000/api/users/login', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    email: 'admin@example.com',
                    password: 'password123'
                })
            });
            const data = await response.json();
            document.getElementById('result').innerHTML = JSON.stringify(data, null, 2);
        }
    </script>
</body>
</html>
```

## 🚨 Error Codes

| Code | Description | Solution |
|------|-------------|----------|
| 400 | Bad Request | Check request format |
| 401 | Unauthorized | Invalid credentials |
| 403 | Forbidden | Insufficient permissions |
| 404 | Not Found | Check endpoint URL |
| 500 | Internal Server Error | Check service logs |

## 📊 Response Format

### Success Response
```json
{
  "success": true,
  "data": {...},
  "message": "Optional success message"
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error description",
  "error": "Detailed error information"
}
```

---

## 🔍 Troubleshooting API Issues

1. **Check service status**: `docker-compose ps`
2. **View logs**: `docker-compose logs api-gateway`
3. **Test health endpoint**: `curl http://localhost:8000/health`
4. **Verify database**: Check if users exist in database
5. **Check CORS**: Ensure proper headers are set