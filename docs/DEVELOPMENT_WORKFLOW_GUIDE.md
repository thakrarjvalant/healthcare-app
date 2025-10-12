# Development Workflow Guide

## Overview

This guide provides detailed instructions on how to develop, test, and deploy changes to the Healthcare Management System, including how to reflect frontend and backend changes to the project running in your local browser.

## Prerequisites

Before starting development, ensure you have:
- Docker Desktop installed and running
- Node.js 14.x or higher
- PHP 8.0 or higher
- Git for version control
- A code editor (VS Code recommended)

## Local Development Setup

### 1. Clone the Repository
```bash
git clone <repository-url>
cd healthcare-app
```

### 2. Start the Development Environment
```bash
# Start all services in detached mode
docker-compose up -d

# Or start specific services for focused development
docker-compose up -d db api-gateway user-service
```

### 3. Verify Services are Running
```bash
docker-compose ps
```

You should see all services in the "Up" state.

## Frontend Development

### Development Server

The frontend uses React with a development server that provides hot reloading:

1. **Navigate to the frontend directory:**
   ```bash
   cd frontend
   ```

2. **Install dependencies (if not already installed):**
   ```bash
   npm install
   ```

3. **Start the development server:**
   ```bash
   npm start
   ```

The development server will start on `http://localhost:3000` and automatically reload when you make changes.

### Reflecting Frontend Changes

1. **Save your changes** in your code editor
2. **The development server automatically reloads** the browser
3. **If automatic reload doesn't work**, manually refresh the browser at `http://localhost:3000`

### Frontend Architecture

- **Components**: Reusable UI elements in `src/components/`
- **Pages**: Role-specific dashboards in `src/pages/`
- **Services**: API communication in `src/services/`
- **Context**: State management in `src/context/`
- **Hooks**: Custom React hooks in `src/hooks/`
- **Utils**: Utility functions in `src/utils/`

### Environment Variables

Frontend environment variables are configured in:
- `docker-compose.yml` (for Docker builds)
- `.env` file in the frontend directory (for local development)

Key variables:
- `REACT_APP_API_BASE_URL`: API Gateway URL
- `REACT_APP_USER_SERVICE_BASE_URL`: User service URL
- `REACT_APP_ADMIN_UI_BASE_URL`: Admin UI service URL

## Backend Development

### Individual Service Development

Each backend service can be run locally for focused development:

1. **Navigate to a service directory:**
   ```bash
   cd backend/user-service
   ```

2. **Install dependencies (if not already installed):**
   ```bash
   composer install
   ```

3. **Start the service:**
   ```bash
   php -S localhost:8001
   ```

### Reflecting Backend Changes

1. **Save your changes** in your code editor
2. **For Docker containers**, restart the specific service:
   ```bash
   docker-compose restart user-service
   ```
3. **For local development servers**, the changes are automatically picked up
4. **Refresh your browser** at `http://localhost:3000` to see changes

### Backend Architecture

- **API Gateway**: Routes requests to appropriate microservices (`backend/api-gateway/`)
- **User Service**: Authentication and user management (`backend/user-service/`)
- **Appointment Service**: Appointment scheduling (`backend/appointment-service/`)
- **Clinical Service**: Medical records (`backend/clinical-service/`)
- **Notification Service**: Alerts and messaging (`backend/notification-service/`)
- **Billing Service**: Financial operations (`backend/billing-service/`)
- **Storage Service**: Document storage (`backend/storage/`)
- **Admin UI**: Administrative functions (`backend/admin-ui/`)
- **Shared Components**: Common utilities (`backend/shared/`)
- **Database**: Migration and seeding scripts (`backend/database/`)

## Database Development

### Running Migrations

To update the database schema:

```bash
# Run all pending migrations
docker-compose run --rm user-service php /var/www/shared/../database/migrate.php

# Or run specific migrations
docker-compose run --rm user-service php /var/www/shared/../database/migrations/001_create_users_table.php
```

### Running Seeders

To populate the database with test data:

```bash
# Run all seeders
docker-compose run --rm user-service php /var/www/shared/../database/seed.php

# Or run specific seeders
docker-compose run --rm user-service php /var/www/shared/../database/seeds/UserSeeder.php
```

### Database Access

To access the database directly:

```bash
# Connect to MySQL
docker-compose exec db mysql -u healthcare_user -pyour_strong_password healthcare_db

# Or use a GUI tool like MySQL Workbench with:
# Host: localhost:3306
# Database: healthcare_db
# Username: healthcare_user
# Password: your_strong_password
```

## Docker Development Workflow

### Rebuilding Containers

When you make changes that require rebuilding containers:

