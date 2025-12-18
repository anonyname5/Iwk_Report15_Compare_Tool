# Quick Start Guide

This guide will help you get the IWK Report 15 Comparison Tool up and running quickly.

## 🐳 Option 1: Docker Installation (Recommended)

### Prerequisites
- Docker Desktop for Windows installed and running
- Oracle Container Registry account (free registration at https://container-registry.oracle.com)

### Steps

1. **Open PowerShell or Command Prompt** in the project directory:
   ```powershell
   cd "C:\Users\User\Downloads\Mini Project\Iwk_Report15_Compare_Tool"
   ```

2. **Login to Oracle Container Registry**:
   ```powershell
   docker login container-registry.oracle.com
   ```
   Enter your Oracle account credentials when prompted.

3. **Run the installation script** (if on Linux/Mac):
   ```bash
   chmod +x docker-install.sh
   ./docker-install.sh
   ```
   
   **For Windows**, run these commands manually:
   ```powershell
   # Build and start containers
   docker-compose up -d
   
   # Wait for Oracle to initialize (check logs)
   docker logs -f iwk_oracle
   ```
   Wait until you see "DATABASE IS READY TO USE!" (takes 2-5 minutes)

4. **Initialize Laravel application**:
   ```powershell
   # Copy environment file
   docker exec iwk_finance_app cp docker.env .env
   
   # Generate application key
   docker exec iwk_finance_app php artisan key:generate
   
   # Install dependencies
   docker exec iwk_finance_app composer install
   
   # Run database migrations
   docker exec iwk_finance_app php artisan migrate
   
   # Set permissions
   docker exec iwk_finance_app chown -R www-data:www-data /var/www/html/storage
   docker exec iwk_finance_app chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
   ```

5. **Access the application**:
   Open your browser and go to: **http://localhost:8000**

---

## 💻 Option 2: Standard Installation (Without Docker)

### Prerequisites
- PHP 8.2 or higher
- Composer installed
- Node.js and NPM installed
- Oracle Database (or SQLite for development)

### Steps

1. **Install PHP dependencies**:
   ```powershell
   composer install
   ```

2. **Install Node.js dependencies**:
   ```powershell
   npm install
   ```

3. **Create environment file**:
   ```powershell
   # Copy docker.env to .env (or create manually)
   copy docker.env .env
   ```

4. **Edit `.env` file** and configure:
   ```env
   APP_NAME="IWK Report 15 Comparison Tool"
   APP_ENV=local
   APP_DEBUG=true
   APP_URL=http://localhost:8000
   
   # For SQLite (development - easier setup)
   DB_CONNECTION=sqlite
   DB_DATABASE=database/database.sqlite
   
   # OR for Oracle (production)
   # DB_CONNECTION=oracle
   # DB_HOST=localhost
   # DB_PORT=1521
   # DB_DATABASE=XEPDB1
   # DB_USERNAME=iwk_finance
   # DB_PASSWORD=iwk_password
   ```

5. **Create SQLite database** (if using SQLite):
   ```powershell
   # Create empty database file
   New-Item -ItemType File -Path "database\database.sqlite" -Force
   ```

6. **Generate application key**:
   ```powershell
   php artisan key:generate
   ```

7. **Run database migrations**:
   ```powershell
   php artisan migrate
   ```

8. **Build frontend assets**:
   ```powershell
   npm run build
   ```

9. **Start the development server**:
   ```powershell
   php artisan serve
   ```

10. **Access the application**:
    Open your browser and go to: **http://localhost:8000**

---

## ✅ Verify Installation

Once running, you should see:
- **Home page**: Upload form for Excel files
- **Navigation**: Links to upload, view results, and normalize files

### Test the Application

1. Go to the home page (`/`)
2. You should see the upload form with two file inputs
3. Try uploading two Excel files to test the comparison feature

---

## 🛠️ Common Commands

### Docker Commands

```powershell
# View running containers
docker ps

# View logs
docker logs iwk_finance_app
docker logs iwk_oracle

# Stop containers
docker-compose down

# Restart containers
docker-compose restart

# Rebuild containers
docker-compose up -d --build

# Access container shell
docker exec -it iwk_finance_app bash
```

### Laravel Commands

```powershell
# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Run migrations
php artisan migrate

# View logs
# Windows: type storage\logs\laravel.log
# Linux/Mac: tail -f storage/logs/laravel.log
```

---

## 🐛 Troubleshooting

### Port Already in Use

If port 8000 is already in use:

**Docker**: Edit `docker-compose.yml` and change:
```yaml
ports:
  - "8001:80"  # Change 8000 to 8001
```

**Standard**: Use a different port:
```powershell
php artisan serve --port=8001
```

### Oracle Connection Issues

1. Check Oracle container is running:
   ```powershell
   docker ps | findstr oracle
   ```

2. Verify Oracle is ready:
   ```powershell
   docker logs iwk_oracle
   ```

3. Test connection from Laravel:
   ```powershell
   docker exec iwk_finance_app php artisan tinker
   # Then in tinker: DB::connection()->getPdo();
   ```

### Permission Errors

**Windows**: Usually not an issue, but if you see permission errors:
```powershell
# Make sure storage is writable
icacls storage /grant Users:F /T
```

**Docker**:
```powershell
docker exec iwk_finance_app chmod -R 775 /var/www/html/storage
docker exec iwk_finance_app chown -R www-data:www-data /var/www/html/storage
```

### Composer/Node Issues

If dependencies fail to install:

```powershell
# Clear Composer cache
composer clear-cache

# Clear NPM cache
npm cache clean --force

# Try again
composer install
npm install
```

---

## 📝 Next Steps

1. **Upload Test Files**: Try uploading two Excel files to test the comparison
2. **View Results**: Check the `/results` page to see all comparisons
3. **Normalize Files**: Use `/normalize` to standardize BRAIN Excel files
4. **Read Full Documentation**: See `README.md` for detailed information

---

## 🆘 Need Help?

- Check the full `README.md` for detailed documentation
- Review `DOCKER_README.md` for Docker-specific information
- Check Laravel logs: `storage/logs/laravel.log`
- Check Docker logs: `docker logs iwk_finance_app`

---

**Happy Comparing! 🎉**

