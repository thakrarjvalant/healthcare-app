# Healthcare App - Complete Container Build Script
# Builds all containers one by one with progress tracking

Write-Host "🚀 Healthcare App - Complete Container Build Process" -ForegroundColor Green
Write-Host "=============================================" -ForegroundColor Blue

# Enable Docker BuildKit for optimized builds
$env:DOCKER_BUILDKIT = "1"
$env:COMPOSE_DOCKER_CLI_BUILD = "1"
Write-Host "✅ Docker BuildKit enabled" -ForegroundColor Yellow

# Clean up orphaned resources first
Write-Host "`n🧹 Cleaning Docker orphans and unused resources..." -ForegroundColor Cyan
docker system prune -af --volumes

# Define services in build order (dependencies first)
$services = @(
    @{name="db"; description="MySQL Database"},
    @{name="api-gateway"; description="API Gateway Service"},
    @{name="user-service"; description="User Management Service"},
    @{name="appointment-service"; description="Appointment Service"},
    @{name="clinical-service"; description="Clinical Records Service"},
    @{name="billing-service"; description="Billing & Invoicing Service"},
    @{name="notification-service"; description="Notification Service"},
    @{name="storage-service"; description="Document Storage Service"},
    @{name="admin-ui"; description="Admin User Interface"},
    @{name="frontend"; description="React Frontend Application"}
)

$totalServices = $services.Count
$currentService = 0
$successfulBuilds = @()
$failedBuilds = @()

foreach ($service in $services) {
    $currentService++
    $serviceName = $service.name
    $description = $service.description
    
    Write-Host "`n[$currentService/$totalServices] 🔨 Building $description ($serviceName)..." -ForegroundColor Blue
    Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Gray
    
    $buildStartTime = Get-Date
    
    try {
        # Build the service
        $buildResult = docker-compose build $serviceName 2>&1
        
        if ($LASTEXITCODE -eq 0) {
            $buildEndTime = Get-Date
            $buildDuration = ($buildEndTime - $buildStartTime).TotalSeconds
            
            Write-Host "✅ $description built successfully! (${buildDuration:F1}s)" -ForegroundColor Green
            $successfulBuilds += $serviceName
        } else {
            Write-Host "❌ $description build failed!" -ForegroundColor Red
            Write-Host "Error details:" -ForegroundColor Yellow
            Write-Host $buildResult -ForegroundColor Red
            $failedBuilds += $serviceName
        }
    }
    catch {
        Write-Host "❌ $description build failed with exception: $($_.Exception.Message)" -ForegroundColor Red
        $failedBuilds += $serviceName
    }
    
    # Show current progress
    Write-Host "Progress: $currentService/$totalServices completed" -ForegroundColor Gray
}

# Final Summary
Write-Host "`n🎯 BUILD SUMMARY" -ForegroundColor Blue
Write-Host "===================" -ForegroundColor Blue

if ($successfulBuilds.Count -gt 0) {
    Write-Host "`n✅ Successfully Built ($($successfulBuilds.Count) services):" -ForegroundColor Green
    foreach ($service in $successfulBuilds) {
        Write-Host "  • $service" -ForegroundColor White
    }
}

if ($failedBuilds.Count -gt 0) {
    Write-Host "`n❌ Failed Builds ($($failedBuilds.Count) services):" -ForegroundColor Red
    foreach ($service in $failedBuilds) {
        Write-Host "  • $service" -ForegroundColor White
    }
    Write-Host "`n🔧 To retry failed builds, run:" -ForegroundColor Yellow
    foreach ($service in $failedBuilds) {
        Write-Host "docker-compose build $service" -ForegroundColor Cyan
    }
}

# Show final Docker images
Write-Host "`n📊 Current Docker Images:" -ForegroundColor Blue
docker images --filter "reference=healthcare-app-*"

if ($failedBuilds.Count -eq 0) {
    Write-Host "`n🎉 All containers built successfully!" -ForegroundColor Green
    Write-Host "💡 Next steps:" -ForegroundColor Yellow
    Write-Host "  1. Run: docker-compose up -d" -ForegroundColor Cyan
    Write-Host "  2. Access frontend at: http://localhost:3000" -ForegroundColor Cyan
    Write-Host "  3. Check service status with: docker-compose ps" -ForegroundColor Cyan
} else {
    Write-Host "`n⚠️  Some containers failed to build. Please check the errors above." -ForegroundColor Yellow
}

Write-Host "`n✨ Build process completed!" -ForegroundColor Green