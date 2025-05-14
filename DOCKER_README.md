# IWK Finance with Docker and Oracle

This guide provides instructions for running the IWK Finance application using Docker with Apache web server and Oracle database.

## Prerequisites

- Docker and Docker Compose installed
- Oracle Container Registry access (for Oracle Database image)
- At least 4GB of RAM available for containers

## Setup Instructions

### 1. Login to Oracle Container Registry

Before pulling the Oracle database image, you need to authenticate with the Oracle Container Registry:

```bash
docker login container-registry.oracle.com
```

You'll need an Oracle account with accepted terms for the Oracle Database software.

### 2. Prepare the Environment

Create necessary directories:

```bash
mkdir -p oracle_setup oracle_startup tnsnames docker/apache
```

### 3. Add Oracle Instance Client credentials

Ensure database connection configuration files are in place:

```bash
# Verify tnsnames.ora exists
cat tnsnames/tnsnames.ora
```

### 4. Build and Start Containers

```bash
# Build and start the containers
docker-compose up -d

# Monitor Oracle database initialization (this may take a few minutes)
docker logs -f iwk_oracle
```

### 5. Initialize the Laravel Application

Once the Oracle database is ready, run the following commands:

```bash
# Copy the environment configuration
docker exec iwk_finance_app cp docker.env .env

# Generate application key
docker exec iwk_finance_app php artisan key:generate

# Run migrations
docker exec iwk_finance_app php artisan migrate

# Optimize the application
docker exec iwk_finance_app php artisan optimize
```

### 6. Access the Application

The application should now be running at http://localhost:8000

## Container Architecture

The Docker setup consists of two main containers:

1. **app**: Apache container that runs both the web server and the Laravel application
2. **oracle**: Oracle XE database server

## Oracle Database Information

- Host: oracle
- Port: 1521
- Service Name: XEPDB1
- Username: iwk_finance
- Password: iwk_password
- Oracle Enterprise Manager Express: http://localhost:5500/em

## Container Management

```bash
# Stop containers
docker-compose down

# View logs
docker-compose logs -f

# Restart containers
docker-compose restart

# View Apache logs
docker exec -it iwk_finance_app tail -f /var/log/apache2/error.log
docker exec -it iwk_finance_app tail -f /var/log/apache2/access.log
```

## Troubleshooting

### Oracle Connection Issues

If the application cannot connect to Oracle:

1. Check Oracle container is running: `docker ps`
2. Verify Oracle logs: `docker logs iwk_oracle`
3. Ensure tnsnames.ora configuration is correct
4. Verify Oracle user was created successfully

### Web Server Issues

If you encounter web server issues:

1. Check Apache is running: `docker ps | grep app`
2. Verify Apache logs: `docker logs iwk_finance_app`
3. Test if the server is responding: `curl -I http://localhost:8000`

### Permission Issues

If you encounter permission issues with Laravel storage or cache:

```bash
docker exec iwk_finance_app chown -R www-data:www-data /var/www/html/storage
docker exec iwk_finance_app chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
``` 