# Healthcare App Documentation

Welcome to the comprehensive documentation for the Healthcare Management System. This documentation is organized into several sections to help you find the information you need quickly.

## 📚 Documentation Structure

### 📖 Consolidated Documentation
- [HEALTHCARE_APP_DOCUMENTATION.md](HEALTHCARE_APP_DOCUMENTATION.md) - Complete system documentation in a single file

### 🚀 Getting Started
- [SETUP_GUIDE.md](SETUP_GUIDE.md) - Complete setup instructions for development and production environments
- [TROUBLESHOOTING.md](TROUBLESHOOTING.md) - Common issues and solutions
- [DOCKER_BUILD_INSTRUCTIONS.md](DOCKER_BUILD_INSTRUCTIONS.md) - Docker container build and deployment instructions

### 🎯 Features & Functionality
- [features/DASHBOARD_FEATURES.md](features/DASHBOARD_FEATURES.md) - Detailed documentation of all dashboard features by role
- [features/TRANSFERRED_FEATURES_SUMMARY.md](features/TRANSFERRED_FEATURES_SUMMARY.md) - Summary of features transferred between roles
- [features/FEATURE_TRANSFER_CORRECTION_SUMMARY.md](features/FEATURE_TRANSFER_CORRECTION_SUMMARY.md) - Corrections to feature transfer documentation
- [features/Functionality_Documentation.md](features/Functionality_Documentation.md) - General functionality overview

### 👥 Roles & Permissions
- [roles/TEST_CREDENTIALS.md](roles/TEST_CREDENTIALS.md) - Test user credentials for each role
- [roles/ROLE_RESTRUCTURING_DOCUMENTATION.md](roles/ROLE_RESTRUCTURING_DOCUMENTATION.md) - Documentation of role restructuring changes

### 🔄 Updates & Changes
- [updates/CHANGELOG.md](updates/CHANGELOG.md) - Complete changelog of all versions and updates
- [updates/DATABASE_SEEDERS_FINAL_REPORT.md](updates/DATABASE_SEEDERS_FINAL_REPORT.md) - Database seeding implementation report
- [updates/ENHANCEMENT_REPORT.md](updates/ENHANCEMENT_REPORT.md) - System enhancement documentation
- [updates/IMPLEMENTATION_SUMMARY.md](updates/IMPLEMENTATION_SUMMARY.md) - Implementation summary of major features
- [updates/LOGIN_FIXES_DOCUMENTATION.md](updates/LOGIN_FIXES_DOCUMENTATION.md) - Documentation of login system fixes

### 🏗️ Technical Documentation

#### API Documentation
- [api/](api/) - Complete API documentation for all microservices

#### Architecture
- [architecture/system-architecture.md](architecture/system-architecture.md) - System architecture overview

#### Developer Guides
- [developer-guides/](developer-guides/) - Guides for developers working on the system

#### User Guides
- [user-guides/](user-guides/) - Guides for end users of different roles

### ⚡ Performance & Optimization
- [FRONTEND_BUILD_OPTIMIZATION.md](FRONTEND_BUILD_OPTIMIZATION.md) - Frontend build optimization techniques

## 📖 Quick Links

### For Developers
- [Developer Getting Started Guide](developer-guides/getting-started.md)
- [User Service API](api/user-service-api.md)
- [Appointment Service Documentation](developer-guides/appointment-service.md)

### For Administrators
- [Admin User Guide](user-guides/admin-user-guide.md)
- [System Architecture](architecture/system-architecture.md)
- [Test Credentials](roles/TEST_CREDENTIALS.md)

### For End Users
- [Patient User Guide](user-guides/patient-user-guide.md)
- [Doctor User Guide](user-guides/doctor-user-guide.md)
- [Receptionist User Guide](user-guides/receptionist-user-guide.md)

## 📊 System Overview

The Healthcare Management System is a microservices-based application with the following key components:

1. **Frontend** - React-based user interface
2. **API Gateway** - Central routing for all API requests
3. **Microservices** - Independent services for different functions:
   - User Service
   - Appointment Service
   - Clinical Service
   - Billing Service
   - Notification Service
   - Storage Service
   - Admin UI
4. **Database** - MySQL database for data persistence

## 🔧 Key Features

### Role-Based Access Control
The system implements a comprehensive RBAC system with dynamic roles and permissions.

### Feature Modules
- User Management
- Appointment Management
- Patient Management
- Clinical Management
- Billing & Payments
- Front Desk Operations
- System Administration
- Role Management
- Audit & Compliance
- Reports & Analytics

## 🆘 Support

For issues not covered in this documentation, please:
1. Check the [TROUBLESHOOTING.md](TROUBLESHOOTING.md) guide
2. Review the [CHANGELOG.md](updates/CHANGELOG.md) for recent changes
3. Contact the development team