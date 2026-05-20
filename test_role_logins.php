<?php
/**
 * Test script to verify all role logins and features in the healthcare management system
 */

echo "🔍 Healthcare Management System - Role Login and Feature Verification\n";
echo "═══════════════════════════════════════════════════════════════════════\n\n";

echo "📋 Test Credentials Summary:\n";
echo "- Admin: admin@example.com / password123\n";
echo "- Doctor: jane.smith@example.com / password123\n";
echo "- Receptionist: bob.receptionist@example.com / password123\n";
echo "- Patient: john.doe@example.com / password123\n";
echo "- Medical Coordinator: medical.coordinator@example.com / password123\n";
echo "- Super Admin: super.admin@example.com / password123\n\n";

echo "🔐 Testing Login and Authentication System\n";
echo "─────────────────────────────────────────\n";

// Test database connection
echo "✅ Checking database connection...\n";
try {
    // Include the database connection file
    require_once __DIR__ . '/backend/database/config.php';
    require_once __DIR__ . '/backend/database/DatabaseConnection.php';
    
    $db = \Database\DatabaseConnection::getInstance();
    
    // Test query to check if users exist
    $stmt = $db->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "✅ Database connection successful! Found {$result['count']} users in the system.\n\n";
    
    // Test user existence
    $testUsers = [
        'admin@example.com' => 'Admin',
        'jane.smith@example.com' => 'Doctor',
        'bob.receptionist@example.com' => 'Receptionist',
        'john.doe@example.com' => 'Patient',
        'medical.coordinator@example.com' => 'Medical Coordinator',
        'super.admin@example.com' => 'Super Admin'
    ];
    
    echo "👥 Verifying test users exist in database:\n";
    foreach ($testUsers as $email => $role) {
        $stmt = $db->prepare("SELECT id, name, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            echo "   ✅ {$role} user ({$email}) found with ID: {$user['id']}, Name: {$user['name']}, Role: {$user['role']}\n";
        } else {
            echo "   ❌ {$role} user ({$email}) NOT FOUND in database!\n";
        }
    }
    
    echo "\n";
    
    // Test RBAC system
    echo "🎭 Verifying RBAC system setup:\n";
    
    // Check if roles exist
    $rolesToCheck = ['admin', 'doctor', 'receptionist', 'patient', 'medical_coordinator', 'super_admin'];
    foreach ($rolesToCheck as $roleName) {
        $stmt = $db->prepare("SELECT id, display_name FROM dynamic_roles WHERE name = ?");
        $stmt->execute([$roleName]);
        $role = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($role) {
            echo "   ✅ Role '{$roleName}' ({$role['display_name']}) found with ID: {$role['id']}\n";
        } else {
            echo "   ❌ Role '{$roleName}' NOT FOUND in dynamic_roles table!\n";
        }
    }
    
    echo "\n";
    
    // Check if permissions exist for each role
    echo "🔑 Verifying role permissions:\n";
    $rolePermissions = [
        'super_admin' => [
            'system.configure_roles',
            'users.create',
            'users.read',
            'users.update',
            'users.delete',
            'audit.read'
        ],
        'admin' => [
            'users.create',
            'users.read',
            'users.update',
            'users.delete',
            'audit.read'
        ],
        'doctor' => [
            'patients.clinical_read',
            'medical_records.create',
            'medical_records.read',
            'medical_records.update',
            'appointments.doctor_read',
            'appointments.doctor_update',
            'treatment_plans.create',
            'prescriptions.create'
        ],
        'receptionist' => [
            'front_desk.checkin',
            'front_desk.registration',
            'patients.basic_create',
            'patients.basic_read',
            'appointments.create',
            'appointments.read',
            'appointments.update',
            'appointments.delete',
            'billing.create',
            'billing.read',
            'billing.update',
            'billing.delete',
            'payments.process'
        ],
        'patient' => [
            'appointments.self_book',
            'appointments.self_read',
            'medical_records.self_read',
            'prescriptions.self_read'
        ],
        'medical_coordinator' => [
            'patients.assign_clinician',
            'patients.limited_history'
        ]
    ];
    
    foreach ($rolePermissions as $roleName => $permissions) {
        echo "   📁 {$roleName} permissions:\n";
        
        // Get role ID
        $roleStmt = $db->prepare("SELECT id FROM dynamic_roles WHERE name = ?");
        $roleStmt->execute([$roleName]);
        $role = $roleStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($role) {
            foreach ($permissions as $permission) {
                $permStmt = $db->prepare("
                    SELECT dp.name, dp.display_name 
                    FROM dynamic_permissions dp
                    JOIN dynamic_role_permissions drp ON dp.id = drp.permission_id
                    JOIN dynamic_roles r ON r.id = drp.role_id
                    WHERE r.name = ? AND dp.name = ?
                ");
                $permStmt->execute([$roleName, $permission]);
                $permissionResult = $permStmt->fetch(PDO::FETCH_ASSOC);
                
                if ($permissionResult) {
                    echo "      ✅ {$permission} - OK\n";
                } else {
                    echo "      ❌ {$permission} - MISSING\n";
                }
            }
        } else {
            echo "      ❌ Role {$roleName} not found!\n";
        }
    }
    
    echo "\n";
    
    // Check feature modules
    echo "🎯 Verifying feature modules:\n";
    $featureModules = [
        'user_management',
        'appointment_management', 
        'patient_management',
        'clinical_management',
        'billing_payments',
        'front_desk',
        'system_admin',
        'role_management',
        'audit_compliance',
        'reports_analytics'
    ];
    
    foreach ($featureModules as $moduleName) {
        $stmt = $db->prepare("SELECT id, display_name FROM feature_modules WHERE name = ?");
        $stmt->execute([$moduleName]);
        $module = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($module) {
            echo "   ✅ Module '{$moduleName}' ({$module['display_name']}) found with ID: {$module['id']}\n";
        } else {
            echo "   ❌ Module '{$moduleName}' NOT FOUND in feature_modules table!\n";
        }
    }
    
    echo "\n";
    
    // Summary
    echo "📋 Verification Summary:\n";
    echo "   - Database connection: ✅ CONNECTED\n";
    echo "   - Test users: CHECK INDIVIDUAL RESULTS ABOVE\n";
    echo "   - RBAC system: CHECK INDIVIDUAL RESULTS ABOVE\n";
    echo "   - Role permissions: CHECK INDIVIDUAL RESULTS ABOVE\n";
    echo "   - Feature modules: CHECK INDIVIDUAL RESULTS ABOVE\n\n";
    
    echo "💡 Next Steps:\n";
    echo "   1. If using Docker: Ensure containers are running with 'docker-compose up'\n";
    echo "   2. Access the application at http://localhost:3000\n";
    echo "   3. Test each role login with the credentials listed above\n";
    echo "   4. Verify role-specific features are accessible according to documentation\n\n";
    
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    echo "💡 Make sure the database is running and properly configured.\n\n";
}

echo "🎉 Role verification script completed!\n";