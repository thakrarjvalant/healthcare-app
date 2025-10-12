<?php

// Set up autoloading
require_once 'vendor/autoload.php';
require_once 'DatabaseConnection.php';

use Database\DatabaseConnection;

echo "🌱 Starting Healthcare Management System Database Setup\n";
echo "=" . str_repeat("=", 60) . "\n\n";

try {
    // Test database connection
    $db = DatabaseConnection::getInstance();
    echo "✅ Database connection established successfully!\n\n";
    
    // Run migrations
    echo "🔄 Running Migrations...\n";
    
    // Get all migration files
    $migrationFiles = glob(__DIR__ . '/migrations/*.php');
    
    // Sort migrations by filename
    sort($migrationFiles);
    
    foreach ($migrationFiles as $file) {
        echo "  📄 Processing: " . basename($file) . "\n";
        
        // Require the file and try to detect the migration class it declared.
        $className = resolveMigrationClass($file);
        $migration = new $className();
        $migration->up();
        echo "  ✅ Completed: $className\n";
    }
    
    echo "\n✅ All migrations completed successfully!\n\n";
    
    // Run seeders
    echo "🔄 Running Seeders...\n";
    
    // Define seeding order (order matters for foreign key constraints)
    $seeders = [
        'UserSeeder' => '👥 Seeding Users (Admin, Doctors, Patients, Receptionists)',
        'RBACSeeder' => '🔐 Seeding RBAC (Roles, Permissions, Mappings)', 
        'SystemConfigSeeder' => '⚙️ Seeding System Configuration',
        'AppointmentSeeder' => '📅 Seeding Appointments',
        'MedicalRecordSeeder' => '🏥 Seeding Medical Records & Clinical Data',
        'FinancialSeeder' => '💰 Seeding Financial Data (Invoices, Payments)',
        'DynamicRBACSeeder' => '🎭 Seeding Dynamic RBAC System'
    ];

    foreach ($seeders as $seederClass => $description) {
        echo "  🔄 {$description}\n";
        
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
            echo "  ✅ Completed successfully!\n";
            
        } catch (Exception $e) {
            echo "  ❌ Error: " . $e->getMessage() . "\n";
            // Continue with other seeders even if one fails
            continue;
        }
    }
    
    echo "\n✅ All seeders completed successfully!\n\n";
    
    // Display test credentials
    displayTestCredentials();
    
    // Show user count
    echo "📊 Verifying database content...\n";
    $stmt = $db->getConnection()->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    echo "👥 Total users in database: " . $result['count'] . "\n";
    
    // List all users
    $stmt = $db->getConnection()->query("SELECT id, name, email, role FROM users ORDER BY id");
    $users = $stmt->fetchAll();
    echo "📋 Users list:\n";
    foreach ($users as $user) {
        echo "  - ID: {$user['id']}, Name: {$user['name']}, Email: {$user['email']}, Role: {$user['role']}\n";
    }
    
    echo "\n🎉 Database setup completed successfully!\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

// Try to detect the migration class declared by the file.
function resolveMigrationClass(string $file): string
{
    $before = get_declared_classes();
    require_once $file;
    $after = get_declared_classes();

    $new = array_values(array_diff($after, $before));

    // Prefer any newly-declared class that has an up() method.
    foreach ($new as $c) {
        if (method_exists($c, 'up')) {
            return $c;
        }
    }

    // Fallback: try to convert filename to StudlyCase class name.
    $base = basename($file, '.php');
    $fallback = filenameToClassName($base);
    if ($fallback && class_exists($fallback) && method_exists($fallback, 'up')) {
        return $fallback;
    }

    // Also try fallback in global namespace (some files might declare class without namespace).
    if ($fallback && class_exists($fallback, false) && method_exists($fallback, 'up')) {
        return $fallback;
    }

    // Nothing found — show helpful error.
    throw new Exception("Could not detect migration class in file: $file");
}

// Convert a filename like "001_create_users_table" into "CreateUsersTable"
function filenameToClassName(string $name): string
{
    // Remove leading non-letters/digits (like numeric prefixes)
    $name = preg_replace('/^[^a-zA-Z]*/', '', $name);
    // Split on non-alphanumeric and uppercase each segment
    $parts = preg_split('/[^a-zA-Z0-9]+/', $name);
    $parts = array_filter($parts, fn($p) => $p !== '');
    $parts = array_map(fn($p) => ucfirst(strtolower($p)), $parts);
    return implode('', $parts);
}

function displayTestCredentials()
{
    echo "🔑 TEST CREDENTIALS\n";
    echo "-" . str_repeat("-", 40) . "\n";
    echo "🛡️  Admin:        admin@example.com / password123\n";
    echo "👨‍⚕️  Doctor:       jane.smith@example.com / password123\n";
    echo "👥 Receptionist:  bob.receptionist@example.com / password123\n";
    echo "🏥 Patient:       john.doe@example.com / password123\n";
    echo "🏥 Medical Coordinator: medical.coordinator@example.com / password123\n";
    echo "\n";
}