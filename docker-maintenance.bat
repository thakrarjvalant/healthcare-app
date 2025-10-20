@echo off
TITLE Healthcare Management System - Docker Maintenance

echo 🏥 Healthcare Management System - Docker Maintenance
echo ==================================================
echo.

REM Check if we're in the correct directory
if not exist "docker-compose.yml" (
    echo ❌ Error: docker-compose.yml not found!
    echo Please run this script from the project root directory.
    pause
    exit /b 1
)

echo ⏳ Step 1: Shutting down all containers...
docker-compose down
if %ERRORLEVEL% EQU 0 (
    echo ✅ Containers stopped successfully
) else (
    echo ⚠️  Warning: Some containers may not have stopped cleanly
)

echo.
echo 🧹 Step 2: Removing all Docker images...
REM Remove all images related to this project
docker rmi -f healthcare-app-admin-ui:latest 2>nul
docker rmi -f healthcare-app-appointment-service:latest 2>nul
docker rmi -f healthcare-app-notification-service:latest 2>nul
docker rmi -f healthcare-app-user-service:latest 2>nul
docker rmi -f healthcare-app-storage-service:latest 2>nul
docker rmi -f healthcare-app-billing-service:latest 2>nul
docker rmi -f healthcare-app-clinical-service:latest 2>nul
docker rmi -f healthcare-app-db-init:latest 2>nul
docker rmi -f healthcare-app-frontend:latest 2>nul
docker rmi -f healthcare-app-api-gateway:latest 2>nul

REM Also remove any dangling images
docker image prune -f 2>nul

echo ✅ Docker images removed

echo.
echo 🏗️  Step 3: Rebuilding all Docker images...
docker-compose build
if %ERRORLEVEL% EQU 0 (
    echo ✅ All images built successfully
) else (
    echo ❌ Error: Failed to build images
    pause
    exit /b 1
)

echo.
echo 🚀 Step 4: Starting all containers...
docker-compose up -d
if %ERRORLEVEL% EQU 0 (
    echo ✅ All containers started successfully
) else (
    echo ❌ Error: Failed to start containers
    pause
    exit /b 1
)

echo.
echo ⏱️  Waiting for services to initialize...
timeout /t 10 /nobreak >nul

echo.
echo 📋 Container Status:
docker-compose ps

echo.
echo 🎉 Docker maintenance completed successfully!
echo The Healthcare Management System is now running with fresh containers.
echo.
echo 🔗 Access points:
echo    Frontend: http://localhost:3000
echo    API Gateway: http://localhost:8000
echo    User Service: http://localhost:8001
echo    Appointment Service: http://localhost:8002
echo    Clinical Service: http://localhost:8003
echo    Notification Service: http://localhost:8004
echo    Billing Service: http://localhost:8005
echo    Storage Service: http://localhost:8006
echo    Admin UI: http://localhost:8007

echo.
echo Press any key to exit...
pause >nul