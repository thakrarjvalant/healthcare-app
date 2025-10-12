# Healthcare App Documentation Index

This index provides a comprehensive overview of all documentation available for the Healthcare Management System.

## 📚 Documentation Categories

### 🎯 Features & Functionality
- [DASHBOARD_FEATURES.md](features/DASHBOARD_FEATURES.md) - Complete dashboard feature documentation by role
- [TRANSFERRED_FEATURES_SUMMARY.md](features/TRANSFERRED_FEATURES_SUMMARY.md) - Summary of features transferred between roles
- [FEATURE_TRANSFER_CORRECTION_SUMMARY.md](features/FEATURE_TRANSFER_CORRECTION_SUMMARY.md) - Corrections to feature transfer documentation
- [Functionality_Documentation.md](features/Functionality_Documentation.md) - General functionality overview

### 👥 Roles & Permissions
- [TEST_CREDENTIALS.md](roles/TEST_CREDENTIALS.md) - Test user credentials for each role
- [ROLE_RESTRUCTURING_DOCUMENTATION.md](roles/ROLE_RESTRUCTURING_DOCUMENTATION.md) - Documentation of role restructuring changes

### 🔄 Updates & Changes
- [CHANGELOG.md](updates/CHANGELOG.md) - Complete changelog of all versions and updates
- [DATABASE_SEEDERS_FINAL_REPORT.md](updates/DATABASE_SEEDERS_FINAL_REPORT.md) - Database seeding implementation report
- [ENHANCEMENT_REPORT.md](updates/ENHANCEMENT_REPORT.md) - System enhancement documentation
- [IMPLEMENTATION_SUMMARY.md](updates/IMPLEMENTATION_SUMMARY.md) - Implementation summary of major features
- [LOGIN_FIXES_DOCUMENTATION.md](updates/LOGIN_FIXES_DOCUMENTATION.md) - Documentation of login system fixes
- [RECENT_CHANGES_SUMMARY.md](updates/RECENT_CHANGES_SUMMARY.md) - Summary of recent changes

### 🚀 Getting Started
- [SETUP_GUIDE.md](SETUP_GUIDE.md) - Complete setup instructions
- [TROUBLESHOOTING.md](TROUBLESHOOTING.md) - Common issues and solutions
- [DOCKER_BUILD_INSTRUCTIONS.md](DOCKER_BUILD_INSTRUCTIONS.md) - Docker container build instructions

### 🏗️ Technical Documentation

#### API Documentation
- [api-gateway.md](api/api-gateway.md) - API Gateway documentation
- [appointment-service-api.md](api/appointment-service-api.md) - Appointment Service API
- [billing-service-api.md](api/billing-service-api.md) - Billing Service API
- [clinical-service-api.md](api/clinical-service-api.md) - Clinical Service API
- [notification-service-api.md](api/notification-service-api.md) - Notification Service API
- [storage-service-api.md](api/storage-service-api.md) - Storage Service API
- [user-service-api.md](api/user-service-api.md) - User Service API

#### Architecture
- [system-architecture.md](architecture/system-architecture.md) - System architecture overview

#### Developer Guides
- [getting-started.md](developer-guides/getting-started.md) - Developer getting started guide
- [appointment-service.md](developer-guides/appointment-service.md) - Appointment Service developer guide
- [clinical-service.md](developer-guides/clinical-service.md) - Clinical Service developer guide

#### User Guides
- [admin-user-guide.md](user-guides/admin-user-guide.md) - Admin user guide
- [doctor-user-guide.md](user-guides/doctor-user-guide.md) - Doctor user guide
- [patient-user-guide.md](user-guides/patient-user-guide.md) - Patient user guide
- [receptionist-user-guide.md](user-guides/receptionist-user-guide.md) - Receptionist user guide

### ⚡ Performance & Optimization
- [FRONTEND_BUILD_OPTIMIZATION.md](FRONTEND_BUILD_OPTIMIZATION.md) - Frontend build optimization techniques

## 🔍 Quick Search

### By User Role
- **Admin**: [admin-user-guide.md](user-guides/admin-user-guide.md), [DASHBOARD_FEATURES.md](features/DASHBOARD_FEATURES.md)
- **Doctor**: [doctor-user-guide.md](user-guides/doctor-user-guide.md), [DASHBOARD_FEATURES.md](features/DASHBOARD_FEATURES.md)
- **Patient**: [patient-user-guide.md](user-guides/patient-user-guide.md), [DASHBOARD_FEATURES.md](features/DASHBOARD_FEATURES.md)
- **Receptionist**: [receptionist-user-guide.md](user-guides/receptionist-user-guide.md), [DASHBOARD_FEATURES.md](features/DASHBOARD_FEATURES.md)
- **Medical Coordinator**: [DASHBOARD_FEATURES.md](features/DASHBOARD_FEATURES.md), [TRANSFERRED_FEATURES_SUMMARY.md](features/TRANSFERRED_FEATURES_SUMMARY.md)

### By System Component
- **Authentication**: [user-service-api.md](api/user-service-api.md), [LOGIN_FIXES_DOCUMENTATION.md](updates/LOGIN_FIXES_DOCUMENTATION.md)
- **Appointments**: [appointment-service-api.md](api/appointment-service-api.md), [DASHBOARD_FEATURES.md](features/DASHBOARD_FEATURES.md)
- **Billing**: [billing-service-api.md](api/billing-service-api.md), [TRANSFERRED_FEATURES_SUMMARY.md](features/TRANSFERRED_FEATURES_SUMMARY.md)
- **Clinical**: [clinical-service-api.md](api/clinical-service-api.md), [DASHBOARD_FEATURES.md](features/DASHBOARD_FEATURES.md)
- **Database**: [DATABASE_SEEDERS_FINAL_REPORT.md](updates/DATABASE_SEEDERS_FINAL_REPORT.md), [system-architecture.md](architecture/system-architecture.md)

### By Development Task
- **Setup**: [SETUP_GUIDE.md](SETUP_GUIDE.md), [getting-started.md](developer-guides/getting-started.md)
- **API Development**: [api/](api/) directory
- **Frontend Development**: [FRONTEND_BUILD_OPTIMIZATION.md](FRONTEND_BUILD_OPTIMIZATION.md), [frontend/README.md](../frontend/README.md)
- **Backend Development**: [backend/README.md](../backend/README.md), [developer-guides/](developer-guides/) directory
- **Database Changes**: [migrations/](../backend/database/migrations/), [seeds/](../backend/database/seeds/)

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

## 🔄 Recent Updates

For the most recent changes to the system, see:
- [RECENT_CHANGES_SUMMARY.md](updates/RECENT_CHANGES_SUMMARY.md) - Summary of recent changes
- [CHANGELOG.md](updates/CHANGELOG.md) - Complete version history

## 📞 Support

For issues not covered in this documentation:
1. Check the [TROUBLESHOOTING.md](TROUBLESHOOTING.md) guide
2. Review the [CHANGELOG.md](updates/CHANGELOG.md) for recent changes
3. Contact the development team