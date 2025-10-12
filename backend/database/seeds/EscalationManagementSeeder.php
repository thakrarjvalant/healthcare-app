<?php

use Database\DatabaseConnection;

/**
 * 🚨 Seed Escalation Management data
 * This seeder populates the escalation management system with initial categories and statuses
 */
class EscalationManagementSeeder
{
    private $db;

    public function __construct()
    {
        $this->db = DatabaseConnection::getInstance();
    }

    public function seed()
    {
        // 📂 Seed Escalation Categories
        $categories = [
            [
                'name' => 'system_issue',
                'display_name' => 'System Issue',
                'description' => 'Technical problems with the system or application',
                'priority' => 'high'
            ],
            [
                'name' => 'security_breach',
                'display_name' => 'Security Breach',
                'description' => 'Potential or actual security incidents',
                'priority' => 'critical'
            ],
            [
                'name' => 'data_issue',
                'display_name' => 'Data Issue',
                'description' => 'Problems with data integrity, access, or quality',
                'priority' => 'high'
            ],
            [
                'name' => 'user_access',
                'display_name' => 'User Access',
                'description' => 'User account, role, or permission issues',
                'priority' => 'medium'
            ],
            [
                'name' => 'performance',
                'display_name' => 'Performance',
                'description' => 'System performance or responsiveness issues',
                'priority' => 'medium'
            ],
            [
                'name' => 'compliance',
                'display_name' => 'Compliance',
                'description' => 'Regulatory or compliance-related concerns',
                'priority' => 'high'
            ],
            [
                'name' => 'feature_request',
                'display_name' => 'Feature Request',
                'description' => 'Requests for new functionality or improvements',
                'priority' => 'low'
            ],
            [
                'name' => 'billing_issue',
                'display_name' => 'Billing Issue',
                'description' => 'Problems with billing, payments, or invoices',
                'priority' => 'medium'
            ]
        ];

        foreach ($categories as $category) {
            $stmt = $this->db->prepare("INSERT IGNORE INTO escalation_categories (name, display_name, description, priority) VALUES (?, ?, ?, ?)");
            $stmt->execute([$category['name'], $category['display_name'], $category['description'], $category['priority']]);
        }

        // 🏷️ Seed Escalation Statuses
        $statuses = [
            [
                'name' => 'new',
                'display_name' => 'New',
                'description' => 'Escalation has been reported but not yet reviewed',
                'is_final' => false,
                'sort_order' => 1
            ],
            [
                'name' => 'in_review',
                'display_name' => 'In Review',
                'description' => 'Escalation is being reviewed by the appropriate team',
                'is_final' => false,
                'sort_order' => 2
            ],
            [
                'name' => 'in_progress',
                'display_name' => 'In Progress',
                'description' => 'Work has begun to resolve the escalation',
                'is_final' => false,
                'sort_order' => 3
            ],
            [
                'name' => 'on_hold',
                'display_name' => 'On Hold',
                'description' => 'Escalation is temporarily paused pending further information or action',
                'is_final' => false,
                'sort_order' => 4
            ],
            [
                'name' => 'resolved',
                'display_name' => 'Resolved',
                'description' => 'Escalation has been successfully resolved',
                'is_final' => true,
                'sort_order' => 5
            ],
            [
                'name' => 'closed',
                'display_name' => 'Closed',
                'description' => 'Escalation is closed with no further action required',
                'is_final' => true,
                'sort_order' => 6
            ],
            [
                'name' => 'rejected',
                'display_name' => 'Rejected',
                'description' => 'Escalation was determined to be invalid or not actionable',
                'is_final' => true,
                'sort_order' => 7
            ]
        ];

        foreach ($statuses as $status) {
            $stmt = $this->db->prepare("INSERT IGNORE INTO escalation_statuses (name, display_name, description, is_final, sort_order) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$status['name'], $status['display_name'], $status['description'], $status['is_final'], $status['sort_order']]);
        }

        echo "✅ Escalation management data seeded successfully!\n";
    }

    public function unseed()
    {
        $this->db->exec("DELETE FROM escalation_audit_logs");
        $this->db->exec("DELETE FROM escalation_attachments");
        $this->db->exec("DELETE FROM escalation_comments");
        $this->db->exec("DELETE FROM escalations");
        $this->db->exec("DELETE FROM escalation_statuses");
        $this->db->exec("DELETE FROM escalation_categories");
        echo "🗑️ Escalation management data unseeded successfully!\n";
    }
}