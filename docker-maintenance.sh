#!/bin/bash

# Docker Maintenance Script for Healthcare Management System
# This script shuts down all containers, removes all images, and rebuilds the entire environment

echo "🏥 Healthcare Management System - Docker Maintenance"
echo "=================================================="
echo ""

# Check if we're in the correct directory
if [ ! -f "docker-compose.yml" ]; then
    echo "❌ Error: docker-compose.yml not found!"
    echo "Please run this script from the project root directory."
    exit 1
fi

echo "⏳ Step 1: Shutting down all containers..."
docker-compose down
if [ $? -eq 0 ]; then
    echo "✅ Containers stopped successfully"
else
    echo "⚠️  Warning: Some containers may not have stopped cleanly"
fi

echo ""
echo "🧹 Step 2: Removing all Docker images..."
# Remove all images related to this project
docker rmi -f healthcare-app-admin-ui:latest 2>/dev/null
docker rmi -f healthcare-app-appointment-service:latest 2>/dev/null
docker rmi -f healthcare-app-notification-service:latest 2>/dev/null
docker rmi -f healthcare-app-user-service:latest 2>/dev/null
docker rmi -f healthcare-app-storage-service:latest 2>/dev/null
docker rmi -f healthcare-app-billing-service:latest 2>/dev/null
docker rmi -f healthcare-app-clinical-service:latest 2>/dev/null
docker rmi -f healthcare-app-db-init:latest 2>/dev/null
docker rmi -f healthcare-app-frontend:latest 2>/dev/null
docker rmi -f healthcare-app-api-gateway:latest 2>/dev/null

# Also remove any dangling images
docker image prune -f 2>/dev/null

echo "✅ Docker images removed"

echo ""
echo "🏗️  Step 3: Rebuilding all Docker images..."
docker-compose build
if [ $? -eq 0 ]; then
    echo "✅ All images built successfully"
else
    echo "❌ Error: Failed to build images"
    exit 1
fi

echo ""
echo "🚀 Step 4: Starting all containers..."
docker-compose up -d
if [ $? -eq 0 ]; then
    echo "✅ All containers started successfully"
else
    echo "❌ Error: Failed to start containers"
    exit 1
fi

echo ""
echo "⏱️  Waiting for services to initialize..."
sleep 10

echo ""
echo "📋 Container Status:"
docker-compose ps

echo ""
echo "🎉 Docker maintenance completed successfully!"
echo "The Healthcare Management System is now running with fresh containers."
echo ""
echo "🔗 Access points:"
echo "   Frontend: http://localhost:3000"
echo "   API Gateway: http://localhost:8000"
echo "   User Service: http://localhost:8001"
echo "   Appointment Service: http://localhost:8002"
echo "   Clinical Service: http://localhost:8003"
echo "   Notification Service: http://localhost:8004"
echo "   Billing Service: http://localhost:8005"
echo "   Storage Service: http://localhost:8006"
echo "   Admin UI: http://localhost:8007"#!/bin/bash

# Docker Maintenance Script for Healthcare Management System
# This script shuts down all containers, removes all images, and rebuilds the entire environment

echo "🏥 Healthcare Management System - Docker Maintenance"
echo "=================================================="
echo ""

# Check if we're in the correct directory
if [ ! -f "docker-compose.yml" ]; then
    echo "❌ Error: docker-compose.yml not found!"
    echo "Please run this script from the project root directory."
    exit 1
fi

echo "⏳ Step 1: Shutting down all containers..."
docker-compose down
if [ $? -eq 0 ]; then
    echo "✅ Containers stopped successfully"
else
    echo "⚠️  Warning: Some containers may not have stopped cleanly"
fi

echo ""
echo "🧹 Step 2: Removing all Docker images..."
# Remove all images related to this project
docker rmi -f healthcare-app-admin-ui:latest 2>/dev/null
docker rmi -f healthcare-app-appointment-service:latest 2>/dev/null
docker rmi -f healthcare-app-notification-service:latest 2>/dev/null
docker rmi -f healthcare-app-user-service:latest 2>/dev/null
docker rmi -f healthcare-app-storage-service:latest 2>/dev/null
docker rmi -f healthcare-app-billing-service:latest 2>/dev/null
docker rmi -f healthcare-app-clinical-service:latest 2>/dev/null
docker rmi -f healthcare-app-db-init:latest 2>/dev/null
docker rmi -f healthcare-app-frontend:latest 2>/dev/null
docker rmi -f healthcare-app-api-gateway:latest 2>/dev/null

# Also remove any dangling images
docker image prune -f 2>/dev/null

echo "✅ Docker images removed"

echo ""
echo "🏗️  Step 3: Rebuilding all Docker images..."
docker-compose build
if [ $? -eq 0 ]; then
    echo "✅ All images built successfully"
else
    echo "❌ Error: Failed to build images"
    exit 1
fi

echo ""
echo "🚀 Step 4: Starting all containers..."
docker-compose up -d
if [ $? -eq 0 ]; then
    echo "✅ All containers started successfully"
else
    echo "❌ Error: Failed to start containers"
    exit 1
fi

echo ""
echo "⏱️  Waiting for services to initialize..."
sleep 10

echo ""
echo "📋 Container Status:"
docker-compose ps

echo ""
echo "🎉 Docker maintenance completed successfully!"
echo "The Healthcare Management System is now running with fresh containers."
echo ""
echo "🔗 Access points:"
echo "   Frontend: http://localhost:3000"
echo "   API Gateway: http://localhost:8000"
echo "   User Service: http://localhost:8001"
echo "   Appointment Service: http://localhost:8002"
echo "   Clinical Service: http://localhost:8003"
echo "   Notification Service: http://localhost:8004"
echo "   Billing Service: http://localhost:8005"
echo "   Storage Service: http://localhost:8006"
echo "   Admin UI: http://localhost:8007"