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

3. The application will be available at:
   - Frontend: `http://localhost:3000`
   - User Service API: `http://localhost:8001`
   - Appointment Service API: `http://localhost:8002`
   - Clinical Service API: `http://localhost:8003`
   - Notification Service API: `http://localhost:8004`
   - Billing Service API: `http://localhost:8005`
   - Storage Service API: `http://localhost:8006`
   - Admin UI: `http://localhost:8007`

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

### Architecture
- [System Architecture](docs/architecture/system-architecture.md)
- [Database Schema](docs/architecture/database-schema.md)

### API
- [User Service API](docs/api/user-service-api.md)
- [Appointment Service API](docs/api/appointment-service-api.md)
- [Clinical Service API](docs/api/clinical-service-api.md)
- [Notification Service API](docs/api/notification-service-api.md)
- [Billing Service API](docs/api/billing-service-api.md)
- [Storage Service API](docs/api/storage-service-api.md)

### User Guides
- [Patient User Guide](docs/user-guides/patient-user-guide.md)
- [Doctor User Guide](docs/user-guides/doctor-user-guide.md)
- [Receptionist User Guide](docs/user-guides/receptionist-user-guide.md)
- [Admin User Guide](docs/user-guides/admin-user-guide.md)

### Developer Guides
- [User Service Developer Guide](docs/developer-guides/user-service.md)
- [Appointment Service Developer Guide](docs/developer-guides/appointment-service.md)
- [Clinical Service Developer Guide](docs/developer-guides/clinical-service.md)
- [Notification Service Developer Guide](docs/developer-guides/notification-service.md)
- [Billing Service Developer Guide](docs/developer-guides/billing-service.md)
- [Storage Service Developer Guide](docs/developer-guides/storage-service.md)

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