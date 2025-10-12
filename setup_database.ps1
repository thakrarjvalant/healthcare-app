# Healthcare Management System Database Setup Script
Write-Host "🌱 Setting up Healthcare Management System Database..." -ForegroundColor Green
Write-Host ("=" * 60) -ForegroundColor Green

# Check if MySQL is installed and running
Write-Host "🔍 Checking for MySQL installation..." -ForegroundColor Yellow

# Try to find MySQL installation
$mysqlPaths = @(
    "C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql.exe",
    "C:\Program Files\MySQL\MySQL Server 5.7\bin\mysql.exe",
    "C:\xampp\mysql\bin\mysql.exe",
    "C:\wamp64\bin\mysql\mysql8.0.27\bin\mysql.exe"
)

$mysqlPath = $null
foreach ($path in $mysqlPaths) {
    if (Test-Path $path) {
        $mysqlPath = $path
        break
    }
}

if ($mysqlPath -eq $null) {
    Write-Host "❌ MySQL not found. Please install MySQL or XAMPP." -ForegroundColor Red
    exit 1
}

Write-Host "✅ MySQL found at: $mysqlPath" -ForegroundColor Green

# Create database
Write-Host "🔄 Creating database..." -ForegroundColor Yellow
& $mysqlPath -u root -e "CREATE DATABASE IF NOT EXISTS healthcare_db;"
if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ Failed to create database" -ForegroundColor Red
    exit 1
}
Write-Host "✅ Database 'healthcare_db' created successfully" -ForegroundColor Green

# Create tables
Write-Host "🔄 Creating tables..." -ForegroundColor Yellow
$tablesSQL = @"
USE healthcare_db;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('patient', 'doctor', 'receptionist', 'admin', 'medical_coordinator') NOT NULL DEFAULT 'patient',
    verified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS dynamic_roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) UNIQUE NOT NULL,
    display_name VARCHAR(100) NOT NULL,
    description TEXT,
    color VARCHAR(7),
    icon VARCHAR(50),
    is_system_role BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS feature_modules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) UNIQUE NOT NULL,
    display_name VARCHAR(100) NOT NULL,
    description TEXT,
    icon VARCHAR(50),
    color VARCHAR(7),
    is_core_module BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS dynamic_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,
    display_name VARCHAR(100) NOT NULL,
    module VARCHAR(50),
    feature VARCHAR(50),
    action VARCHAR(50),
    resource VARCHAR(50) NULL,
    is_system_permission BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS dynamic_role_permissions (
    role_id INT,
    permission_id INT,
    PRIMARY KEY (role_id, permission_id)
);

# This table is no longer used
);
"@

$tablesSQL | Out-File -FilePath "temp_tables.sql" -Encoding UTF8
& $mysqlPath -u root < "temp_tables.sql"
Remove-Item "temp_tables.sql" -Force

if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ Failed to create tables" -ForegroundColor Red
    exit 1
}
Write-Host "✅ Tables created successfully" -ForegroundColor Green

# Insert users
Write-Host "🔄 Seeding users..." -ForegroundColor Yellow
$usersSQL = @"
USE healthcare_db;

INSERT IGNORE INTO users (name, email, password, role, verified) VALUES 
('John Doe', 'john.doe@example.com', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'patient', TRUE),
('Dr. Jane Smith', 'jane.smith@example.com', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'doctor', TRUE),
('Receptionist Bob', 'bob.receptionist@example.com', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'receptionist', TRUE),
('Admin User', 'admin@example.com', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', TRUE),
('Medical Coordinator', 'medical.coordinator@example.com', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'medical_coordinator', TRUE);
"@

$usersSQL | Out-File -FilePath "temp_users.sql" -Encoding UTF8
& $mysqlPath -u root < "temp_users.sql"
Remove-Item "temp_users.sql" -Force

if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ Failed to seed users" -ForegroundColor Red
    exit 1
}
Write-Host "✅ Users seeded successfully" -ForegroundColor Green

# Verify users
Write-Host "🔍 Verifying database content..." -ForegroundColor Yellow
$verifySQL = @"
USE healthcare_db;
SELECT COUNT(*) as user_count FROM users;
SELECT id, name, email, role FROM users ORDER BY id;
"@

$verifySQL | Out-File -FilePath "temp_verify.sql" -Encoding UTF8
& $mysqlPath -u root < "temp_verify.sql"
Remove-Item "temp_verify.sql" -Force

Write-Host "🎉 Database setup completed successfully!" -ForegroundColor Green
Write-Host "🔑 Test Credentials:" -ForegroundColor Green
Write-Host "   Admin: admin@example.com / password123" -ForegroundColor Cyan
Write-Host "   Medical Coordinator: medical.coordinator@example.com / password123" -ForegroundColor Cyan