<?php
// Simple mock database for testing
class MockDatabase {
    private static $users = [
        1 => [
            'id' => 1,
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => '$2y$10$lb66x4qEgeUvRsU0A26SkO.lXatB.jYnhQbfi5PF5NuHldd6bTz2G', // password123
            'role' => 'patient',
            'verified' => true,
            'created_at' => '2024-01-01 00:00:00',
            'updated_at' => '2024-01-01 00:00:00'
        ],
        2 => [
            'id' => 2,
            'name' => 'Dr. Jane Smith',
            'email' => 'jane.smith@example.com',
            'password' => '$2y$10$lb66x4qEgeUvRsU0A26SkO.lXatB.jYnhQbfi5PF5NuHldd6bTz2G', // password123
            'role' => 'doctor',
            'verified' => true,
            'created_at' => '2024-01-01 00:00:00',
            'updated_at' => '2024-01-01 00:00:00'
        ],
        3 => [
            'id' => 3,
            'name' => 'Receptionist Bob',
            'email' => 'bob.receptionist@example.com',
            'password' => '$2y$10$lb66x4qEgeUvRsU0A26SkO.lXatB.jYnhQbfi5PF5NuHldd6bTz2G', // password123
            'role' => 'receptionist',
            'verified' => true,
            'created_at' => '2024-01-01 00:00:00',
            'updated_at' => '2024-01-01 00:00:00'
        ],
        4 => [
            'id' => 4,
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => '$2y$10$lb66x4qEgeUvRsU0A26SkO.lXatB.jYnhQbfi5PF5NuHldd6bTz2G', // password123
            'role' => 'admin',
            'verified' => true,
            'created_at' => '2024-01-01 00:00:00',
            'updated_at' => '2024-01-01 00:00:00'
        ],
        5 => [
            'id' => 5,
            'name' => 'Medical Coordinator',
            'email' => 'medical.coordinator@example.com',
            'password' => '$2y$10$lb66x4qEgeUvRsU0A26SkO.lXatB.jYnhQbfi5PF5NuHldd6bTz2G', // password123
            'role' => 'medical_coordinator',
            'verified' => true,
            'created_at' => '2024-01-01 00:00:00',
            'updated_at' => '2024-01-01 00:00:00'
        ]
    ];

    public static function getUserByEmail($email) {
        foreach (self::$users as $user) {
            if ($user['email'] === $email) {
                return $user;
            }
        }
        return null;
    }

    public static function getUserById($id) {
        return self::$users[$id] ?? null;
    }

    public static function getAllUsers() {
        return array_values(self::$users);
    }
}
?>