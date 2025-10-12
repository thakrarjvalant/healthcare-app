<?php

use Database\DatabaseConnection;

/**
 * 🎭 Seed Enhanced Dynamic RBAC data
 * This seeder populates the new dynamic role system with all roles and permissions
 */
class DynamicRBACSeeder
{
    private $db;

    public function __construct()
    {
        $this->db = DatabaseConnection::getInstance();
    }

    public function seed()
    {
        // 🎭 Seed Dynamic Roles with proper metadata
        $roles = [
            [
                'name' => 'super_admin',
                'display_name' => 'Super Administrator',
                'description' => 'System super administrator with full access and dynamic role configuration',
                'color' => '#dc3545',
                'icon' => 'crown',
                'is_system_role' => true
            ],
            [
                'name' => 'admin', 
                'display_name' => 'Administrator',
                'description' => 'System administrator with user management and audit oversight',
                'color' => '#007bff',
                'icon' => 'shield-alt',
                'is_system_role' => true
            ],
            [
                'name' => 'doctor',
                'display_name' => 'Doctor',
                'description' => 'Medical professional with clinical duties and patient care',
                'color' => '#17a2b8',
                'icon' => 'user-md',
                'is_system_role' => true
            ],
            [
                'name' => 'receptionist',
                'display_name' => 'Receptionist',
                'description' => 'Front desk operations and patient registration',
                'color' => '#ffc107',
                'icon' => 'concierge-bell',
                'is_system_role' => true
            ],
            [
                'name' => 'patient',
                'display_name' => 'Patient',
                'description' => 'Healthcare recipient with personal health record access',
                'color' => '#6c757d',
                'icon' => 'user',
                'is_system_role' => true
            ],
            [
                'name' => 'medical_coordinator',
                'display_name' => 'Medical Coordinator',
                'description' => 'Manages all appointment scheduling, rescheduling, and cancellations system-wide, resolves slot conflicts, oversees patient assignment to clinicians, and acts as liaison between clinical and administrative teams with limited audited access to patient histories',
                'color' => '#20c997',
                'icon' => 'user-clock',
                'is_system_role' => true
            ]
        ];

        foreach ($roles as $role) {
            $stmt = $this->db->prepare("INSERT IGNORE INTO dynamic_roles (name, display_name, description, color, icon, is_system_role) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$role['name'], $role['display_name'], $role['description'], $role['color'], $role['icon'], $role['is_system_role']]);
        }

        // 🛡️ Seed Feature Modules
        $modules = [
            [
                'name' => 'user_management',
                'display_name' => 'User Management',
                'description' => 'Create, update, delete users and manage user accounts',
                'icon' => 'users',
                'color' => '#007bff',
                'is_core_module' => true
            ],
            [
                'name' => 'appointment_management',
                'display_name' => 'Appointment Management', 
                'description' => 'Schedule, reschedule, cancel appointments and manage scheduling',
                'icon' => 'calendar',
                'color' => '#28a745',
                'is_core_module' => true
            ],
            [
                'name' => 'patient_management',
                'display_name' => 'Patient Management',
                'description' => 'Manage patient records, history, and personal information',
                'icon' => 'clipboard-user',
                'color' => '#17a2b8',
                'is_core_module' => true
            ],
            [
                'name' => 'clinical_management',
                'display_name' => 'Clinical Management',
                'description' => 'Medical records, treatment plans, clinical notes',
                'icon' => 'stethoscope',
                'color' => '#dc3545',
                'is_core_module' => true
            ],
            [
                'name' => 'billing_payments',
                'display_name' => 'Billing & Payments',
                'description' => 'Process payments, manage invoices, insurance claims',
                'icon' => 'credit-card',
                'color' => '#ffc107',
                'is_core_module' => true
            ],
            [
                'name' => 'front_desk',
                'display_name' => 'Front Desk Operations',
                'description' => 'Patient check-in, queue management, registration',
                'icon' => 'concierge-bell',
                'color' => '#6f42c1',
                'is_core_module' => true
            ],
            [
                'name' => 'system_admin',
                'display_name' => 'System Administration', 
                'description' => 'System settings, monitoring, configuration',
                'icon' => 'cogs',
                'color' => '#343a40',
                'is_core_module' => true
            ],
            [
                'name' => 'role_management',
                'display_name' => 'Role Management',
                'description' => 'Dynamic role configuration, permissions, RBAC',
                'icon' => 'user-cog',
                'color' => '#e83e8c',
                'is_core_module' => true
            ],
            [
                'name' => 'audit_compliance',
                'display_name' => 'Audit & Compliance',
                'description' => 'Audit logs, compliance reporting, security monitoring',
                'icon' => 'shield-check',
                'color' => '#20c997',
                'is_core_module' => true
            ],
            [
                'name' => 'reports_analytics',
                'display_name' => 'Reports & Analytics',
                'description' => 'Generate reports, analytics, performance metrics',
                'icon' => 'chart-bar',
                'color' => '#fd7e14',
                'is_core_module' => true
            ]
        ];

        foreach ($modules as $module) {
            $stmt = $this->db->prepare("INSERT IGNORE INTO feature_modules (name, display_name, description, icon, color, is_core_module) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$module['name'], $module['display_name'], $module['description'], $module['icon'], $module['color'], $module['is_core_module']]);
        }

        // 🔑 Seed Dynamic Permissions with enhanced structure
        $permissions = [
            // Super Admin permissions
            ['name' => 'system.configure_roles', 'display_name' => 'Configure Dynamic Roles', 'module' => 'role_management', 'feature' => 'role_config', 'action' => 'configure'],
            ['name' => 'system.manage_permissions', 'display_name' => 'Manage Permissions Matrix', 'module' => 'role_management', 'feature' => 'permissions', 'action' => 'manage'],
            ['name' => 'system.feature_allocation', 'display_name' => 'Allocate Features to Roles', 'module' => 'role_management', 'feature' => 'features', 'action' => 'allocate'],
            
            // User Management
            ['name' => 'users.create', 'display_name' => 'Create Users', 'module' => 'user_management', 'feature' => 'users', 'action' => 'create'],
            ['name' => 'users.read', 'display_name' => 'View Users', 'module' => 'user_management', 'feature' => 'users', 'action' => 'read'],
            ['name' => 'users.update', 'display_name' => 'Update Users', 'module' => 'user_management', 'feature' => 'users', 'action' => 'update'],
            ['name' => 'users.delete', 'display_name' => 'Delete Users', 'module' => 'user_management', 'feature' => 'users', 'action' => 'delete'],
            ['name' => 'users.assign_roles', 'display_name' => 'Assign User Roles', 'module' => 'user_management', 'feature' => 'roles', 'action' => 'assign'],

            // Doctor permissions (Clinical focus)
            ['name' => 'patients.clinical_read', 'display_name' => 'View Assigned Patients', 'module' => 'patient_management', 'feature' => 'patients', 'action' => 'read', 'resource' => 'assigned'],
            ['name' => 'patients.clinical_update', 'display_name' => 'Update Patient Clinical Info', 'module' => 'patient_management', 'feature' => 'patients', 'action' => 'update', 'resource' => 'clinical'],
            ['name' => 'medical_records.create', 'display_name' => 'Create Medical Records', 'module' => 'clinical_management', 'feature' => 'records', 'action' => 'create'],
            ['name' => 'medical_records.read', 'display_name' => 'View Medical Records', 'module' => 'clinical_management', 'feature' => 'records', 'action' => 'read'],
            ['name' => 'medical_records.update', 'display_name' => 'Update Medical Records', 'module' => 'clinical_management', 'feature' => 'records', 'action' => 'update'],
            ['name' => 'appointments.doctor_read', 'display_name' => 'View Own Appointments', 'module' => 'appointment_management', 'feature' => 'appointments', 'action' => 'read', 'resource' => 'own'],
            ['name' => 'appointments.doctor_update', 'display_name' => 'Update Own Appointments', 'module' => 'appointment_management', 'feature' => 'appointments', 'action' => 'update', 'resource' => 'own'],
            ['name' => 'treatment_plans.create', 'display_name' => 'Create Treatment Plans', 'module' => 'clinical_management', 'feature' => 'treatment', 'action' => 'create'],
            ['name' => 'prescriptions.create', 'display_name' => 'Write Prescriptions', 'module' => 'clinical_management', 'feature' => 'prescriptions', 'action' => 'create'],

            // Receptionist permissions (Front desk focus)
            ['name' => 'front_desk.checkin', 'display_name' => 'Patient Check-in', 'module' => 'front_desk', 'feature' => 'checkin', 'action' => 'process'],
            ['name' => 'front_desk.registration', 'display_name' => 'Patient Registration', 'module' => 'front_desk', 'feature' => 'registration', 'action' => 'create'],
            ['name' => 'front_desk.queue_management', 'display_name' => 'Manage Patient Queue', 'module' => 'front_desk', 'feature' => 'queue', 'action' => 'manage'],
            ['name' => 'patients.basic_create', 'display_name' => 'Create Basic Patient Records', 'module' => 'patient_management', 'feature' => 'patients', 'action' => 'create', 'resource' => 'basic'],
            ['name' => 'patients.basic_read', 'display_name' => 'View Basic Patient Info', 'module' => 'patient_management', 'feature' => 'patients', 'action' => 'read', 'resource' => 'basic'],
            // Appointment Management permissions for Receptionist
            ['name' => 'appointments.create', 'display_name' => 'Create Appointments', 'module' => 'appointment_management', 'feature' => 'appointments', 'action' => 'create'],
            ['name' => 'appointments.read', 'display_name' => 'View All Appointments', 'module' => 'appointment_management', 'feature' => 'appointments', 'action' => 'read'],
            ['name' => 'appointments.update', 'display_name' => 'Update All Appointments', 'module' => 'appointment_management', 'feature' => 'appointments', 'action' => 'update'],
            ['name' => 'appointments.delete', 'display_name' => 'Cancel Appointments', 'module' => 'appointment_management', 'feature' => 'appointments', 'action' => 'delete'],
            ['name' => 'appointments.resolve_conflicts', 'display_name' => 'Resolve Appointment Conflicts', 'module' => 'appointment_management', 'feature' => 'conflicts', 'action' => 'resolve'],
            // Billing & Payments permissions for Receptionist
            ['name' => 'billing.create', 'display_name' => 'Create Invoices', 'module' => 'billing_payments', 'feature' => 'invoices', 'action' => 'create'],
            ['name' => 'billing.read', 'display_name' => 'View Invoices', 'module' => 'billing_payments', 'feature' => 'invoices', 'action' => 'read'],
            ['name' => 'billing.update', 'display_name' => 'Update Invoices', 'module' => 'billing_payments', 'feature' => 'invoices', 'action' => 'update'],
            ['name' => 'billing.delete', 'display_name' => 'Delete Invoices', 'module' => 'billing_payments', 'feature' => 'invoices', 'action' => 'delete'],
            ['name' => 'payments.process', 'display_name' => 'Process Payments', 'module' => 'billing_payments', 'feature' => 'payments', 'action' => 'process'],

            // Patient permissions (Self-service)
            ['name' => 'appointments.self_book', 'display_name' => 'Book Own Appointments', 'module' => 'appointment_management', 'feature' => 'appointments', 'action' => 'create', 'resource' => 'self'],
            ['name' => 'appointments.self_read', 'display_name' => 'View Own Appointments', 'module' => 'appointment_management', 'feature' => 'appointments', 'action' => 'read', 'resource' => 'self'],
            ['name' => 'medical_records.self_read', 'display_name' => 'View Own Medical Records', 'module' => 'clinical_management', 'feature' => 'records', 'action' => 'read', 'resource' => 'self'],
            ['name' => 'prescriptions.self_read', 'display_name' => 'View Own Prescriptions', 'module' => 'clinical_management', 'feature' => 'prescriptions', 'action' => 'read', 'resource' => 'self'],

            // Admin permissions (Restricted scope)
            ['name' => 'audit.read', 'display_name' => 'View Audit Logs', 'module' => 'audit_compliance', 'feature' => 'logs', 'action' => 'read'],
            ['name' => 'system.basic_settings', 'display_name' => 'Basic System Settings', 'module' => 'system_admin', 'feature' => 'settings', 'action' => 'configure', 'resource' => 'basic'],
            ['name' => 'reports.operational', 'display_name' => 'View Operational Reports', 'module' => 'reports_analytics', 'feature' => 'reports', 'action' => 'read', 'resource' => 'operational'],
            ['name' => 'escalations.handle', 'display_name' => 'Handle System Escalations', 'module' => 'system_admin', 'feature' => 'escalations', 'action' => 'handle'],
            
            // Medical Coordinator permissions (Patient assignment focus)
            ['name' => 'patients.assign_clinician', 'display_name' => 'Assign Patients to Clinicians', 'module' => 'patient_management', 'feature' => 'assignment', 'action' => 'assign'],
            ['name' => 'patients.limited_history', 'display_name' => 'Limited Access to Patient Histories', 'module' => 'patient_management', 'feature' => 'history', 'action' => 'read', 'resource' => 'limited']
        ];

        foreach ($permissions as $permission) {
            $stmt = $this->db->prepare("INSERT IGNORE INTO dynamic_permissions (name, display_name, module, feature, action, resource, is_system_permission) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $permission['name'], 
                $permission['display_name'], 
                $permission['module'], 
                $permission['feature'], 
                $permission['action'],
                $permission['resource'] ?? null,
                true
            ]);
        }

        // 🔗 Seed Role-Permission Mappings according to new role structure
        $rolePermissions = [
            'super_admin' => [
                // Full system access including role configuration
                'system.configure_roles', 'system.manage_permissions', 'system.feature_allocation',
                'users.create', 'users.read', 'users.update', 'users.delete', 'users.assign_roles',
                'audit.read', 'system.basic_settings', 'reports.operational', 'escalations.handle'
            ],
            'admin' => [
                // Restricted to user management, audit, escalations
                'users.create', 'users.read', 'users.update', 'users.delete', 'users.assign_roles',
                'audit.read', 'system.basic_settings', 'reports.operational', 'escalations.handle'
            ],
            'doctor' => [
                // Clinical duties only
                'patients.clinical_read', 'patients.clinical_update',
                'medical_records.create', 'medical_records.read', 'medical_records.update',
                'appointments.doctor_read', 'appointments.doctor_update',
                'treatment_plans.create', 'prescriptions.create'
            ],
            'receptionist' => [
                // Front desk operations + Appointment Management + Billing & Payments
                'front_desk.checkin', 'front_desk.registration', 'front_desk.queue_management',
                'patients.basic_create', 'patients.basic_read',
                // Appointment Management permissions
                'appointments.create', 'appointments.read', 'appointments.update', 'appointments.delete',
                'appointments.resolve_conflicts',
                // Billing & Payments permissions
                'billing.create', 'billing.read', 'billing.update', 'billing.delete',
                'payments.process'
            ],
            'patient' => [
                // Self-service only
                'appointments.self_book', 'appointments.self_read',
                'medical_records.self_read', 'prescriptions.self_read'
            ],
            'medical_coordinator' => [
                // Patient assignment focus (appointment management transferred to receptionist per user request)
                'patients.assign_clinician', 'patients.limited_history'
            ]
        ];

        foreach ($rolePermissions as $roleName => $permissionNames) {
            // Get role ID
            $roleStmt = $this->db->prepare("SELECT id FROM dynamic_roles WHERE name = ?");
            $roleStmt->execute([$roleName]);
            $roleId = $roleStmt->fetchColumn();

            foreach ($permissionNames as $permissionName) {
                // Get permission ID
                $permStmt = $this->db->prepare("SELECT id FROM dynamic_permissions WHERE name = ?");
                $permStmt->execute([$permissionName]);
                $permissionId = $permStmt->fetchColumn();

                if ($roleId && $permissionId) {
                    $stmt = $this->db->prepare("INSERT IGNORE INTO dynamic_role_permissions (role_id, permission_id) VALUES (?, ?)");
                    $stmt->execute([$roleId, $permissionId]);
                }
            }
        }

        echo "✅ Dynamic RBAC data seeded successfully!\n";
    }

    public function unseed()
    {
        // Clean up Dynamic RBAC tables
        $this->db->exec("DELETE FROM dynamic_role_permissions");
        $this->db->exec("DELETE FROM user_dynamic_roles");
        $this->db->exec("DELETE FROM dynamic_permissions");
        $this->db->exec("DELETE FROM feature_modules");
        $this->db->exec("DELETE FROM dynamic_roles");
        
        echo "🗑️ Dynamic RBAC data unseeded successfully!\n";
    }
}