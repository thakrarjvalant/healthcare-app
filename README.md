# Healthcare Management System

A comprehensive healthcare management system built with a microservices architecture.

## Overview

This Healthcare Management System is designed to streamline the operations of healthcare facilities by providing a centralized platform for managing patients, doctors, appointments, medical records, billing, and more. The system follows a microservices architecture to ensure scalability, maintainability, and flexibility.

## Features

### User Roles
- **Patient**: Can register, login, book appointments, and view medical history
- **Doctor**: Can manage appointments and create treatment plans
- **Receptionist**: Can manage appointments and patient information
- **Admin**: Can manage users, roles, permissions, and system configuration
- **Medical Coordinator**: Can manage patient assignments and limited patient history access

### Role-Based Dashboards
Each user role has a unique dashboard with specialized features:
- **Admin Dashboard**: System management, user management, reports & analytics, audit logs
- **Doctor Dashboard**: Appointment management, treatment plans, patient reports, medical records
- **Patient Dashboard**: Appointment booking, medical history, personal reports, health tracking
- **Receptionist Dashboard**: Front desk operations, patient registration, check-in management, schedule coordination
- **Medical Coordinator Dashboard**: Patient assignment and limited history access

### Permission Management
All dashboards now include a "Refresh Permissions" button that allows users to manually refresh their permissions without logging out when administrators make changes to role assignments.

### Functional Features (New!)
All dashboard modules now include fully functional interfaces:
- **Interactive Forms**: Real data entry and management
- **Modal-based UI**: Non-intrusive popup interfaces
- **Real-time Updates**: Immediate feedback on user actions
- **API Integration**: Connected to backend services with fallback data
- **Comprehensive CRUD**: Create, read, update, delete operations for all data types

### Services
1. **User Service**: Handles user registration, authentication, and profile management
2. **Appointment Service**: Manages appointment booking, scheduling, and availability
3. **Clinical Service**: Handles medical records, treatment plans, and clinical data
4. **Notification Service**: Sends emails, SMS, and other notifications
5. **Billing Service**: Manages billing and invoicing
6. **Storage Service**: Handles document storage and retrieval
7. **Admin UI**: Provides administrative interface for user and system management

## Project Structure

```
healthcare-app/
├── backend/
│   ├── user-service/
│   ├── appointment-service/
│   ├── clinical-service/
│   ├── notification-service/
│   ├── billing-service/
│   ├── storage/
│   ├── admin-ui/
│   ├── shared/
│   ├── database/
│   └── README.md
├── frontend/
│   ├── public/
│   ├── src/
│   └── README.md
├── docs/
│   ├── architecture/
│   ├── api/
│   ├── user-guides/
│   └── developer-guides/
├── docker-compose.yml
└── README.md
```

## Getting Started

### Prerequisites
- Docker and Docker Compose
- Node.js 14.x or higher (for local development)
- PHP 8.0 or higher (for local development)
- MySQL 5.7 or higher (for local development)

### Installation

1. Clone the repository:
   ```bash
   git clone <repository-url>
   cd healthcare-app
   ```

2. Start the services using Docker Compose:
   ```bash
   docker-compose up -d
   ```

3. Initialize the database (first time only):
   ```bash
   # Run database migrations
   docker-compose exec user-service php /var/www/shared/../database/migrate.php
   
   # Seed the database with initial users
   docker-compose exec user-service php /var/www/shared/../database/seed.php
   ```

4. The application will be available at:
   - Frontend: `http://localhost:3000`
   - API Gateway: `http://localhost:8000` (Central API router)
   - User Service API: `http://localhost:8001`
   - Appointment Service API: `http://localhost:8002`
   - Clinical Service API: `http://localhost:8003`
   - Notification Service API: `http://localhost:8004`
   - Billing Service API: `http://localhost:8005`
   - Storage Service API: `http://localhost:8006`
   - Admin UI: `http://localhost:8007`
   - Database: `localhost:3306` (MySQL)

### Building Docker Containers

If you need to rebuild the Docker containers, you can use the provided PowerShell scripts:

1. **Build All Containers**: Run `build-all-containers.ps1` as Administrator
2. **Build Frontend Only**: Run `build-frontend.ps1` as Administrator

Detailed instructions for Docker builds are available in [DOCKER_BUILD_INSTRUCTIONS.md](docs/DOCKER_BUILD_INSTRUCTIONS.md).

### Default Login Credentials

After running the database seed, you can use these credentials:

- **Admin**: `admin@example.com` / `password123`
- **Doctor**: `jane.smith@example.com` / `password123`
- **Receptionist**: `bob.receptionist@example.com` / `password123`
- **Patient**: `john.doe@example.com` / `password123`
- **Medical Coordinator**: `medical.coordinator@example.com` / `password123`

### Database Connection Details

For MySQL Workbench or other database tools:
- **Host**: `localhost`
- **Port**: `3306`
- **Database**: `healthcare_db`
- **Username**: `healthcare_user`
- **Password**: `your_strong_password`

### Local Development

#### Frontend
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

#### Backend Services
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

## Documentation

### 📚 Complete Documentation
All documentation has been consolidated into a single comprehensive guide:
- [Healthcare App Documentation](docs/HEALTHCARE_APP_DOCUMENTATION.md) - Complete system documentation

### 🚀 Quick Links
- [Setup Guide](docs/SETUP_GUIDE.md) - Complete installation instructions
- [Dashboard Features](docs/features/DASHBOARD_FEATURES.md) - Detailed feature documentation
- [Test Credentials](docs/roles/TEST_CREDENTIALS.md) - Working login credentials
- [Troubleshooting Guide](docs/TROUBLESHOOTING.md) - Common issues and solutions

### 🏗️ Technical Documentation
- [System Architecture](docs/architecture/system-architecture.md)
- [API Reference](docs/api/)
- [User Guides](docs/user-guides/)
- [Developer Guides](docs/developer-guides/)

### 📖 Consolidated Documentation
For the most up-to-date and comprehensive information about the system, please refer to:
- [Healthcare App Documentation](docs/HEALTHCARE_APP_DOCUMENTATION.md)

## Testing

### Frontend
Run frontend tests with:
```bash
cd frontend
npm test
```

### Backend Services
Run backend tests with:
```bash
cd backend/user-service
composer test
```

## Deployment

### Docker
The application can be deployed using Docker and Docker Compose as shown in the Installation section.

### Kubernetes
For production deployments, Kubernetes manifests are available in the `k8s` directory.

## Contributing

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Create a pull request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Support

If you encounter any issues or have questions about the system, please contact our support team at support@healthcaresystem.com or call (123) 456-7890.