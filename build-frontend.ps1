# Healthcare App Frontend Docker Build Script
# This script should be run as Administrator to ensure Docker access

Write-Host "🚀 Healthcare App Frontend Docker Build" -ForegroundColor Green
Write-Host "=====================================" -ForegroundColor Blue

# Enable Docker BuildKit for optimized builds
$env:DOCKER_BUILDKIT = "1"
Write-Host "✅ Docker BuildKit enabled" -ForegroundColor Yellow

# Navigate to frontend directory
Set-Location -Path "d:\customprojects\healthcare-app\frontend"

# Clean up any previous build attempts
Write-Host "`n🧹 Cleaning up previous builds..." -ForegroundColor Cyan
docker system prune -f

# Build the frontend container
Write-Host "`n🔨 Building frontend container..." -ForegroundColor Blue
Write-Host "This may take several minutes depending on your system..." -ForegroundColor Gray

$buildStartTime = Get-Date
docker build -t healthcare-app-frontend .

if ($LASTEXITCODE -eq 0) {
    $buildEndTime = Get-Date
    $buildDuration = ($buildEndTime - $buildStartTime).TotalSeconds
    
    Write-Host "`n✅ Frontend container built successfully! (${buildDuration:F1}s)" -ForegroundColor Green
    Write-Host "`n🐳 Container Information:" -ForegroundColor Blue
    docker images healthcare-app-frontend --format "table {{.Repository}}\t{{.Tag}}\t{{.Size}}\t{{.CreatedAt}}"
    
    Write-Host "`n💡 To run the container:" -ForegroundColor Yellow
    Write-Host "docker run -p 3000:3000 healthcare-app-frontend" -ForegroundColor Cyan
    Write-Host "`nThen access the application at http://localhost:3000" -ForegroundColor Cyan
} else {
    Write-Host "`n❌ Frontend container build failed!" -ForegroundColor Red
    Write-Host "Please check the error messages above and ensure Docker is running properly." -ForegroundColor Yellow
}

Write-Host "`n✨ Build process completed!" -ForegroundColor Green