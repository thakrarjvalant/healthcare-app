# Healthcare Management System - Database Setup Guide

## Overview
This guide provides comprehensive instructions for setting up the database for the Healthcare Management System. The system requires a MySQL database with specific tables, relationships, and seed data.

## Prerequisites
- MySQL 5.7 or higher installed
- Access to MySQL command line or a database management tool
- PHP 7.4 or higher (for running seeders)

## Database Schema

### Core Tables
1. **users** - Stores user information and authentication details
2. **dynamic_roles** - Defines system roles with metadata
3. **feature_modules** - Feature modules available in the system
4. **dynamic_permissions** - Individual permissions within the system
5. **dynamic_role_permissions** - Relationship between roles and permissions
6. **role_feature_access** - Access levels for roles to feature modules

### Users Table Structure
```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('patient', 'doctor', 'receptionist', 'admin', 'medical_coordinator') NOT NULL DEFAULT 'patient',
    verified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

## Setup Methods

### Method 1: Automated SQL Script
Use the provided [healthcare_db_setup.sql](file:///d%3A/customprojects/healthcare-app/healthcare_db_setup.sql) file:

```bash
mysql -u root -p < healthcare_db_setup.sql
```

### Method 2: Manual Setup
1. Create the database:
   ```sql
   CREATE DATABASE healthcare_db;
   USE healthcare_db;
   ```

2. Create tables using the SQL statements from [healthcare_db_setup.sql](file:///d%3A/customprojects/healthcare-app/healthcare_db_setup.sql)

3. Insert seed data using the INSERT statements from the same file

### Method 3: Using Database Management Tools
1. Open phpMyAdmin, MySQL Workbench, or similar tool
2. Create a new database named `healthcare_db`
3. Import the [healthcare_db_setup.sql](file:///d%3A/customprojects/healthcare-app/healthcare_db_setup.sql) file

## Running Seeders

### Prerequisites for Running Seeders
1. Ensure MySQL is running
2. Database `healthcare_db` exists
3. PHP is installed and in your PATH
4. Composer dependencies are installed:
   ```bash
   cd backend/database
   composer install
   ```

### Running All Seeders
```bash
cd backend/database
php master_seed.php
```

### Running Individual Seeders
```bash
cd backend/database
php seed.php
```

## Expected Users After Seeding

| Name | Email | Role | Password |
|------|-------|------|----------|
| Admin User | admin@example.com | admin | password123 |
| Dr. Jane Smith | jane.smith@example.com | doctor | password123 |
| Receptionist Bob | bob.receptionist@example.com | receptionist | password123 |
| John Doe | john.doe@example.com | patient | password123 |
| Medical Coordinator | medical.coordinator@example.com | medical_coordinator | password123 |

## Verification

### Verify Database Connection
```bash
cd backend/database
php verify_users.php
```

### Manual Verification
```sql
USE healthcare_db;
SELECT COUNT(*) as user_count FROM users;
SELECT id, name, email, role FROM users ORDER BY id;
```

Expected output should show 5 users with the roles listed above.

## Troubleshooting

### Common Issues

1. **"Access denied" errors**
   - Solution: Ensure you're using correct MySQL credentials
   - Create a dedicated user if needed:
     ```sql
     CREATE USER 'healthcare_user'@'localhost' IDENTIFIED BY 'your_strong_password';
     GRANT ALL PRIVILEGES ON healthcare_db.* TO 'healthcare_user'@'localhost';
     FLUSH PRIVILEGES;
     ```

2. **"Database not found" errors**
   - Solution: Create the database first:
     ```sql
     CREATE DATABASE healthcare_db;
     ```

3. **"Table already exists" errors**
   - Solution: The script uses `IF NOT EXISTS` clauses and is safe to run multiple times
   - To start fresh, drop the database first:
     ```sql
     DROP DATABASE healthcare_db;
     ```

4. **PHP execution issues**
   - Solution: Ensure PHP is installed and in your PATH
   - Check PHP version: `php --version`

### Environment Variables
The system uses these environment variables for database connection:
- `DB_HOST` - Database host (default: localhost)
- `DB_PORT` - Database port (default: 3306)
- `DB_NAME` - Database name (default: healthcare_db)
- `DB_USER` - Database username (default: healthcare_user)
- `DB_PASS` - Database password (default: your_strong_password)

## Role-Based Feature Access

After seeding, the following role-feature mappings will be established:

### Super Admin
- Full access to all modules

### Admin
- User Management (admin)
- System Administration (write)
- Audit & Compliance (read)
- Reports & Analytics (read)

### Doctor
- Patient Management (write)
- Clinical Management (admin)
- Appointment Management (read)

### Receptionist
- Front Desk Operations (admin)
- Patient Management (write)

### Patient
- Appointment Management (read)
- Clinical Management (read)
- Patient Management (read)

### Medical Coordinator
- Appointment Management (admin)
- Patient Management (write)
- Audit & Compliance (read)

## Additional Resources

- [DATABASE_SCHEMA.md](./DATABASE_SCHEMA.md) - Detailed database schema documentation
- [RBAC.md](./RBAC.md) - Role-Based Access Control documentation
- [healthcare_db_setup.sql](file:///d%3A/customprojects/healthcare-app/healthcare_db_setup.sql) - SQL setup script
- [DATABASE_SETUP_INSTRUCTIONS.md](file:///d%3A/customprojects/healthcare-app/DATABASE_SETUP_INSTRUCTIONS.md) - Detailed setup instructions
- [SEEDER_STATUS.md](file:///d%3A/customprojects/healthcare-app/SEEDER_STATUS.md) - Seeder status and troubleshooting guide