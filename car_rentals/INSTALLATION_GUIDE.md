# Car Rentals Module - Installation Guide

Complete step-by-step installation guide for integrating the Car Rental module into your Solar App.

## Prerequisites Checklist

Before starting, ensure you have:

- [ ] Laravel 11+ application (Solar App)
- [ ] PHP 8.2 or higher
- [ ] MySQL 8.0+ or PostgreSQL
- [ ] Composer installed
- [ ] Node.js 18+ and NPM/Yarn
- [ ] Git (for version control)
- [ ] Admin access to the database
- [ ] Test environment ready

## Installation Steps

### Step 1: Backup Your Database

**IMPORTANT:** Always backup your database before running migrations.

```bash
# MySQL backup
mysqldump -u your_user -p your_database > backup_$(date +%Y%m%d_%H%M%S).sql

# PostgreSQL backup
pg_dump -U your_user your_database > backup_$(date +%Y%m%d_%H%M%S).sql
```

### Step 2: Update Composer Autoloader

Add the Car Rentals module namespace to your `composer.json`:

```json
{
  "autoload": {
    "psr-4": {
      "App\\": "app/",
      "App\\Modules\\CarRentals\\": "car_rentals/backend/",
      "Database\\Factories\\": "database/factories/",
      "Database\\Seeders\\": "database/seeders/"
    }
  }
}
```

Regenerate autoloader:

```bash
composer dump-autoload
```

### Step 3: Register API Routes

Edit `routes/api.php` and add at the bottom:

```php
<?php

// Car Rentals Module Routes
require __DIR__ . '/../car_rentals/backend/routes/api.php';
```

### Step 4: Copy and Run Migrations

```bash
# Copy migration files to Laravel migrations directory
cp car_rentals/backend/Migrations/*.php database/migrations/

# Verify migrations are copied
ls -la database/migrations/ | grep -E "vehicles|rentals"

# Run migrations
php artisan migrate

# If you encounter errors, rollback and fix:
php artisan migrate:rollback --step=1
```

**Expected Output:**
```
Migrating: 2025_01_01_000001_create_vehicles_table
Migrated:  2025_01_01_000001_create_vehicles_table (45.23ms)
Migrating: 2025_01_01_000002_create_rentals_table
Migrated:  2025_01_01_000002_create_rentals_table (52.18ms)
Migrating: 2025_01_01_000003_create_rental_extras_table
Migrated:  2025_01_01_000003_create_rental_extras_table (28.45ms)
...
```

### Step 5: Copy Translation Files

```bash
# Create directories if they don't exist
mkdir -p lang/en lang/de lang/fr lang/es lang/it

# Copy translation files
cp car_rentals/shared/translations/en/car_rentals.php lang/en/
cp car_rentals/shared/translations/de/car_rentals.php lang/de/
cp car_rentals/shared/translations/fr/car_rentals.php lang/fr/

# Verify translations are copied
ls -la lang/*/car_rentals.php
```

### Step 6: Seed Sample Data (Optional but Recommended)

```bash
# Run the vehicle seeder
php artisan db:seed --class='App\Modules\CarRentals\Seeders\VehicleSeeder'
```

**Expected Output:**
```
Vehicles seeded successfully!
```

This will create:
- 6 sample vehicles (VW Golf, BMW 320i, Mercedes Sprinter, Tesla Model 3, Audi Q5, Ford Fiesta)
- With multilanguage descriptions
- Across different locations (Berlin, Munich, Hamburg, Frankfurt)

### Step 7: Verify Database Tables

```bash
# Check if tables were created
php artisan tinker
```

In tinker:
```php
// Should return 6 if you ran the seeder
\App\Modules\CarRentals\Models\Vehicle::count();

// Should return table columns
Schema::getColumnListing('vehicles');

// Exit tinker
exit
```

### Step 8: Test API Endpoints

First, create a test user or use existing credentials:

```bash
php artisan tinker
```

In tinker:
```php
// Create a test customer
$customer = \App\Models\User::factory()->create([
    'email' => 'test.customer@example.com',
    'password' => bcrypt('password123'),
    'name' => 'Test Customer',
]);

// Assign customer role
$customer->assignRole('customer');

// Create a test manager
$manager = \App\Models\User::factory()->create([
    'email' => 'test.manager@example.com',
    'password' => bcrypt('password123'),
    'name' => 'Test Manager',
]);
$manager->assignRole('manager');

exit
```

