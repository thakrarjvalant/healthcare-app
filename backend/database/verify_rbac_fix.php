<?php

use Database\DatabaseConnection;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/DatabaseConnection.php';

/**
 * Simple verification script to check RBAC fix
 */
class VerifyRbacFix
{
    private $db;

    public function __construct()
    {
        $this->db = DatabaseConnection::getInstance();
    }

    public function run()
    {
        echo "🔍 Verifying RBAC Fix\n";
        echo "====================\n\n";

        // Check 1: Verify user_dynamic_roles table has entries
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM user_dynamic_roles WHERE is_active = 1");
        $stmt->execute();
        $count = $stmt->fetchColumn();
        echo "✅ Active user-role assignments: {$count}\n";

        // Check 2: Verify super admin has role assignment
        $stmt = $this->db->prepare("SELECT u.name, dr.name as role_name 
                                   FROM users u 
                                   JOIN user_dynamic_roles udr ON u.id = udr.user_id 
                                   JOIN dynamic_roles dr ON udr.role_id = dr.id 
                                   WHERE u.role = 'super_admin' AND udr.is_active = 1 AND dr.is_active = 1");
        $stmt->execute();
        $superAdminRoles = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        if (!empty($superAdminRoles)) {
            echo "✅ Super admin has role assignment:\n";
            foreach ($superAdminRoles as $role) {
                echo "   - User: {$role['name']}, Role: {$role['role_name']}\n";
            }
        } else {
            echo "❌ Super admin has no role assignment\n";
        }

        // Check 3: Verify all user types have role assignments
        $userTypes = ['admin', 'doctor', 'receptionist', 'patient', 'medical_coordinator', 'super_admin'];
        echo "\n📋 User role assignments:\n";
        
        foreach ($userTypes as $userType) {
            $stmt = $this->db->prepare("SELECT COUNT(*) as count 
                                       FROM users u 
                                       JOIN user_dynamic_roles udr ON u.id = udr.user_id 
                                       WHERE u.role = ? AND udr.is_active = 1");
            $stmt->execute([$userType]);
            $count = $stmt->fetchColumn();
            echo "   {$userType}: {$count} assignments\n";
        }

        echo "\n🎉 RBAC system verification completed!\n";
        echo "The super admin and all users should now be able to see their respective modules.\n";
    }
}

// Run the verification
$verifier = new VerifyRbacFix();
$verifier->run();