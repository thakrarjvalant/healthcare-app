#!/bin/bash
set -e

echo "=== Healthcare Management System Startup ==="

# Kill any existing PHP servers
pkill -f "php -S" 2>/dev/null || true
sleep 1

echo "Starting API Gateway on port 8000..."
cd /home/runner/workspace/backend/api-gateway
php -S 0.0.0.0:8000 index.php > /tmp/api-gateway.log 2>&1 &

echo "Starting User Service on port 8001..."
cd /home/runner/workspace/backend/user-service
php -S 0.0.0.0:8001 index.php > /tmp/user-service.log 2>&1 &

echo "Starting Appointment Service on port 8002..."
cd /home/runner/workspace/backend/appointment-service
php -S 0.0.0.0:8002 index.php > /tmp/appointment-service.log 2>&1 &

echo "Starting Clinical Service on port 8003..."
cd /home/runner/workspace/backend/clinical-service
php -S 0.0.0.0:8003 index.php > /tmp/clinical-service.log 2>&1 &

echo "Starting Notification Service on port 8004..."
cd /home/runner/workspace/backend/notification-service
php -S 0.0.0.0:8004 index.php > /tmp/notification-service.log 2>&1 &

echo "Starting Billing Service on port 8005..."
cd /home/runner/workspace/backend/billing-service
php -S 0.0.0.0:8005 index.php > /tmp/billing-service.log 2>&1 &

echo "Starting Storage Service on port 8006..."
cd /home/runner/workspace/backend/storage
php -S 0.0.0.0:8006 index.php > /tmp/storage-service.log 2>&1 &

echo "Starting Admin UI Service on port 8007..."
cd /home/runner/workspace/backend/admin-ui
php -S 0.0.0.0:8007 index.php > /tmp/admin-ui.log 2>&1 &

# Wait for API gateway to be ready before starting frontend
echo "Waiting for API gateway to be ready..."
for i in $(seq 1 20); do
    if curl -s http://localhost:8000/health > /dev/null 2>&1; then
        echo "API gateway is ready!"
        break
    fi
    sleep 0.5
done

echo "All backend services started!"
echo ""
echo "Starting React frontend on port 5000..."
cd /home/runner/workspace/frontend
HOST=0.0.0.0 PORT=5000 npm start