Test the API:

```bash
# 1. Login and get token
curl -X POST http://localhost:8000/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test.customer@example.com",
    "password": "password123"
  }'

# 2. List vehicles (use token from step 1)
curl -X GET http://localhost:8000/api/v1/car-rentals/vehicles \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"

# Expected: JSON response with list of vehicles
```

### Step 9: Install Frontend Components (Admin Dashboard)

```bash
# Navigate to your Frontend directory
cd Frontend

# Create module directory
mkdir -p src/modules/car-rentals

# Copy Vue components
cp -r ../car_rentals/frontend/components src/modules/car-rentals/
cp -r ../car_rentals/frontend/views src/modules/car-rentals/
cp -r ../car_rentals/frontend/store src/modules/car-rentals/

# Install if not already installed
npm install
```

Update `Frontend/src/router/index.js` to add car rental routes:

```javascript
import VehicleList from '@/modules/car-rentals/views/VehicleList.vue'

const routes = [
  // ... existing routes

  // Car Rentals Module
  {
    path: '/admin/car-rentals',
    name: 'CarRentals',
    component: () => import('@/layouts/AdminLayout.vue'),
    meta: { requiresAuth: true, role: ['manager', 'admin'] },
    children: [
      {
        path: 'vehicles',
        name: 'VehicleList',
        component: VehicleList,
        meta: { title: 'Vehicles' }
      },
      // Add more routes as needed
    ]
  }
]
```

### Step 10: Install Frontend Components (Customer Portal)

```bash
# Navigate to your FrontendUser directory
cd FrontendUser

# Create module directory
mkdir -p src/modules/car-rentals

# Copy Vue components
cp -r ../car_rentals/frontend/components src/modules/car-rentals/
cp -r ../car_rentals/frontend/views src/modules/car-rentals/

# Install if not already installed
npm install
```

Update `FrontendUser/src/router/index.js`:

```javascript
const routes = [
  // ... existing routes

  // Car Rentals Module (Customer View)
  {
    path: '/rentals',
    name: 'CustomerRentals',
    component: () => import('@/layouts/CustomerLayout.vue'),
    meta: { requiresAuth: true, role: 'customer' },
    children: [
      {
        path: '',
        name: 'BrowseVehicles',
        component: () => import('@/modules/car-rentals/views/VehicleList.vue'),
        meta: { title: 'Browse Vehicles' }
      },
      // Add more routes as needed
    ]
  }
]
```

### Step 11: Configure Workflow Scheduled Jobs

Edit `app/Console/Kernel.php`:

```php
<?php

namespace App\Console;

use App\Modules\CarRentals\Services\WorkflowService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // Check for overdue rentals every hour
        $schedule->call(function () {
            $workflowService = app(WorkflowService::class);
            $workflowService->checkOverdueRentals();
        })->hourly()->name('check-overdue-rentals');

        // Send rental reminders daily at 9 AM
        $schedule->command('car-rentals:send-reminders')
            ->dailyAt('09:00')
            ->name('send-rental-reminders');
    }
}
```

Start the scheduler:

```bash
# In development
php artisan schedule:work

# In production, add to crontab:
# * * * * * cd /path/to/your/project && php artisan schedule:run >> /dev/null 2>&1
```

### Step 12: Start Development Servers

```bash
# Terminal 1: Laravel API
cd api
php artisan serve

# Terminal 2: Frontend Admin Dashboard
cd Frontend
npm run dev

# Terminal 3: Frontend Customer Portal
cd FrontendUser
npm run dev

# Terminal 4: Queue worker (for notifications)
cd api
php artisan queue:work
```

### Step 13: Access the Application

- **API**: http://localhost:8000
- **Admin Dashboard**: http://localhost:3000 (or configured port)
- **Customer Portal**: http://localhost:3001 (or configured port)

Login with test credentials:
- Customer: `test.customer@example.com` / `password123`
- Manager: `test.manager@example.com` / `password123`

## Verification Checklist

After installation, verify:

