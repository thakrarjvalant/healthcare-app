@echo off
title Healthcare Management System Database Setup

echo 🌱 Healthcare Management System Database Setup
echo ==================================================

echo.
echo 🔍 Checking for MySQL...
mysql --version >nul 2>&1
if %errorlevel% == 0 (
    echo ✅ MySQL is installed
) else (
    echo ❌ MySQL not found
    echo 💡 Please install MySQL or XAMPP before running this script
    pause
    exit /b 1
)

echo.
echo 🔄 Creating database and tables...
mysql -u root -e "CREATE DATABASE IF NOT EXISTS healthcare_db;" >nul 2>&1
if %errorlevel% == 0 (
    echo ✅ Database 'healthcare_db' created or already exists
) else (
    echo ❌ Failed to create database
    echo 💡 Make sure MySQL is running and you have sufficient privileges
    pause
    exit /b 1
)

echo.
echo 🔄 Setting up tables and seed data...
mysql -u root < healthcare_db_setup.sql >nul 2>&1
if %errorlevel% == 0 (
    echo ✅ Tables and seed data created successfully
) else (
    echo ❌ Failed to create tables and seed data
    pause
    exit /b 1
)

echo.
echo 🔍 Verifying setup...
mysql -u root -e "USE healthcare_db; SELECT COUNT(*) as user_count FROM users;" >nul 2>&1
if %errorlevel% == 0 (
    echo ✅ Database setup verified successfully
) else (
    echo ❌ Database verification failed
    pause
    exit /b 1
)

echo.
echo 🎉 Database setup completed successfully!
echo.
echo 🔑 Test Credentials:
echo    Admin: admin@example.com / password123
echo    Medical Coordinator: medical.coordinator@example.com / password123
echo    Doctor: jane.smith@example.com / password123
echo    Receptionist: bob.receptionist@example.com / password123
echo    Patient: john.doe@example.com / password123
echo.
echo 💡 You can now start the application
echo.
pause