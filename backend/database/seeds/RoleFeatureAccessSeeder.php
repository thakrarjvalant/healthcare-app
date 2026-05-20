<?php

use Database\DatabaseConnection;

/**
 * 🎯 Seed Role-Feature Access data
 * This seeder populates the role_feature_access table with proper feature access mappings
 */
class RoleFeatureAccessSeeder
{
    private $db;

    public function __construct()
    {
        $this->db = DatabaseConnection::getInstance();
    }

    public function seed()
    {
        // Define Role-Feature Access Matrix
        $roleFeatureAccess = [
            'super_admin' => [
                'user_management' => 'admin',
                'appointment_management' => 'admin', 
                'patient_management' => 'admin',
                'clinical_management' => 'admin',
                'billing_payments' => 'admin',
                'front_desk' => 'admin',
                'system_admin' => 'admin',
                'role_management' => 'admin',
                'audit_compliance' => 'admin',
                'reports_analytics' => 'admin'
            ],
            'admin' => [
                'user_management' => 'admin',
                'system_admin' => 'write',
                'audit_compliance' => 'read',
                'reports_analytics' => 'read'
            ],
            'doctor' => [
                'patient_management' => 'write',
                'clinical_management' => 'admin',
                'appointment_management' => 'read'
            ],
            'receptionist' => [
                'front_desk' => 'admin',
                'patient_management' => 'write',
                'appointment_management' => 'admin',
                'billing_payments' => 'admin'
            ],
            'patient' => [
                'appointment_management' => 'read',
                'clinical_management' => 'read',
                'patient_management' => 'read'
            ],
            'medical_coordinator' => [
                'patient_management' => 'write',
                'audit_compliance' => 'read'
            ]
        ];

        $insertedCount = 0;
        
        foreach ($roleFeatureAccess as $roleName => $featureAccess) {
            // Get role ID
            $roleStmt = $this->db->prepare("SELECT id FROM dynamic_roles WHERE name = ?");
            $roleStmt->execute([$roleName]);
            $roleId = $roleStmt->fetchColumn();
            
            if (!$roleId) {
                echo "⚠️ Role '{$roleName}' not found\n";
                continue;
            }
            
            foreach ($featureAccess as $moduleName => $accessLevel) {
                // Get module ID
                $moduleStmt = $this->db->prepare("SELECT id FROM feature_modules WHERE name = ?");
                $moduleStmt->execute([$moduleName]);
                $moduleId = $moduleStmt->fetchColumn();
                
                if (!$moduleId) {
                    echo "⚠️ Module '{$moduleName}' not found\n";
                    continue;
                }
                
                // Check if assignment already exists
                $checkStmt = $this->db->prepare("SELECT id FROM role_feature_access WHERE role_id = ? AND module_id = ?");
                $checkStmt->execute([$roleId, $moduleId]);
                
                if ($checkStmt->fetchColumn()) {
                    echo "ℹ️ Feature access already exists for role '{$roleName}' and module '{$moduleName}'\n";
                    continue;
                }
                
                // Insert feature access assignment
                $insertStmt = $this->db->prepare("INSERT INTO role_feature_access (role_id, module_id, access_level, granted_by) VALUES (?, ?, ?, ?)");
                $result = $insertStmt->execute([$roleId, $moduleId, $accessLevel, 1]); // 1 as default admin user ID
                
                if ($result) {
                    $insertedCount++;
                    echo "✅ Assigned feature access: {$roleName} → {$moduleName} ({$accessLevel})\n";
                } else {
                    echo "❌ Failed to assign feature access: {$roleName} → {$moduleName}\n";
                }
            }
        }
        
        echo "✅ Role-Feature Access assignments completed successfully! ({$insertedCount} records inserted)\n";
    }

    public function unseed()
    {
        $this->db->exec("DELETE FROM role_feature_access");
        echo "🗑️ Role-Feature Access assignments unseeded successfully!\n";
    }
}