# CLI API Testing System

A comprehensive command-line testing system for the SaaS Central API, built with Laravel Artisan commands.

## 🚀 Quick Start

```bash
# Test registration with random data
php artisan api:test:register --random

# Login with credentials
php artisan api:test:login --email=test@example.com --password=Password123!

# Get current user (uses saved token)
php artisan api:test:user

# List all available plans
php artisan api:test:plans
```

## 📁 File Structure

```
/home/test/saas/saas-central/
├── app/Console/Commands/ApiTest/   # Artisan commands
│   ├── BaseApiCommand.php          # Base class with shared functionality
│   ├── RegisterCommand.php         # Registration testing
│   ├── LoginCommand.php            # Login testing
│   ├── UserCommand.php             # Get current user
│   ├── LogoutCommand.php           # Logout testing
│   ├── PlansCommand.php            # Plans endpoints
│   ├── MyTenantCommand.php         # Tenant management
│   ├── BillingOverviewCommand.php  # Billing information
│   ├── TestCommand.php             # Generic endpoint testing
│   └── ClearTokensCommand.php      # Token management
├── cli_tests/
│   ├── bearer.txt                  # Saved regular user token
│   ├── admin-bearer.txt            # Saved admin token
│   ├── Responses/                  # JSON response history
│   └── ApiTestHelper.php           # Legacy Tinker helper
```

## 🔑 Authentication & Token Management

### Automatic Token Storage
- After successful login/register, tokens are automatically saved
- Regular user tokens → `cli_tests/bearer.txt`
- Admin tokens → `cli_tests/admin-bearer.txt`
- Subsequent authenticated requests automatically use saved tokens

### Manual Token Management
```bash
# Use a specific token for one request
php artisan api:test:user --token="your-token-here"

# Use a custom token file
php artisan api:test:user --token-file=custom-bearer.txt

# Save login token with custom name
php artisan api:test:login --email=admin@example.com --password=AdminPass! --save-as=admin-bearer.txt

# Clear saved tokens
php artisan api:test:clear-tokens        # Clear regular token
php artisan api:test:clear-tokens --all  # Clear all tokens
php artisan api:test:clear-tokens --admin # Clear only admin token
```

## 📚 Available Commands

### Authentication Commands

#### `api:test:register` - User Registration
```bash
# With specific data
php artisan api:test:register \
  --email=john@example.com \
  --password=Password123! \
  --name="John Doe" \
  --company="Acme Corp" \
  --subdomain=acme \
  --plan_id=1

# With random test data
php artisan api:test:register --random

# Options:
#   --email        Email address
#   --password     Password (min 8 chars)
#   --name         Full name
#   --company      Company name
#   --subdomain    Tenant subdomain
#   --plan_id      Subscription plan (default: 1)
#   --phone        Phone number (optional)
#   --random       Generate random test data
```

#### `api:test:login` - User Login
```bash
# Basic login
php artisan api:test:login --email=john@example.com --password=Password123!

# Login and save as admin token
php artisan api:test:login --email=admin@example.com --password=AdminPass! --save-as=admin-bearer.txt

# Options:
#   --email        Email address
#   --password     Password
#   --save-as      Custom token filename
```

#### `api:test:user` - Get Current User
```bash
# Get current authenticated user
php artisan api:test:user

# Use specific token
php artisan api:test:user --token="custom-token"
```

#### `api:test:logout` - Logout User
```bash
# Logout current user
php artisan api:test:logout

# Logout and clear saved token
php artisan api:test:logout --clear-token
```

### Tenant Management Commands

#### `api:test:my-tenant` - Get/Update Tenant
```bash
# Get current tenant info
php artisan api:test:my-tenant

# Update tenant information
php artisan api:test:my-tenant --update \
  --company="New Company Name" \
  --phone="+1234567890" \
  --address="123 Main St" \
  --city="San Francisco" \
  --state="CA" \
  --postal_code="94105" \
  --country="USA"
```

### Billing Commands

#### `api:test:billing:overview` - Billing Overview
```bash
# Get comprehensive billing information
php artisan api:test:billing:overview
```

### Plan Commands

#### `api:test:plans` - List/Get Plans
```bash
# List all available plans
php artisan api:test:plans

# Get specific plan by ID
php artisan api:test:plans --id=2
```

### Generic Testing Command

#### `api:test` - Test Any Endpoint
```bash
# GET request
php artisan api:test /api/v1/some-endpoint

# POST with data
php artisan api:test /api/v1/endpoint --method=POST --data='{"key":"value"}'

# PUT without auth
php artisan api:test /api/v1/public --method=PUT --auth=false --data='{"name":"test"}'

# DELETE with admin token
php artisan api:test /api/v1/admin/resource/123 --method=DELETE --admin

# Options:
#   endpoint       The API endpoint path
#   --method       HTTP method (GET, POST, PUT, PATCH, DELETE)
#   --data         JSON request body
#   --auth         Include auth token (default: true)
#   --admin        Use admin token instead of regular
```