1. **Rebuild specific service:**
   ```bash
   docker-compose build user-service
   ```

2. **Rebuild all services:**
   ```bash
   docker-compose build
   ```

3. **Rebuild with no cache (for major changes):**
   ```bash
   docker-compose build --no-cache
   ```

### Optimized Builds

The system uses BuildKit for faster builds:

```bash
# Enable BuildKit
export DOCKER_BUILDKIT=1
export COMPOSE_DOCKER_CLI_BUILD=1

# Build with optimizations
docker-compose build
```

### Container Management

```bash
# View running containers
docker-compose ps

# View container logs
docker-compose logs user-service

# Stop all containers
docker-compose down

# Stop and remove volumes
docker-compose down --volumes

# Start specific containers
docker-compose up -d user-service

# Restart specific container
docker-compose restart user-service
```

## Testing Changes

### Frontend Testing

1. **Unit Tests:**
   ```bash
   cd frontend
   npm test
   ```

2. **End-to-End Tests:**
   ```bash
   # Run E2E tests (if configured)
   npm run test:e2e
   ```

### Backend Testing

1. **Unit Tests:**
   ```bash
   cd backend/user-service
   composer test
   ```

2. **API Testing:**
   ```bash
   # Test API endpoints
   curl http://localhost:8000/health
   curl http://localhost:8001/api/users
   ```

## Debugging

### Frontend Debugging

1. **Check browser console** for JavaScript errors
2. **Use React DevTools** for component debugging
3. **Check network tab** for API call issues

### Backend Debugging

1. **Check service logs:**
   ```bash
   docker-compose logs user-service
   ```

2. **Enable detailed logging** in service configuration

3. **Use debugging tools** like Xdebug for PHP services

### Common Issues and Solutions

1. **Network Errors**: Ensure all services are running and ports are available
2. **Database Connection Issues**: Check database credentials and service status
3. **Permission Errors**: Verify RBAC permissions and user roles
4. **Build Failures**: Check Dockerfile and .dockerignore configurations

## Deployment Workflow

### Local Testing

1. **Test all changes locally** before committing
2. **Run full test suite** if available
3. **Verify functionality** in browser

### Committing Changes

1. **Stage changes:**
   ```bash
   git add .
   ```

2. **Commit with descriptive message:**
   ```bash
   git commit -m "Add feature X to improve Y"
   ```

3. **Push to repository:**
   ```bash
   git push origin main
   ```

### Production Deployment

For production deployment:

1. **Update environment variables** for production
2. **Run database migrations** if schema changed
3. **Deploy updated containers** to production environment
4. **Verify deployment** with health checks

## Best Practices

### Code Organization

1. **Follow established patterns** in each service
2. **Use consistent naming conventions**
3. **Document complex logic** with comments
4. **Keep functions small and focused**

### Performance

1. **Minimize API calls** in frontend components
2. **Use caching** where appropriate
3. **Optimize database queries**
4. **Lazy load** non-critical resources

### Security

1. **Validate all inputs** on both frontend and backend
2. **Use parameterized queries** to prevent SQL injection
3. **Implement proper authentication** for all endpoints
4. **Regularly update dependencies**

### Testing

1. **Write unit tests** for new functionality
2. **Test edge cases** and error conditions
3. **Perform integration testing** for service interactions
4. **Regularly run test suites**

## Troubleshooting

### Service Won't Start

1. **Check logs:**
   ```bash
   docker-compose logs [service-name]
   ```

2. **Verify dependencies** are running:
   ```bash
   docker-compose ps
   ```

3. **Check port conflicts:**
   ```bash
   netstat -ano | findstr :[port]
   ```

### Database Issues

1. **Verify connection settings** in docker-compose.yml
2. **Check database logs:**
   ```bash
   docker-compose logs db
   ```

3. **Ensure migrations are run:**
   ```bash
   docker-compose run --rm user-service php /var/www/shared/../database/migrate.php
   ```

### Frontend Issues

1. **Check browser console** for errors
2. **Verify API endpoints** are accessible:
   ```bash
   curl http://localhost:8000/health
   ```

3. **Check environment variables** in .env file

### Network Issues

1. **Ensure Docker Desktop** is running
2. **Check firewall settings**
3. **Verify localhost resolution**

## Conclusion

This development workflow guide provides a comprehensive approach to developing, testing, and deploying changes to the Healthcare Management System. By following these practices, you can efficiently make changes and see them reflected in your local browser environment.

Remember to:
1. Always test changes locally before committing
2. Follow established patterns and best practices
3. Keep services running during development
4. Use the hot reload features of the development server
5. Monitor logs for debugging information

---
*Guide last updated: October 12, 2025*