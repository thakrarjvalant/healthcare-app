# Dynamic RBAC Implementation Guide

> **NOTE**: This guide has been superseded by the [consolidated documentation](docs/HEALTHCARE_APP_DOCUMENTATION.md). Please refer to the new documentation for the most up-to-date information.

## Overview

The Healthcare Management System implements a dynamic Role-Based Access Control (RBAC) system that provides fine-grained permission management and feature access control. This system allows administrators to create custom roles, assign granular permissions, and control access to system features.

## System Architecture

### Core Components

1. **Database Schema**: Enhanced RBAC tables with metadata and relationships
2. **Backend API**: RESTful endpoints for role and permission management
3. **Frontend UI**: Dashboards with permission-aware components
4. **Authentication Context**: Centralized permission checking

### Database Structure

The dynamic RBAC system uses the following tables:

- `dynamic_roles`: Roles with metadata (name, display_name, description, color, icon)
- `dynamic_permissions`: Granular permissions with module/feature/action structure
- `dynamic_role_permissions`: Role-permission mappings
- `user_dynamic_roles`: User-role assignments
- `feature_modules`: System feature modules

## API Endpoints

### Role Management
- `GET /admin/roles` - Get all dynamic roles
- `POST /admin/roles` - Create new role
- `PUT /admin/roles/{id}` - Update existing role
- `DELETE /admin/roles/{id}` - Delete role

### Permission Management
- `GET /admin/permissions` - Get all permissions
- `GET /admin/roles/{id}/permissions` - Get role permissions
- `POST /admin/roles/{id}/permissions` - Assign permission to role
- `DELETE /admin/roles/{id}/permissions/{permission_id}` - Remove permission from role

### Feature Access Management
- `GET /admin/modules` - Get feature modules
- `GET /admin/roles/{id}/features` - Get role feature access
- `POST /admin/roles/{id}/features` - Update role feature access

### User Role Management
- `GET /admin/users/{id}/roles` - Get user roles
- `POST /admin/users/{id}/roles` - Assign role to user
- `DELETE /admin/users/{id}/roles/{role_id}` - Remove role from user

## Frontend Implementation

### Permission Checking

The system provides permission checking through the `hasPermission` method:

```javascript
const { hasPermission } = useContext(AuthContext);
if (hasPermission('users.create')) {
  // Show create user functionality
}
```

### Feature Access Control

The system also provides feature-level access control:

1. **FeatureGuard Component**: Controls access to entire features/modules
```jsx
<FeatureGuard requiredFeature="user_management" requiredAccessLevel="admin">
  <UserManagementDashboard />
</FeatureGuard>
```

### Role-Based Dashboards

Each user role has a dedicated dashboard with appropriate functionality:

- **Super Administrator**: Full system control with role management
- **Administrator**: User management and system oversight
- **Doctor**: Clinical functionality
- **Receptionist**: Front desk operations
- **Patient**: Self-service features
- **Medical Coordinator**: Patient assignment functionality

## Implementation Details

### AuthContext

The authentication context provides centralized permission management:

```javascript
const AuthContext = {
  user: { /* user data with roles and permissions */ },
  hasPermission: (permission) => { /* check if user has permission */ },
  hasAnyPermission: (permissions) => { /* check if user has any of the permissions */ },
  refreshPermissions: () => { /* refresh user permissions from backend */ }
}
```

### API Service

The API service provides methods for all RBAC operations:

```javascript
class ApiService {
  static async getDynamicRoles() { /* fetch roles */ }
  static async getAllPermissions() { /* fetch permissions */ }
  static async getRolePermissions(roleId) { /* fetch role permissions */ }
  static async assignPermissionToRole(roleId, permissionId) { /* assign permission */ }
  static async removePermissionFromRole(roleId, permissionId) { /* remove permission */ }
  // ... other methods
}
```

## Role Definitions

### Super Administrator
- Full system access
- Role configuration capabilities
- Feature allocation control
- System administration

### Administrator
- User management
- Audit log access
- Limited system configuration

### Doctor
- Patient clinical data access
- Medical record management
- Appointment viewing (own)

### Receptionist
- Front desk operations
- Patient registration
- Appointment management
- Billing operations

### Patient
- Self-service features
- Own appointment management
- Personal health records

### Medical Coordinator
- Patient assignment to clinicians
- Limited patient history access

## Best Practices

### Security
1. Always verify permissions on both frontend and backend
2. Use permission guards for UI elements
3. Implement proper error handling
4. Log RBAC-related activities

### Performance
1. Cache permission data appropriately
2. Minimize API calls during permission checks
3. Use efficient database queries
4. Implement pagination for large datasets

### Maintenance
1. Regularly audit role assignments
2. Review permission mappings
3. Monitor feature access usage
4. Update documentation when making changes

## Testing

The system includes comprehensive testing for:

1. **API Endpoints**: Verify all RBAC endpoints work correctly
2. **Permission Checks**: Ensure proper access control
3. **Feature Access**: Validate feature-level restrictions
4. **User Flows**: Test complete user journeys with different roles

## Troubleshooting

### Common Issues

1. **Permission Not Working**: Verify user has role assigned and role has permission
2. **Feature Not Visible**: Check role feature access configuration
3. **API Errors**: Ensure proper authentication and authorization headers
4. **UI Elements Missing**: Confirm PermissionGuard/FeatureGuard configurations

### Debugging Tools

1. **RBAC Test Component**: Diagnostic tool for permission checking
2. **API Endpoint Testing**: Direct endpoint verification
3. **Database Queries**: Direct database verification
4. **Log Analysis**: Audit trail review

## Future Enhancements

1. **Dynamic Permission Creation**: Allow creation of custom permissions
2. **Role Hierarchy**: Implement role inheritance
3. **Time-based Access**: Add temporal permission constraints
4. **Conditional Permissions**: Context-aware permission checking
5. **Advanced Auditing**: Enhanced RBAC activity tracking

---

## 📖 Updated Documentation

For the most current and comprehensive documentation, please refer to:
- [Healthcare App Documentation](docs/HEALTHCARE_APP_DOCUMENTATION.md) - Complete system documentation
- [Feature Status Report](docs/FEATURE_STATUS_REPORT.md) - Current feature implementation status