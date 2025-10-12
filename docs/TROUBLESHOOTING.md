# Healthcare App - Troubleshooting Guide

## 🔍 Common Issues & Solutions

### 1. Login Shows "Test Patient" Instead of Real User

**Symptoms:**
- Logging in with admin credentials shows "Welcome, Test Patient!"
- localStorage shows mock user data
- Wrong role displayed in dashboard

**Solution:**
```bash
# 1. Clear browser cache and localStorage
# Open browser Dev Tools (F12) → Application → Local Storage → Delete all

# 2. Ensure frontend is using latest build
docker-compose build frontend
docker-compose up -d frontend

# 3. Verify API is working
curl -X POST -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password123"}' \
  http://localhost:8000/api/users/login
```

### 2. API Gateway Not Responding

**Symptoms:**
- Frontend can't reach the API
- Network errors in browser console
- 502/503 errors

**Solution:**
```bash
# Check API Gateway status
docker-compose ps api-gateway

# Restart API Gateway
docker-compose restart api-gateway

# Check logs
docker-compose logs api-gateway

# Test health endpoint
curl http://localhost:8000/health
```

### 3. Database Connection Failed

**Symptoms:**
- Services can't connect to database
- "Database connection failed" errors
- Migration/seed failures

**Solution:**
```bash
# Restart database
docker-compose restart db

# Check database is accepting connections
docker-compose exec db mysql -u healthcare_user -pyour_strong_password -e "SELECT 1;"

# Verify database exists
docker-compose exec db mysql -u healthcare_user -pyour_strong_password healthcare_db -e "SHOW TABLES;"

# Re-run migrations if needed
docker-compose run --rm user-service php /var/www/shared/../database/migrate.php
```

### 4. Frontend Build Issues

**Symptoms:**
- Changes not reflected in browser
- Old version still running
- Build taking too long

**Solution:**
```bash
# Force rebuild without cache
docker-compose build --no-cache frontend

# Clean restart
docker-compose down
docker-compose up -d

# Check build logs
docker-compose logs frontend
```

### 5. Port Already in Use

**Symptoms:**
- "Port 3000 is already in use"
- Services won't start

**Solution:**
```bash
# Windows: Find what's using the port
netstat -ano | findstr :3000

# Stop conflicting services
docker-compose down

# Or kill the process using the port
taskkill /PID <PID> /F
```

### 6. User Service Mock Data Still Active

**Symptoms:**
- Login returns mock data instead of database users
- Old credentials still working
- Database users not recognized

**Solution:**
```bash
# Verify user service is using database
docker-compose exec user-service grep -n "DatabaseConnection" /var/www/html/api.php

# Check if shared database file exists
docker-compose exec user-service ls -la /var/www/shared/DatabaseConnection.php

# Restart user service
docker-compose restart user-service

# Test direct user service
curl -X POST -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password123"}' \
  http://localhost:8001/login
```

## 🔧 Advanced Troubleshooting

### Container Logs
```bash
# View all logs
docker-compose logs

# Specific service logs
docker-compose logs frontend
docker-compose logs api-gateway
docker-compose logs user-service

# Follow logs in real-time
docker-compose logs -f frontend
```

### Service Status Check
```bash
# Check all services
docker-compose ps

# Health checks
curl http://localhost:8000/health          # API Gateway
curl http://localhost:3000                 # Frontend
curl http://localhost:8001/users          # User Service
```

### Database Inspection
```bash
# Connect to database
docker-compose exec db mysql -u healthcare_user -pyour_strong_password healthcare_db

# Check users table
SELECT id, name, email, role FROM users;

# Verify password hashes
SELECT email, LEFT(password, 20) as password_preview FROM users;
```

### Network Connectivity
```bash
# Test container networking
docker-compose exec frontend ping api-gateway
docker-compose exec api-gateway ping user-service
docker-compose exec user-service ping db
```

## 🚨 Emergency Reset

If all else fails, perform a complete reset:

```bash
# 1. Stop all services
docker-compose down --volumes --remove-orphans

# 2. Remove all containers and images
docker system prune -a

# 3. Rebuild everything
docker-compose build --no-cache

# 4. Start database first
docker-compose up -d db

# 5. Wait and run migrations
timeout /t 30
docker-compose run --rm user-service php /var/www/shared/../database/migrate.php
docker-compose run --rm user-service php /var/www/shared/../database/seed.php

# 6. Start all services
docker-compose up -d

# 7. Clear browser cache and localStorage
```

## 📞 Getting Help

If issues persist:

1. **Check logs** for specific error messages
2. **Verify environment** - Docker Desktop running, ports available
3. **Test individual components** - API, database, frontend separately
4. **Document the error** - exact error messages, steps to reproduce
5. **Check network connectivity** between services

### Useful Commands

```bash
# Quick service restart
docker-compose restart <service-name>

# Rebuild specific service
docker-compose build <service-name>

# Execute commands in container
docker-compose exec <service-name> <command>

# View container filesystem
docker-compose exec <service-name> ls -la /path

# Check environment variables
docker-compose exec <service-name> env
```