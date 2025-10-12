# Healthcare Management System - Setup & Deployment Guide

> **NOTE**: This guide has been superseded by the [consolidated documentation](HEALTHCARE_APP_DOCUMENTATION.md). Please refer to the new documentation for the most up-to-date information.

## 🚀 Quick Start

### Prerequisites
- **Docker Desktop** (must be running)
- **Node.js 14.x+** (for local development)
- **PHP 8.0+** (for local development)
- **Git** (for version control)

### 🐳 Docker Setup (Recommended)

1. **Start Docker Desktop**
   ```bash
   # Ensure Docker Desktop is running on Windows
   # Check if Docker is available
   docker --version
   ```

2. **Clone and Navigate**
   ```bash
   cd d:\customprojects\healthcare-app
   ```

3. **Clean Previous Containers**
   ```bash
   docker-compose down --volumes --remove-orphans
   ```

4. **Build All Services**
   ```bash
   docker-compose build
   ```

5. **Start Database & Run Setup**
   ```bash
   # Start only the database first
   docker-compose up -d db
   
   # Wait 30 seconds for database to initialize
   timeout /t 30
   
   # Run database migrations (creates tables)
   docker-compose exec db mysql -u healthcare_user -pyour_strong_password healthcare_db -e "SHOW TABLES;"
   
   # If no tables exist, run migrations
   docker-compose run --rm user-service php /var/www/shared/../database/migrate.php
   
   # Seed database with initial users
   docker-compose run --rm user-service php /var/www/shared/../database/seed.php
   ```

6. **Start All Services**
   ```bash
   docker-compose up -d
   ```

7. **Verify Services & Database**
   ```bash
   # Check all services are running
   docker-compose ps
   
   # Test API Gateway
   curl http://localhost:8000/health
   
   # Verify database users were created
   docker-compose exec db mysql -u healthcare_user -pyour_strong_password healthcare_db -e "SELECT id, name, email, role FROM users;"
   ```

### 🌐 Access Points

Once all services are running:

| Service | URL | Purpose |
|---------|-----|---------|
| **Frontend** | http://localhost:3000 | Main React Application |
| **API Gateway** | http://localhost:8000 | Central API Router |
| **User Service** | http://localhost:8001 | Authentication & Users |
| **Appointment Service** | http://localhost:8002 | Appointment Management |
| **Clinical Service** | http://localhost:8003 | Medical Records |
| **Notification Service** | http://localhost:8004 | Alerts & Messages |
| **Billing Service** | http://localhost:8005 | Invoices & Payments |
| **Storage Service** | http://localhost:8006 | Document Storage |
| **Admin UI** | http://localhost:8007 | Administrative Interface |
| **Database** | localhost:3306 | MySQL Database |

### 🔍 Health Checks

Test if services are responding:

```bash
# API Gateway Health
curl http://localhost:8000/health

# User Service
curl http://localhost:8001/api/users

# Frontend (should return HTML)
curl http://localhost:3000
```

## 🛠️ Development Setup

### Frontend Development
```bash
cd frontend
npm install
npm start
# Runs on http://localhost:3000
```

### Backend Service Development
```bash
# Example: User Service
cd backend/user-service
composer install
php -S localhost:8001
```

### Database Operations

**Run Migrations:**
```bash
cd backend/database
composer install
php migrate.php
```

**Seed Database:**
```bash
cd backend/database
php seed.php
```

## 🐛 Troubleshooting

### Common Issues

1. **Docker Desktop Not Running**
   ```
   Error: "The system cannot find the file specified"
   Solution: Start Docker Desktop application
   ```

2. **Port Already in Use**
   ```bash
   docker-compose down
   # Check what's using the port
   netstat -ano | findstr :3000
   ```

3. **Database Connection Failed**
   ```bash
   # Restart database service
   docker-compose restart db
   # Check database logs
   docker-compose logs db
   ```

4. **Service Build Failures**
   ```bash
   # Rebuild specific service
   docker-compose build --no-cache user-service
   ```

### Service Status Check
```bash
# View all service logs
docker-compose logs

# Check specific service
docker-compose logs frontend
docker-compose logs api-gateway
```

## 📊 Architecture Overview

```text
Frontend (React) → API Gateway → Microservices
                      ↓
                  MySQL Database
```

### Service Communication
- Frontend communicates with API Gateway (port 8000)
- API Gateway routes requests to appropriate microservices
- All services connect to shared MySQL database
- Each service runs on dedicated port (8001-8007)

## 🔐 Login Credentials

**Database Users** (Created by seed script):
- **Admin**: `admin@example.com` / `password123`
- **Doctor**: `jane.smith@example.com` / `password123`  
- **Receptionist**: `bob.receptionist@example.com` / `password123`
- **Patient**: `john.doe@example.com` / `password123`

**Database Connection:**
- Host: localhost:3306
- Database: healthcare_db
- User: healthcare_user
- Password: your_strong_password

**Important**: The system no longer uses mock authentication. All login attempts are validated against the MySQL database.

## 📋 API Documentation

### Authentication Endpoints
- POST `/api/users/register` - User registration
- POST `/api/users/login` - User login
- GET `/api/users/me` - Current user info

### Appointment Endpoints
- GET `/api/appointments` - List appointments
- POST `/api/appointments` - Book appointment
- PUT `/api/appointments/{id}` - Update appointment

### Clinical Endpoints
- GET `/api/clinical/records` - Medical records
- POST `/api/clinical/records` - Create record

## 🚀 Production Deployment

For production deployment, consider:
- Use proper SSL certificates
- Configure environment variables
- Set up proper logging
- Implement monitoring
- Use production database credentials
- Enable security headers

## 📝 Development Notes

- Each microservice is independently deployable
- Shared PHP utilities in `/backend/shared`
- Database migrations are manual PHP scripts
- Frontend uses React Router for SPA navigation
- API Gateway handles CORS and routing

---

## 📖 Updated Documentation

For the most current and comprehensive documentation, please refer to:
- [Healthcare App Documentation](HEALTHCARE_APP_DOCUMENTATION.md) - Complete system documentation
- [Development Workflow Guide](DEVELOPMENT_WORKFLOW_GUIDE.md) - Guide for making and testing changes
- [Docker Optimization Summary](DOCKER_OPTIMIZATION_SUMMARY.md) - Docker build improvements
- [Feature Status Report](FEATURE_STATUS_REPORT.md) - Current feature implementation status

---

## 🆘 Need Help?

If you encounter issues:
1. Check Docker Desktop is running
2. Verify all ports are available (3000, 8000-8007, 3306)
3. Review service logs: `docker-compose logs [service-name]`
4. Restart services: `docker-compose restart`
5. Full reset: `docker-compose down --volumes && docker-compose up --build`