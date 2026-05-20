#!/bin/bash
# Healthcare Management System Docker Setup Script

set -e

echo "🏥 Healthcare Management System Docker Setup"
echo "==========================================="

# Function to check if Docker is running
check_docker() {
    echo "🔍 Checking Docker availability..."
    if ! docker info >/dev/null 2>&1; then
        echo "❌ Docker is not running. Please start Docker Desktop first."
        exit 1
    fi
    echo "✅ Docker is available"
}

# Function to build and start services
setup_services() {
    echo "🏗️ Building and starting services..."
    
    # Navigate to project directory
    cd /d/customprojects/healthcare-app
    
    # Build and start all services
    docker-compose up --build -d
    
    echo "✅ Services are starting up..."
    echo "⏳ Waiting for services to be ready..."
    
    # Wait for the database to be ready
    echo "🔍 Waiting for database to be ready..."
    timeout 120 sh -c 'until docker-compose exec db mysqladmin ping -h localhost --silent; do sleep 2; done' || {
        echo "❌ Database failed to start"
        exit 1
    }
    
    # Wait for database initialization to complete
    echo "🔍 Waiting for database initialization..."
    sleep 30
    
    echo "✅ Database initialization completed"
}

# Function to verify system status
verify_system() {
    echo "🔍 Verifying system status..."
    
    # Check if all services are running
    docker-compose ps
    
    # Test the API gateway
    echo "🧪 Testing API gateway..."
    sleep 10  # Give services time to fully start
    
    # Show container logs to confirm everything is working
    echo "📋 Recent logs from API gateway:"
    docker-compose logs api-gateway | tail -20
}

# Main execution
main() {
    check_docker
    setup_services
    verify_system
    
    echo ""
    echo "🎉 Healthcare Management System is ready!"
    echo ""
    echo "🌐 Access the application at: http://localhost:3000"
    echo ""
    echo "🔑 Test Credentials:"
    echo "   Admin:        admin@example.com / password123"
    echo "   Doctor:       jane.smith@example.com / password123"
    echo "   Receptionist: bob.receptionist@example.com / password123"
    echo "   Patient:      john.doe@example.com / password123"
    echo "   Medical Coordinator: medical.coordinator@example.com / password123"
    echo ""
    echo "🔧 Services are running on:"
    echo "   Frontend:     http://localhost:3000"
    echo "   API Gateway:  http://localhost:8000"
    echo "   User Service: http://localhost:8001"
    echo "   Appointment:  http://localhost:8002"
    echo "   Clinical:     http://localhost:8003"
    echo "   Notification: http://localhost:8004"
    echo "   Billing:      http://localhost:8005"
    echo "   Admin UI:     http://localhost:8007"
    echo ""
    echo "💡 To stop the system: docker-compose down"
    echo "💡 To view logs: docker-compose logs -f"
}

# Run main function
main "$@"