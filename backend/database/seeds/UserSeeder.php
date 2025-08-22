<?php

use Database\DatabaseConnection;

/**
 * Seed the users table with initial data
 */
class UserSeeder
{
    private $db;

    public function __construct()
    {
        $this->db = DatabaseConnection::getInstance();
    }

    public function seed()
    {
        $users = [
            [
                'name' => 'John Doe',
                'email' => 'john.doe@example.com',
                'password' => password_hash('password123', PASSWORD_DEFAULT),
                'role' => 'patient',
                'verified' => true
            ],
            [
                'name' => 'Dr. Jane Smith',
                'email' => 'jane.smith@example.com',
                'password' => password_hash('password123', PASSWORD_DEFAULT),
                'role' => 'doctor',
                'verified' => true
            ],
            [
                'name' => 'Receptionist Bob',
                'email' => 'bob.receptionist@example.com',
                'password' => password_hash('password123', PASSWORD_DEFAULT),
                'role' => 'receptionist',
                'verified' => true
            ],
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => password_hash('password123', PASSWORD_DEFAULT),
                'role' => 'admin',
                'verified' => true
            ]
        ];

        foreach ($users as $user) {
            $stmt = $this->db->prepare("INSERT INTO users (name, email, password, role, verified) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $user['name'],
                $user['email'],
                $user['password'],
                $user['role'],
                $user['verified']
            ]);
        }
    }

    public function unseed()
    {
        $this->db->exec("DELETE FROM users WHERE email IN ('john.doe@example.com', 'jane.smith@example.com', 'bob.receptionist@example.com', 'admin@example.com')");
    }
}