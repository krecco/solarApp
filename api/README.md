# SaaS Central API

This is the central management application for the multi-tenant SaaS platform. It handles user authentication, tenant provisioning, billing, and orchestration.

## Features

- 🔐 **Authentication**: User registration, login, email verification
- 🏢 **Tenant Management**: Create, provision, suspend, and manage tenants
- 💳 **Billing**: Subscription plans, Stripe integration (ready for implementation)
- 🔑 **API Keys**: Secure API key generation and management for tenants
- 📊 **Plan Management**: Multiple subscription tiers with feature flags
- 🚀 **Async Provisioning**: Queue-based tenant provisioning

## Tech Stack

- Laravel 12.x
- PostgreSQL 15+
- Laravel Sanctum (API Authentication)
- Laravel Cashier (Stripe Billing)
- Spatie Laravel Data (DTOs)
- Spatie Laravel Permission (RBAC)

## Installation

### Prerequisites

- PHP 8.3+
- PostgreSQL 15+
- Composer 2.x
- Node.js 18+ (for frontend assets)

### Setup Steps

1. **Clone and install dependencies**
```bash
cd /home/test/saas/saas-central
composer install
```

2. **Configure environment**
```bash
cp .env.example .env
php artisan key:generate
```

3. **Update .env file**
```env
APP_NAME="SaaS Central"
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=saas_central
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

# Stripe (optional for Phase 1)
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
```

4. **Create database**
```sql
CREATE DATABASE saas_central;
```

5. **Run migrations and seeders**
```bash
php artisan migrate
php artisan db:seed
```

6. **Start the development server**
```bash
php artisan serve
```

## API Documentation

### Base URL
```
http://localhost:8000/api/v1
```

### Authentication

All protected endpoints require a Bearer token in the Authorization header:
```
Authorization: Bearer {token}
```

### Main Endpoints

#### Authentication
- `POST /register` - Register new user
- `POST /login` - Login user
- `POST /logout` - Logout user
- `GET /user` - Get current user

#### Plans
- `GET /plans` - List all plans (public)
- `GET /plans/{id}` - Get plan details
- `POST /plans/compare` - Compare multiple plans

#### Tenants
- `GET /tenants` - List user's tenants
- `POST /tenants` - Create new tenant
- `GET /tenants/{uuid}` - Get tenant details
- `PATCH /tenants/{uuid}` - Update tenant
- `DELETE /tenants/{uuid}` - Delete tenant
- `POST /tenants/{uuid}/suspend` - Suspend tenant
- `POST /tenants/{uuid}/activate` - Activate tenant

#### API Keys
- `GET /tenants/{uuid}/api-keys` - List API keys
- `POST /tenants/{uuid}/api-keys` - Create API key
- `DELETE /tenants/{uuid}/api-keys/{id}` - Revoke API key
- `POST /tenants/{uuid}/api-keys/{id}/rotate` - Rotate API key

#### Utilities
- `POST /check-subdomain` - Check subdomain availability
- `GET /health` - Health check

## Testing

### Run Tests
```bash
php artisan test
```

### Test Specific Features
```bash
php artisan test --filter=ApiEndpointsTest
```

### Manual Testing with cURL

1. **Register a user**
```bash
curl -X POST http://localhost:8000/api/v1/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

2. **Login**
```bash
curl -X POST http://localhost:8000/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password123"
  }'
```

3. **Create a tenant** (use token from login)
```bash
curl -X POST http://localhost:8000/api/v1/tenants \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "subdomain": "acme",
    "company_name": "Acme Corp",
    "plan_id": 1
  }'
```

## Queue Processing

For async tenant provisioning, run the queue worker:
```bash
php artisan queue:work
```

For development, you can use sync queue driver in .env:
```env
QUEUE_CONNECTION=sync
```

## Database Schema

The application uses these main tables:
- `users` - User accounts
- `plans` - Subscription plans
- `tenants` - Tenant organizations
- `api_keys` - API keys for tenant communication
- `provisioning_jobs` - Track provisioning status
- `subscription_events` - Audit trail
- `tenant_sync_queue` - Central to tenant sync

## Project Structure

```
app/
├── Data/              # DTOs for validation
├── Http/
│   ├── Controllers/
│   │   └── Api/      # API controllers
│   └── Middleware/   # Custom middleware
├── Jobs/             # Background jobs
├── Models/           # Eloquent models
└── Services/         # Business logic
    ├── ApiKeyService.php
    ├── DatabaseSchemaService.php
    ├── EncryptionService.php
    ├── SubdomainValidator.php
    └── TenantProvisioningService.php
```

## Development Tips

1. **Check logs**: `storage/logs/laravel.log`
2. **Clear caches**: `php artisan optimize:clear`
3. **Refresh database**: `php artisan migrate:fresh --seed`
4. **Generate IDE helper**: `php artisan ide-helper:generate`

## Security

- All passwords are hashed using bcrypt
- API keys are stored as SHA-256 hashes
- Database credentials are encrypted using Laravel's encryption
- CSRF protection enabled for web routes
- Rate limiting configured for API endpoints

## Next Steps

1. Configure PostgreSQL for production
2. Set up Stripe for billing
3. Implement tenant application
4. Add frontend with Vue.js
5. Set up monitoring and logging

## Support

For issues or questions, refer to the documentation in `/home/test/saas/saas_docu/`
