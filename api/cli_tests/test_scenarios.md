# CLI API Testing - Example Scenarios

This file contains example test scenarios showing how to use the CLI commands to test common workflows.

## 🚀 Quick Start - Complete Registration Flow

```bash
# 1. Register a new user with random data
php artisan api:test:register --random

# 2. Check your email for verification code, then verify
php artisan api:test:verify-email --email=test@example.com --code=123456

# 3. Complete your profile
php artisan api:test:profile:complete --company-name="Acme Corp" --phone="+1234567890"

# 4. Check user details
php artisan api:test:user
```

## 💳 Subscription Management Flow

```bash
# 1. View available plans
php artisan api:test:plans

# 2. Check current billing status
php artisan api:test:billing:overview

# 3. Add payment method (test mode)
php artisan api:test:payment-method add --payment-method-id=tok_visa

# 4. Upgrade to Professional plan
php artisan api:test:subscription update --plan-id=2

# 5. View updated billing
php artisan api:test:billing:overview
```

## 🔐 Password Reset Flow

```bash
# 1. Request password reset
php artisan api:test:forgot-password --email=john@example.com

# 2. Check email and validate token first
php artisan api:test:reset-password --email=john@example.com --token=abc123... --validate-only

# 3. Reset password
php artisan api:test:reset-password --email=john@example.com --token=abc123... --password=NewSecurePass123!
```

## 📱 OTP Login Flow

```bash
# 1. Send OTP code
php artisan api:test:otp:send --email=user@example.com

# 2. Verify OTP to login
php artisan api:test:otp:verify --email=user@example.com --otp=123456
```

## 🏢 Tenant Management

```bash
# 1. View current tenant
php artisan api:test:my-tenant

# 2. Update tenant information
php artisan api:test:my-tenant --update --company="Acme Corporation" --custom-domain="acme.com"
```

## 👨‍💼 Admin Operations

```bash
# 1. Login as admin
php artisan api:test:login --email=admin@yoursaas.com --password=AdminPass123! --save-as=admin

# 2. View dashboard
php artisan api:test:admin:dashboard --admin

# 3. List all users
php artisan api:test:admin:users list --page=1 --per-page=20

# 4. Search for specific user
php artisan api:test:admin:users list --search=john

# 5. View tenant details
php artisan api:test:admin:tenants show 123e4567-e89b-12d3-a456-426614174000

# 6. Suspend a tenant
php artisan api:test:admin:tenants suspend 123e4567-e89b-12d3-a456-426614174000
```

## 🧪 Testing Different Environments

```bash
# Test on staging
php artisan api:test:register --env=staging --email=staging@test.com

# Test on production (be careful!)
php artisan api:test:user --env=production
```

## 📝 Saving and Analyzing Responses

```bash
# Save all responses for analysis
php artisan api:test:register --random --save-response
php artisan api:test:user --save-response
php artisan api:test:billing:overview --save-response

# Responses saved in: cli_tests/Responses/
```

## 🔄 Complete Customer Journey

```bash
# Step 1: Registration
php artisan api:test:register \
  --name="Jane Smith" \
  --email="jane@techcorp.com" \
  --password="Secure123!" \
  --company="Tech Corp" \
  --subdomain="techcorp"

# Step 2: Verify email (check email first)
php artisan api:test:verify-email \
  --email="jane@techcorp.com" \
  --code=654321

# Step 3: Complete profile
php artisan api:test:profile:complete \
  --company-name="Tech Corp Inc." \
  --phone="+1-555-0123" \
  --timezone="America/New_York" \
  --marketing-consent=true

# Step 4: Add payment method
php artisan api:test:payment-method add \
  --payment-method-id=tok_visa \
  --set-default

# Step 5: Subscribe to a plan
php artisan api:test:subscription update \
  --plan-id=3 \
  --promo-code=LAUNCH20

# Step 6: Verify subscription
php artisan api:test:billing:overview
```

## 🛠️ Utility Commands

```bash
# Clear all tokens
php artisan api:test:clear-tokens --all

# Test any endpoint directly
php artisan api:test v1/custom-endpoint --method=GET --admin

# Switch between users
php artisan api:test:login --email=user1@example.com --save-as=user1
php artisan api:test:login --email=user2@example.com --save-as=user2
php artisan api:test:user --token=user1  # Use user1's token
php artisan api:test:user --token=user2  # Use user2's token
```

## 🔍 Interactive Mode Examples

Many commands support interactive mode when run without parameters:

```bash
# Interactive registration
php artisan api:test:register

# Interactive subscription management
php artisan api:test:subscription

# Interactive admin tenant management
php artisan api:test:admin:tenants
```

## 📊 Testing Error Scenarios

```bash
# Test invalid credentials
php artisan api:test:login --email=wrong@email.com --password=wrongpass

# Test expired token
php artisan api:test:reset-password --email=test@example.com --token=expired_token

# Test rate limiting
for i in {1..10}; do php artisan api:test:otp:send --email=test@example.com; done
```

## Tips

1. **Token Management**: Tokens are automatically saved and loaded. Use `--admin` flag for admin endpoints.
2. **Response Saving**: Use `--save-response` to keep a record of all API responses.
3. **Environment Switching**: Use `--env=staging` or `--env=production` to test different environments.
4. **Interactive Mode**: Run commands without parameters for guided input.
5. **Database Verification**: Commands show what database changes occurred after each operation.
