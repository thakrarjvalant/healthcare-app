<?php

use Database\DatabaseConnection;

/**
 * 🔐 Seed RBAC (Role-Based Access Control) data
 * This seeder populates roles, permissions, and role-permission mappings
 */
class RBACSeeder
{
    private $db;

    public function __construct()
    {
        $this->db = DatabaseConnection::getInstance();
    }

    public function seed()
    {
        // 📋 Seed Roles
        $roles = [
            ['name' => 'admin', 'description' => 'System Administrator with full access'],
            ['name' => 'doctor', 'description' => 'Medical Doctor with clinical access'],
            ['name' => 'receptionist', 'description' => 'Front desk staff with appointment and billing access'],
            ['name' => 'patient', 'description' => 'Patient with limited personal data access']
        ];

        foreach ($roles as $role) {
            $stmt = $this->db->prepare("INSERT IGNORE INTO roles (name, description) VALUES (?, ?)");
            $stmt->execute([$role['name'], $role['description']]);
        }

        // 🔑 Seed Permissions
        $permissions = [
            // User Management
            ['name' => 'users.create', 'description' => 'Create new users', 'module' => 'user_management'],
            ['name' => 'users.read', 'description' => 'View user information', 'module' => 'user_management'],
            ['name' => 'users.update', 'description' => 'Update user information', 'module' => 'user_management'],
            ['name' => 'users.delete', 'description' => 'Delete users', 'module' => 'user_management'],
            
            // Patient Management
            ['name' => 'patients.create', 'description' => 'Create patient records', 'module' => 'patient_management'],
            ['name' => 'patients.read', 'description' => 'View patient records', 'module' => 'patient_management'],
            ['name' => 'patients.update', 'description' => 'Update patient records', 'module' => 'patient_management'],
            ['name' => 'patients.delete', 'description' => 'Delete patient records', 'module' => 'patient_management'],
            
            // Medical Records
            ['name' => 'medical_records.create', 'description' => 'Create medical records', 'module' => 'medical_records'],
            ['name' => 'medical_records.read', 'description' => 'View medical records', 'module' => 'medical_records'],
            ['name' => 'medical_records.update', 'description' => 'Update medical records', 'module' => 'medical_records'],
            ['name' => 'medical_records.delete', 'description' => 'Delete medical records', 'module' => 'medical_records'],
            
            // Appointments
            ['name' => 'appointments.create', 'description' => 'Schedule appointments', 'module' => 'appointments'],
            ['name' => 'appointments.read', 'description' => 'View appointments', 'module' => 'appointments'],
            ['name' => 'appointments.update', 'description' => 'Modify appointments', 'module' => 'appointments'],
            ['name' => 'appointments.delete', 'description' => 'Cancel appointments', 'module' => 'appointments'],
            
            // Prescriptions
            ['name' => 'prescriptions.create', 'description' => 'Write prescriptions', 'module' => 'prescriptions'],
            ['name' => 'prescriptions.read', 'description' => 'View prescriptions', 'module' => 'prescriptions'],
            ['name' => 'prescriptions.update', 'description' => 'Modify prescriptions', 'module' => 'prescriptions'],
            ['name' => 'prescriptions.refill', 'description' => 'Process prescription refills', 'module' => 'prescriptions'],
            
            // Billing & Payments
            ['name' => 'billing.create', 'description' => 'Create invoices', 'module' => 'billing'],
            ['name' => 'billing.read', 'description' => 'View billing information', 'module' => 'billing'],
            ['name' => 'billing.update', 'description' => 'Update billing information', 'module' => 'billing'],
            ['name' => 'payments.process', 'description' => 'Process payments', 'module' => 'billing'],
            
            // System Administration
            ['name' => 'system.settings', 'description' => 'Manage system settings', 'module' => 'administration'],
            ['name' => 'system.monitoring', 'description' => 'View system monitoring', 'module' => 'administration'],
            ['name' => 'audit.read', 'description' => 'View audit logs', 'module' => 'administration'],
            ['name' => 'rbac.manage', 'description' => 'Manage roles and permissions', 'module' => 'administration'],
            
            // Reports
            ['name' => 'reports.medical', 'description' => 'Generate medical reports', 'module' => 'reports'],
            ['name' => 'reports.financial', 'description' => 'Generate financial reports', 'module' => 'reports'],
            ['name' => 'reports.operational', 'description' => 'Generate operational reports', 'module' => 'reports'],
            
            // Documents
            ['name' => 'documents.upload', 'description' => 'Upload documents', 'module' => 'documents'],
            ['name' => 'documents.read', 'description' => 'View documents', 'module' => 'documents'],
            ['name' => 'documents.share', 'description' => 'Share documents', 'module' => 'documents'],
            ['name' => 'documents.delete', 'description' => 'Delete documents', 'module' => 'documents']
        ];

        foreach ($permissions as $permission) {
            $stmt = $this->db->prepare("INSERT IGNORE INTO permissions (name, description, module) VALUES (?, ?, ?)");
            $stmt->execute([$permission['name'], $permission['description'], $permission['module']]);
        }

        // 🔗 Seed Role-Permission Mappings
        $rolePermissions = [
            'admin' => [
                // Full system access
                'users.create', 'users.read', 'users.update', 'users.delete',
                'patients.create', 'patients.read', 'patients.update', 'patients.delete',
                'medical_records.create', 'medical_records.read', 'medical_records.update', 'medical_records.delete',
                'appointments.create', 'appointments.read', 'appointments.update', 'appointments.delete',
                'prescriptions.create', 'prescriptions.read', 'prescriptions.update', 'prescriptions.refill',
                'billing.create', 'billing.read', 'billing.update', 'payments.process',
                'system.settings', 'system.monitoring', 'audit.read', 'rbac.manage',
                'reports.medical', 'reports.financial', 'reports.operational',
                'documents.upload', 'documents.read', 'documents.share', 'documents.delete'
            ],
            'doctor' => [
                // Clinical access
                'patients.read', 'patients.update',
                'medical_records.create', 'medical_records.read', 'medical_records.update',
                'appointments.read', 'appointments.update',
                'prescriptions.create', 'prescriptions.read', 'prescriptions.update', 'prescriptions.refill',
                'reports.medical',
                'documents.upload', 'documents.read', 'documents.share'
            ],
            'receptionist' => [
                // Front desk access
                'patients.create', 'patients.read', 'patients.update',
                'appointments.create', 'appointments.read', 'appointments.update', 'appointments.delete',
                'billing.create', 'billing.read', 'billing.update', 'payments.process',
                'reports.operational', 'reports.financial',
                'documents.upload', 'documents.read'
            ],
            'patient' => [
                // Limited personal access
                'appointments.read',
                'prescriptions.read',
                'medical_records.read',
                'documents.upload', 'documents.read'
            ]
        ];

        foreach ($rolePermissions as $roleName => $permissionNames) {
            // Get role ID
            $roleStmt = $this->db->prepare("SELECT id FROM roles WHERE name = ?");
            $roleStmt->execute([$roleName]);
            $roleId = $roleStmt->fetchColumn();

            foreach ($permissionNames as $permissionName) {
                // Get permission ID
                $permStmt = $this->db->prepare("SELECT id FROM permissions WHERE name = ?");
                $permStmt->execute([$permissionName]);
                $permissionId = $permStmt->fetchColumn();

                if ($roleId && $permissionId) {
                    $stmt = $this->db->prepare("INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
                    $stmt->execute([$roleId, $permissionId]);
                }
            }
        }

        echo "✅ RBAC data seeded successfully!\n";
    }

    public function unseed()
    {
        $this->db->exec("DELETE FROM role_permissions");
        $this->db->exec("DELETE FROM permissions");
        $this->db->exec("DELETE FROM roles");
        echo "🗑️ RBAC data unseeded successfully!\n";
    }
}