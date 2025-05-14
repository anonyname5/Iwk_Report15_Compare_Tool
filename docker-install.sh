#!/bin/bash
set -e

echo "Running Docker installation script for IWK Finance system..."

# Make sure directories exist
mkdir -p oracle_setup oracle_startup tnsnames docker/apache

# Ensure script is executable
chmod +x docker-install.sh

# Check if Docker and Docker Compose are installed
if ! command -v docker >/dev/null 2>&1; then
    echo "Error: Docker is not installed. Please install Docker first."
    exit 1
fi

if ! command -v docker-compose >/dev/null 2>&1; then
    echo "Error: Docker Compose is not installed. Please install Docker Compose first."
    exit 1
fi

echo "Logging in to Oracle Container Registry..."
echo "Please enter your Oracle Container Registry credentials when prompted"
echo "If you don't have an account, please register at https://container-registry.oracle.com"
docker login container-registry.oracle.com

echo "Building and starting containers..."
docker-compose up -d

echo "Waiting for Oracle database to initialize (this may take several minutes)..."
echo "Checking status every 30 seconds..."

# Wait for Oracle to be ready
ready=false
max_attempts=30
attempt=1

while [ $attempt -le $max_attempts ]; do
    echo "Attempt $attempt/$max_attempts: Checking if Oracle is ready..."
    
    if docker logs iwk_oracle 2>&1 | grep -q "DATABASE IS READY TO USE!"; then
        ready=true
        break
    fi
    
    echo "Oracle database not ready yet. Waiting 30 seconds..."
    sleep 30
    (( attempt++ ))
done

if [ "$ready" = false ]; then
    echo "Error: Oracle database did not initialize within the expected time."
    echo "Please check logs with: docker logs iwk_oracle"
    exit 1
fi

echo "Oracle database is ready!"

# Check if Apache is running properly
echo "Checking Apache web server status..."
if ! docker ps | grep -q iwk_finance_app; then
    echo "Error: Apache container is not running."
    echo "Please check logs with: docker logs iwk_finance_app"
    exit 1
fi

echo "Apache is running properly!"

echo "Setting up Laravel application..."
docker exec iwk_finance_app cp docker.env .env
docker exec iwk_finance_app php artisan key:generate
docker exec iwk_finance_app composer install --no-dev --optimize-autoloader
docker exec iwk_finance_app php artisan config:cache
docker exec iwk_finance_app php artisan route:cache
docker exec iwk_finance_app php artisan view:cache

echo "Setting correct permissions..."
docker exec iwk_finance_app chown -R www-data:www-data /var/www/html/storage
docker exec iwk_finance_app chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

echo "Installation completed successfully!"
echo "The IWK Finance application is now available at: http://localhost:8000"
echo ""
echo "Container Information:"
echo "- Web Server: Apache (iwk_finance_app)"
echo "- Database: Oracle XE (iwk_oracle)"
echo ""
echo "Oracle Database Information:"
echo "- Host: oracle"
echo "- Port: 1521"
echo "- Service Name: XEPDB1"
echo "- Username: iwk_finance"
echo "- Password: iwk_password"
echo "- Oracle Enterprise Manager Express: http://localhost:5500/em" 