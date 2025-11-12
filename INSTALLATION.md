# Solar Planning Application - Installation Guide

This guide will help you set up and run the Solar Planning application, which consists of a Laravel 12 backend API and a Vue 3 frontend.

## Table of Contents

- [Prerequisites](#prerequisites)
- [Backend Setup (Laravel API)](#backend-setup-laravel-api)
- [Frontend Setup (Vue 3)](#frontend-setup-vue-3)
- [Database Setup](#database-setup)
- [Configuration](#configuration)
- [Running the Application](#running-the-application)
- [Scheduled Jobs](#scheduled-jobs)
- [Troubleshooting](#troubleshooting)

---

## Prerequisites

Before you begin, ensure you have the following installed on your system:

### Required Software

1. **PHP 8.2 or higher**
   ```bash
   php --version
   ```

2. **Composer** (PHP dependency manager)
   ```bash
   composer --version
   ```

3. **Node.js 18+ and npm** (for frontend)
   ```bash
   node --version
   npm --version
   ```

4. **PostgreSQL 14+** (database)
   ```bash
   psql --version
   ```

5. **Git** (version control)
   ```bash
   git --version
   ```

### Optional but Recommended

- **Redis** (for caching and queues)
- **Supervisor** (for queue workers)
- **Nginx or Apache** (for production deployment)

---

## Backend Setup (Laravel API)

### 1. Navigate to API Directory

```bash
cd solarApp/api
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install Required Packages

The application requires the following additional packages:

```bash
# PDF generation library
composer require barryvdh/laravel-dompdf

# Permissions management
composer require spatie/laravel-permission

# Activity logging
composer require spatie/laravel-activitylog
```

### 4. Environment Configuration

Copy the example environment file and configure it:

```bash
cp .env.example .env
```

Edit the `.env` file with your configuration:

```env
# Application
APP_NAME="Solar Planning"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=solar_planning
DB_USERNAME=postgres
DB_PASSWORD=your_password

# Queue
QUEUE_CONNECTION=database

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@solarplanning.com"
MAIL_FROM_NAME="${APP_NAME}"

# File Storage
FILESYSTEM_DISK=private

# Session
SESSION_DRIVER=database
SESSION_LIFETIME=120

# Cache
CACHE_DRIVER=file

# Frontend URL (for CORS)
FRONTEND_URL=http://localhost:5173
```

### 5. Generate Application Key

```bash
php artisan key:generate
```

### 6. Create Storage Links

```bash
php artisan storage:link
```

### 7. Set File Permissions

```bash
chmod -R 775 storage bootstrap/cache
```

---

## Database Setup

### 1. Create PostgreSQL Database

```bash
# Connect to PostgreSQL
psql -U postgres

# Create database
CREATE DATABASE solar_planning;

# Create user (optional)
CREATE USER solar_user WITH PASSWORD 'your_secure_password';

# Grant privileges
GRANT ALL PRIVILEGES ON DATABASE solar_planning TO solar_user;

# Exit
\q
```

### 2. Run Migrations

```bash
cd api
php artisan migrate
```

This will create all necessary tables:
- users
- solar_plants
- investments
- investment_repayments
- notifications
- user_preferences
- files
- file_containers
- settings
- activity_log
- and more...

### 3. Seed Database (Optional)

Create initial data including roles and default settings:

```bash
php artisan db:seed
```

Or create specific seeders:

```bash
# Create roles
php artisan db:seed --class=RoleSeeder

# Create default settings
php artisan db:seed --class=SettingSeeder

# Create demo data for testing
php artisan db:seed --class=DemoDataSeeder
```

---

## Frontend Setup (Vue 3)

### 1. Navigate to Frontend Directory

```bash
cd ../frontend
```

### 2. Install Node Dependencies

```bash
npm install
```

### 3. Environment Configuration

Create a `.env` file in the frontend directory:

```bash
cp .env.example .env
```

Edit the `.env` file:

```env
VITE_API_URL=http://localhost:8000/api/v1
VITE_APP_NAME="Solar Planning"
```

### 4. Build Frontend (Development)

```bash
npm run dev
```

This will start the Vite development server on `http://localhost:5173`

### 5. Build Frontend (Production)

```bash
npm run build
```

The production build will be created in the `dist` folder.

---

## Configuration

### Setting Up Roles and Permissions

The application uses Spatie Laravel Permission for role-based access control. Default roles:

- **admin**: Full system access
- **manager**: Manage plants and investments
- **customer**: View and manage own investments

Create an admin user:

```bash
cd api
php artisan tinker
```

```php
$user = User::create([
    'name' => 'Admin User',
    'email' => 'admin@example.com',
    'password' => Hash::make('password'),
    'email_verified_at' => now(),
]);

$user->assignRole('admin');
```

### Configuring File Storage

The application uses Laravel's filesystem for storing files. Configure your storage disk in `config/filesystems.php`:

```php
'private' => [
    'driver' => 'local',
    'root' => storage_path('app/private'),
    'visibility' => 'private',
],
```

Ensure the private storage directory exists:

```bash
mkdir -p storage/app/private/{uploads,contracts,exports}
```

### Email Configuration

For production, configure a real SMTP server or email service:

**Using Gmail:**
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
```

**Using AWS SES:**
```env
MAIL_MAILER=ses
AWS_ACCESS_KEY_ID=your-key
AWS_SECRET_ACCESS_KEY=your-secret
AWS_DEFAULT_REGION=eu-central-1
```

### PDF Generation Configuration

The application uses DomPDF for generating PDF contracts. Configure in `config/dompdf.php`:

```php
return [
    'defines' => [
        'font_dir' => storage_path('fonts/'),
        'font_cache' => storage_path('fonts/'),
        'temp_dir' => sys_get_temp_dir(),
        'chroot' => realpath(base_path()),
        'enable_font_subsetting' => false,
        'pdf_backend' => 'CPDF',
        'default_media_type' => 'screen',
        'default_paper_size' => 'a4',
        'default_font' => 'DejaVu Sans',
        'dpi' => 96,
        'enable_php' => false,
        'enable_javascript' => true,
        'enable_remote' => true,
        'font_height_ratio' => 1.1,
        'enable_html5_parser' => true,
    ],
];
```

---

## Running the Application

### Development Mode

**Terminal 1 - Backend API:**
```bash
cd api
php artisan serve
```
API will be available at `http://localhost:8000`

**Terminal 2 - Queue Worker:**
```bash
cd api
php artisan queue:work
```

**Terminal 3 - Frontend:**
```bash
cd frontend
npm run dev
```
Frontend will be available at `http://localhost:5173`

### Production Mode

#### Using Nginx

1. Build the frontend:
```bash
cd frontend
npm run build
```

2. Configure Nginx:
```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/solarApp/frontend/dist;

    index index.html;

    location / {
        try_files $uri $uri/ /index.html;
    }

    location /api {
        proxy_pass http://localhost:8000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
    }
}
```

3. Configure Laravel for production:
```bash
cd api
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

4. Set up Supervisor for queue workers:

Create `/etc/supervisor/conf.d/solar-planning-worker.conf`:
```ini
[program:solar-planning-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/solarApp/api/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/solarApp/api/storage/logs/worker.log
stopwaitsecs=3600
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start solar-planning-worker:*
```

---

## Scheduled Jobs

The application includes several scheduled jobs that need to run periodically.

### Setting Up Laravel Scheduler

Add this to your crontab (`crontab -e`):

```cron
* * * * * cd /path/to/solarApp/api && php artisan schedule:run >> /dev/null 2>&1
```

### Available Scheduled Jobs

The following jobs are configured in `app/Console/Kernel.php`:

1. **Check Overdue Repayments** (Daily at 2:00 AM)
   - Identifies and marks overdue investment repayments
   - Sends notification emails to investors

2. **Send Repayment Reminders** (Daily at 9:00 AM)
   - Sends reminder emails for upcoming repayments (7 days before due date)

3. **Send Monthly Reports** (1st of each month at 8:00 AM)
   - Generates and sends monthly investment reports to investors

4. **Cleanup Old Activity Logs** (Weekly on Sunday at 3:00 AM)
   - Removes activity logs older than 6 months

5. **Prune Soft Deleted Records** (Monthly on 1st at 4:00 AM)
   - Permanently deletes records marked with rs=99 older than 30 days

### Manual Job Execution

You can manually trigger scheduled jobs for testing:

```bash
# Run all scheduled jobs
php artisan schedule:run

# Test repayment checks
php artisan repayments:check-overdue

# Test reminder emails
php artisan repayments:send-reminders

# Generate monthly reports
php artisan reports:send-monthly
```

---

## Troubleshooting

### Common Issues

#### 1. Database Connection Failed

**Error:** `SQLSTATE[08006] [7] connection refused`

**Solution:**
- Check if PostgreSQL is running: `sudo systemctl status postgresql`
- Verify database credentials in `.env`
- Ensure database exists: `psql -U postgres -l`

#### 2. Permission Denied on Storage

**Error:** `The stream or file "/storage/logs/laravel.log" could not be opened`

**Solution:**
```bash
cd api
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

#### 3. PDF Generation Fails

**Error:** `Class 'Barryvdh\DomPDF\ServiceProvider' not found`

**Solution:**
```bash
composer require barryvdh/laravel-dompdf
php artisan config:clear
```

#### 4. CORS Errors

**Error:** `Access to XMLHttpRequest has been blocked by CORS policy`

**Solution:**
- Check `FRONTEND_URL` in `.env`
- Update `config/cors.php`:
```php
'allowed_origins' => [env('FRONTEND_URL', 'http://localhost:5173')],
'supports_credentials' => true,
```

#### 5. Queue Jobs Not Processing

**Error:** Jobs remain in `pending` state

**Solution:**
```bash
# Check queue worker is running
ps aux | grep "queue:work"

# Restart queue worker
php artisan queue:restart

# Check failed jobs
php artisan queue:failed
```

#### 6. File Upload Fails

**Error:** `413 Request Entity Too Large`

**Solution:**
- Increase PHP upload limits in `php.ini`:
```ini
upload_max_filesize = 10M
post_max_size = 10M
```
- Restart PHP-FPM: `sudo systemctl restart php8.2-fpm`

### Logging

Check logs for errors:

```bash
# Laravel logs
tail -f api/storage/logs/laravel.log

# Nginx error logs
tail -f /var/log/nginx/error.log

# Queue worker logs
tail -f api/storage/logs/worker.log
```

### Database Migrations Issues

If migrations fail, you can:

1. **Reset and remigrate:**
```bash
php artisan migrate:fresh
```

2. **Rollback and remigrate:**
```bash
php artisan migrate:rollback
php artisan migrate
```

3. **Check migration status:**
```bash
php artisan migrate:status
```

---

## Testing

### Backend Tests

```bash
cd api

# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature

# Run with coverage
php artisan test --coverage
```

### Frontend Tests

```bash
cd frontend

# Run unit tests
npm run test:unit

# Run with coverage
npm run test:coverage
```

---

## Security Considerations

### Production Checklist

- [ ] Set `APP_DEBUG=false` in production
- [ ] Use strong `APP_KEY` (generated with `php artisan key:generate`)
- [ ] Use HTTPS for all connections
- [ ] Configure proper file permissions (644 for files, 755 for directories)
- [ ] Set up firewall rules
- [ ] Use strong database passwords
- [ ] Enable rate limiting
- [ ] Regular security updates
- [ ] Backup database regularly
- [ ] Monitor logs for suspicious activity

### Rate Limiting

The application includes rate limiting by default:
- API requests: 60 per minute per IP
- Authentication attempts: 5 per minute

Configure in `app/Http/Kernel.php` if needed.

---

## Additional Resources

- [Laravel 12 Documentation](https://laravel.com/docs/12.x)
- [Vue 3 Documentation](https://vuejs.org/guide/introduction.html)
- [PrimeVue Documentation](https://primevue.org/)
- [PostgreSQL Documentation](https://www.postgresql.org/docs/)

---

## Support

For issues or questions:
1. Check this documentation
2. Review application logs
3. Search existing GitHub issues
4. Create a new issue with detailed information

---

**Last Updated:** November 2025
**Version:** 1.0.0
