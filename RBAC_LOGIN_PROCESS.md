# RBAC Login Process Documentation

## Overview

The Healthcare Management System implements a comprehensive RBAC (Role-Based Access Control) system that dynamically fetches user permissions and feature access from database tables during the login process. This ensures that users have the appropriate access levels based on their assigned roles.

## Login Process Flow

### 1. User Authentication
1. User submits login credentials (email/password)
2. Credentials are verified against the user database
3. Upon successful authentication, a JWT token is generated and returned

### 2. RBAC Data Fetching
After successful authentication, the frontend immediately fetches RBAC data through a series of API calls:

#### Step 1: Fetch User Roles
- **Endpoint**: `GET /admin/users/{userId}/roles`
- **Backend Method**: `RoleController.getUserRoles()`
- **Database Query**: 
  ```sql
  SELECT dr.*, GROUP_CONCAT(dp.name SEPARATOR ',') as permissions
  FROM user_dynamic_roles udr
  JOIN dynamic_roles dr ON udr.role_id = dr.id
  LEFT JOIN dynamic_role_permissions drp ON dr.id = drp.role_id AND drp.is_active = 1
  LEFT JOIN dynamic_permissions dp ON drp.permission_id = dp.id
  WHERE udr.user_id = ? AND udr.is_active = 1 AND dr.is_active = 1
  GROUP BY dr.id
  ```
- **Data Retrieved**: All active roles assigned to the user with their basic information

#### Step 2: Fetch Role Permissions
For each role assigned to the user:
- **Endpoint**: `GET /admin/roles/{roleId}/permissions`
- **Backend Method**: `RoleController.getRolePermissions()`
- **Database Query**:
  ```sql
  SELECT dp.* FROM dynamic_role_permissions drp
  JOIN dynamic_permissions dp ON drp.permission_id = dp.id
  WHERE drp.role_id = ? AND drp.is_active = 1 AND dp.is_active = 1
  ORDER BY dp.name
  ```
- **Data Retrieved**: All active permissions associated with each role

#### Step 3: Fetch Role Feature Access
For each role assigned to the user:
- **Endpoint**: `GET /admin/roles/{roleId}/features`
- **Backend Method**: `RoleController.getRoleFeatureAccess()`
- **Database Query**:
  ```sql
  -- Feature access is no longer used
  ```
- **Data Retrieved**: No feature access data is retrieved

### 3. Data Processing and Storage

#### Frontend Processing
1. **Permission Aggregation**: All permissions from all user roles are collected and duplicates are removed
2. **Feature Access Mapping**: Feature access information is organized by role ID
3. **User Object Construction**: A comprehensive user object is created containing:
   - Basic user information (id, name, email, etc.)
   - Assigned roles with details
   - Aggregated permissions list
   - Role-based feature access mappings
4. **State Management**: The user object is stored in React context state
5. **Persistence**: The user object is serialized and stored in localStorage for session persistence

#### Data Structure Example
```javascript
{
  id: 36,
  name: "Super Administrator",
  email: "superadmin@example.com",
  role: "super_admin",
  roles: [
    {
      id: 43,
      name: "super_admin",
      display_name: "Super Administrator",
      // ... other role properties
    }
  ],
  permissions: [
    "system.configure_roles",
    "system.manage_permissions",
    "users.create",
    "users.read",
    // ... all other permissions
  ]
  // featureAccess has been removed
}
```

### 4. Permission Checking

#### Runtime Permission Verification
The system provides multiple methods for checking permissions:

1. **Context Method**: `hasPermission(permissionName)`
   ```javascript
   const { hasPermission } = useContext(AuthContext);
   if (hasPermission('users.create')) {
     // Show create user button
   }
   ```

2. **PermissionGuard Component**: Wraps UI elements that require specific permissions
   ```jsx
   <PermissionGuard requiredPermissions={['users.create']}>
     <button>Create User</button>
   </PermissionGuard>
   ```

3. **FeatureGuard Component**: Controls access to entire feature modules (no longer used)
   ```jsx
   <!-- FeatureGuard is no longer used -->
   ```

## Database Schema

### Core RBAC Tables

#### 1. `dynamic_roles`
Stores role definitions with metadata:
- `id`: Primary key
- `name`: Internal role name (e.g., "super_admin")
- `display_name`: User-friendly role name
- `description`: Role description
- `color`: UI display color
- `icon`: UI display icon
- `is_system_role`: Boolean flag for system roles

#### 2. `dynamic_permissions`
Stores granular permissions:
- `id`: Primary key
- `name`: Permission identifier (e.g., "users.create")
- `display_name`: User-friendly name
- `module`: System module (e.g., "user_management")
- `feature`: Feature within module (e.g., "users")
- `action`: Action type (e.g., "create")
- `resource`: Optional resource specification

#### 3. `user_dynamic_roles`
Maps users to roles:
- `id`: Primary key
- `user_id`: Reference to users table
- `role_id`: Reference to dynamic_roles table
- `is_active`: Boolean flag for active assignments

#### 4. `dynamic_role_permissions`
Maps roles to permissions:
- `id`: Primary key
- `role_id`: Reference to dynamic_roles table
- `permission_id`: Reference to dynamic_permissions table
- `is_active`: Boolean flag for active assignments

#### 5. `feature_modules`
Defines system feature modules:
- `id`: Primary key
- `name`: Module identifier
- `display_name`: User-friendly name
- `description`: Module description

#### 6. `role_feature_access`
Controls module access by role (no longer used)

## Security Considerations

### 1. Data Freshness
- RBAC data is fetched fresh on every login
- Users can manually refresh permissions through the UI
- Backend caches are cleared when permissions change

### 2. Access Control
- All RBAC endpoints require admin/super_admin authentication
- Permission checks are performed on both frontend and backend

### 3. Data Integrity
- Database constraints ensure referential integrity
- Soft deletes maintain audit trails
- Transactional operations for role/permission assignments

## Performance Optimization

### 1. Efficient Queries
- Indexed database columns for fast lookups
- JOIN operations optimized for RBAC relationships
- Result caching for frequently accessed data

### 2. Data Aggregation
- Single query per role for permissions
- Client-side deduplication of permissions
- Lazy loading of non-essential data

### 3. Caching Strategy
- Backend caching of role definitions
- Frontend localStorage for session persistence
- Cache invalidation on permission changes

## Troubleshooting

### Common Issues

1. **Missing Permissions**: Verify user has active role assignments
2. **Permission Denied**: Confirm permission exists in dynamic_permissions table
3. **Role Not Assigned**: Ensure entry exists in user_dynamic_roles table

### Debugging Tools

1. **RBAC Test Component**: Diagnostic tool in super admin dashboard
2. **Database Queries**: Direct verification of table data
3. **API Endpoint Testing**: curl commands to verify endpoints
4. **Browser DevTools**: localStorage inspection and network monitoring

## Best Practices

### 1. Role Design
- Create roles with clear responsibilities
- Use granular permissions for precise control
- Regularly audit role assignments

### 2. Permission Management
- Follow principle of least privilege
- Document permission purposes
- Review permissions periodically

### 3. Feature Access
- Feature access is no longer used

This comprehensive RBAC system ensures that users have exactly the permissions they need to perform their duties while maintaining security and system integrity.