<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Database\DatabaseConnection;

/**
 * Run database migrations
 */
class Migrate
{
    private $db;

    public function __construct()
    {
        $this->db = DatabaseConnection::getInstance();
    }

    public function run()
    {
        // Get all migration files
        $migrationFiles = glob(__DIR__ . '/migrations/*.php');
        
        // Sort migrations by filename
        sort($migrationFiles);
        
        foreach ($migrationFiles as $file) {
            require_once $file;
            
            // Get class name from filename
            $className = basename($file, '.php');
            
            // Create instance of migration class
            $migration = new $className();
            
            // Run up method
            $migration->up();
            
            echo "Ran migration: $className\n";
        }
        
        echo "All migrations completed successfully.\n";
    }
}

// Run migrations
$migrate = new Migrate();
$migrate->run();