## 💾 Response Storage

All API responses are automatically saved to timestamped JSON files in `cli_tests/Responses/`:

```
cli_tests/Responses/
├── api_v1_register_2024-01-15_143022.json
├── api_v1_login_2024-01-15_143045.json
└── api_v1_user_2024-01-15_143102.json
```

Each file contains:
- Request details (endpoint, method, payload)
- Response status and headers
- Response body
- Execution time

To disable response saving:
```bash
php artisan api:test:user --no-save
```

## 🗄️ Database Verification

Commands automatically verify database changes and show relevant information:

```
📊 Database Verification:
✅ User created:
   - ID: 42
   - Email: john@example.com
   - Email verified: No

✅ Tenant created:
   - ID: 15
   - UUID: 123e4567-e89b-12d3-a456-426614174000
   - Subdomain: acme
   - Status: active

🔍 Useful SQL queries:
   SELECT * FROM users WHERE email = 'john@example.com';
   SELECT * FROM tenants WHERE id = 15;
```

## 🎯 Common Testing Workflows

### Complete Registration Flow
```bash
# 1. Register new user
php artisan api:test:register --random

# 2. Get user details
php artisan api:test:user

# 3. Get tenant info
php artisan api:test:my-tenant

# 4. Check billing
php artisan api:test:billing:overview
```

### Admin Testing
```bash
# 1. Login as admin
php artisan api:test:login --email=admin@example.com --password=AdminPass! --save-as=admin-bearer.txt

# 2. Test admin endpoints
php artisan api:test /api/v1/admin/dashboard --admin
php artisan api:test /api/v1/admin/users --admin
php artisan api:test /api/v1/admin/tenants --admin
```

### Update Tenant Workflow
```bash
# 1. Get current tenant
php artisan api:test:my-tenant

# 2. Update tenant info
php artisan api:test:my-tenant --update --company="Updated Corp" --phone="+1234567890"

# 3. Verify changes
php artisan api:test:my-tenant
```

## 🛠️ Global Options

All commands support these options:
- `--token` - Use specific bearer token
- `--token-file` - Use token from file
- `--no-save` - Don't save response to file

## 📝 Output Format

Commands provide rich, colored output:
- 🚀 Request details
- ✅ Success responses
- ❌ Error responses
- 📊 Database verification
- 💾 Response file location
- 🔍 Useful SQL queries

## 🔧 Troubleshooting

### No token found
```bash
# Check if token exists
ls cli_tests/bearer.txt

# Login to create new token
php artisan api:test:login --email=your@email.com --password=YourPass!
```

### Invalid token
```bash
# Clear old token
php artisan api:test:clear-tokens

# Login again
php artisan api:test:login --email=your@email.com --password=YourPass!
```

### Response not saved
```bash
# Check Responses directory exists
ls cli_tests/Responses/

# Ensure not using --no-save option
php artisan api:test:user  # Will save response
```

## 🚦 Exit Codes

- `0` - Success
- `1` - Command error (missing args, invalid JSON, etc.)
- HTTP status codes are displayed but don't affect exit code

## 🔄 Integration with CI/CD

```bash
# Example GitHub Actions usage
- name: Test API Registration
  run: php artisan api:test:register --random

- name: Test Authentication
  run: |
    php artisan api:test:login --email=test@example.com --password=TestPass123!
    php artisan api:test:user
    php artisan api:test:logout --clear-token
```

## 📖 Additional Resources

- API Documentation: See `/docs` directory
- Postman Collection: Can be generated from responses
- Database Schema: Check `/database/migrations`

## 📚 Additional Documentation

- **[Command Reference](command_reference.md)** - Quick reference table of all commands with examples
- **[Test Scenarios](test_scenarios.md)** - Complete workflow examples and common use cases
- **[Implementation Progress](implementation_progress.md)** - Development tracking and implementation notes

## 🤝 Contributing

To add new test commands:

1. Create new command in `app/Console/Commands/ApiTest/`
2. Extend `BaseApiCommand`
3. Implement required methods:
   - `prepareRequestData()`
   - `getExamplePayload()`
   - `getEndpointDescription()`
4. Override optional methods as needed:
   - `handleTokenSaving()`
   - `verifyDatabaseChanges()`

Example:
```php
class NewEndpointCommand extends BaseApiCommand
{
    protected $signature = 'api:test:new-endpoint {--param=}';
    protected string $method = 'POST';
    protected string $endpoint = '/api/v1/new-endpoint';
    
    protected function prepareRequestData(): array
    {
        return [
            'param' => $this->option('param'),
        ];
    }
    
    // ... implement other required methods
}
```
