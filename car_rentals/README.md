# Car Rental Management Module

A **modular, multilanguage car rental management system** built on top of the Solar App framework. This module demonstrates how the base framework can be adapted for different business use cases while reusing 70%+ of core functionality.

## 🎯 Overview

This car rental module provides a complete solution for managing vehicle rentals with:

- ✅ **Modular Architecture** - Easy to swap out for other business domains
- ✅ **Multilanguage Support** - English, German, French, Spanish, Italian
- ✅ **Workflow Automation** - Automated booking, verification, and notification workflows
- ✅ **Role-Based Access** - Customer, Manager, and Admin roles with appropriate permissions
- ✅ **Document Management** - Rental agreements, inspection checklists, payment receipts
- ✅ **Vue 3 Components** - Ready-to-use frontend components for admin and customer portals

## 📁 Project Structure

```
car_rentals/
├── backend/                          # Laravel backend module
│   ├── Controllers/                  # API controllers
│   │   ├── VehicleController.php     # Vehicle CRUD operations
│   │   └── RentalController.php      # Rental/booking management
│   ├── Models/                       # Eloquent models
│   │   ├── Vehicle.php               # Vehicle model with multilang support
│   │   ├── Rental.php                # Rental/booking model
│   │   ├── RentalExtra.php           # Rental extras (GPS, child seat, etc.)
│   │   ├── RentalPayment.php         # Payment tracking
│   │   ├── VehicleReview.php         # Customer reviews
│   │   └── VehicleMaintenance.php    # Maintenance records
│   ├── Services/                     # Business logic services
│   │   └── WorkflowService.php       # Rental workflow state machine
│   ├── Enums/                        # PHP enums
│   │   ├── VehicleStatus.php         # available, rented, maintenance, retired
│   │   ├── RentalStatus.php          # draft, pending, verified, confirmed, active, completed, etc.
│   │   ├── VehicleCategory.php       # economy, compact, midsize, luxury, suv, van
│   │   ├── TransmissionType.php      # manual, automatic
│   │   └── FuelType.php              # gasoline, diesel, electric, hybrid
│   ├── Requests/                     # Form request validation
│   ├── Resources/                    # API resources for JSON transformation
│   ├── Notifications/                # Email and database notifications
│   ├── Migrations/                   # Database schema migrations
│   ├── Seeders/                      # Sample data seeders
│   └── routes/                       # API route definitions
│       └── api.php                   # Car rental API routes
├── frontend/                         # Vue 3 components
│   ├── components/                   # Reusable Vue components
│   │   ├── VehicleCard.vue           # Vehicle display card
│   │   └── RentalStatusTimeline.vue  # Visual rental status timeline
│   ├── views/                        # Page views
│   │   └── VehicleList.vue           # Vehicle listing page
│   ├── store/                        # Pinia store modules
│   ├── composables/                  # Vue composables
│   └── locales/                      # Frontend translations
├── shared/                           # Shared resources
│   ├── workflows/                    # Workflow definitions
│   ├── documents/                    # Document templates
│   │   ├── rental_agreement_template.md
│   │   └── vehicle_inspection_checklist.md
│   ├── translations/                 # Language files
│   │   ├── en/car_rentals.php        # English translations
│   │   ├── de/car_rentals.php        # German translations
│   │   └── fr/car_rentals.php        # French translations
│   └── config/                       # Configuration files
│       └── workflow.config.json      # Workflow state machine configuration
├── docs/                             # Documentation
├── module.config.json                # Module configuration
└── README.md                         # This file
```

## 🚀 Quick Start

### Prerequisites

- PHP 8.2+
- Laravel 11+
- MySQL 8.0+ or PostgreSQL
- Node.js 18+ (for Vue 3 frontend)
- Composer
- NPM or Yarn

### Installation

#### 1. Copy the module to your project

```bash
# Copy the entire car_rentals folder to your Laravel project root
cp -r car_rentals /path/to/your/laravel/project/
```

#### 2. Update Laravel autoloader

