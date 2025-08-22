# Developer Guide: Getting Started

This guide provides instructions for developers to set up and work with the Healthcare Management System.

## Prerequisites

Before you begin, ensure you have the following installed:

- PHP 8.0 or higher
- Composer
- MySQL 5.7 or higher
- Node.js 14.x or higher
- npm 6.x or higher
- Git

## Project Structure

The project follows a microservices architecture with the following structure:

```
healthcare-app/
├── backend/
│   ├── user-service/
│   ├── appointment-service/
│   ├── clinical-service/
│   ├── notification-service/
│   ├── billing-service/
│   ├── admin-ui/
│   ├── storage/
│   ├── shared/
│   └── database/
├── frontend/
│   ├── public/
│   └── src/
└── docs/
    ├── architecture/
    ├── api/
    ├── user-guides/
    └── developer-guides/
```

## Setting Up the Backend

### 1. Clone the Repository

```bash
git clone <repository-url>
cd healthcare-app
```

### 2. Set Up User Service

1. Navigate to the user service directory:
   ```bash
   cd backend/user-service
   ```

2. Install PHP dependencies:
   ```bash
   composer install
   ```

3. Create a `.env` file with your database configuration:
   ```bash
   cp .env.example .env
   ```

4. Update the `.env` file with your database credentials:
   ```
   DB_HOST=localhost
   DB_PORT=3306
   DB_NAME=healthcare_users
   DB_USER=your_username
   DB_PASS=your_password
   ```

5. Run database migrations:
   ```bash
   php artisan migrate
   ```

6. Start the development server:
   ```bash
   php artisan serve
   ```

### 3. Set Up Other Services

Repeat similar steps for other services:
- appointment-service
- clinical-service
- notification-service
- billing-service
- admin-ui
- storage

Each service may have specific setup requirements detailed in their respective README files.

## Setting Up the Frontend

### 1. Navigate to the Frontend Directory

```bash
cd frontend
```

### 2. Install Dependencies

```bash
npm install
```

### 3. Configure Environment Variables

Create a `.env` file with the following content:
```
REACT_APP_API_BASE_URL=http://localhost:8000/api
```

### 4. Start the Development Server

```bash
npm start
```

The frontend will be available at `http://localhost:3000`.

## Development Workflow

### Backend Development

1. Create a new branch for your feature:
   ```bash
   git checkout -b feature/your-feature-name
   ```

2. Make changes to the appropriate service

3. Write tests for your changes

4. Run tests:
   ```bash
   ./vendor/bin/phpunit
   ```

5. Commit your changes:
   ```bash
   git add .
   git commit -m "Description of your changes"
   ```

6. Push your branch and create a pull request

### Frontend Development

1. Create a new branch for your feature:
   ```bash
   git checkout -b feature/your-feature-name
   ```

2. Make changes to the frontend code

3. Write tests for your changes

4. Run tests:
   ```bash
   npm test
   ```

5. Commit your changes:
   ```bash
   git add .
   git commit -m "Description of your changes"
   ```

6. Push your branch and create a pull request

## Code Standards

### Backend (PHP)

- Follow PSR-12 coding standards
- Use meaningful variable and function names
- Write PHPDoc comments for all public methods
- Keep functions small and focused
- Use dependency injection where possible

### Frontend (JavaScript/React)

- Follow Airbnb JavaScript style guide
- Use functional components with hooks
- Write JSDoc comments for complex functions
- Keep components small and focused
- Use PropTypes for type checking

## Database Migrations

To create a new migration:

```bash
php artisan make:migration migration_name
```

To run migrations:

```bash
php artisan migrate
```

To rollback migrations:

```bash
php artisan migrate:rollback
```

## Testing

### Backend Testing

Run all tests:

```bash
./vendor/bin/phpunit
```

Run specific test:

```bash
./vendor/bin/phpunit --filter TestClassName
```

### Frontend Testing

Run all tests:

```bash
npm test
```

Run tests in watch mode:

```bash
npm test -- --watch
```

## Deployment

### Backend

1. Configure environment variables for production
2. Run database migrations
3. Set up web server (Apache/Nginx) to point to the public directory
4. Configure SSL certificate

### Frontend

1. Build the production version:
   ```bash
   npm run build
   ```

2. Deploy the contents of the `build` directory to your web server

## Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Check database credentials in `.env` file
   - Ensure MySQL service is running
   - Verify database exists

2. **Composer Dependencies Not Found**
   - Run `composer install` to install dependencies
   - Check PHP version compatibility

3. **Node Modules Not Found**
   - Run `npm install` to install dependencies
   - Check Node.js version compatibility

### Getting Help

If you encounter issues not covered in this guide:

1. Check the project's issue tracker
2. Contact the development team
3. Refer to the service-specific README files for more detailed information