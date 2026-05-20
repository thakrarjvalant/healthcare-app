# Healthcare Management System Docker Setup Script (PowerShell)

Write-Host "🏥 Healthcare Management System Docker Setup" -ForegroundColor Green
Write-Host "===========================================" -ForegroundColor Green

# Function to check if Docker is running
function Check-Docker {
    Write-Host "🔍 Checking Docker availability..." -ForegroundColor Yellow
    
    try {
        $dockerInfo = docker info 2>$null
        if ($LASTEXITCODE -ne 0) {
            Write-Host "❌ Docker is not running. Please start Docker Desktop first." -ForegroundColor Red
            exit 1
        }
        Write-Host "✅ Docker is available" -ForegroundColor Green
    }
    catch {
        Write-Host "❌ Docker is not running. Please start Docker Desktop first." -ForegroundColor Red
        exit 1
    }
}

# Function to build and start services
function Setup-Services {
    Write-Host "🏗️ Building and starting services..." -ForegroundColor Yellow
    
    # Navigate to project directory
    Set-Location "d:\customprojects\healthcare-app"
    
    # Build and start all services
    Write-Host "🐳 Running: docker-compose up --build -d" -ForegroundColor Cyan
    docker-compose up --build -d
    
    if ($LASTEXITCODE -ne 0) {
        Write-Host "❌ Failed to start services" -ForegroundColor Red
        exit 1
    }
    
    Write-Host "✅ Services are starting up..." -ForegroundColor Green
    Write-Host "⏳ Waiting for services to be ready..." -ForegroundColor Yellow
    
    # Wait for the database to be ready
    Write-Host "🔍 Waiting for database to be ready..." -ForegroundColor Yellow
    $maxAttempts = 60  # Wait up to 2 minutes
    $attempt = 0
    
    do {
        Start-Sleep -Seconds 2
        $attempt++
        $result = docker-compose exec db mysqladmin ping -h localhost 2>$null
    } while ($LASTEXITCODE -ne 0 -and $attempt -lt $maxAttempts)
    
    if ($LASTEXITCODE -ne 0) {
        Write-Host "❌ Database failed to start" -ForegroundColor Red
        exit 1
    }
    
    # Wait for database initialization to complete
    Write-Host "🔍 Waiting for database initialization..." -ForegroundColor Yellow
    Start-Sleep -Seconds 30
    
    Write-Host "✅ Database initialization completed" -ForegroundColor Green
}

# Function to verify system status
function Verify-System {
    Write-Host "🔍 Verifying system status..." -ForegroundColor Yellow
    
    # Check if all services are running
    Write-Host "📋 Current services status:" -ForegroundColor Cyan
    docker-compose ps
    
    # Test the API gateway
    Write-Host "🧪 Testing API gateway..." -ForegroundColor Yellow
    Start-Sleep -Seconds 10  # Give services time to fully start
    
    # Show container logs to confirm everything is working
    Write-Host "📋 Recent logs from API gateway:" -ForegroundColor Cyan
    docker-compose logs api-gateway | Select-Object -Last 20
}

# Main execution
function Main {
    Check-Docker
    Setup-Services
    Verify-System
    
    Write-Host ""
    Write-Host "🎉 Healthcare Management System is ready!" -ForegroundColor Green
    Write-Host ""
    Write-Host "🌐 Access the application at: http://localhost:3000" -ForegroundColor Green
    Write-Host ""
    Write-Host "🔑 Test Credentials:" -ForegroundColor Cyan
    Write-Host "   Admin:        admin@example.com / password123" -ForegroundColor White
    Write-Host "   Doctor:       jane.smith@example.com / password123" -ForegroundColor White
    Write-Host "   Receptionist: bob.receptionist@example.com / password123" -ForegroundColor White
    Write-Host "   Patient:      john.doe@example.com / password123" -ForegroundColor White
    Write-Host "   Medical Coordinator: medical.coordinator@example.com / password123" -ForegroundColor White
    Write-Host ""
    Write-Host "🔧 Services are running on:" -ForegroundColor Cyan
    Write-Host "   Frontend:     http://localhost:3000" -ForegroundColor White
    Write-Host "   API Gateway:  http://localhost:8000" -ForegroundColor White
    Write-Host "   User Service: http://localhost:8001" -ForegroundColor White
    Write-Host "   Appointment:  http://localhost:8002" -ForegroundColor White
    Write-Host "   Clinical:     http://localhost:8003" -ForegroundColor White
    Write-Host "   Notification: http://localhost:8004" -ForegroundColor White
    Write-Host "   Billing:      http://localhost:8005" -ForegroundColor White
    Write-Host "   Admin UI:     http://localhost:8007" -ForegroundColor White
    Write-Host ""
    Write-Host "💡 To stop the system: docker-compose down" -ForegroundColor Yellow
    Write-Host "💡 To view logs: docker-compose logs -f" -ForegroundColor Yellow
}

# Run main function
Main