Add the module namespace to your `composer.json`:

```json
{
  "autoload": {
    "psr-4": {
      "App\\": "app/",
      "App\\Modules\\CarRentals\\": "car_rentals/backend/"
    }
  }
}
```

Then run:

```bash
composer dump-autoload
```

#### 3. Register the module routes

In your `routes/api.php`, include the car rentals routes:

```php
require __DIR__ . '/../car_rentals/backend/routes/api.php';
```

#### 4. Run migrations

```bash
# Copy migrations to Laravel migrations folder
cp car_rentals/backend/Migrations/*.php database/migrations/

# Run migrations
php artisan migrate
```

#### 5. Seed sample data (optional)

```bash
php artisan db:seed --class=App\\Modules\\CarRentals\\Seeders\\VehicleSeeder
```

#### 6. Copy language files

```bash
# Copy translation files to Laravel lang directory
cp -r car_rentals/shared/translations/* lang/
```

#### 7. Install frontend components

```bash
# Copy Vue components to your frontend project
# For Admin Dashboard:
cp -r car_rentals/frontend/* Frontend/src/modules/car-rentals/

# For Customer Portal:
cp -r car_rentals/frontend/* FrontendUser/src/modules/car-rentals/
```

## 🔧 Configuration

### Module Configuration

Edit `car_rentals/module.config.json` to customize:

- Module name and description
- API route prefixes
- Frontend route prefixes
- Supported languages
- Workflow definitions

### Workflow Configuration

Edit `car_rentals/shared/config/workflow.config.json` to customize:

- Rental status workflow
- State transitions
- Automated notifications
- Check-in/check-out workflows

## 📊 Database Schema

### Tables Created

- **vehicles** - Vehicle inventory
- **rentals** - Rental bookings
- **rental_extras** - Additional services (GPS, child seat, etc.)
- **rental_payments** - Payment tracking
- **vehicle_reviews** - Customer reviews
- **vehicle_maintenance** - Maintenance records

### Key Relationships

```
users (existing) ─┐
                  ├─► vehicles ──► rentals ──┬─► rental_extras
                  │                           ├─► rental_payments
                  │                           └─► vehicle_reviews
                  └─► vehicle_maintenance
```

## 🌐 Multilanguage Support

### Supported Languages

- **English (en)** - Default
- **German (de)** - Deutsch
- **French (fr)** - Français
- **Spanish (es)** - Español
- **Italian (it)** - Italiano

### Adding New Language

1. Create new translation file: `car_rentals/shared/translations/NEW_LANG/car_rentals.php`
2. Copy structure from `en/car_rentals.php`
3. Translate all strings
4. Update `module.config.json` to include new language in `languages_supported`

### Using Translations in Code

**Backend (PHP):**
```php
trans('car_rentals.vehicle.title', [], 'de'); // German
trans('car_rentals.notifications.booking_confirmed.subject', ['name' => $user->name], 'fr'); // French
```

**Frontend (Vue):**
```vue
<template>
  <h1>{{ $t('car_rentals.vehicle.title') }}</h1>
</template>
```

## 🔄 Workflow Automation

### Rental Booking Workflow

```
draft → pending → verified → confirmed → active → completed
              ↓              ↓
           rejected      cancelled
```

### Automated Notifications

- **On Booking Submission** - Customer confirmation, Manager alert
- **On Verification** - Customer notification
- **On Confirmation** - Booking confirmed with pickup details
- **7 Days Before Pickup** - Reminder email
- **1 Day Before Pickup** - Final reminder with location
- **1 Day Before Return** - Return reminder
- **On Completion** - Thank you email + review request
- **On Overdue** - Overdue alerts to customer and manager

### Scheduled Jobs

Add to `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // Check for overdue rentals every hour
    $schedule->call(function () {
        $workflowService = app(WorkflowService::class);
        $workflowService->checkOverdueRentals();
    })->hourly();
}
```

## 📡 API Endpoints

### Vehicles

