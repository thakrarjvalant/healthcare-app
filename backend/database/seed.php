<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Database\DatabaseConnection;

/**
 * Run database seeders
 */
class Seed
{
    private $db;

    public function __construct()
    {
        $this->db = DatabaseConnection::getInstance();
    }

    public function run()
    {
        // Get all seeder files
        $seederFiles = glob(__DIR__ . '/seeds/*.php');
        
        // Sort seeders by filename
        sort($seederFiles);
        
        foreach ($seederFiles as $file) {
            require_once $file;
            
            // Get class name from filename
            $className = basename($file, '.php');
            
            // Create instance of seeder class
            $seeder = new $className();
            
            // Run seed method
            $seeder->seed();
            
            echo "Ran seeder: $className\n";
        }
        
        echo "All seeders completed successfully.\n";
    }
}

// Run seeders
$seed = new Seed();
$seed->run();