- [ ] Database tables created successfully
- [ ] Sample vehicles visible in database
- [ ] API endpoints respond correctly
- [ ] Authentication works
- [ ] Role-based access control works
- [ ] Vehicle list loads in admin dashboard
- [ ] Vehicle list loads in customer portal
- [ ] Translations load correctly
- [ ] Workflow notifications send emails
- [ ] Scheduled jobs run without errors

## Testing the Complete Flow

### 1. Customer Books a Vehicle

1. Login as customer
2. Browse available vehicles
3. Select a vehicle
4. Choose pickup/return dates
5. Add extras (GPS, child seat, etc.)
6. Submit booking
7. Verify status is "pending"
8. Check email for booking confirmation

### 2. Manager Verifies Booking

1. Login as manager
2. View pending bookings
3. Review customer documents
4. Approve booking
5. Verify status changes to "verified"

### 3. Customer Makes Payment

1. Customer receives verification email
2. Processes payment
3. Status changes to "confirmed"

### 4. Manager Checks In Vehicle

1. Manager navigates to confirmed rental
2. Records vehicle mileage
3. Documents vehicle condition
4. Takes photos
5. Customer signs digital form
6. Status changes to "active"

### 5. Manager Checks Out Vehicle

1. Manager inspects returned vehicle
2. Records return mileage
3. Notes any damages
4. Calculates excess mileage fees
5. Processes security deposit refund
6. Status changes to "completed"

### 6. Customer Leaves Review

1. Customer receives review request email
2. Rates vehicle 1-5 stars
3. Writes review comment
4. Review appears on vehicle page

## Troubleshooting

### Issue: Migration Fails

**Error**: `SQLSTATE[42S01]: Base table or view already exists`

**Solution**:
```bash
# Drop the table and re-run migration
php artisan tinker
Schema::dropIfExists('vehicles');
exit

php artisan migrate
```

### Issue: Class Not Found

**Error**: `Class 'App\Modules\CarRentals\Models\Vehicle' not found`

**Solution**:
```bash
composer dump-autoload
php artisan config:clear
php artisan cache:clear
```

### Issue: Translations Not Loading

**Error**: Translation key returns `car_rentals.vehicle.title` instead of "Vehicle"

**Solution**:
```bash
# Verify language files exist
ls -la lang/en/car_rentals.php

# Clear cache
php artisan config:clear
php artisan cache:clear

# Check locale is set
php artisan tinker
app()->getLocale();
```

### Issue: API Returns 401 Unauthorized

**Solution**:
```bash
# Verify Sanctum is configured
php artisan config:cache

# Check if user has valid token
# In your API client, ensure Authorization header is set:
# Authorization: Bearer {your-token-here}
```

### Issue: Queue Jobs Not Processing

**Solution**:
```bash
# Make sure queue worker is running
php artisan queue:work

# Check queue configuration in .env
# QUEUE_CONNECTION=database (or redis, etc.)

# Verify jobs table exists
php artisan queue:table
php artisan migrate
```

## Rollback Instructions

If you need to uninstall the module:

```bash
# 1. Rollback migrations (in reverse order)
php artisan migrate:rollback --step=6

# 2. Remove routes
# Edit routes/api.php and remove the car rentals require line

# 3. Remove translations
rm lang/*/car_rentals.php

# 4. Remove frontend components
rm -rf Frontend/src/modules/car-rentals
rm -rf FrontendUser/src/modules/car-rentals

# 5. Update composer.json (remove CarRentals namespace)
composer dump-autoload
```

## Next Steps

After successful installation:

1. **Customize Workflow** - Edit `car_rentals/shared/config/workflow.config.json`
2. **Add More Vehicles** - Use the admin dashboard to add your vehicle fleet
3. **Configure Email Templates** - Customize notification templates
4. **Set Up Payment Gateway** - Integrate Stripe/PayPal for payments
5. **Deploy to Production** - Follow Laravel deployment best practices

## Support

If you encounter any issues:

- Check the main **README.md** for detailed documentation
- Review **TASK_3_FRAMEWORK_ARCHITECTURE_AND_CAR_RENTAL_DEMO.md**
- Contact support: support@example.com

---

**Installation completed successfully! 🎉**

Your Car Rental Management Module is now ready to use.
