# Car Rentals Module - Architecture Documentation

## Overview

This document explains the architectural decisions, design patterns, and modularity principles used in the Car Rentals module.

## Core Principles

### 1. Modularity

The module is designed to be **plug-and-play** and **swappable**. All domain-specific logic is isolated in the `car_rentals/` directory.

**Key Benefits:**
- Easy to swap out for other business domains (real estate, equipment rental, hotel booking)
- Can be enabled/disabled without affecting core application
- Independent deployment possible
- Clear separation of concerns

### 2. Reusability

The module leverages **70%+ of the Solar App's core infrastructure**:

**Reused Components:**
- ✅ Authentication (Laravel Sanctum)
- ✅ Authorization (Spatie Permissions)
- ✅ User Management
- ✅ Document Management (FileContainer polymorphic)
- ✅ Notification System
- ✅ Messaging System
- ✅ Activity Logging
- ✅ Settings Management
- ✅ Multi-language Support

**Custom Components (30%):**
- Vehicles (domain-specific asset)
- Rentals (domain-specific transaction)
- Workflow automation specific to car rentals

### 3. Multi-language First

All text content supports multiple languages:

- **Backend**: Uses Laravel's translation system (`trans()`)
- **Frontend**: Uses Vue i18n (`$t()`)
- **Documents**: Template variables support language-specific content
- **Database**: Stores multilang data in JSON columns for flexibility

## Architecture Patterns

### 1. Modular Monolith Pattern

```
Laravel App (Monolith)
├── Core Modules (Shared)
│   ├── Authentication
│   ├── User Management
│   ├── Document Management
│   └── ...
└── Domain Modules (Pluggable)
    ├── Solar Plants (existing)
    ├── Car Rentals (new)
    └── [Future modules...]
```

**Benefits:**
- Simple deployment (single application)
- Shared infrastructure (database, auth, etc.)
- Easy to migrate to microservices later if needed
- No network overhead between modules

### 2. Repository Pattern (Implicit via Eloquent)

Models act as repositories with additional business logic:

```php
// Vehicle.php
public function isAvailable(): bool
{
    return $this->status === VehicleStatus::AVAILABLE;
}

public function getAverageRating(): float
{
    return $this->reviews()->avg('rating') ?? 0.0;
}
```

### 3. Service Layer Pattern

Complex business logic extracted to services:

```php
// WorkflowService.php
public function transitionRental(Rental $rental, RentalStatus $newStatus, ?int $userId = null): bool
{
    // Business logic for state transitions
    // Handles notifications, side effects, logging
}
```

**Benefits:**
- Controllers stay thin
- Business logic testable independently
- Reusable across controllers/commands

### 4. State Machine Pattern

Rental workflow implemented as state machine:

```
States: draft → pending → verified → confirmed → active → completed
```

**Features:**
- Valid transitions defined in enum
- Automatic notifications on state change
- Audit trail of all transitions
- Prevents invalid state changes

### 5. API Resource Pattern

Consistent API responses using Laravel resources:

```php
// VehicleResource.php
public function toArray(Request $request): array
{
    return [
        'id' => $this->id,
        'full_name' => $this->getFullName(),
        'status' => [
            'value' => $this->status->value,
            'label' => $this->status->label(),
            'color' => $this->status->color(),
        ],
        // ...
    ];
}
```

**Benefits:**
- Consistent JSON structure
- Hide internal fields
- Transform data for API consumers
- Conditional field inclusion

### 6. Observer Pattern (via Notifications)

Events trigger notifications automatically:

```php
// On rental status change
$rental->user->notify(new RentalStatusChanged($rental, $template));
```

**Notification Channels:**
- Email
- Database (in-app notifications)
- SMS (optional)
- Push (optional)

## Database Design

### Polymorphic Relationships

The module uses polymorphic relationships for flexibility:

```php
// FileContainer is polymorphic
Vehicle->fileContainer() // Photos, documents
Rental->fileContainer()  // Driver's license, contracts
```

**Benefits:**
- Single file management system for all entities
- Consistent API for file operations
- Easy to extend to other entities

### Soft Deletes

All main entities use soft deletes:

```php
use SoftDeletes;
```

**Benefits:**
- Data recovery possible
- Maintain referential integrity
- Audit trail preservation

### UUID Primary Keys

Main entities (vehicles, rentals) use UUIDs:

```php
use HasUuids;
protected $keyType = 'string';
public $incrementing = false;
```

