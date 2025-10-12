<?php

// Robustly load Composer autoload (give a clear error if it's missing)
$autoload = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
} else {
    // Try an alternate location (in case script is moved)
    $alt = __DIR__ . '/vendor/autoload.php';
    if (file_exists($alt)) {
        require_once $alt;
    } else {
        fwrite(STDERR, "Composer autoload not found at: $autoload\n");
        fwrite(STDERR, "Run 'composer install' in the project root (d:\\customprojects\\healthcare-app) to create vendor/autoload.php\n");
        exit(1);
    }
}

use Database\DatabaseConnection;

// Ensure the DatabaseConnection class is available (try common locations before failing)
if (!class_exists('Database\\DatabaseConnection')) {
	$candidates = [
		__DIR__ . '/DatabaseConnection.php',
		__DIR__ . '/../DatabaseConnection.php',
		__DIR__ . '/../database/DatabaseConnection.php',
		__DIR__ . '/../../src/Database/DatabaseConnection.php',
		__DIR__ . '/../../Database/DatabaseConnection.php',
		__DIR__ . '/../../../src/Database/DatabaseConnection.php',
	];
	$tried = [];
	$found = false;
	foreach ($candidates as $path) {
		$tried[] = $path;
		if (file_exists($path)) {
			require_once $path;
			if (class_exists('Database\\DatabaseConnection')) {
				$found = true;
				break;
			}
		}
	}
	if (!$found) {
		fwrite(STDERR, "Class Database\\DatabaseConnection not found. Tried the following files:\n");
		foreach ($tried as $p) {
			fwrite(STDERR, " - $p\n");
		}
		fwrite(STDERR, "Make sure the class exists and is autoloadable (run 'composer dump-autoload' or 'composer install' in the project root), or create:\n");
		fwrite(STDERR, "d:\\customprojects\\healthcare-app\\backend\\database\\DatabaseConnection.php implementing Database\\DatabaseConnection::getInstance().\n");
		exit(1);
	}
}

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

    public function run($unseedFirst = false)
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
            
            // If unseedFirst is true and seeder has unseed method, run it
            if ($unseedFirst && method_exists($seeder, 'unseed')) {
                $seeder->unseed();
                echo "Unseeded: $className\n";
            }
            
            // Run seed method
            $seeder->seed();
            
            echo "Ran seeder: $className\n";
        }
        
        echo "All seeders completed successfully.\n";
    }
}

// Run seeders
$seed = new Seed();

// Check if --unseed flag is passed
$unseedFirst = in_array('--unseed', $argv);

$seed->run($unseedFirst);
