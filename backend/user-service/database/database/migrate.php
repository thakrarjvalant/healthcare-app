<?php

// Robustly load Composer autoload (give a clear error if it's missing)
$autoload = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
} else {
    fwrite(STDERR, "Composer autoload not found at: $autoload\n");
    fwrite(STDERR, "Run 'composer install' in the backend/database directory to create vendor/autoload.php\n");
    exit(1);
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
            // Require the file and try to detect the migration class it declared.
            $className = $this->resolveMigrationClass($file);
            $migration = new $className();
            $migration->up();
            echo "Ran migration: $className\n";
        }
        
        echo "All migrations completed successfully.\n";
    }

    // Try to detect the migration class declared by the file.
    private function resolveMigrationClass(string $file): string
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
        $fallback = $this->filenameToClassName($base);
        if ($fallback && class_exists($fallback) && method_exists($fallback, 'up')) {
            return $fallback;
        }

        // Also try fallback in global namespace (some files might declare class without namespace).
        if ($fallback && class_exists($fallback, false) && method_exists($fallback, 'up')) {
            return $fallback;
        }

        // Nothing found — show helpful error.
        fwrite(STDERR, "Could not detect migration class in file: $file\n");
        if (!empty($new)) {
            fwrite(STDERR, "Declared classes after include:\n");
            foreach ($new as $c) {
                fwrite(STDERR, " - $c\n");
            }
        } else {
            fwrite(STDERR, "No new classes were declared by the file.\n");
        }
        fwrite(STDERR, "Tried fallback class name: $fallback\n");
        fwrite(STDERR, "Ensure the migration file declares a class with an up() method, or returns/exports a known class.\n");
        exit(1);
    }

    // Convert a filename like "001_create_users_table" into "CreateUsersTable"
    private function filenameToClassName(string $name): string
    {
        // Remove leading non-letters/digits (like numeric prefixes)
        $name = preg_replace('/^[^a-zA-Z]*/', '', $name);
        // Split on non-alphanumeric and uppercase each segment
        $parts = preg_split('/[^a-zA-Z0-9]+/', $name);
        $parts = array_filter($parts, fn($p) => $p !== '');
        $parts = array_map(fn($p) => ucfirst(strtolower($p)), $parts);
        return implode('', $parts);
    }

}

// Run migrations
$migrate = new Migrate();
$migrate->run();