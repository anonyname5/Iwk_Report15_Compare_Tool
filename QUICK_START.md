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
   
   # Install dependencies (MUST be done before artisan commands)
   docker exec iwk_finance_app composer install
   
   # Generate application key
   docker exec iwk_finance_app php artisan key:generate
   
   # Run database migrations
   docker exec iwk_finance_app php artisan migrate
   
   # Set permissions
   docker exec iwk_finance_app chown -R www-data:www-data /var/www/html/storage
   docker exec iwk_finance_app chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
   ```

5. **Access the application**:
   Open your browser and go to: **http://localhost:8000**

6. **Access the Oracle Database**:
   
   **Database Connection Details:**
   - Host: `localhost` (from your computer) or `oracle` (from within Docker)
   - Port: `1521`
   - Service Name: `XEPDB1`
   - Username: `iwk_finance`
   - Password: `iwk_password`
   
   **Method 1: Using SQL*Plus (Command Line)**
   
   Connect directly from the Oracle container:
   ```powershell
   docker exec -it iwk_oracle sqlplus iwk_finance/iwk_password@XEPDB1
   ```
   
   Or connect as sysdba for administrative tasks:
   ```powershell
   docker exec -it iwk_oracle sqlplus / as sysdba
   ```
   
   **Method 2: Using Oracle SQL Developer (GUI Tool)**
   
   1. Download Oracle SQL Developer from: https://www.oracle.com/database/sqldeveloper/
   2. Create a new connection with these settings:
      - **Connection Name**: IWK Finance
      - **Username**: `iwk_finance`
      - **Password**: `iwk_password`
      - **Hostname**: `localhost`
      - **Port**: `1521`
      - **Service Name**: `XEPDB1`
   3. Click "Test" to verify connection, then "Save" and "Connect"
   
   **Method 3: Using Oracle Enterprise Manager Express (Web UI)**
   
   Access the web-based database management interface:
   - URL: **http://localhost:5500/em**
   - Username: `sys` (or `iwk_finance`)
   - Password: `oracle_root_password` (for sys) or `iwk_password` (for iwk_finance)
   - Connect as: `SYSDBA` (for sys) or `Normal` (for iwk_finance)
   
   **Method 4: Using Laravel Tinker (Laravel's REPL)**
   
   Access the database through Laravel:
   ```powershell
   docker exec -it iwk_finance_app php artisan tinker
   ```
   
   Then run database queries:
   ```php
   // Get all tables
   DB::select("SELECT table_name FROM user_tables");
   
   // Query a table
   DB::table('your_table_name')->get();
   
   // Run raw SQL
   DB::select("SELECT * FROM your_table_name");
   ```
   
   **Method 5: Using DBeaver (Recommended GUI Tool)**
   
   DBeaver is a free, cross-platform database tool. Here's how to set it up:
   
   **Step 1: Download and Install DBeaver**
   - Download from: https://dbeaver.io/download/
   - Install the Community Edition (free)
   
   **Step 2: Install Oracle Driver**
   1. Open DBeaver
   2. Go to **Database** → **Driver Manager**
   3. Find **Oracle** in the list
   4. If not present, click **New Driver** → Search for "Oracle"
   5. DBeaver will automatically download the Oracle JDBC driver
   
   **Step 3: Create New Database Connection**
   1. Click **New Database Connection** (plug icon) or **Database** → **New Database Connection**
   2. Select **Oracle** from the list
   3. Click **Next**
   
   **Step 4: Configure Connection Settings**
   
   In the **Main** tab, enter:
   - **Host**: `localhost`
   - **Port**: `1521`
   - **Database/Service**: `XEPDB1` (this is the Service Name)
   - **Username**: `iwk_finance`
   - **Password**: `iwk_password`
   
   **Important**: Make sure to select **Service name** (not SID) in the connection type dropdown if available.
   
   **Step 5: Test Connection**
   1. Click **Test Connection**
   2. If prompted to download Oracle drivers, click **Download**
   3. Wait for "Connected" message
   4. Click **Finish** to save the connection
   
   **Step 6: Browse Your Database**
   - Expand the connection in the Database Navigator
   - Navigate to: **Schemas** → **IWK_FINANCE** → **Tables**
   - You'll see all your application tables
   
   **Troubleshooting DBeaver:**
   
   If connection fails:
   - Verify Oracle container is running: `docker ps | findstr oracle`
   - Check if port 1521 is accessible: `telnet localhost 1521`
   - Try using **TNS** connection type instead of **Basic**
   - Ensure you're using **Service Name** = `XEPDB1`, not SID
   
   **Alternative: Using TNS Connection in DBeaver**
   
   If basic connection doesn't work, you can use TNS:
   1. In connection settings, switch to **TNS** tab
   2. TNS Alias: `XEPDB1`
   3. TNS Configuration file: Point to your `tnsnames/tnsnames.ora` file
   
   **Other Database Tools:**
   
   - **DataGrip** (JetBrains, paid): Similar setup, use same connection details
   - **Toad for Oracle** (Quest Software): Professional Oracle tool
   - **Oracle SQL Developer** (Free, Oracle official): See Method 2 above

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

### Database Commands

```powershell
# Connect to Oracle database via SQL*Plus
docker exec -it iwk_oracle sqlplus iwk_finance/iwk_password@XEPDB1

# Connect as sysdba (for admin tasks)
docker exec -it iwk_oracle sqlplus / as sysdba

# Run SQL query directly
docker exec -i iwk_oracle sqlplus iwk_finance/iwk_password@XEPDB1 <<EOF
SELECT * FROM user_tables;
EXIT;
EOF

# Access Laravel Tinker for database queries
docker exec -it iwk_finance_app php artisan tinker

# Check database connection from Laravel
docker exec iwk_finance_app php artisan tinker
# Then: DB::connection()->getPdo();
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

4. **Password Expiration Error (ORA-28002)**:
   
   If you see: `ORA-28002: the password will expire within 7 days`
   
   This warning is being treated as an error. Fix it immediately with this one-liner:
   
   ```powershell
   # Quick fix: Reset password and disable expiration
   docker exec -i iwk_oracle bash -c "echo 'ALTER SESSION SET CONTAINER = XEPDB1;
   ALTER USER iwk_finance IDENTIFIED BY iwk_password;
   ALTER PROFILE DEFAULT LIMIT PASSWORD_LIFE_TIME UNLIMITED;
   EXIT;' | sqlplus / as sysdba"
   ```
   
   Then retry the migration:
   ```powershell
   docker exec iwk_finance_app php artisan migrate
   ```
   
   **Note**: For new installations, this is now fixed automatically in the setup scripts. If you're setting up a fresh database, the password expiration is disabled by default.

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

### "Failed to open stream: No such file or directory" Error

If you see an error like:
```
Warning: require(/var/www/html/vendor/autoload.php): Failed to open stream
```

This means you're trying to run `php artisan` commands before installing Composer dependencies. **Always run `composer install` first**, then run artisan commands:

```powershell
# Correct order:
docker exec iwk_finance_app composer install
docker exec iwk_finance_app php artisan key:generate
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

