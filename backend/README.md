# Backend Services

This directory contains the backend services for the Healthcare Management System. Each service is implemented as a separate module with its own controllers, models, and business logic.

## Services

1. [User Service](user-service/README.md) - Handles user registration, authentication, and profile management
2. [Appointment Service](appointment-service/README.md) - Manages appointment booking, scheduling, and availability
3. [Clinical Service](clinical-service/README.md) - Handles medical records, treatment plans, and clinical data
4. [Notification Service](notification-service/README.md) - Sends emails, SMS, and other notifications
5. [Billing Service](billing-service/README.md) - Manages billing and invoicing
6. [Storage Service](storage/README.md) - Handles document storage and retrieval
7. [Admin UI](admin-ui/README.md) - Provides administrative interface for user and system management

## Shared Components

The `shared` directory contains components that are used across multiple services:

- `utils` - Utility functions used by multiple services
- `exceptions` - Custom exception classes
- `middleware` - Middleware functions for authentication, logging, etc.

## Database

The `database` directory contains database-related files:

- `migrations` - Database migration scripts
- `seeds` - Database seed data
- `config.php` - Database configuration

## Getting Started

### Prerequisites

- PHP 8.0 or higher
- Composer
- MySQL 5.7 or higher

### Installation

1. Navigate to each service directory
2. Run `composer install` to install dependencies
3. Set up the database and run migrations
4. Configure environment variables
5. Start each service

### Environment Variables

Each service requires the following environment variables:

- `DB_HOST` - Database host
- `DB_PORT` - Database port
- `DB_NAME` - Database name
- `DB_USER` - Database username
- `DB_PASS` - Database password

### Running Migrations

To set up the database schema, run the migration scripts:

```bash
cd database
php migrate.php
```

### Seeding Data

To populate the database with initial data, run the seed scripts:

```bash
cd database
php seed.php
```

## API Documentation

Each service has its own API documentation:

- [User Service API](../docs/api/user-service-api.md)
- [Appointment Service API](../docs/api/appointment-service-api.md)
- [Clinical Service API](../docs/api/clinical-service-api.md)
- [Notification Service API](../docs/api/notification-service-api.md)
- [Billing Service API](../docs/api/billing-service-api.md)
- [Storage Service API](../docs/api/storage-service-api.md)

## Development

### Code Structure

Each service follows a consistent structure:

```
service-name/
├── controllers/        # HTTP request handlers
├── models/            # Data models
├── middleware/        # Middleware functions
├── config/            # Configuration files
├── ServiceName.php    # Main service class
└── api.php            # API endpoint definitions
```

### Testing

Each service should have unit and integration tests. Run tests with:

```bash
composer test
```

### Coding Standards

- Follow PSR-12 coding standards
- Use meaningful variable and function names
- Write PHPDoc comments for all public methods
- Keep functions small and focused
- Use dependency injection where possible

## Deployment

### Docker

The services can be deployed using Docker. See the docker-compose.yml file for configuration.

### Kubernetes

For production deployments, use Kubernetes with the provided manifests.

## Security

- All API endpoints require authentication
- Role-based access control is implemented
- Input validation is performed on all data
- SQL injection prevention through prepared statements
- Cross-site scripting (XSS) prevention through output escaping

## Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Check database credentials in environment variables
   - Verify database server is running
   - Check database permissions

2. **Service Not Starting**
   - Check service logs for error messages
   - Verify dependencies are installed
   - Check environment variables

### Getting Help

If you encounter issues not covered in this guide:

1. Check the service logs for error messages
2. Review the API documentation
3. Contact the development team