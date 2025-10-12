<?php

use Database\DatabaseConnection;
use Shared\RBAC\DynamicRBACManager;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/DatabaseConnection.php';
require_once __DIR__ . '/../shared/rbac/DynamicRBACManager.php';

/**
 * Test script to verify user-role assignments in the dynamic RBAC system
 */
class TestUserRoles
{
    private $db;
    private $rbacManager;

    public function __construct()
    {
        $this->db = DatabaseConnection::getInstance();
        $this->rbacManager = new DynamicRBACManager();
    }

    public function run()
    {
        echo "🔍 Testing User-Role Assignments in Dynamic RBAC System\n";
        echo "=====================================================\n\n";

        // Test 1: Check if super admin has all permissions
        $this->testSuperAdminPermissions();

        // Test 2: Check if admin has appropriate permissions
        $this->testAdminPermissions();

        // Test 3: Check if regular users have their assigned roles
        $this->testUserRoles();

        // Test 4: Check role-feature access
        $this->testRoleFeatureAccess();

        echo "✅ All tests completed!\n";
    }

    private function testSuperAdminPermissions()
    {
        echo "🧪 Test 1: Super Admin Permissions\n";
        
        // Get super admin user
        $stmt = $this->db->prepare("SELECT id FROM users WHERE role = 'super_admin' LIMIT 1");
        $stmt->execute();
        $superAdmin = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($superAdmin) {
            $userId = $superAdmin['id'];
            
            // Check if user has super_admin role
            $hasRole = $this->rbacManager->hasAnyRole($userId, 'super_admin');
            echo "   Has super_admin role: " . ($hasRole ? "✅ YES" : "❌ NO") . "\n";
            
            // Check role-feature access
            $roles = $this->rbacManager->getUserRoles($userId);
            if (!empty($roles)) {
                $roleId = $roles[0]['id'];
                $featureAccess = $this->rbacManager->getRoleFeatureAccess($roleId);
                echo "   Feature modules accessible: " . count($featureAccess) . "\n";
                
                if (count($featureAccess) > 0) {
                    echo "   ✅ Super admin can access modules\n";
                } else {
                    echo "   ❌ Super admin cannot access any modules\n";
                }
            }
        } else {
            echo "   ⚠️ No super admin user found\n";
        }
        
        echo "\n";
    }

    private function testAdminPermissions()
    {
        echo "🧪 Test 2: Admin Permissions\n";
        
        // Get admin user
        $stmt = $this->db->prepare("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
        $stmt->execute();
        $admin = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($admin) {
            $userId = $admin['id'];
            
            // Check if user has admin role
            $hasRole = $this->rbacManager->hasAnyRole($userId, 'admin');
            echo "   Has admin role: " . ($hasRole ? "✅ YES" : "❌ NO") . "\n";
            
            // Check specific permissions
            $hasUserCreate = $this->rbacManager->hasPermission($userId, 'users.create');
            echo "   Can create users: " . ($hasUserCreate ? "✅ YES" : "❌ NO") . "\n";
            
            $hasAuditRead = $this->rbacManager->hasPermission($userId, 'audit.read');
            echo "   Can read audit logs: " . ($hasAuditRead ? "✅ YES" : "❌ NO") . "\n";
        } else {
            echo "   ⚠️ No admin user found\n";
        }
        
        echo "\n";
    }

    private function testUserRoles()
    {
        echo "🧪 Test 3: User Role Assignments\n";
        
        // Get sample users
        $stmt = $this->db->prepare("SELECT id, name, role FROM users WHERE role IN ('doctor', 'receptionist', 'patient') LIMIT 3");
        $stmt->execute();
        $users = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($users as $user) {
            $userId = $user['id'];
            $userRole = $user['role'];
            
            // Check if user has their assigned role
            $hasRole = $this->rbacManager->hasAnyRole($userId, $userRole);
            echo "   {$user['name']} ({$userRole}): " . ($hasRole ? "✅ Has role" : "❌ Missing role") . "\n";
        }
        
        echo "\n";
    }

    private function testRoleFeatureAccess()
    {
        echo "🧪 Test 4: Role-Feature Access\n";
        
        // Test different roles
        $testRoles = ['super_admin', 'admin', 'doctor', 'receptionist'];
        
        foreach ($testRoles as $roleName) {
            // Get role
            $stmt = $this->db->prepare("SELECT id FROM dynamic_roles WHERE name = ? AND is_active = 1");
            $stmt->execute([$roleName]);
            $role = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($role) {
                $roleId = $role['id'];
                $featureAccess = $this->rbacManager->getRoleFeatureAccess($roleId);
                echo "   {$roleName}: " . count($featureAccess) . " modules accessible\n";
            } else {
                echo "   {$roleName}: ❌ Role not found\n";
            }
        }
        
        echo "\n";
    }
}

// Run the test
$test = new TestUserRoles();
$test->run();