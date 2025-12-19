# IWK Report 15 Comparison Tool

A Laravel-based web application for comparing and analyzing financial Excel reports from different systems (BRAIN vs BS). This tool enables users to upload, parse, normalize, compare, and export detailed comparison reports of financial data.

## 📋 Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
  - [Standard Installation](#standard-installation)
  - [Docker Installation](#docker-installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [Project Structure](#project-structure)
- [API Routes](#api-routes)
- [Database Schema](#database-schema)
- [Troubleshooting](#troubleshooting)
- [License](#license)

## ✨ Features

### Core Functionality

- **Excel File Comparison**: Compare two Excel files (BRAIN vs BS) side-by-side
- **Intelligent Parsing**: Automatically extracts cost centers, main descriptions, and financial data
- **Data Normalization**: Standardizes inconsistent Excel formats to a uniform structure
- **Detailed Analysis**: Identifies missing cost centers, value differences, and structural changes
- **Excel Export**: Generates comprehensive comparison reports in Excel format
- **Visual Indicators**: Color-coded results showing increases, decreases, and missing data

### Key Capabilities

- **Cost Center Analysis**: Groups and compares data by cost center codes
- **Main Description Comparison**: Compares 8 types of main descriptions:
  - Commercial Totals
  - Domestic Totals
  - Non-billable Totals
  - Govt.Domestic Totals
  - Govt. Premises Totals
  - Govt. Quarters Totals
  - Industrial Totals
  - Ind. No HC Totals
- **Configurable Description Types**: Choose between 3 or 4 description types:
  - **Standard (3 types)**: Connected, Nil, IST
  - **Extended (4 types)**: Connected, Nil, IST, CST (optional)
  - Selectable per upload/comparison via checkbox
- **Financial Metrics**: Extracts and compares 35+ financial columns including:
  - Billing Total
  - Receipts Total
  - Outstanding Balance
  - Aging buckets (1-60+ months)
- **Flexible Matching**: Handles variations in Excel formatting (spaces, dots, case sensitivity)

## 📦 Requirements

### Standard Installation

- PHP 8.2 or higher
- Composer
- Node.js and NPM
- Database: Oracle Database (with `yajra/laravel-oci8`) or SQLite (for development)
- Web Server: Apache or Nginx
- PHP Extensions:
  - PDO
  - MBstring
  - XML
  - OpenSSL
  - Fileinfo
  - GD or Imagick

### Docker Installation

- Docker 20.10+
- Docker Compose 2.0+
- Oracle Container Registry account (for Oracle Database image)
- Minimum 4GB RAM available for containers

## 🚀 Installation

### Standard Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/anonyname5/Iwk_Report15_Compare_Tool.git
   cd Iwk_Report15_Compare_Tool
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node.js dependencies**
   ```bash
   npm install
   ```

4. **Configure environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configure database** (Edit `.env` file)
   ```env
   DB_CONNECTION=oracle  # or 'sqlite' for development
   DB_HOST=localhost
   DB_PORT=1521
   DB_DATABASE=XEPDB1
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

6. **Run migrations**
   ```bash
   php artisan migrate
   ```

7. **Build frontend assets**
   ```bash
   npm run build
   ```

8. **Start the development server**
   ```bash
   php artisan serve
   ```

   The application will be available at `http://localhost:8000`

### Docker Installation

1. **Login to Oracle Container Registry**
   ```bash
   docker login container-registry.oracle.com
   ```
   You'll need an Oracle account with accepted terms for the Oracle Database software.

2. **Prepare the environment**
   ```bash
   mkdir -p oracle_setup oracle_startup tnsnames docker/apache
   ```

3. **Configure TNS names** (if using Oracle)
   Ensure `tnsnames/tnsnames.ora` exists with proper Oracle connection configuration.

4. **Build and start containers**
   ```bash
   docker-compose up -d
   ```

5. **Monitor Oracle database initialization**
   ```bash
   docker logs -f iwk_oracle
   ```
   Wait until you see "DATABASE IS READY TO USE!" (this may take a few minutes).

6. **Initialize the Laravel application**
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

7. **Access the application**
   The application should now be running at `http://localhost:8000`

## ⚙️ Configuration

### Environment Variables

Key environment variables in `.env`:

```env
APP_NAME="IWK Report 15 Comparison Tool"
APP_ENV=production
APP_DEBUG=false
APP_URL=http://localhost:8000

DB_CONNECTION=oracle
DB_HOST=oracle
DB_PORT=1521
DB_DATABASE=XEPDB1
DB_USERNAME=iwk_finance
DB_PASSWORD=iwk_password

# Oracle TNS Configuration (if using TNS)
DB_TNS=
ORACLE_TNS_ADMIN=/var/www/html/tnsnames
```

### Docker Configuration

The Docker setup includes:
- **App Container**: Apache web server with PHP 8.2
- **Oracle Container**: Oracle XE 21.3.0 database
- **Network**: Bridge network for container communication
- **Volumes**: Persistent Oracle data storage

**Oracle Database Information:**
- Host: `oracle` (within Docker network) or `localhost` (from host)
- Port: `1521`
- Service Name: `XEPDB1`
- Username: `iwk_finance`
- Password: `iwk_password`
- Oracle Enterprise Manager Express: `http://localhost:5500/em`

## 📖 Usage

### Uploading Files for Comparison

1. Navigate to the home page (`/`)
2. Enter a **Comparison Name** to identify this comparison
3. **Optional**: Check **"Include CST (4 Description Types)"** if your files contain CST data
   - Unchecked: Uses 3 description types (Connected, Nil, IST) - default
   - Checked: Uses 4 description types (Connected, Nil, IST, CST)
4. Upload **BRAIN File** (File 1)
5. Upload **BS File** (File 2)
6. Click **Upload & Process**

The system will:
- Parse both Excel files
- Extract cost centers, main descriptions, and financial data
- Handle 3 or 4 description types based on your selection
- Store the parsed data in the database
- Redirect to the results page

**Note**: The CST option must be selected for both files in a comparison if you want to compare CST data. The system will handle mixed scenarios (one file with CST, one without) gracefully.

### Viewing Comparison Results

1. Navigate to **View Results** (`/results`)
2. Browse the list of all comparisons
3. Each comparison shows:
   - Comparison name
   - File names (BRAIN and BS)
   - Upload date
   - Status (No Changes / Value Changes / Structure Changes)
4. Click **Download** to get the comparison Excel file

### Normalizing BRAIN Files

1. Navigate to **Normalize BRAIN Files** (`/normalize`)
2. **Optional**: Check **"Include CST (4 Description Types)"** if you want CST included in the normalized output
   - Unchecked: Normalizes with 3 description types (Connected, Nil, IST) - default
   - Checked: Normalizes with 4 description types (Connected, Nil, IST, CST)
3. Upload a BRAIN Excel file
4. The system will:
   - Standardize the file structure
   - Ensure all required description types exist (creates missing ones with zero values)
   - Apply consistent formatting
   - Download the normalized file automatically

### Comparison Report Features

The generated comparison Excel includes:
- **Summary Section**: Overview of differences
- **Detailed Comparison**: Side-by-side comparison with:
  - Cost center codes
  - Main descriptions
  - Financial values from both files
  - Difference calculations
  - Percentage changes
  - Color coding (green for increases, red for decreases)

## 📁 Project Structure

```
Iwk_Report15_Compare_Tool/
├── app/
│   ├── Http/Controllers/
│   │   ├── ExcelController.php          # Main comparison logic
│   │   └── NormalizationController.php  # File normalization
│   ├── Models/
│   │   └── ExcelJson.php                # Database model
│   ├── Services/
│   │   ├── ExcelParserService.php       # Excel parsing logic
│   │   ├── ComparisonService.php       # Comparison logic
│   │   └── ExcelExportService.php       # Excel export generation
│   ├── Helpers/
│   │   └── DescriptionTypesHelper.php   # Description types management
│   └── imports/
│       └── FinanceReportImport.php      # Excel import class
├── config/
│   └── database.php                     # Database configuration
├── database/
│   └── migrations/
│       └── 2025_04_14_030523_create_excel_jsons_table.php
├── resources/
│   └── views/
│       ├── excel/
│       │   ├── index.blade.php          # Comparison list
│       │   ├── compare.blade.php       # Comparison view
│       │   └── show.blade.php          # Single file view
│       ├── Normalize/
│       │   └── upload.blade.php        # Normalization form
│       └── upload.blade.php            # Main upload form
├── routes/
│   └── web.php                          # Application routes
├── docker-compose.yml                   # Docker configuration
├── Dockerfile                           # Docker image definition
└── README.md                            # This file
```

## 🛣️ API Routes

| Method | Route | Description |
|--------|-------|-------------|
| GET | `/` | Upload form for Excel files |
| POST | `/upload` | Process uploaded Excel files |
| GET | `/results` | List all comparisons |
| GET | `/view/{id}` | View details of a single file |
| GET | `/compare/{comparisonName}` | Download comparison Excel (original format) |
| GET | `/export/{comparisonName}` | Download comparison Excel (detailed format) |
| GET | `/normalize` | Normalization upload form |
| POST | `/normalize` | Process file normalization |
| DELETE | `/delete/{comparisonName}` | Delete a comparison and its associated files |

## 🗄️ Database Schema

### `excel_jsons` Table

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `file_name` | string | Original filename |
| `data` | json | Parsed Excel structure |
| `file_type` | string | 'file_1' or 'file_2' |
| `comparison_name` | string | Groups files for comparison |
| `include_cst` | boolean | Whether CST (4th Service Level) is included (default: false) |
| `created_at` | timestamp | Creation timestamp |
| `updated_at` | timestamp | Last update timestamp |

**Indexes:**
- `['comparison_name', 'file_type']` - For fast comparison lookups

**Description Types:**
- When `include_cst = false`: Uses 3 Service Level (Connected, Nil, IST)
- When `include_cst = true`: Uses 4 Service Level (Connected, Nil, IST, CST)

## 🔧 Troubleshooting

### Common Issues

#### Oracle Connection Issues

**Problem**: Application cannot connect to Oracle database.

**Solutions**:
1. Verify Oracle container is running:
   ```bash
   docker ps | grep oracle
   ```

2. Check Oracle logs:
   ```bash
   docker logs iwk_oracle
   ```

3. Verify TNS configuration:
   ```bash
   cat tnsnames/tnsnames.ora
   ```

4. Test Oracle connection:
   ```bash
   docker exec iwk_finance_app php artisan tinker
   # Then try: DB::connection()->getPdo();
   ```

#### Web Server Issues

**Problem**: Application not responding or showing errors.

**Solutions**:
1. Check Apache/PHP-FPM is running:
   ```bash
   docker ps | grep app
   ```

2. View application logs:
   ```bash
   docker logs iwk_finance_app
   # Or for Laravel logs:
   docker exec iwk_finance_app tail -f storage/logs/laravel.log
   ```

3. Check Apache error logs:
   ```bash
   docker exec iwk_finance_app tail -f /var/log/apache2/error.log
   ```

#### Permission Issues

**Problem**: Permission denied errors for storage or cache.

**Solutions**:
```bash
# For Docker:
docker exec iwk_finance_app chown -R www-data:www-data /var/www/html/storage
docker exec iwk_finance_app chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# For standard installation:
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

#### Excel Parsing Errors

**Problem**: Files fail to parse or show incorrect data.

**Solutions**:
1. Verify file format is `.xlsx` or `.xls`
2. Check file is not corrupted
3. Review Laravel logs for specific error messages:
   ```bash
   tail -f storage/logs/laravel.log
   ```
4. Ensure file structure matches expected format (cost centers, main descriptions, etc.)

#### Memory Issues

**Problem**: Out of memory errors when processing large files.

**Solutions**:
1. Increase PHP memory limit in `php.ini`:
   ```ini
   memory_limit = 512M
   ```

2. For Docker, update Dockerfile or docker-compose.yml:
   ```yaml
   environment:
     - PHP_MEMORY_LIMIT=512M
   ```

### Container Management

```bash
# Stop containers
docker-compose down

# View logs
docker-compose logs -f

# Restart containers
docker-compose restart

# Rebuild containers
docker-compose up -d --build

# Access container shell
docker exec -it iwk_finance_app bash
```

## 🆕 Recent Updates

### Configurable Service Level (v1.1.0)

The application now supports configurable Service Level:

- **Standard Mode (3 types)**: Connected, Nil, IST - Default behavior
- **Extended Mode (4 types)**: Connected, Nil, IST, CST - Optional

**Features:**
- Select CST inclusion per upload/comparison via checkbox
- Backward compatible with existing 3-type files
- Automatic handling of missing description types
- Dynamic parsing, comparison, and export based on selection

**Usage:**
- Check "Include CST (4 Description Types)" when uploading files that contain CST data
- The system automatically handles 3 or 4 types throughout the workflow
- Comparisons work correctly even when one file has CST and the other doesn't

See `IMPLEMENTATION_PLAN.md` for detailed technical documentation.

## 📝 Development

### Running Tests

```bash
php artisan test
```

### Code Style

The project uses Laravel Pint for code formatting:

```bash
./vendor/bin/pint
```

### Development Mode

For development with hot-reloading:

```bash
composer dev
```

This runs:
- Laravel development server
- Queue worker
- Laravel Pail (log viewer)
- Vite dev server

## 📄 License

This project is proprietary software developed for IWK. All rights reserved.

## 👥 Support

For issues, questions, or contributions, please contact the development team.

---

**Version**: 1.1.0  
**Last Updated**: December 2025

### Changelog

#### v1.1.0 (December 2025)
- ✨ Added configurable Service Level (3 or 4 types with CST option)
- ✨ Added CST checkbox to upload and normalization forms
- 🔧 Updated all services to support dynamic Service Level
- 🗄️ Added `include_cst` column to database schema
- 🐛 Fixed variable name conflict bug in ExcelParserService
- ✅ All features tested and verified

#### v1.0.0 (May 2025)
- Initial release
- Excel file comparison functionality
- Data normalization
- Export capabilities
