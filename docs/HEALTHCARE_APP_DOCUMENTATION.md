# Healthcare Management System Documentation

## Table of Contents
1. [Overview](#overview)
2. [System Architecture](#system-architecture)
3. [Getting Started](#getting-started)
   - [Prerequisites](#prerequisites)
   - [Installation](#installation)
   - [Database Setup](#database-setup)
4. [Core Components](#core-components)
   - [Frontend](#frontend)
   - [Backend Services](#backend-services)
   - [Database](#database)
5. [Role-Based Access Control (RBAC)](#role-based-access-control-rbac)
   - [User Roles](#user-roles)
   - [Permissions System](#permissions-system)
   - [Dynamic RBAC Implementation](#dynamic-rbac-implementation)
6. [Database Seeders](#database-seeders)
   - [Seeder Overview](#seeder-overview)
   - [Running Seeders](#running-seeders)
7. [Development Guide](#development-guide)
   - [Frontend Development](#frontend-development)
   - [Backend Development](#backend-development)
   - [Docker Optimization](#docker-optimization)
8. [Features Status](#features-status)
9. [Troubleshooting](#troubleshooting)
10. [API Reference](#api-reference)

## Overview

The Healthcare Management System is a comprehensive platform designed to streamline healthcare facility operations. Built with a microservices architecture, it provides centralized management for patients, doctors, appointments, medical records, billing, and more.

### Key Features

- **Multi-role Dashboards**: Unique interfaces for Admin, Doctor, Receptionist, Patient, and Medical Coordinator
- **Dynamic RBAC**: Fine-grained permission management with customizable roles
- **Microservices Architecture**: Scalable and maintainable service structure
- **Docker Deployment**: Containerized services for easy deployment
- **Real-time Features**: Notifications, audit logging, and system monitoring

## System Architecture

```
Frontend (React) → API Gateway (Port 8000) → Microservices
                      ↓
                  MySQL Database (Port 3306)
```

### Services

1. **Frontend**: React application (Port 3000)
2. **API Gateway**: Central routing for all API requests (Port 8000)
3. **User Service**: Authentication and user management (Port 8001)
4. **Appointment Service**: Appointment scheduling and management (Port 8002)
5. **Clinical Service**: Medical records and treatment plans (Port 8003)
6. **Notification Service**: Alerts and messaging (Port 8004)
7. **Billing Service**: Invoicing and payments (Port 8005)
8. **Storage Service**: Document storage (Port 8006)
9. **Admin UI**: Administrative interface (Port 8007)
10. **Database**: MySQL data persistence (Port 3306)

## Getting Started

### Prerequisites

- Docker Desktop (must be running)
- Node.js 14.x or higher (for local development)
- PHP 8.0 or higher (for local development)
- Git (for version control)

### Installation

1. **Clone the Repository**
   ```bash
   git clone <repository-url>
   cd healthcare-app
   ```

2. **Start Docker Desktop**
   Ensure Docker Desktop is running on your system.

3. **Clean Previous Containers (if any)**
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

### Access Points

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

## Core Components

### Frontend

The frontend is a React application that provides user interfaces for all roles. It communicates with backend services through the API Gateway.

#### Key Features

- Role-specific dashboards with permission-aware components
- Responsive design for various device sizes
- Real-time notifications and alerts
- Audit logging for security compliance
- Data visualization components

#### Technologies Used

- React 18
- JavaScript/JSX
- CSS3 with modular styling
- Axios for API communication
- Context API for state management

### Backend Services

The system uses a microservices architecture with PHP-based services for each functional area.

#### API Gateway

Central routing service that directs requests to appropriate microservices.

#### User Service

Handles user authentication, registration, and profile management.

#### Appointment Service

Manages appointment scheduling, availability, and booking.

#### Clinical Service

Handles medical records, treatment plans, and clinical data.

#### Notification Service

Sends alerts, emails, and system notifications.

#### Billing Service

Manages invoicing, payments, and financial transactions.

#### Storage Service

Handles document storage and retrieval.

#### Admin UI

Provides administrative interfaces for system management.

### Database

MySQL 8.0 database with a comprehensive schema for healthcare data management.

#### Key Tables

- `users`: User accounts and authentication
- `dynamic_roles`: Role definitions with metadata
- `dynamic_permissions`: Granular permissions
- `dynamic_role_permissions`: Role-permission mappings
- `user_dynamic_roles`: User-role assignments
- `feature_modules`: System feature modules
- `role_feature_access`: Role-module access levels
- `appointments`: Appointment scheduling
- `medical_records`: Clinical data
- `billing_invoices`: Financial records

## Role-Based Access Control (RBAC)

### User Roles

The system implements a dynamic RBAC system with the following predefined roles:

1. **Super Administrator**
   - Full system access including role configuration
   - Manage all users, roles, and permissions
   - System administration and monitoring

2. **Administrator**
   - User management and audit oversight
   - Limited system configuration
   - Escalation handling

3. **Doctor**
   - Clinical duties only
   - Patient medical records access
   - Appointment management (own)

4. **Receptionist**
   - Front desk operations
   - Patient registration
   - Appointment scheduling
   - Billing operations

5. **Patient**
   - Self-service features
   - Own appointment management
   - Personal health records

6. **Medical Coordinator**
   - Patient assignment to clinicians
   - Limited patient history access

### Permissions System

The system uses granular permissions organized by modules and actions:

#### Permission Structure
- **Module**: Functional area (e.g., user_management, appointment_management)
- **Feature**: Specific capability within a module
- **Action**: Operation type (create, read, update, delete)
- **Resource**: Optional resource scope (own, all, assigned)

#### Example Permissions
- `users.create`: Create users
- `appointments.read`: View appointments
- `medical_records.update`: Update medical records

### Dynamic RBAC Implementation

The RBAC system allows administrators to:
- Create custom roles
- Assign granular permissions
- Control feature access by role
- Audit role changes

#### API Endpoints

**Role Management**
- `GET /admin/roles` - Get all dynamic roles
- `POST /admin/roles` - Create new role
- `PUT /admin/roles/{id}` - Update existing role
- `DELETE /admin/roles/{id}` - Delete role

**Permission Management**
- `GET /admin/permissions` - Get all permissions
- `GET /admin/roles/{id}/permissions` - Get role permissions
- `POST /admin/roles/{id}/permissions` - Assign permission to role
- `DELETE /admin/roles/{id}/permissions/{permission_id}` - Remove permission from role

**Feature Access Management**
- `GET /admin/modules` - Get feature modules
- `GET /admin/roles/{id}/features` - Get role feature access
- `POST /admin/roles/{id}/features` - Update role feature access

**User Role Management**
- `GET /admin/users/{id}/roles` - Get user roles
- `POST /admin/users/{id}/roles` - Assign role to user
- `DELETE /admin/users/{id}/roles/{role_id}` - Remove role from user

## Database Seeders

### Seeder Overview

The system includes comprehensive database seeders that populate the database with test data:

1. **UserSeeder**: Core user accounts for all system roles
2. **DynamicRBACSeeder**: Role definitions, permissions, and mappings
3. **SystemConfigSeeder**: System settings and operational data
4. **AppointmentSeeder**: Realistic appointment scheduling scenarios
5. **MedicalRecordSeeder**: Clinical and health data
6. **FinancialSeeder**: Billing and payment workflows

### Running Seeders

To seed the database with test data:

```bash
# Run all seeders
docker-compose run --rm user-service php /var/www/shared/../database/seed.php

# Or run specific seeders
docker-compose run --rm user-service php /var/www/shared/../database/seeds/UserSeeder.php
```

### Default Login Credentials

After running the database seed, you can use these credentials:

- **Admin**: `admin@example.com` / `password123`
- **Doctor**: `jane.smith@example.com` / `password123`
- **Receptionist**: `bob.receptionist@example.com` / `password123`
- **Patient**: `john.doe@example.com` / `password123`
- **Medical Coordinator**: `medical.coordinator@example.com` / `password123`

## Development Guide

### Frontend Development

#### Local Development Setup

1. Navigate to the frontend directory:
   ```bash
   cd frontend
   ```

2. Install dependencies:
   ```bash
   npm install
   ```

3. Start the development server:
   ```bash
   npm start
   ```

#### Key Frontend Components

- **AuthContext**: Centralized authentication and permission management
- **ApiService**: HTTP client for backend communication
- **PermissionGuard**: Component for protecting UI elements by permission
- **FeatureGuard**: Component for controlling access to entire features

#### Reflecting Changes to Local Browser

1. For frontend changes:
   - Save your changes
   - The development server will automatically reload
   - Refresh your browser if needed

2. For backend changes:
   - Save your changes
   - Restart the affected service:
     ```bash
     docker-compose restart [service-name]
     ```

### Backend Development

Each backend service can be run locally:

1. Navigate to a service directory:
   ```bash
   cd backend/user-service
   ```

2. Install dependencies:
   ```bash
   composer install
   ```

3. Start the service:
   ```bash
   php -S localhost:8000
   ```

### Docker Optimization

#### Build Time Improvements

The frontend Docker build has been optimized with:

1. **Multi-stage Build**: Separates build and runtime environments
2. **Layer Caching**: Dependencies cached separately from source code
3. **Alpine Base Images**: Smaller base images for faster downloads
4. **Enhanced .dockerignore**: Excludes unnecessary files from build context
5. **npm ci**: Faster and more reliable dependency installation

#### Performance Results

| Scenario | Before | After | Improvement |
|----------|--------|-------|-------------|
| **Clean Build** | 6-8 min | 1-2 min | 75% faster |
| **Code Change** | 5-7 min | 30-60 sec | 85% faster |
| **Dependency Change** | 6-8 min | 2-3 min | 60% faster |
| **No Changes** | 2-3 min | 5-10 sec | 95% faster |

#### Optimization Techniques

1. **Multi-Stage Docker Build**
   ```dockerfile
   # Stage 1: Build stage
   FROM node:18-alpine AS builder
   # ... build process
   
   # Stage 2: Production stage
   FROM node:18-alpine AS production
   # ... only production files
   ```

2. **Enhanced .dockerignore**
   Excludes node_modules, build artifacts, development files, and documentation.

3. **Layer Caching Optimization**
   ```dockerfile
   # Copy package files first (changes less frequently)
   COPY package*.json ./
   RUN npm ci --only=production --silent
   
   # Copy source code later (changes more frequently)
   COPY src/ ./src/
   ```

4. **BuildKit Integration**
   ```bash
   export DOCKER_BUILDKIT=1
   export COMPOSE_DOCKER_CLI_BUILD=1
   ```

## Features Status

### Fully Implemented Features

✅ **Admin Dashboard**
- User management
- Role and permission management
- Audit logs
- System settings
- Escalation management

✅ **Doctor Dashboard**
- Appointment management
- Clinical notes
- Treatment plans
- Patient management
- Schedule management

✅ **Receptionist Dashboard**
- Front desk operations
- Appointment scheduling
- Patient registration
- Billing operations

✅ **Patient Dashboard**
- Appointment booking
- Medical history
- Personal reports
- Health tracking

✅ **Dynamic RBAC System**
- Role creation and management
- Permission assignment
- Feature access control
- Audit logging

✅ **Database Seeders**
- All 6 seeders implemented and working
- 180+ test records created
- Proper role-permission mappings

### Partially Implemented Features

⚠️ **Medical Coordinator Dashboard**
- Patient assignment functionality (UI implemented but backend integration pending)
- Limited patient history access (UI implemented but backend integration pending)

### Planned Features

🔄 **Advanced Reporting**
- Comprehensive analytics dashboard
- Export capabilities
- Custom report builder

🔄 **Telemedicine Integration**
- Video consultation features
- Remote patient monitoring
- Digital prescription capabilities

## Troubleshooting

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

### Network Issues

If you encounter "Network error - Could not connect to the server" errors:

1. Ensure all services are running:
   ```bash
   docker-compose ps
   ```

2. Check that the API Gateway is accessible:
   ```bash
   curl http://localhost:8000/health
   ```

3. Verify frontend environment variables are correctly set in docker-compose.yml:
   ```yaml
   environment:
     - REACT_APP_API_BASE_URL=http://localhost:8000/api
     - REACT_APP_USER_SERVICE_BASE_URL=http://localhost:8000/api/users
     - REACT_APP_ADMIN_UI_BASE_URL=http://localhost:8000/api
   ```

## API Reference

### Authentication Endpoints

- `POST /api/users/register` - User registration
- `POST /api/users/login` - User login
- `GET /api/users/me` - Current user info

### User Management Endpoints

- `GET /api/admin/users` - List all users
- `POST /api/admin/users` - Create new user
- `PUT /api/admin/users/{id}` - Update user
- `DELETE /api/admin/users/{id}` - Delete user

### Role Management Endpoints

- `GET /api/admin/roles` - List all roles
- `POST /api/admin/roles` - Create new role
- `PUT /api/admin/roles/{id}` - Update role
- `DELETE /api/admin/roles/{id}` - Delete role

### Permission Management Endpoints

- `GET /api/admin/permissions` - List all permissions
- `GET /api/admin/roles/{id}/permissions` - Get role permissions
- `POST /api/admin/roles/{id}/permissions` - Assign permission to role
- `DELETE /api/admin/roles/{id}/permissions/{permission_id}` - Remove permission from role

### Appointment Endpoints

- `GET /api/appointments` - List appointments
- `POST /api/appointments` - Book appointment
- `PUT /api/appointments/{id}` - Update appointment

### Clinical Endpoints

- `GET /api/clinical/records` - Medical records
- `POST /api/clinical/records` - Create record

### Escalation Management Endpoints

- `GET /api/admin/escalations` - List escalations
- `POST /api/admin/escalations` - Create escalation
- `PUT /api/admin/escalations/{id}` - Update escalation
- `DELETE /api/admin/escalations/{id}` - Delete escalation

---
*Documentation last updated: October 12, 2025*