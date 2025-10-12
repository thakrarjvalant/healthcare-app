<?php

use Database\DatabaseConnection;

/**
 * 🎭 Seed User-Dynamic Role assignments
 * This seeder properly assigns users to roles in the dynamic RBAC system
 */
class UserDynamicRolesSeeder
{
    private $db;

    public function __construct()
    {
        $this->db = DatabaseConnection::getInstance();
    }

    public function seed()
    {
        // Get all users with their roles
        $stmt = $this->db->prepare("SELECT id, name, email, role FROM users WHERE role IS NOT NULL");
        $stmt->execute();
        $users = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $adminUserId = null;
        
        foreach ($users as $user) {
            $userId = $user['id'];
            $userRole = $user['role'];
            
            // Store admin user ID for later use
            if ($userRole === 'admin') {
                $adminUserId = $userId;
            }
            
            // Skip users without roles or with invalid roles
            if (empty($userRole)) {
                continue;
            }
            
            // Get role ID from dynamic_roles table
            $roleStmt = $this->db->prepare("SELECT id FROM dynamic_roles WHERE name = ? AND is_active = 1");
            $roleStmt->execute([$userRole]);
            $roleId = $roleStmt->fetchColumn();
            
            // If role exists in dynamic_roles, assign it to the user
            if ($roleId) {
                // Check if assignment already exists
                $checkStmt = $this->db->prepare("SELECT id FROM user_dynamic_roles WHERE user_id = ? AND role_id = ? AND is_active = 1");
                $checkStmt->execute([$userId, $roleId]);
                
                if (!$checkStmt->fetchColumn()) {
                    // Assign role to user
                    $assignStmt = $this->db->prepare("INSERT INTO user_dynamic_roles (user_id, role_id, assigned_by) VALUES (?, ?, ?)");
                    $assignStmt->execute([$userId, $roleId, $adminUserId ?? $userId]);
                    
                    echo "✅ Assigned role '{$userRole}' to user '{$user['name']}' (ID: {$userId})\n";
                } else {
                    echo "ℹ️ Role '{$userRole}' already assigned to user '{$user['name']}' (ID: {$userId})\n";
                }
            } else {
                echo "⚠️ Role '{$userRole}' not found in dynamic_roles table for user '{$user['name']}' (ID: {$userId})\n";
            }
        }

        echo "✅ User-Dynamic Role assignments completed successfully!\n";
    }

    public function unseed()
    {
        $this->db->exec("DELETE FROM user_dynamic_roles");
        echo "🗑️ User-Dynamic Role assignments unseeded successfully!\n";
    }
}