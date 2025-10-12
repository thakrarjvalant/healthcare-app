#### **Enhanced RBAC Tables**
```sql
-- Dynamic roles with metadata
CREATE TABLE dynamic_roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    display_name VARCHAR(100) NOT NULL,
    description TEXT,
    color VARCHAR(7) DEFAULT '#666666',
    icon VARCHAR(50) DEFAULT 'user',
    is_system_role BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_by INT,
    updated_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Enhanced permissions with feature modules
CREATE TABLE dynamic_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    display_name VARCHAR(150) NOT NULL,
    description TEXT,
    module VARCHAR(50) NOT NULL,
    feature VARCHAR(50) NOT NULL,
    action VARCHAR(50) NOT NULL,
    resource VARCHAR(50),
    is_system_permission BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Role-Permission mappings with conditions
CREATE TABLE dynamic_role_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    permission_id INT NOT NULL,
    conditions JSON,
    granted_by INT,
    granted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    revoked_at TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE
);

-- User-Role assignments with contexts
CREATE TABLE user_dynamic_roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    role_id INT NOT NULL,
    context JSON,
    assigned_by INT,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE
);

-- Feature modules configuration
CREATE TABLE feature_modules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    display_name VARCHAR(100) NOT NULL,
    description TEXT,
    icon VARCHAR(50),
    color VARCHAR(7) DEFAULT '#007bff',
    is_core_module BOOLEAN DEFAULT FALSE,
    is_enabled BOOLEAN DEFAULT TRUE,
    configuration JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Role-Feature assignments
CREATE TABLE role_feature_access (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    module_id INT NOT NULL,
    access_level ENUM('none', 'read', 'write', 'admin') DEFAULT 'read',
    custom_permissions JSON,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Audit logs for role and permission changes
CREATE TABLE rbac_audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    action VARCHAR(50) NOT NULL,
    entity_type ENUM('role', 'permission', 'assignment', 'module') NOT NULL,
    entity_id INT NOT NULL,
    old_values JSON,
    new_values JSON,
    performed_by INT,
    performed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    user_agent TEXT
);
```

---

## 🔧 Technical Implementation

### **Frontend Components**

#### **Updated Dashboard Files**
```
📁 frontend/src/pages/
├── 👑 superadmin/Dashboard.js    # Super Admin role configuration
├── 👨‍💼 admin/Dashboard.js (updated)     # Restricted admin functions
└── 🏢 receptionist/Dashboard.js (updated) # Front-desk focus
```

#### **Backend APIs**

#### **Updated Service Files**
```
📁 backend/
├── 🔐 shared/rbac/DynamicRBACManager.php     # Enhanced RBAC system
└── 🗄️ database/
    ├── migrations/009_create_dynamic_rbac_system.php
    └── seeds/DynamicRBACSeeder.php
```

---

## 📊 Permission Matrix

### **Feature Access by Role**

| **Feature Module** | **Super Admin** | **Admin** | **Doctor** | **Receptionist** | **Patient** |
|-------------------|-----------------|-----------|------------|------------------|-------------|
| **User Management** | ✅ Admin | ✅ Admin | ❌ None | ❌ None | ❌ None |
| **Appointment Management** | ✅ Admin | ❌ None | 🔍 Read (Own) | ❌ None | 🔍 Read (Self) |
| **Patient Management** | ✅ Admin | ❌ None | ✏️ Write (Assigned) | ✏️ Write (Basic) | 🔍 Read (Self) |
| **Clinical Management** | ✅ Admin | ❌ None | ✅ **Admin** | ❌ None | 🔍 Read (Self) |
| **Front Desk** | ✅ Admin | ❌ None | ❌ None | ✅ **Admin** | ❌ None |
| **Role Management** | ✅ **Admin** | ❌ None | ❌ None | ❌ None | ❌ None |
| **System Admin** | ✅ Admin | ✏️ Write (Basic) | ❌ None | ❌ None | ❌ None |
| **Audit & Compliance** | ✅ Admin | 🔍 Read | ❌ None | ❌ None | ❌ None |

**Legend:** ✅ Admin = Full Control | ✏️ Write = Modify Access | 🔍 Read = View Only | ❌ None = No Access

---

## 🚀 Implementation Steps

### **Database Setup**
```bash
# 1. Run migrations
php backend/database/migrate.php

# 2. Seed dynamic RBAC data
php backend/database/seeds/DynamicRBACSeeder.php

# 3. Update existing user roles
# (Manual process to assign roles to selected users)
```

### **Frontend Deployment**
```bash
# 1. Install any new dependencies
npm install

# 2. Update routing in App.js (already implemented)
# 3. Test role-based dashboard access
```

---

## 🧪 Testing & Validation

### **Test User Accounts Needed**
```sql
-- Super Admin user
INSERT INTO users (name, email, password, role) 
VALUES ('Super Admin', 'superadmin@healthcare.com', 'hashed_password', 'super_admin');
```

### **Validation Checklist**
- [ ] Super Admin can configure roles dynamically
- [ ] Admin restricted from direct appointment management
- [ ] Receptionist restricted from appointment scheduling
- [ ] Doctor retains clinical access only
- [ ] Patient self-service unchanged
- [ ] All permissions properly enforced
- [ ] Audit logging captures role changes

---

## 🔒 Security & Compliance

### **Enhanced Security Features**
- **Granular Permissions**: Resource-specific access control
- **Audit Trail**: Complete RBAC change logging
- **Session Management**: Role-based session validation
- **Context-Aware Access**: Conditional permissions based on assignment
- **HIPAA Compliance**: Limited patient data access with audit

### **Access Control Rules**
1. **Doctor** access restricted to **assigned patients only**
2. **Receptionist** has **basic patient info** access only
3. **Admin** has **no direct clinical data** access
4. **Super Admin** access is **fully logged** and monitored

---

## 📈 Benefits of New Structure

### **🔐 Enhanced Security**
- **Role Isolation**: Clear separation of duties
- **Minimal Privileges**: Each role has only required permissions
- **Audit Compliance**: Complete activity tracking
- **Dynamic Configuration**: Flexible permission management

### **👥 User Experience**
- **Specialized Dashboards**: Role-optimized interfaces
- **Reduced Complexity**: Simplified user interfaces per role
- **Clear Responsibilities**: Defined scope for each role
- **Improved Workflows**: Optimized for role-specific tasks

---

## 🔄 Migration Path

### **Existing Users**
1. **Current Admins**: Choose between Super Admin or restricted Admin role
2. **Current Receptionists**: Training on front-desk focus
3. **Doctors & Patients**: No changes required

### **System Configuration**
1. Deploy database changes
2. Update frontend routing
3. Configure new role permissions
4. Train users on new interfaces
5. Monitor and adjust permissions as needed

---

## 💡 Future Enhancements

### **Planned Features**
- **Advanced Role Management**: More granular role configuration options
- **Enhanced Analytics**: Better reporting and monitoring capabilities
- **Improved User Interface**: Enhanced dashboard components
- **Additional Security Features**: More robust access control mechanisms