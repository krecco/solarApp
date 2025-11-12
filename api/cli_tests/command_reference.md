# CLI API Testing Commands - Quick Reference

## 🔑 Authentication Commands

| Command | Description | Example |
|---------|-------------|---------|
| `api:test:register` | Register new user account | `php artisan api:test:register --random` |
| `api:test:login` | Login with email/password | `php artisan api:test:login --email=user@example.com --password=Pass123!` |
| `api:test:logout` | Logout current user | `php artisan api:test:logout --clear-token` |
| `api:test:user` | Get current user info | `php artisan api:test:user` |
| `api:test:profile:complete` | Complete user profile (Step 2) | `php artisan api:test:profile:complete --company-name="Acme Corp"` |

## ✉️ Email Verification Commands

| Command | Description | Example |
|---------|-------------|---------|
| `api:test:verify-email` | Verify email with code/token | `php artisan api:test:verify-email --email=user@example.com --code=123456` |
| `api:test:resend-verification` | Resend verification email | `php artisan api:test:resend-verification --email=user@example.com` |

## 🔐 Password Reset Commands

| Command | Description | Example |
|---------|-------------|---------|
| `api:test:forgot-password` | Request password reset | `php artisan api:test:forgot-password --email=user@example.com` |
| `api:test:reset-password` | Reset password with token | `php artisan api:test:reset-password --email=user@example.com --token=abc123 --password=NewPass123!` |

## 📱 OTP Authentication Commands

| Command | Description | Example |
|---------|-------------|---------|
| `api:test:otp:send` | Send OTP code | `php artisan api:test:otp:send --email=user@example.com` |
| `api:test:otp:verify` | Verify OTP code | `php artisan api:test:otp:verify --email=user@example.com --otp=123456` |
| `api:test:otp:resend` | Resend OTP code | `php artisan api:test:otp:resend --email=user@example.com` |

## 🏢 Tenant Management Commands

| Command | Description | Example |
|---------|-------------|---------|
| `api:test:my-tenant` | View/update own tenant | `php artisan api:test:my-tenant --update --company="New Name"` |

## 💳 Billing & Subscription Commands

| Command | Description | Example |
|---------|-------------|---------|
| `api:test:billing:overview` | View billing overview | `php artisan api:test:billing:overview` |
| `api:test:subscription` | Manage subscription | `php artisan api:test:subscription update --plan-id=2` |
| `api:test:payment-method` | Manage payment methods | `php artisan api:test:payment-method add --payment-method-id=tok_visa` |

## 📋 Plan Commands

| Command | Description | Example |
|---------|-------------|---------|
| `api:test:plans` | List/view plans | `php artisan api:test:plans --id=2` |

## 👨‍💼 Admin Commands (Requires Admin Role)

| Command | Description | Example |
|---------|-------------|---------|
| `api:test:admin:dashboard` | View admin dashboard | `php artisan api:test:admin:dashboard --format=table` |
| `api:test:admin:users` | Manage users | `php artisan api:test:admin:users list --search=john` |
| `api:test:admin:tenants` | Manage tenants | `php artisan api:test:admin:tenants show UUID` |

## 🛠️ Utility Commands

| Command | Description | Example |
|---------|-------------|---------|
| `api:test` | Test any endpoint | `php artisan api:test v1/custom-endpoint --method=GET` |
| `api:test:clear-tokens` | Clear saved tokens | `php artisan api:test:clear-tokens --all` |

## Common Options

Most commands support these options:

- `--admin` : Use admin token instead of regular token
- `--env=ENV` : Set environment (local/staging/production)
- `--save-response` : Save response to timestamped file
- `--token=FILE` : Use specific token file
- `--help` : Show command help and examples

## Interactive Mode

Run any command without required parameters to enter interactive mode:

```bash
php artisan api:test:register     # Interactive registration
php artisan api:test:subscription  # Interactive subscription management
```

## Token Files

- `cli_tests/bearer.txt` : Default token file
- `cli_tests/admin-bearer.txt` : Admin token file
- Custom files with `--save-as=name` option

## Response Files

All saved responses are stored in: `cli_tests/Responses/`

Format: `{command}_{timestamp}.json`
