# Quick Copy-Paste Prompt for New Conversation

Copy and paste this into a new conversation:

---

I need to create a CLI-based API testing system for my Laravel 12 SaaS application located at `/home/test/saas/saas-central`. 

**Requirements:**
1. Run individual API endpoint tests via CLI commands
2. Store bearer tokens in `bearer.txt` after login/register
3. Auto-include tokens for authenticated requests
4. Show formatted responses and database changes
5. Document each endpoint with payloads and expected responses

**Example usage I want:**
```bash
# Register
php artisan api:test register --email=test@example.com --password=Test123! --company="Acme" --subdomain=acme

# Login (saves token to bearer.txt)
php artisan api:test login --email=test@example.com --password=Test123!

# Use saved token automatically
php artisan api:test user
php artisan api:test my-tenant
```

**Location:** `/home/test/saas/saas-central/cli_tests`

**Endpoints to cover:**
- Auth: register, login, logout, user, profile/complete
- Email: verify, resend
- Password: forgot, reset
- OTP: send, verify
- Tenant: my-tenant (GET/PUT)
- Billing: overview, subscription
- Plans: list, show
- Admin: dashboard, users, tenants

Each test should show:
- HTTP status
- Pretty JSON response
- Database changes (what was inserted/updated)
- Save full response to file

Questions:
1. Should I use Artisan commands or bash scripts with curl?
2. Does Laravel have extensions for this (like Swagger CLI)?
3. Best way to handle email verification testing?
4. How to structure the test files and documentation?

Please help me build this practical CLI testing tool that makes API testing as simple as running one command.
