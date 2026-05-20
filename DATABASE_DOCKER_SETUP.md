# Healthcare Management System - Docker Setup Guide

## Overview
This guide explains how to set up and run the Healthcare Management System using Docker. The system includes automated database initialization with migrations and seeders.

## Prerequisites

- Docker Desktop (with Docker Compose)
- Git (for cloning the repository)

## Quick Start

### 1. Clone and Navigate to Project
```bash
cd d:/customprojects/healthcare-app
```

### 2. Build and Start Services
```bash
docker-compose up --build -d
```

### 3. Wait for Initialization
The database will be automatically initialized with:
- Database schema (migrations)
- Seed data (users, roles, permissions, etc.)

Wait approximately 2-3 minutes for full initialization.

### 4. Verify Setup
Check if all services are running:
```bash
docker-compose ps
```

## Services

| Service | Port | Description |
|---------|------|-------------|
| Frontend | 3000 | React-based user interface |
| API Gateway | 8000 | Central API entry point |
| User Service | 8001 | User authentication and management |
| Appointment Service | 8002 | Appointment scheduling |
| Clinical Service | 8003 | Clinical records |
| Notification Service | 8004 | Notifications |
| Billing Service | 8005 | Billing system |
| Admin UI | 8007 | Administrative interface |

## Database Initialization

The system automatically runs:
1. **Migrations** - Sets up database schema
2. **Seeders** - Populates initial data

The database initialization happens in the `db-init` container which:
- Waits for MySQL to be ready
- Runs migrations to create tables
- Runs seeders to populate data

## Test Credentials

After initialization, you can use these test accounts:

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@example.com | password123 |
| Doctor | jane.smith@example.com | password123 |
| Receptionist | bob.receptionist@example.com | password123 |
| Patient | john.doe@example.com | password123 |
| Medical Coordinator | medical.coordinator@example.com | password123 |
| Super Admin | super.admin@example.com | password123 |

## Scripts

### Automated Setup
We provide scripts to automate the setup process:

#### Windows (PowerShell)
```powershell
cd docker-setup
./start-services.ps1
```

#### Linux/macOS (Bash)
```bash
cd docker-setup
chmod +x start-services.sh
./start-services.sh
```

## Troubleshooting

### Common Issues

1. **Docker not running**
   - Ensure Docker Desktop is running
   - Check with: `docker info`

2. **Database initialization fails**
   - Check logs: `docker-compose logs db-init`
   - Ensure sufficient time for initialization (2-3 minutes)

3. **Services not starting**
   - Check logs: `docker-compose logs <service-name>`
   - Example: `docker-compose logs api-gateway`

### Reset Database
To reset and reinitialize the database:
```bash
docker-compose down -v
docker-compose up --build -d
```

### View Logs
Monitor all services:
```bash
docker-compose logs -f
```

Monitor specific service:
```bash
docker-compose logs -f api-gateway
```

## Architecture

The system consists of:
- **Frontend**: React application (Port 3000)
- **API Gateway**: Central routing (Port 8000)
- **Microservices**: Individual services (Ports 8001-8007)
- **Database**: MySQL (Port 3306)
- **Database Init**: Initialization container

## Stopping the System

To stop all services:
```bash
docker-compose down
```

To stop and remove volumes (will lose data):
```bash
docker-compose down -v
```

## Development

For development, you can:
- Mount local code directories as volumes
- Use the `development` context in Dockerfiles
- Enable hot reloading in the frontend

## Production Deployment

For production deployment:
- Use environment-specific `.env` files
- Configure SSL certificates
- Set strong passwords in environment variables
- Use externalized database storage

---

**Note**: The database seeding includes comprehensive test data for all user roles, permissions, appointments, medical records, and financial data to demonstrate the full functionality of the healthcare management system.