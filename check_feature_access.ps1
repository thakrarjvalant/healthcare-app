# PowerShell script to check feature access data in the database

# Check if Docker containers are running
Write-Host "Checking Docker containers..."
docker-compose ps

# Try to check the database directly
Write-Host "Checking feature access records..."
docker-compose exec db mysql -u root -pexample_password healthcare_db -e "SELECT COUNT(*) as count FROM role_feature_access;"

# Check feature modules
Write-Host "Checking feature modules..."
docker-compose exec db mysql -u root -pexample_password healthcare_db -e "SELECT * FROM feature_modules;"

# Check dynamic roles
Write-Host "Checking dynamic roles..."
docker-compose exec db mysql -u root -pexample_password healthcare_db -e "SELECT * FROM dynamic_roles;"