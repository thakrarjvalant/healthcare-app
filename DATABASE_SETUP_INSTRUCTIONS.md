# Healthcare Management System - Database Setup Instructions

## Overview
This document provides instructions for setting up the database for the Healthcare Management System. The system requires a MySQL database with specific tables and seed data.

## Prerequisites
- MySQL 5.7 or higher installed
- Access to MySQL command line or a database management tool (phpMyAdmin, MySQL Workbench, etc.)

## Database Setup Options

### Option 1: Using MySQL Command Line

1. Open Command Prompt or Terminal
2. Navigate to the project directory:
   ```
   cd d:\customprojects\healthcare-app
   ```

3. Execute the SQL file:
   ```
   mysql -u root -p < healthcare_db_setup.sql
   ```
   
   Enter your MySQL root password when prompted.

### Option 2: Using phpMyAdmin or MySQL Workbench

1. Open your database management tool
2. Create a new database named `healthcare_db`
3. Select the `healthcare_db` database
4. Import the `healthcare_db_setup.sql` file

### Option 3: Manual Execution

If you prefer to run the commands manually:

1. Connect to MySQL:
   ```
   mysql -u root -p
   ```

2. Create and use the database:
   ```sql
   CREATE DATABASE IF NOT EXISTS healthcare_db;
   USE healthcare_db;
   ```

3. Create the required tables by copying and pasting the CREATE TABLE statements from the [healthcare_db_setup.sql](file:///d%3A/customprojects/healthcare-app/healthcare_db_setup.sql) file

4. Insert the seed data by copying and pasting the INSERT statements

## Expected Results

After successful setup, you should have:

1. A database named `healthcare_db`
2. Six tables:
   - users
   - dynamic_roles
   - feature_modules
   - dynamic_permissions
   - dynamic_role_permissions
3. Five users in the users table:
   - Admin User (admin@example.com)
   - Dr. Jane Smith (doctor)
   - Receptionist Bob (receptionist)
   - John Doe (patient)
   - Medical Coordinator (medical.coordinator@example.com)

## Test Credentials

- **Admin User**: admin@example.com / password123
- **Medical Coordinator**: medical.coordinator@example.com / password123
- **Doctor**: jane.smith@example.com / password123
- **Receptionist**: bob.receptionist@example.com / password123
- **Patient**: john.doe@example.com / password123

## Troubleshooting

### If you get "Access denied" errors:
- Make sure you're using the correct MySQL username and password
- If you don't have root access, create a user with sufficient privileges:
  ```sql
  CREATE USER 'healthcare_user'@'localhost' IDENTIFIED BY 'your_strong_password';
  GRANT ALL PRIVILEGES ON healthcare_db.* TO 'healthcare_user'@'localhost';
  FLUSH PRIVILEGES;
  ```

### If tables already exist:
- The script uses `IF NOT EXISTS` clauses, so it's safe to run multiple times
- If you want to start fresh, drop the database first:
  ```sql
  DROP DATABASE healthcare_db;
  ```

## Verification

To verify the setup, run these queries:

```sql
USE healthcare_db;
SELECT COUNT(*) as user_count FROM users;
SELECT id, name, email, role FROM users ORDER BY id;
```

You should see 5 users with the roles mentioned above.