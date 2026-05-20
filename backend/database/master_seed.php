<?php

/**
 * 🎯 Master Seeder - Orchestrates all database seeding in correct order
 * This script runs all seeders in the proper sequence to ensure data integrity
 */

// Robustly load Composer autoload
$autoload = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
} else {
    $alt = __DIR__ . '/vendor/autoload.php';
    if (file_exists($alt)) {
        require_once $alt;
    } else {
        fwrite(STDERR, "❌ Composer autoload not found at: $autoload\n");
        fwrite(STDERR, "Run 'composer install' in the project root to create vendor/autoload.php\n");
        exit(1);
    }
}

use Database\DatabaseConnection;

// Load DatabaseConnection class
if (!class_exists('Database\\DatabaseConnection')) {
    $candidates = [
        __DIR__ . '/DatabaseConnection.php',
        __DIR__ . '/../DatabaseConnection.php',
        __DIR__ . '/../database/DatabaseConnection.php'
    ];
    
    foreach ($candidates as $path) {
        if (file_exists($path)) {
            require_once $path;
            break;
        }
    }
}

class MasterSeeder
{
    private $db;

    public function __construct()
    {
        try {
            $this->db = DatabaseConnection::getInstance();
            echo "✅ Database connection established successfully!\n\n";
        } catch (Exception $e) {
            echo "❌ Database connection failed: " . $e->getMessage() . "\n";
            exit(1);
        }
    }

    public function run()
    {
        echo "🌱 Starting Healthcare Management System Database Seeding\n";
        echo "=" . str_repeat("=", 60) . "\n\n";

        // Define seeding order (order matters for foreign key constraints)
        $seeders = [
            'UserSeeder' => '👥 Seeding Users (Admin, Doctors, Patients, Receptionists)',
            'DynamicRBACSeeder' => '🎭 Seeding Dynamic RBAC System',
            'UserDynamicRolesSeeder' => '👤 Seeding User-Dynamic Role Assignments',
            'RoleFeatureAccessSeeder' => '🎯 Seeding Role-Feature Access Assignments',
            'SystemConfigSeeder' => '⚙️ Seeding System Configuration',
            'AppointmentSeeder' => '📅 Seeding Appointments',
            'MedicalRecordSeeder' => '🏥 Seeding Medical Records & Clinical Data',
            'FinancialSeeder' => '💰 Seeding Financial Data (Invoices, Payments)',
            'PatientDoctorAssignmentSeeder' => '👨‍⚕️ Seeding Patient-Doctor Assignments',
            'EscalationManagementSeeder' => '⚠️ Seeding Escalation Management Data'
        ];

        $startTime = microtime(true);
        $successCount = 0;
        $errorCount = 0;

        foreach ($seeders as $seederClass => $description) {
            echo "🔄 {$description}\n";
            
            try {
                // Load seeder file
                $seederFile = __DIR__ . "/seeds/{$seederClass}.php";
                if (!file_exists($seederFile)) {
                    throw new Exception("Seeder file not found: {$seederFile}");
                }
                
                require_once $seederFile;
                
                // Check if class exists
                if (!class_exists($seederClass)) {
                    throw new Exception("Seeder class not found: {$seederClass}");
                }
                
                // Create instance and run seeder
                $seeder = new $seederClass();
                if (!method_exists($seeder, 'seed')) {
                    throw new Exception("Seeder class {$seederClass} does not have a seed() method");
                }
                
                $seeder->seed();
                $successCount++;
                
                echo "   ✅ Completed successfully!\n\n";
                
            } catch (Exception $e) {
                $errorCount++;
                echo "   ❌ Error: " . $e->getMessage() . "\n\n";
                
                // Continue with other seeders even if one fails
                continue;
            }
        }

        $endTime = microtime(true);
        $duration = round($endTime - $startTime, 2);

        // Summary
        echo "🎯 " . str_repeat("=", 60) . "\n";
        echo "📊 SEEDING SUMMARY\n";
        echo "=" . str_repeat("=", 60) . "\n";
        echo "✅ Successful: {$successCount}\n";
        echo "❌ Failed: {$errorCount}\n";
        echo "⏱️ Total Time: {$duration} seconds\n\n";

        if ($errorCount === 0) {
            echo "🎉 All seeders completed successfully!\n";
            echo "🚀 Your Healthcare Management System database is ready!\n\n";
            
            // Display test credentials
            $this->displayTestCredentials();
            
        } else {
            echo "⚠️ Some seeders failed. Please check the errors above.\n";
        }
        
        echo "📝 Next Steps:\n";
        echo "   1. Start your backend server\n";
        echo "   2. Start your frontend development server\n";
        echo "   3. Login with the test credentials above\n";
        echo "   4. Explore the enhanced dashboard features!\n\n";
    }

    private function displayTestCredentials()
    {
        echo "🔑 TEST CREDENTIALS\n";
        echo "-" . str_repeat("-", 40) . "\n";
        echo "🛡️  Admin:        admin@example.com / password123\n";
        echo "👨‍⚕️  Doctor:       jane.smith@example.com / password123\n";
        echo "👥 Receptionist:  bob.receptionist@example.com / password123\n";
        echo "🏥 Patient:       john.doe@example.com / password123\n";
        echo "🏥 Medical Coordinator: medical.coordinator@example.com / password123\n";
        echo "👑 Super Admin:   super.admin@example.com / password123\n\n";
    }

    public function unseed()
    {
        echo "🗑️ Starting database cleanup...\n\n";
        
        $seeders = [
            'EscalationManagementSeeder',
            'PatientDoctorAssignmentSeeder',
            'FinancialSeeder',
            'MedicalRecordSeeder', 
            'AppointmentSeeder',
            'SystemConfigSeeder',
            'RoleFeatureAccessSeeder',
            'UserDynamicRolesSeeder',
            'DynamicRBACSeeder',
            'UserSeeder'
        ];

        foreach (array_reverse($seeders) as $seederClass) {
            try {
                $seederFile = __DIR__ . "/seeds/{$seederClass}.php";
                if (file_exists($seederFile)) {
                    require_once $seederFile;
                    $seeder = new $seederClass();
                    if (method_exists($seeder, 'unseed')) {
                        $seeder->unseed();
                    }
                }
            } catch (Exception $e) {
                echo "⚠️ Warning during cleanup: " . $e->getMessage() . "\n";
            }
        }
        
        echo "✅ Database cleanup completed!\n\n";
    }
}

// Handle command line arguments
if ($argc > 1 && $argv[1] === 'unseed') {
    $seeder = new MasterSeeder();
    $seeder->unseed();
} else {
    $seeder = new MasterSeeder();
    $seeder->run();
}