**Benefits:**
- No sequential ID exposure
- Distributed system ready
- Merge multiple databases easier

### JSON Columns

Used for flexible data:

```php
$table->json('features'); // Vehicle features array
$table->json('multilang_data'); // Translations
```

**Benefits:**
- Schema-less flexibility
- No additional tables needed
- Easy to query with JSON functions

## Frontend Architecture

### Component-Based Design

```
VehicleCard (Presentational)
├── Props: vehicle, showBookButton, etc.
├── Events: @book, @edit, @delete
└── Self-contained logic

VehicleList (Container)
├── Manages state (vehicles, filters)
├── API calls
├── Uses VehicleCard for display
└── Handles events from VehicleCard
```

**Benefits:**
- Reusable components
- Easy to test
- Clear responsibilities
- Composable

### Composables (Vue 3)

Extract reusable logic:

```javascript
// useVehicles.js
export function useVehicles() {
  const vehicles = ref([])
  const loading = ref(false)

  const fetchVehicles = async (filters) => {
    // API logic
  }

  return { vehicles, loading, fetchVehicles }
}
```

### Pinia Store

Centralized state management:

```javascript
// stores/carRentals.js
export const useCarRentalsStore = defineStore('carRentals', {
  state: () => ({
    vehicles: [],
    currentRental: null,
  }),
  actions: {
    async fetchVehicles() { },
    async createRental(data) { },
  },
})
```

## Workflow Automation

### Configuration-Driven Workflow

Workflow defined in JSON config:

```json
{
  "states": {
    "pending": {
      "transitions_to": ["verified", "rejected"],
      "notifications": {
        "on_enter": {
          "customer": "booking_submitted",
          "manager": "new_booking_received"
        }
      }
    }
  }
}
```

**Benefits:**
- No code changes for workflow adjustments
- Easy to visualize workflow
- Version controlled
- Environment-specific workflows possible

### Scheduled Jobs

Laravel scheduler handles recurring tasks:

```php
$schedule->call(function () {
    $workflowService->checkOverdueRentals();
})->hourly();
```

**Tasks:**
- Check for overdue rentals
- Send reminder emails
- Generate reports
- Cleanup old data

## Security Architecture

### Authentication

```
Laravel Sanctum (API Token Auth)
├── Stateless tokens
├── Token expiration
├── Token refresh
└── Multiple tokens per user
```

### Authorization

```
Spatie Permission
├── Role-based access (customer, manager, admin)
├── Permission-based access
├── Middleware protection
└── Policy checks in controllers
```

### API Security

```
Route::middleware(['auth:sanctum', 'role:manager|admin'])
```

**Layers:**
1. Authentication (Sanctum)
2. Authorization (Spatie)
3. Request validation (FormRequest)
4. Business logic validation (Service)
5. Database constraints

## Scalability Considerations

### Horizontal Scaling

The module is designed for horizontal scaling:

- **Stateless API** - No session dependencies
- **Database-backed queues** - Can use Redis for better performance
- **File storage** - Can use S3 for distributed file storage
- **Caching** - Redis/Memcached ready

### Performance Optimizations

1. **Eager Loading**
   ```php
   Vehicle::with(['owner', 'manager', 'reviews'])->get();
   ```

2. **Database Indexing**
   - Composite indexes on frequently queried columns
   - Full-text indexes for search

3. **Pagination**
   - All list endpoints paginated
   - Default 15 items per page

4. **Caching Strategy**
   ```php
   Cache::remember('vehicles:available', 3600, fn() => Vehicle::available()->get());
   ```

5. **Query Optimization**
   - Select only needed columns
   - Use `exists()` instead of `count() > 0`
   - Avoid N+1 queries

## Swappability Architecture

### Abstract Entity Mapping

The module uses abstract entities that can be mapped to any domain:

```json
{
  "abstract_entities": {
    "Asset": "Vehicle",
    "Transaction": "Rental",
    "Payment": "RentalPayment",
    "AssetExtra": "RentalExtra"
  }
}
```

### Swap Checklist

To swap for another domain (e.g., hotel booking):

1. **Update Module Config**
   ```json
   {
     "abstract_entities": {
       "Asset": "Room",
       "Transaction": "Booking",
       "Payment": "BookingPayment",
       "AssetExtra": "RoomAmenity"
     }
   }
   ```

2. **Rename Models**
   - `Vehicle` → `Room`
   - `Rental` → `Booking`
   - Update relationships

