# Docker Troubleshooting and Role Verification Guide

## Overview
This guide addresses the Docker environment issues encountered during the role verification process and provides steps to complete the verification once Docker is operational.

## Current Status
- ✅ System architecture and components are properly configured
- ✅ All required files and configurations exist
- ✅ Database seeding mechanism is properly set up
- ✅ All test users and roles are defined
- ❌ Docker containers cannot be started due to environment issues
- ❌ Cannot verify actual role logins and features

## Docker Troubleshooting Steps

### 1. Verify Docker Installation
```bash
# Check Docker version
docker --version

# Check Docker Compose version
docker-compose --version

# Check if Docker daemon is running
docker info
```

### 2. Restart Docker Service
- Open Docker Desktop application
- Stop Docker Desktop if running
- Wait 30 seconds
- Start Docker Desktop again
- Wait for the whale icon to become stable

### 3. Clear Docker Cache (if needed)
```bash
# Remove all unused containers, networks, images
docker system prune -a

# Remove volumes (WARNING: This will remove all data)
docker volume prune
```

### 4. Try Alternative Docker Commands
Instead of using PowerShell, try running from Command Prompt (cmd):

```cmd
cd d:\customprojects\healthcare-app
docker-compose down
docker-compose up -d --build
```

### 5. Check Port Availability
```bash
# Check if required ports are free
netstat -an | findstr :3000
netstat -an | findstr :8000
netstat -an | findstr :3306
```

## Steps to Complete Role Verification

### Phase 1: Docker Environment Resolution
1. Ensure Docker Desktop is fully running
2. Verify that no other applications are using the required ports
3. Run the following commands in Command Prompt (not PowerShell):
   ```cmd
   cd d:\customprojects\healthcare-app
   docker-compose down
   docker-compose up -d --build
   ```
4. Wait 2-3 minutes for all services to initialize
5. Check running containers:
   ```cmd
   docker ps
   ```

### Phase 2: Application Access Verification
1. Access the application at http://localhost:3000
2. Verify the login page loads correctly
3. Check that the API endpoints are accessible:
   - http://localhost:8000/api/health (API Gateway)
   - http://localhost:8001/api/users/health (User Service)

### Phase 3: Role Login Verification
Test each role with the following credentials:

| Role | Email | Password | Expected Dashboard |
|------|-------|----------|-------------------|
| Super Admin | super.admin@example.com | password123 | Super Admin Dashboard |
| Admin | admin@example.com | password123 | Admin Dashboard |
| Doctor | jane.smith@example.com | password123 | Doctor Dashboard |
| Receptionist | bob.receptionist@example.com | password123 | Receptionist Dashboard |
| Patient | john.doe@example.com | password123 | Patient Dashboard |
| Medical Coordinator | medical.coordinator@example.com | password123 | Medical Coordinator Dashboard |

### Phase 4: Feature Access Verification
For each role, verify access to role-specific features:

#### Super Admin Features:
- [ ] All admin features plus role configuration
- [ ] Dynamic role creation and modification
- [ ] Permission matrix management
- [ ] Feature allocation system

#### Admin Features:
- [ ] User management system
- [ ] System configuration
- [ ] Reports and analytics
- [ ] Audit logs
- [ ] Escalation management
- [ ] Role management

#### Doctor Features:
- [ ] Advanced appointment management
- [ ] Treatment plan management
- [ ] Patient reports system
- [ ] Medical records access

#### Receptionist Features:
- [ ] Comprehensive appointment management
- [ ] Patient registration system
- [ ] Patient check-in system
- [ ] Billing and payment processing

#### Patient Features:
- [ ] Advanced appointment booking
- [ ] Medical records access
- [ ] Personal reports dashboard

#### Medical Coordinator Features:
- [ ] Appointment management system
- [ ] Patient assignment management
- [ ] Limited patient history access

## Expected Outcomes
Once Docker is running properly:
1. All six roles should successfully authenticate
2. Each role should be redirected to the appropriate dashboard
3. Each role should see only the features they have permission to access
4. RBAC system should properly enforce access controls
5. All UI elements should render correctly for each role

## Troubleshooting Common Issues

### If Login Fails
- Clear browser cache and cookies
- Check that database seeding completed successfully
- Verify that user accounts exist in the database

### If Features Are Missing
- Check role permissions in the database
- Verify RBAC configuration
- Confirm that the correct dashboard is loading

### If API Calls Fail
- Verify that API gateway is accessible
- Check that backend services are running
- Confirm database connectivity

## Verification Checklist

### Before Testing:
- [ ] Docker containers are running
- [ ] All services are healthy
- [ ] Database is seeded with test data
- [ ] Frontend is accessible

### During Testing:
- [ ] Super Admin login and features verified
- [ ] Admin login and features verified
- [ ] Doctor login and features verified
- [ ] Receptionist login and features verified
- [ ] Patient login and features verified
- [ ] Medical Coordinator login and features verified

### After Testing:
- [ ] All role-specific features are working correctly
- [ ] Unauthorized access is properly blocked
- [ ] RBAC system functions as expected
- [ ] Documentation is updated if needed

## Next Steps
1. Resolve Docker environment issues using the steps above
2. Complete the role verification process
3. Document any issues found during testing
4. Address any RBAC or feature access discrepancies
5. Perform final acceptance testing