# Prompt Instructions for CLI API Testing System

## New Conversation Starting Prompt

I need to create a CLI-based API testing system for my Laravel SaaS application. Here are the specific requirements and context:

### Project Context
- **Backend**: Laravel 12 API at `/home/test/saas/saas-central`
- **Purpose**: Manual API testing via CLI commands
- **Location**: Tests should be in `/home/test/saas/saas-central/cli_tests`

### Core Requirements

1. **Individual Test Execution**
   - Run specific API endpoints individually (not full test suites)
   - Example: `php artisan api:test register --email=test@example.com --password=Test123!`
   - Store responses for inspection
   - Show formatted output with colors

2. **Token Management**
   - After login/register, store bearer token in `bearer.txt`
   - Auto-include token from file for authenticated requests
   - Option to override with custom token
   - Clear token command

3. **Response Handling**
   - Show HTTP status code
   - Pretty-print JSON responses
   - Save full response to timestamped file
   - Show relevant database changes

4. **Documentation Requirements**
   Each endpoint should have:
   - Required/optional parameters
   - Example payloads
   - Expected responses (success & error cases)
   - Database tables affected
   - SQL queries to verify results

5. **Available Endpoints to Test**
   ```
   # Authentication
   POST /api/v1/register
   POST /api/v1/login
   POST /api/v1/logout
   GET  /api/v1/user
   POST /api/v1/profile/complete
   
   # Email Verification  
   POST /api/v1/email/verify
   POST /api/v1/email/resend
   
   # Password Reset
   POST /api/v1/password/forgot
   POST /api/v1/password/reset
   
   # OTP
   POST /api/v1/otp/send
   POST /api/v1/otp/verify
   
   # Tenant Management
   GET  /api/v1/my-tenant
   PUT  /api/v1/my-tenant
   
   # Billing
   GET  /api/v1/billing/overview
   POST /api/v1/billing/subscription
   DELETE /api/v1/billing/subscription
   
   # Plans
   GET  /api/v1/plans
   GET  /api/v1/plans/{id}
   
   # Admin Endpoints (need admin role)
   GET  /api/v1/admin/dashboard
   GET  /api/v1/admin/users
   POST /api/v1/admin/users
   GET  /api/v1/admin/tenants
   PUT  /api/v1/admin/tenants/{uuid}
   ```

### Preferred Implementation Approach

I'm open to suggestions, but I'm thinking either:

1. **Custom Artisan Commands** 
   - One command per endpoint
   - Shared base class for common functionality
   - Built-in to Laravel project

2. **Shell Scripts with curl**
   - Standalone bash/zsh scripts
   - Easy to modify without PHP knowledge
   - Can be run from anywhere

3. **Hybrid Approach**
   - Artisan command that generates/runs curl commands
   - Best of both worlds

### Example Usage Vision

```bash
# Register new user
php artisan api:test register --email=john@example.com --password=Test123! --company="Acme Corp" --subdomain=acme

# Output:
✅ POST /api/v1/register - 201 Created
📧 User: john@example.com
🏢 Tenant: acme.yoursaas.com
🔑 Token saved to bearer.txt

📊 Database Changes:
- users: 1 row inserted (id: 42)
- tenants: 1 row inserted (id: 15, subdomain: acme)

💾 Full response saved to: responses/register_2024-01-15_143022.json

# Login with existing user
php artisan api:test login --email=john@example.com --password=Test123!

# Get current user (uses token from bearer.txt)
php artisan api:test user

# Update tenant info
php artisan api:test tenant:update --company="Acme Corporation"

# Admin: List all users
php artisan api:test admin:users --role=admin --token=custom-admin-token
```

### Additional Features Wanted

1. **Test Scenarios**
   - Chain multiple requests (register → verify → login → update)
   - Save and replay test scenarios
   - Compare responses against expected output

2. **Database Helpers**
   - Reset test database before tests
   - Seed with test data
   - Show SQL queries executed

3. **Email Testing**
   - Check Mailtrap/log for sent emails
   - Extract verification codes/links
   - Auto-click verification links

4. **Stripe Testing**
   - Use Stripe test tokens
   - Verify webhook handling
   - Check subscription states

### Questions to Address

1. Does Laravel have built-in tools for this kind of CLI API testing?
2. Should we use Pest PHP or stick with standard Artisan commands?
3. How to best document each endpoint (inline help, separate docs, or both)?
4. Should we generate OpenAPI/Swagger docs as well?
5. Best way to handle different environments (local, staging, production)?

### File Structure Preference

```
/cli_tests/
├── Commands/           # Artisan commands
├── Documentation/      # Endpoint docs
├── Responses/         # Saved responses
├── Scenarios/         # Test scenarios
├── Scripts/           # Bash/curl alternatives
├── bearer.txt         # Current auth token
└── README.md          # Usage guide
```

Please help me build this CLI testing system with a focus on:
- Practical, easy-to-use commands
- Clear output and error messages
- Comprehensive documentation
- Database verification steps
- Token management automation

The goal is to make API testing as simple as running a single command and getting clear feedback about what happened.