3. **Adjust Enums**
   - `VehicleCategory` → `RoomType`
   - `VehicleStatus` → `RoomStatus`

4. **Update Workflow**
   - Modify state transitions for hotel booking
   - Adjust notification templates

5. **Translate Content**
   - Replace `car_rentals` namespace
   - Translate all strings

6. **Update UI Components**
   - `VehicleCard` → `RoomCard`
   - Adjust styling for hotel booking

## Testing Strategy

### Unit Tests

Test individual components:

```php
// VehicleTest.php
public function test_vehicle_calculates_average_rating_correctly()
{
    $vehicle = Vehicle::factory()->create();
    VehicleReview::factory()->create(['vehicle_id' => $vehicle->id, 'rating' => 5]);
    VehicleReview::factory()->create(['vehicle_id' => $vehicle->id, 'rating' => 3]);

    $this->assertEquals(4.0, $vehicle->getAverageRating());
}
```

### Feature Tests

Test complete workflows:

```php
// RentalFlowTest.php
public function test_complete_rental_workflow()
{
    $customer = User::factory()->customer()->create();
    $vehicle = Vehicle::factory()->available()->create();

    // 1. Customer creates booking
    $response = $this->actingAs($customer)->post('/rentals', [...]);
    $response->assertStatus(201);

    // 2. Manager verifies
    $manager = User::factory()->manager()->create();
    $rental = Rental::latest()->first();
    $response = $this->actingAs($manager)->post("/rentals/{$rental->id}/verify", [
        'action' => 'approve'
    ]);
    $response->assertStatus(200);

    // 3. Assert status changed
    $this->assertEquals('verified', $rental->fresh()->status->value);
}
```

### Integration Tests

Test API endpoints:

```php
// VehicleApiTest.php
public function test_customer_can_list_available_vehicles()
{
    $customer = User::factory()->customer()->create();
    Vehicle::factory()->available()->count(5)->create();

    $response = $this->actingAs($customer)->getJson('/v1/car-rentals/vehicles');

    $response->assertStatus(200)
        ->assertJsonStructure(['success', 'data', 'meta'])
        ->assertJsonCount(5, 'data');
}
```

## Monitoring and Observability

### Logging

Structured logging for important events:

```php
Log::info("Rental status transition", [
    'rental_id' => $rental->id,
    'old_status' => $oldStatus->value,
    'new_status' => $newStatus->value,
    'user_id' => $userId,
]);
```

### Activity Tracking

All changes logged via Activity model:

```php
Activity::create([
    'subject_type' => Rental::class,
    'subject_id' => $rental->id,
    'description' => 'Status changed',
    'properties' => ['old' => 'pending', 'new' => 'verified'],
    'causer_id' => auth()->id(),
]);
```

### Metrics

Track key metrics:

- **Business Metrics**
  - Total rentals per day/week/month
  - Average rental duration
  - Revenue per vehicle
  - Booking conversion rate

- **System Metrics**
  - API response times
  - Queue job processing times
  - Database query performance
  - Cache hit rates

## Future Enhancements

### Microservices Migration Path

If the application grows, this module can be extracted to a microservice:

```
Current: Modular Monolith
└── Car Rentals Module

Future: Microservices
├── API Gateway
├── Car Rentals Service (this module)
├── User Service
├── Document Service
└── Notification Service
```

**Migration Steps:**
1. Extract module to separate repository
2. Set up independent database
3. Create REST/GraphQL API
4. Implement service-to-service auth
5. Set up message queue for events

### Event Sourcing

For better audit trail and replay capability:

```php
// Instead of updating state directly
$rental->status = RentalStatus::VERIFIED;
$rental->save();

// Store events
RentalVerified::dispatch($rental, $verifiedBy);

// Rebuild state from events
$rental = Rental::reconstituteFromEvents($events);
```

## Conclusion

This architecture provides:

- ✅ **Modularity** - Easy to swap for other domains
- ✅ **Scalability** - Ready for horizontal scaling
- ✅ **Maintainability** - Clean separation of concerns
- ✅ **Testability** - Comprehensive test coverage possible
- ✅ **Security** - Multiple layers of protection
- ✅ **Performance** - Optimized for speed
- ✅ **Flexibility** - Easily extensible

The module demonstrates how a well-architected Laravel application can be both powerful and flexible, serving as a foundation for multiple business domains.

---

**For questions about architecture decisions, contact: architecture@example.com**