```
GET    /v1/car-rentals/vehicles              List vehicles (with filters)
POST   /v1/car-rentals/vehicles              Create vehicle (manager/admin)
GET    /v1/car-rentals/vehicles/{id}         Get vehicle details
PUT    /v1/car-rentals/vehicles/{id}         Update vehicle (manager/admin)
DELETE /v1/car-rentals/vehicles/{id}         Delete vehicle (admin)
GET    /v1/car-rentals/vehicles/{id}/availability  Check availability
```

### Rentals

```
GET    /v1/car-rentals/rentals               List rentals (role-filtered)
POST   /v1/car-rentals/rentals               Create rental booking
GET    /v1/car-rentals/rentals/{id}          Get rental details
PUT    /v1/car-rentals/rentals/{id}          Update rental
POST   /v1/car-rentals/rentals/{id}/verify   Verify rental (manager/admin)
POST   /v1/car-rentals/rentals/{id}/checkin  Check-in vehicle (manager/admin)
POST   /v1/car-rentals/rentals/{id}/checkout Check-out vehicle (manager/admin)
POST   /v1/car-rentals/rentals/{id}/cancel   Cancel rental
```

### Example: Create a Rental Booking

```bash
POST /v1/car-rentals/rentals
Authorization: Bearer {token}
Content-Type: application/json

{
  "vehicle_id": "uuid-of-vehicle",
  "pickup_date": "2025-06-01T10:00:00Z",
  "return_date": "2025-06-07T10:00:00Z",
  "daily_rate": 85.00,
  "security_deposit": 1500.00,
  "insurance_fee": 50.00,
  "document_language": "de",
  "extras": [
    {
      "name": "GPS Navigation",
      "quantity": 1,
      "unit_price": 5.00,
      "total_price": 35.00
    },
    {
      "name": "Child Seat",
      "quantity": 1,
      "unit_price": 8.00,
      "total_price": 56.00
    }
  ]
}
```

## 🎨 Frontend Components

### VehicleCard Component

Displays vehicle information in a card format.

**Props:**
- `vehicle` - Vehicle object
- `showBookButton` - Show booking button (customer view)
- `showEditButton` - Show edit button (manager/admin)
- `showDeleteButton` - Show delete button (admin)

**Events:**
- `@book` - User clicks book button
- `@view-details` - User clicks details button
- `@edit` - Manager clicks edit button
- `@delete` - Admin clicks delete button

**Usage:**
```vue
<VehicleCard
  :vehicle="vehicle"
  :show-book-button="true"
  @book="handleBooking"
/>
```

### RentalStatusTimeline Component

Visual timeline showing rental status progression.

**Props:**
- `rental` - Rental object

**Usage:**
```vue
<RentalStatusTimeline :rental="rental" />
```

## 🔄 Swapping for Another Business Domain

This module is designed to be **modular and swappable**. Here's how to adapt it for other use cases:

### Abstract Entities

The module uses abstract entities that can be mapped to any business domain:

| Abstract Entity | Car Rental | Real Estate | Equipment Rental | Hotel Booking |
|----------------|------------|-------------|------------------|---------------|
| **Asset** | Vehicle | Property | Equipment | Room |
| **Transaction** | Rental | Lease/Sale | Rental | Booking |
| **Payment** | RentalPayment | RentPayment | RentalPayment | BookingPayment |
| **AssetExtra** | RentalExtra | PropertyFeature | EquipmentAccessory | RoomAmenity |

### Steps to Swap

1. **Update `module.config.json`**
   - Change `abstract_entities` mapping
   - Update module name and description

2. **Rename Models**
   - `Vehicle` → `YourAsset`
   - `Rental` → `YourTransaction`
   - Update relationships

3. **Update Enums**
   - `VehicleStatus` → `AssetStatus`
   - `VehicleCategory` → `AssetCategory`
   - Adjust enum values to match your domain

4. **Modify Workflow**
   - Edit `workflow.config.json`
   - Adjust states and transitions for your business logic

