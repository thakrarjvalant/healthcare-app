<?php

/**
 * 🏗️ Database Migration Runner
 * This script runs all migrations in the correct order to set up the database schema
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

class MigrationRunner
{
    private $db;
    private $migratedFiles = [];

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
        echo "🏗️ Starting Healthcare Management System Database Migration\n";
        echo "=" . str_repeat("=", 60) . "\n\n";

        // Get list of already migrated files
        $this->loadMigratedFiles();

        // Define migration order (core first, then features)
        $migrationPaths = [
            __DIR__ . '/migrations/core',
            __DIR__ . '/migrations/features'
        ];

        $allMigrations = [];
        
        foreach ($migrationPaths as $path) {
            if (is_dir($path)) {
                $files = glob($path . '/*.php');
                foreach ($files as $file) {
                    $allMigrations[] = $file;
                }
            }
        }

        // Sort migrations by filename to ensure correct order
        usort($allMigrations, function($a, $b) {
            return strcmp(basename($a), basename($b));
        });

        $startTime = microtime(true);
        $successCount = 0;
        $errorCount = 0;

        foreach ($allMigrations as $migrationFile) {
            $filename = basename($migrationFile);
            
            // Skip if already migrated
            if (in_array($filename, $this->migratedFiles)) {
                echo "⏭️  Skipping already migrated: {$filename}\n";
                continue;
            }

            echo "🔄 Running migration: {$filename}\n";
            
            try {
                // Load migration file
                if (!file_exists($migrationFile)) {
                    throw new Exception("Migration file not found: {$migrationFile}");
                }
                
                require_once $migrationFile;
                
                // Extract class name from file content
                $className = $this->getClassNameFromFile($migrationFile);
                
                // Check if class exists
                if (!class_exists($className)) {
                    throw new Exception("Migration class not found: {$className}");
                }
                
                // Create instance and run migration
                $migration = new $className();
                if (!method_exists($migration, 'up')) {
                    throw new Exception("Migration class {$className} does not have an up() method");
                }
                
                $migration->up();
                $successCount++;
                
                // Record successful migration
                $this->recordMigration($filename);
                
                echo "   ✅ Completed successfully!\n\n";
                
            } catch (Exception $e) {
                $errorCount++;
                echo "   ❌ Error: " . $e->getMessage() . "\n\n";
                
                // Continue with other migrations even if one fails
                continue;
            }
        }

        $endTime = microtime(true);
        $duration = round($endTime - $startTime, 2);

        // Summary
        echo "🎯 " . str_repeat("=", 60) . "\n";
        echo "📊 MIGRATION SUMMARY\n";
        echo "=" . str_repeat("=", 60) . "\n";
        echo "✅ Successful: {$successCount}\n";
        echo "❌ Failed: {$errorCount}\n";
        echo "⏱️ Total Time: {$duration} seconds\n\n";

        if ($errorCount === 0) {
            echo "🎉 All migrations completed successfully!\n";
            echo "🚀 Your Healthcare Management System database schema is ready!\n\n";
        } else {
            echo "⚠️ Some migrations failed. Please check the errors above.\n";
        }
    }

    private function getClassNameFromFile($filePath)
    {
        // Read the file content
        $content = file_get_contents($filePath);
        
        // Use regex to find the class name
        if (preg_match('/class\s+(\w+)/', $content, $matches)) {
            return $matches[1];
        }
        
        throw new Exception("Could not extract class name from file: {$filePath}");
    }

    private function loadMigratedFiles()
    {
        try {
            // Create migrations table if it doesn't exist
            $this->db->exec("CREATE TABLE IF NOT EXISTS migrations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255) NOT NULL UNIQUE,
                batch INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");
            
            // Load already migrated files
            $stmt = $this->db->prepare("SELECT migration FROM migrations ORDER BY id");
            $stmt->execute();
            $this->migratedFiles = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        } catch (Exception $e) {
            echo "⚠️ Warning: Could not load migration history: " . $e->getMessage() . "\n";
        }
    }

    private function recordMigration($filename)
    {
        try {
            // Get the current batch number
            $stmt = $this->db->prepare("SELECT MAX(batch) FROM migrations");
            $stmt->execute();
            $batch = $stmt->fetchColumn() ?: 0;
            $batch++;
            
            // Record the migration
            $stmt = $this->db->prepare("INSERT INTO migrations (migration, batch) VALUES (?, ?)");
            $stmt->execute([$filename, $batch]);
            
            // Add to in-memory list
            $this->migratedFiles[] = $filename;
        } catch (Exception $e) {
            echo "⚠️ Warning: Could not record migration: " . $e->getMessage() . "\n";
        }
    }

    public function rollback()
    {
        echo "⏪ Starting Database Rollback\n";
        echo "=" . str_repeat("=", 40) . "\n\n";

        try {
            // Get the last batch of migrations
            $stmt = $this->db->prepare("SELECT migration FROM migrations WHERE batch = (SELECT MAX(batch) FROM migrations) ORDER BY id DESC");
            $stmt->execute();
            $migrations = $stmt->fetchAll(\PDO::FETCH_COLUMN);

            if (empty($migrations)) {
                echo "ℹ️ No migrations to rollback.\n";
                return;
            }

            foreach ($migrations as $filename) {
                echo "🔙 Rolling back: {$filename}\n";
                
                try {
                    $migrationFile = null;
                    $migrationPaths = [
                        __DIR__ . '/migrations/core',
                        __DIR__ . '/migrations/features'
                    ];
                    
                    foreach ($migrationPaths as $path) {
                        $potentialFile = $path . '/' . $filename;
                        if (file_exists($potentialFile)) {
                            $migrationFile = $potentialFile;
                            break;
                        }
                    }
                    
                    if (!$migrationFile || !file_exists($migrationFile)) {
                        throw new Exception("Migration file not found: {$filename}");
                    }
                    
                    require_once $migrationFile;
                    
                    // Extract class name from file content
                    $className = $this->getClassNameFromFile($migrationFile);
                    
                    // Check if class exists
                    if (!class_exists($className)) {
                        throw new Exception("Migration class not found: {$className}");
                    }
                    
                    // Create instance and run down method
                    $migration = new $className();
                    if (!method_exists($migration, 'down')) {
                        throw new Exception("Migration class {$className} does not have a down() method");
                    }
                    
                    $migration->down();
                    
                    // Remove from migrations table
                    $stmt = $this->db->prepare("DELETE FROM migrations WHERE migration = ?");
                    $stmt->execute([$filename]);
                    
                    echo "   ✅ Rolled back successfully!\n\n";
                    
                } catch (Exception $e) {
                    echo "   ❌ Error during rollback: " . $e->getMessage() . "\n\n";
                }
            }
            
            echo "✅ Rollback completed!\n\n";
            
        } catch (Exception $e) {
            echo "❌ Rollback failed: " . $e->getMessage() . "\n";
        }
    }
}

// Handle command line arguments
if ($argc > 1 && $argv[1] === 'rollback') {
    $runner = new MigrationRunner();
    $runner->rollback();
} else {
    $runner = new MigrationRunner();
    $runner->run();
}