# Frontend Build Optimization Script for Windows PowerShell

Write-Host "🚀 Frontend Build Optimization Started..." -ForegroundColor Green

# Enable Docker BuildKit for better performance
$env:DOCKER_BUILDKIT = "1"
$env:COMPOSE_DOCKER_CLI_BUILD = "1"

Write-Host "✅ Docker BuildKit enabled for faster builds" -ForegroundColor Yellow

# Build with optimizations
Write-Host "🔧 Building frontend with optimizations..." -ForegroundColor Cyan

try {
    # Build using the optimized Dockerfile
    docker build `
        --target production `
        --cache-from healthcare-app-frontend:latest `
        --tag healthcare-app-frontend:latest `
        --file frontend/Dockerfile `
        frontend/
    
    if ($LASTEXITCODE -eq 0) {
        Write-Host "✅ Frontend build completed successfully!" -ForegroundColor Green
    } else {
        Write-Host "❌ Build failed with exit code $LASTEXITCODE" -ForegroundColor Red
        exit $LASTEXITCODE
    }
} catch {
    Write-Host "❌ Build failed: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}

Write-Host "📊 Build optimization summary:" -ForegroundColor Blue
Write-Host "  - Multi-stage build reduces final image size" -ForegroundColor White
Write-Host "  - .dockerignore reduces build context from 35MB+ to ~2MB" -ForegroundColor White
Write-Host "  - BuildKit enables advanced caching and parallel processing" -ForegroundColor White
Write-Host "  - Alpine base image reduces download time" -ForegroundColor White
Write-Host "  - npm ci instead of npm install for faster dependency installation" -ForegroundColor White