5. **Update Translations**
   - Replace `car_rentals` namespace
   - Translate all strings for your domain

6. **Customize Frontend Components**
   - Update component names and props
   - Adjust UI to match your business needs

### Example: Real Estate Module

```json
{
  "abstract_entities": {
    "Asset": "Property",
    "Transaction": "Lease",
    "Payment": "RentPayment",
    "AssetExtra": "PropertyFeature"
  }
}
```

Then rename files and classes:
- `Vehicle.php` → `Property.php`
- `Rental.php` → `Lease.php`
- `VehicleCategory` → `PropertyType` (apartment, house, commercial, etc.)

## 🧪 Testing

### Unit Tests

```bash
php artisan test --filter CarRentals
```

### Feature Tests

Create tests in `tests/Feature/CarRentals/`:

```php
public function test_customer_can_book_available_vehicle()
{
    $vehicle = Vehicle::factory()->available()->create();
    $customer = User::factory()->customer()->create();

    $response = $this->actingAs($customer)
        ->postJson('/v1/car-rentals/rentals', [
            'vehicle_id' => $vehicle->id,
            'pickup_date' => now()->addDays(7),
            'return_date' => now()->addDays(14),
            'daily_rate' => $vehicle->daily_rate,
            'security_deposit' => $vehicle->security_deposit,
        ]);

    $response->assertStatus(201)
        ->assertJsonStructure(['success', 'data', 'message']);
}
```

## 📈 Performance Optimization

### Caching

Add caching for frequently accessed data:

```php
// Cache available vehicles
$vehicles = Cache::remember('vehicles:available', 3600, function () {
    return Vehicle::available()->get();
});
```

### Database Indexes

Key indexes are already defined in migrations:

- `vehicles` - `(status, category)`, `(location, status)`, `(make, model)`
- `rentals` - `(user_id, status)`, `(vehicle_id, pickup_date, return_date)`, `(status, pickup_date)`

### Eager Loading

Always eager load relationships:

```php
$rental = Rental::with(['vehicle', 'user', 'extras', 'payments'])->find($id);
```

## 🔐 Security Considerations

### Role-Based Authorization

All endpoints are protected with middleware:

```php
Route::middleware(['auth:sanctum', 'role:manager|admin'])->group(function () {
    // Manager and admin only routes
});
```

### Document Verification

Always verify user ownership before displaying sensitive data:

```php
if ($user->hasRole('customer') && $rental->user_id !== $user->id) {
    abort(403, 'Unauthorized');
}
```

### File Upload Security

When implementing file uploads:
- Validate MIME types
- Limit file sizes
- Store outside web root
- Generate random filenames
- Scan for malware

## 📝 Contributing

To contribute to this module:

1. Create a feature branch
2. Make your changes
3. Write tests
4. Submit a pull request

## 📄 License

This module is open-source software licensed under the MIT license.

## 🆘 Support

For questions or issues:

- **Email**: support@example.com
- **Documentation**: https://docs.example.com/car-rentals
- **GitHub Issues**: https://github.com/yourorg/solar-app/issues

## 🎯 Roadmap

### Planned Features

- [ ] Payment gateway integration (Stripe, PayPal)
- [ ] Real-time vehicle tracking (GPS)
- [ ] Mobile app (React Native / Flutter)
- [ ] Advanced pricing rules (seasonal, demand-based)
- [ ] Insurance claim processing
- [ ] Customer loyalty program
- [ ] Fleet analytics dashboard
- [ ] Integration with accounting systems

## 📚 Additional Resources

- **TASK_1_WORKFLOWS_AND_CUSTOMER_EXPERIENCE.md** - Customer experience guidelines
- **TASK_3_FRAMEWORK_ARCHITECTURE_AND_CAR_RENTAL_DEMO.md** - Detailed architecture analysis
- **DEVELOPER_DOCUMENTATION_FRAMEWORK_ARCHITECTURE.md** - Framework documentation

---

**Built with ❤️ by the Solar App Team**

*Last Updated: 2025-01-14*
