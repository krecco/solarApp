# SOLAR APP - COMPREHENSIVE CODEBASE ANALYSIS

## EXECUTIVE SUMMARY

This is a comprehensive Laravel + Vue.js solar energy investment platform with dual frontends for admin management and customer portal functionality. The application manages solar plants (power generation facilities), investor investments, repayments, documents, and communications.

**Technology Stack:**
- Backend: Laravel 11 (API-based, no traditional views)
- Frontend Admin: Vue 2 with Vuetify
- Frontend Customer: Vue 2 with Vuetify  
- Authentication: Laravel Sanctum (API tokens)
- Authorization: Spatie Permission package
- Database: MySQL/MariaDB with 30+ migrations
- State Management: Pinia (Vue 3 style store)

---

## 1. APPLICATION STRUCTURE

### 1.1 Directory Layout

```
/solarApp/
├── api/                           # Laravel Backend (Main API)
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/Api/   # 27 API Controllers
│   │   │   ├── Middleware/        # Auth, SetLocale
│   │   │   └── Requests/          # Form requests
│   │   ├── Models/                # 24 Eloquent Models
│   │   ├── Services/              # Business logic services
│   │   ├── Mail/                  # Email notifications
│   │   └── Notifications/         # Notification classes
│   ├── database/
│   │   ├── migrations/            # 30 migrations
│   │   ├── seeders/               # 6 seeders
│   │   └── factories/             # User, Plan factories
│   ├── routes/
│   │   ├── api.php               # Main API routes (v1)
│   │   └── web.php               # Web routes (minimal)
│   ├── config/
│   │   ├── roles.php             # Role configuration
│   │   ├── auth.php              # Auth setup
│   │   └── ...                   # Other configs
│   └── tests/
│       └── cli_tests/            # CLI integration tests
│
├── Frontend/                      # Admin Dashboard
│   ├── src/
│   │   ├── views/
│   │   │   ├── dashboard/        # Dashboard views
│   │   │   ├── power-plant/      # Solar plant management
│   │   │   ├── investment/       # Investment management
│   │   │   ├── user/             # User management
│   │   │   ├── invoice/          # Invoice views
│   │   │   ├── admin-settings/   # Admin settings
│   │   │   ├── activity/         # Activity logs
│   │   │   └── ...
│   │   ├── store/                # Pinia stores
│   │   ├── router/               # Vue Router config
│   │   ├── layouts/              # Layout components
│   │   └── components/           # Shared components
│   └── package.json
│
├── FrontendUser/                 # Customer Portal
│   ├── src/
│   │   ├── views/
│   │   │   ├── user/             # User profile & settings
│   │   │   ├── power-plant/      # Customer's plants
│   │   │   ├── investment/       # Customer's investments
│   │   │   └── ...
│   │   ├── store/                # Pinia stores
│   │   ├── router/               # Vue Router config
│   │   └── ...
│   └── package.json
│
└── Backend/                       # Java/Maven backend (Legacy)
    ├── src/
    └── pom.xml
```

### 1.2 Key Framework Versions

**Backend:**
- Laravel Framework 11.x
- PHP 8.2+
- Laravel Sanctum (API authentication)
- Spatie Laravel Permission (role-based access)

**Frontend:**
- Vue 2.x
- Vuetify 2.x
- Vue Router 3.x
- Pinia (state management)
- Axios (HTTP client)

---

## 2. USER ROLES AND AUTHENTICATION

### 2.1 Available Roles

Four main roles defined in `config/roles.php`:

| Role | Default | Purpose | Access Level |
|------|---------|---------|--------------|
| **customer** | ✓ Yes | End users (investors/plant owners) | Limited to own data |
| **user** | | Regular user (mostly unused) | Limited access |
| **manager** | ✗ | Staff managing solar plants | Medium access |
| **admin** | ✗ | System administrators | Full access |

### 2.2 Authentication System

**Authentication Flow:**
1. User registers via `POST /v1/register`
   - Validates email uniqueness
   - Hashes password
   - Assigns default role (customer)
   - Sends verification email
   - Creates auth token

2. User logs in via `POST /v1/login`
   - Validates credentials
   - Generates Sanctum API token
   - Returns user object with roles

3. Token Management:
   - Stored in `personal_access_tokens` table
   - Sent via `Authorization: Bearer {token}` header
   - Revoked on logout

**Guards Used:**
- `sanctum` - For API requests
- `web` - For traditional web sessions (minimal usage)

### 2.3 Authorization System

**Implementation:** Spatie Permission Package
- Roles stored in `roles` table
- Permissions stored in `permissions` table
- Simple role-based checks (not fine-grained permissions)

**Middleware Protection:**
```php
Route::middleware('auth:sanctum')->group(...)           // Authentication required
Route::middleware('role:admin')->group(...)             // Admin only
Route::middleware('role:admin|manager')->group(...)     // Admin or Manager
```

### 2.4 Middleware & Guards

**Key Middleware:**
- `Authenticate.php` - Enforces API authentication (Sanctum)
- `SetLocale.php` - Sets application locale based on user preferences/headers
- `VerifyCsrfToken` - CSRF protection for web routes

**Verification Methods:**
- Email verification via `EmailVerificationController`
- OTP verification via `OtpAuthController`
- Document verification via `AdminDocumentController` (admin-only)

---

## 3. WORKFLOWS BY USER ROLE

### 3.1 CUSTOMER WORKFLOW

**Primary Access:** FrontendUser application

**Key Activities:**
1. **Registration & Profile Setup**
   - Register account
   - Verify email
   - Complete customer profile
   - Upload verification documents
   - Set address information

2. **Solar Plant Management** (as plant owner)
   - Create/edit solar plant project
   - Upload plant documentation
   - Request plant verification
   - View plant status and details
   - Download plant contracts

3. **Investment Activities** (as investor)
   - Browse available solar plants
   - Create investment in solar plant
   - Upload investment documents
   - Wait for admin verification
   - View investment details
   - Track repayment schedule

4. **Repayment Tracking**
   - View scheduled repayments
   - Track payment history
   - Receive payment reminders
   - View invoices

5. **Communication**
   - Start conversations with admin/support
   - Receive notifications
   - Access help/support system

6. **Document Management**
   - Upload verification documents
   - View document status
   - Download contracts and schedules
   - Access user files container

**Frontend Routes:**
- `/` - User dashboard/profile
- `/user-detail-edit` - Edit customer data
- `/user-detail-files` - Upload documents
- `/power-plant-detail` - View/edit plant details
- `/investment-detail` - View investment details
- `/chat-lite` - Messaging system

### 3.2 MANAGER WORKFLOW

**Primary Access:** Frontend application (Admin dashboard)

**Key Capabilities:**
1. **Plant Management**
   - View assigned solar plants
   - Update plant details and status
   - Manage plant extras (add-ons)
   - Track plant repayments
   - Generate repayment reports

2. **Investment Management**
   - View investments in assigned plants
   - Verify customer investments
   - Update investment status
   - Generate investment reports
   - Manage repayment schedules

3. **Document Verification**
   - Review pending customer documents
   - Approve/reject documents
   - View verification statistics

4. **Repayment Management**
   - Record plant repayments
   - Track plant repayment data
   - Send payment reminders
   - Generate repayment logs

5. **Reporting**
   - Basic analytics for assigned plants
   - Investment tracking
   - Repayment status reports

**Features NOT Available to Managers:**
- Cannot create users
- Cannot delete records
- Cannot manage system settings
- Cannot manage campaigns
- Cannot manage web content

### 3.3 ADMIN WORKFLOW

**Primary Access:** Frontend application (Admin dashboard)

**Key Capabilities:**
1. **System Administration**
   - Full access to all data
   - Create/edit/delete users
   - Manage user roles
   - Send welcome emails
   - Update user avatars

2. **User Management**
   - Search users with filters
   - Create new users
   - Update user information
   - Delete users
   - View detailed user profiles with tabs:
     - Account info
     - Documents
     - Investments
     - Power plants
     - Billing/SEPA
     - Activity timeline

3. **Solar Plant Management**
   - Full CRUD operations
   - Create new plants
   - Assign managers to plants
   - Update all plant details
   - Manage plant status (draft → operational → completed)
   - Manage plant extras/add-ons

4. **Investment Management**
   - Full investment control
   - Create investments
   - Verify/approve investments
   - Update investment status
   - Manage repayment schedules
   - Regenerate repayments

5. **Document Management**
   - View all documents
   - Verify documents
   - Reject documents with feedback
   - View verification statistics
   - Track document requirements

6. **Financial Management**
   - Create/send invoices
   - Create repayment reminders
   - Track all repayments
   - Record plant repayments
   - View financial reports
   - Export investment data

7. **Content & Settings Management**
   - Manage campaigns (referral, seasonal, promotional)
   - Create/edit web info (news, announcements)
   - Publish/unpublish content
   - Manage system settings (email, features, etc.)
   - Configure settings by group

8. **Advanced Reporting**
   - Dashboard with key statistics
   - Multi-dimensional analytics
   - Trend analysis
   - Comparative analysis (YoY, MoM, QoQ)
   - Cohort analysis
   - Financial forecasting
   - Export advanced reports

9. **Activity Logging & Audit**
   - View all activity logs
   - Filter activity by user, model, action
   - View timeline of changes
   - Export activity logs

10. **Languages & Localization**
    - Create/manage languages
    - Set default language
    - Manage translations

**Frontend Routes (Admin):**
- `/` - Home/Dashboard
- `/power-plant-list` - List all solar plants
- `/power-plant-detail` - Plant management
- `/investment-list` - Investment management
- `/projects` - Project showcase
- `/user/*` - User management pages
- `/admin-settings/*` - Admin configuration

### 3.4 CUSTOMER (Investor only) vs CUSTOMER (Plant Owner only) vs CUSTOMER (both)

**customer_type in CustomerProfile:**
- `investor` - Only invests in other's plants
- `plant_owner` - Only owns plants
- `both` - Can be both investor and owner

**Differentiated Access:**
- Plant owner can create/manage solar plants
- Investor can create investments in plants
- Users with `both` can do either

---

## 4. DATABASE SCHEMA DETAILS

### 4.1 Users & Authentication Tables

#### **users table**
Core user account information
```sql
id (bigint, primary)
name (string)
email (string, unique)
email_verified_at (timestamp, nullable)
password (string, hashed)
remember_token (string)
avatar_url (string, nullable)
last_login_at (timestamp, nullable)
preferences (json) - UI language, document language, email language
status (string)
created_at, updated_at
deleted_at (soft deletes)
```

**Associated Models:**
- CustomerProfile (one-to-one) - Extended customer information
- User has many roles (via Spatie)
- User has many notifications
- User has many investments
- User has many solar plants
- User has many conversations

#### **customer_profiles table**
Extended customer information (separated from users)
```sql
id (bigint, primary)
user_id (bigint, foreign key)
customer_no (string, nullable) - Customer number
customer_type (enum) - 'investor', 'plant_owner', 'both'
is_business (boolean)
title_prefix (string) - Dr., Prof., etc.
title_suffix (string) - MBA, PhD, etc.
phone_nr (string, nullable)
gender (string, nullable)
user_files_verified (boolean)
user_verified_at (timestamp, nullable)
document_extra_text_block_a (text, nullable)
document_extra_text_block_b (text, nullable)
created_at, updated_at
```

**Key Feature:** Customer profile data separated from users table for cleaner schema and easier migration

#### **personal_access_tokens table**
Sanctum API tokens for authentication
```sql
id (bigint, primary)
tokenable_id (bigint)
tokenable_type (string) - typically 'App\\Models\\User'
name (string)
token (string, hashed, unique)
abilities (json)
last_used_at (timestamp, nullable)
created_at, updated_at
```

#### **roles table** (Spatie)
Role definitions
```sql
id (bigint, primary)
name (string) - 'admin', 'manager', 'customer', 'user'
guard_name (string) - 'web', 'sanctum'
created_at, updated_at
```

#### **permissions table** (Spatie)
Permission definitions (mostly unused in this app)
```sql
id (bigint, primary)
name (string)
guard_name (string)
created_at, updated_at
```

#### **role_has_permissions table** (Spatie)
Links roles to permissions
```sql
permission_id (primary, foreign)
role_id (primary, foreign)
```

#### **model_has_roles table** (Spatie)
Links users to roles
```sql
role_id (primary, foreign)
model_id (primary, foreign)
model_type (string) - 'App\\Models\\User'
```

### 4.2 Solar Plant Tables

#### **solar_plants table**
Main solar power facility information
```sql
id (uuid, primary)
title (string)
description (text, nullable)
location (string, nullable)
address (string, nullable)
postal_code (string, nullable)
city (string, nullable)
country (string, default: 'DE')

# Technical specifications
nominal_power (decimal:2) - kWp
annual_production (decimal:2, nullable) - kWh
consumption (decimal:2, nullable) - kWh
peak_power (decimal:2, nullable) - kWp

# Financial data
total_cost (decimal:2)
investment_needed (decimal:2, nullable)
kwh_price (decimal:4, nullable) - Price per kWh
contract_duration_years (integer, default: 20)
interest_rate (decimal:2, nullable)

# Forecasts & calculations
monthly_forecast (json, nullable) - Monthly production data
repayment_calculation (json, nullable) - Calculated repayment schedule

# Status & lifecycle
status (string) - 'draft', 'active', 'funded', 'operational', 'completed', 'cancelled'
start_date (date, nullable)
operational_date (date, nullable)
end_date (date, nullable)

# Relationships & metadata
user_id (bigint, foreign) - Plant owner/customer
manager_id (bigint, foreign, nullable) - Assigned manager
file_container_id (uuid, foreign, nullable)
contracts_signed (boolean)
contract_signed_at (timestamp, nullable)
rs (integer) - Record status: 0=active, 99=deleted

created_at, updated_at
deleted_at (soft deletes)
```

**Indexes:**
- `[user_id, status]` - Filter by owner and status
- `[manager_id, status]` - Filter by manager and status
- `[status, created_at]` - Filter by status and date

#### **solar_plant_extras table**
Many-to-many relationship between plants and available extras/add-ons
```sql
id (uuid, primary)
solar_plant_id (uuid, foreign)
extra_id (uuid, foreign)
price (decimal:2, nullable) - Override default price
quantity (integer, default: 1)
created_at, updated_at
```

#### **solar_plant_property_owners table**
Alternative property owner (different from investor)
```sql
id (uuid, primary)
solar_plant_id (uuid, foreign)
first_name (string)
last_name (string)
email (string, nullable)
phone (string, nullable)
notes (text, nullable)
created_at, updated_at
```

#### **solar_plant_repayment_data table**
Repayment schedule for plant financing
```sql
id (uuid, primary)
solar_plant_id (uuid, foreign)
amount (decimal:2)
due_date (date)
repayment_type (string) - 'principal', 'interest', 'combined'
status (string) - 'pending', 'paid', 'overdue', 'cancelled'
created_at, updated_at
```

#### **solar_plant_repayment_logs table**
Actual repayment records for plants
```sql
id (uuid, primary)
solar_plant_id (uuid, foreign)
solar_plant_repayment_data_id (uuid, foreign, nullable)
amount (decimal:2)
payment_date (date)
payment_method (string, nullable) - 'bank_transfer', 'sepa', etc.
reference_number (string, nullable)
notes (text, nullable)
created_at, updated_at
```

#### **solar_plant_repayment_log_reminders table**
Reminder tracking for plant repayments
```sql
id (uuid, primary)
solar_plant_id (uuid, foreign)
solar_plant_repayment_data_id (uuid, foreign, nullable)
reminder_date (date)
sent (boolean)
sent_at (timestamp, nullable)
created_at, updated_at
```

**Indexes:**
- `[sent, reminder_date]` - Find unsent reminders

### 4.3 Investment Tables

#### **investments table**
Individual investor investments in solar plants
```sql
id (uuid, primary)
user_id (bigint, foreign) - Investor
solar_plant_id (uuid, foreign) - Plant being invested in

# Investment details
amount (decimal:2) - Investment amount
duration_months (integer)
interest_rate (decimal:2)
repayment_interval (string) - 'monthly', 'quarterly', 'annually'
document_language (string, nullable) - Language preference for documents

# Status
status (string) - 'pending', 'verified', 'active', 'completed', 'cancelled'
contract_status (string, nullable)
verified (boolean)
verified_at (timestamp, nullable)
verified_by (bigint, foreign, nullable) - Admin who verified

# Documents
file_container_id (uuid, foreign, nullable)

# Dates
start_date (date, nullable)
end_date (date, nullable)

# Financial tracking (cached)
total_repayment (decimal:2, nullable) - Total amount to repay
total_interest (decimal:2, nullable) - Total interest
paid_amount (decimal:2, default: 0) - Amount paid so far

notes (text, nullable)
rs (integer) - Record status

created_at, updated_at
deleted_at (soft deletes)
```

**Indexes:**
- `[user_id, status]` - Find investor's investments
- `[solar_plant_id, status]` - Find investments in plant
- `[verified, status]` - Find verified/unverified investments

#### **investment_repayments table**
Individual repayment installments for investments
```sql
id (uuid, primary)
investment_id (uuid, foreign)

# Payment details
amount (decimal:2)
principal (decimal:2)
interest (decimal:2)

# Dates
due_date (date)
paid_date (date, nullable)

# Status
status (string) - 'pending', 'paid', 'overdue', 'cancelled'
payment_method (string, nullable) - 'bank_transfer', 'sepa'
reference_number (string, nullable)

notes (text, nullable)
created_at, updated_at
```

**Indexes:**
- `[investment_id, due_date]` - Find repayments for investment
- `[status, due_date]` - Find pending/overdue repayments
- `paid_date` - Find paid repayments

### 4.4 User Profile Tables

#### **user_addresses table**
Multiple addresses per user (billing, shipping, property)
```sql
id (uuid, primary)
user_id (bigint, foreign)
type (string, default: 'billing') - 'billing', 'shipping', 'property'
street (string)
street_number (string, nullable)
city (string)
postal_code (string)
country (string, default: 'DE')
is_primary (boolean)
created_at, updated_at
```

#### **user_sepa_permissions table**
SEPA direct debit mandates
```sql
id (uuid, primary)
user_id (bigint, foreign)
iban (string)
bic (string, nullable)
account_holder (string)
mandate_reference (string, unique)
mandate_date (date)
is_active (boolean)
created_at, updated_at
```

### 4.5 Document & File Tables

#### **file_containers table**
Container/folder structure for organizing files
```sql
id (uuid, primary)
name (string)
type (string) - 'contracts', 'verification_docs', 'reports', 'plant_docs', 'investment_docs'
description (text, nullable)
created_at, updated_at

# Polymorphic relationship
containable_type (string) - User, SolarPlant, Investment, etc.
containable_id (string)
```

#### **files table**
Individual uploaded files
```sql
id (uuid, primary)
file_container_id (uuid, foreign)

# File metadata
name (string) - Display name
original_name (string) - Original uploaded filename
path (string) - Storage path
mime_type (string)
size (bigint) - File size in bytes
extension (string)
document_type (string, nullable) - Type of document for verification

# Verification
is_verified (boolean)
verified_at (timestamp, nullable)
verified_by_id (bigint, foreign, nullable)

# Polymorphic uploader
uploaded_by_type (string)
uploaded_by_id (string)

created_at, updated_at
```

**Indexes:**
- `[file_container_id, created_at]` - Find files in container
- `is_verified` - Find verified/unverified files

### 4.6 Communication Tables

#### **conversations table**
Messaging/chat conversations
```sql
id (uuid, primary)
subject (string, nullable)
status (string, default: 'active') - 'active', 'archived', 'closed'
created_by_id (bigint, foreign, nullable)
last_message_at (timestamp, nullable)
created_at, updated_at
deleted_at (soft deletes)
```

#### **conversation_participants table**
Many-to-many relationship between users and conversations
```sql
id (uuid, primary)
conversation_id (uuid, foreign)
user_id (bigint, foreign)
last_read_at (timestamp, nullable)
unread_count (integer, default: 0)
created_at, updated_at

unique constraint: [conversation_id, user_id]
```

#### **messages table**
Individual messages in conversations
```sql
id (uuid, primary)
conversation_id (uuid, foreign)
sender_id (bigint, foreign)
body (text)
type (string, default: 'text') - 'text', 'system', 'file_attachment'
attachments (json, nullable) - File attachment metadata
is_read (boolean)
read_at (timestamp, nullable)
created_at, updated_at
deleted_at (soft deletes)
```

**Indexes:**
- `conversation_id` - Find messages in conversation
- `sender_id` - Find messages from sender
- `is_read` - Find unread messages
- `created_at` - Order messages

### 4.7 Financial & Business Tables

#### **invoices table**
Generated invoices
```sql
id (uuid, primary)
invoice_number (string, unique) - Format: INV-YYYYMM-0001
user_id (bigint, foreign)
investment_id (uuid, foreign, nullable)
repayment_id (uuid, foreign, nullable)

# Type & status
type (enum) - 'repayment', 'plant_billing', 'service', 'other'
status (enum) - 'draft', 'sent', 'paid', 'overdue', 'cancelled'

# Dates
invoice_date (date)
due_date (date)
paid_date (date, nullable)

# Financial details
subtotal (decimal:2)
tax_amount (decimal:2)
total_amount (decimal:2)
currency (string, default: 'EUR')
line_items (json, nullable)

# Payment information
payment_method (string, nullable)
payment_reference (string, nullable)
payment_info (json, nullable)

description (text, nullable)
notes (text, nullable)
pdf_path (string, nullable)

rs (integer)
created_at, updated_at
deleted_at (soft deletes)
```

**Indexes:**
- `[user_id, status]` - Find user's invoices
- `[invoice_date, due_date, status]` - Filtering

#### **investment_repayment_reminders table**
Reminders for investment repayments
```sql
id (uuid, primary)
investment_id (uuid, foreign)
repayment_id (uuid, foreign, nullable)
reminder_date (date)
sent (boolean)
sent_at (timestamp, nullable)
created_at, updated_at
```

#### **campaigns table**
Promotional campaigns & bonus offers
```sql
id (uuid, primary)
name (string)
description (text, nullable)
type (enum) - 'referral', 'seasonal', 'bonus', 'promotional'
code (string, unique, nullable) - Campaign code for activation
start_date (date)
end_date (date, nullable)
bonus_amount (decimal:2)
min_investment_amount (decimal:2, nullable)
max_uses (integer, nullable)
current_uses (integer, default: 0)
is_active (boolean)
conditions (json, nullable)
terms (text, nullable)
rs (integer)
created_at, updated_at
deleted_at (soft deletes)
```

### 4.8 Content & Settings Tables

#### **web_infos table**
Website content (news, announcements, pages)
```sql
id (uuid, primary)
title (string)
slug (string, unique) - URL-friendly slug
excerpt (text, nullable)
content (text) - Full content (markdown or HTML)
type (enum) - 'news', 'info', 'page', 'announcement'
is_published (boolean)
is_featured (boolean)
published_at (timestamp, nullable)
author_id (bigint, foreign)
featured_image (string, nullable)
meta (json, nullable) - SEO metadata
tags (json, nullable)
category (string, nullable)
view_count (integer, default: 0)
rs (integer)
created_at, updated_at
deleted_at (soft deletes)
```

#### **languages table**
System languages
```sql
id (bigint, primary)
code (string, unique) - 'en', 'de', etc.
name (string) - 'English', 'Deutsch'
is_default (boolean)
is_active (boolean)
created_at, updated_at
```

#### **settings table**
System configuration settings
```sql
id (uuid, primary)
group (string) - 'email', 'app', 'features', etc.
key (string) - Setting key
value (json, nullable) - Setting value
type (string, nullable)
description (text, nullable)
is_public (boolean)
created_at, updated_at
deleted_at (soft deletes)

unique constraint: [group, key]
```

### 4.9 Activity & Audit Tables

#### **activity_logs table**
Audit trail of all system changes
```sql
id (uuid, primary)
user_id (bigint, foreign, nullable)
action (string) - 'created', 'updated', 'deleted', 'verified', etc.
subject_type (string) - Model class name
subject_id (string, nullable)
old_values (json, nullable) - Before change
new_values (json, nullable) - After change
ip_address (string, nullable)
user_agent (string, nullable)
created_at, updated_at
```

**Indexes:**
- `[user_id, created_at]` - Find user's activities
- `[subject_type, subject_id]` - Find activities for model
- `action` - Filter by action type

### 4.10 Cache Tables

#### **cache table**
Laravel cache storage

#### **jobs table**
Laravel queue jobs

#### **password_reset_tokens table**
Password reset tokens with expiration

#### **sessions table**
Session data

#### **notifications table**
User notifications
```sql
id (uuid, primary)
notifiable_type (string) - Usually 'App\\Models\\User'
notifiable_id (string)
type (string) - Notification class name
data (json)
read_at (timestamp, nullable)
created_at
```

---

## 5. KEY MODEL RELATIONSHIPS

### Relationship Diagram

```
User (1)
├── (1:N) CustomerProfile
├── (1:N) Notifications
├── (1:N) SolarPlants (as owner via user_id)
├── (1:N) SolarPlants (as manager via manager_id)
├── (1:N) Investments
├── (1:N) UserAddresses
├── (1:N) UserSepaPermissions
├── (M:M) Conversations
├── (1:N) Messages (as sender)
├── (1:N) Invoices
└── (1:N) Roles (via model_has_roles)

SolarPlant (1)
├── (1:N) Investments
├── (1:N) SolarPlantExtras
├── (1:1) PropertyOwner
├── (1:N) RepaymentData
├── (1:N) RepaymentLogs
└── (1:1) FileContainer

Investment (1)
├── (1:N) InvestmentRepayments
├── (1:1) FileContainer
└── (1:1) Invoice (optional)

FileContainer (1)
└── (1:N) Files

Conversation (1)
├── (M:M) Users (participants)
└── (1:N) Messages

Campaign (1)
└── (M:N) Investments (via application)
```

---

## 6. FRONTEND COMPONENTS & VIEWS

### 6.1 Admin Frontend (Frontend/)

**Key View Directories:**
- `dashboard/` - Home, statistics, overview
- `power-plant/` - Solar plant CRUD and details
- `power-plant-repayment/` - Repayment management
- `investment/` - Investment list and details
- `user/` - User management and profiles
- `invoice/` - Invoice and reminder management
- `admin-settings/` - Campaign, extra, and system settings
- `activity/` - Activity logs and audit trail
- `project/` - Project showcase pages
- `webinfo/` - Web content management
- `chat/` - Messaging system
- `auth/` - Login and authentication
- `error/` - Error pages

**Key Components:**
- Layout components (headers, sidebars, footers)
- Data tables with filtering/sorting
- Forms for creating/editing resources
- Charts and statistics displays
- Document management components

**Pinia Stores:**
- `app/navbar.js` - Navigation state
- `app-config/index.js` - App configuration
- `vertical-menu/index.js` - Menu state
- `index.js` - Root store configuration

### 6.2 Customer Frontend (FrontendUser/)

**Key View Directories:**
- `user/` - Customer profile, account settings
  - `UsersView.vue` - Profile dashboard
  - `UsersDetailEdit.vue` - Edit customer info
  - `UsersDetailFiles.vue` - Upload documents
- `power-plant/` - Customer's solar plants
  - `Display.vue` - Plant details
  - `UploadSingleFile.vue` - File uploads
  - `PowerBillUpload.vue` - Electricity bill upload
  - `RoofImagesUpload.vue` - Roof images
  - `DownloadOfferAndCalculation.vue` - Offer download
  - `Contracts.vue` - Contract management
- `investment/` - Customer's investments
  - `Display.vue` - Investment details
- `chat/` - Messaging
  - `ChatLite.vue` - Messaging interface
- `auth/` - Login

**Smaller Feature Set:** Customer portal focused on viewing own data, uploading documents, and communicating with support

### 6.3 Key Features Across Frontends

**Common:**
- Authentication (login, register)
- User profile management
- Language/localization support
- Document upload and verification
- Messaging/chat system
- Responsive design with Vuetify

**Admin-Only:**
- User management (CRUD)
- Dashboard with statistics
- Advanced filtering and search
- Export functionality
- Settings management
- Activity logs
- Analytics and reporting

**Customer-Only:**
- Profile customization
- Document upload for verification
- Investment creation
- Repayment tracking

---

## 7. API ENDPOINTS OVERVIEW

### 7.1 Authentication Routes

```
POST   /v1/register                    - User registration
POST   /v1/login                       - User login
POST   /v1/logout                      - Logout (auth required)
GET    /v1/user                        - Get current user
```

### 7.2 OTP & Password Reset

```
POST   /v1/otp/send                    - Send OTP code
POST   /v1/otp/verify                  - Verify OTP
POST   /v1/otp/resend                  - Resend OTP
POST   /v1/password/forgot             - Request password reset
POST   /v1/password/reset              - Reset password
POST   /v1/password/validate-token     - Validate reset token
```

### 7.3 Email Verification

```
POST   /v1/email/verify                - Public email verification
POST   /v1/email/resend                - Public resend
POST   /v1/email/verify-authenticated  - Authenticated resend
POST   /v1/email/resend-authenticated  - Authenticated re-verify
```

### 7.4 User Profile

```
GET    /v1/profile                     - Get user profile
PUT    /v1/profile                     - Update profile
PUT    /v1/profile/password            - Change password
```

### 7.5 Documents & Verification

```
GET    /v1/documents/requirements      - Get required documents
GET    /v1/documents/summary           - Verification summary
GET    /v1/documents/types             - Available document types
GET    /v1/documents                   - List user documents
POST   /v1/documents/upload            - Upload document
GET    /v1/documents/{file}            - Get document details
GET    /v1/documents/{file}/download   - Download document
DELETE /v1/documents/{file}            - Delete unverified document
```

### 7.6 Solar Plants (Role-based)

```
GET    /v1/solar-plants                - List plants (filtered by role)
GET    /v1/solar-plants/{id}           - Get plant details
GET    /v1/solar-plants/statistics     - Plant statistics
POST   /v1/solar-plants                - Create plant (admin/manager)
PUT    /v1/solar-plants/{id}           - Update plant (admin/manager)
DELETE /v1/solar-plants/{id}           - Delete plant (admin/manager)
POST   /v1/solar-plants/{id}/status    - Update status (admin/manager)
```

### 7.7 Investments (Role-based)

```
GET    /v1/investments                 - List investments
GET    /v1/investments/{id}            - Get investment details
POST   /v1/investments                 - Create investment
GET    /v1/investments/statistics      - Investment statistics
PUT    /v1/investments/{id}            - Update (admin/manager)
DELETE /v1/investments/{id}            - Delete (admin/manager)
POST   /v1/investments/{id}/verify     - Verify (admin/manager)
GET    /v1/investments/{id}/repayments - Get repayments
POST   /v1/investments/{id}/repayments/regenerate - Regenerate (admin)
```

### 7.8 Repayments

```
GET    /v1/repayments/statistics       - Repayment statistics
GET    /v1/repayments/overdue          - List overdue
GET    /v1/repayments/upcoming         - List upcoming
POST   /v1/repayments/{id}/mark-paid   - Mark paid (admin/manager)
```

### 7.9 Plant Repayments

```
GET    /v1/plant-repayments            - List plant repayments
GET    /v1/plant-repayments/statistics - Statistics
GET    /v1/plant-repayments/{id}       - Get details
GET    /v1/plant-repayments/plant/{id} - Get for specific plant
POST   /v1/plant-repayments/generate-report - Generate report
POST   /v1/plant-repayments/{id}/record-payment - Record payment (admin/manager)
```

### 7.10 Invoices

```
GET    /v1/invoices                    - List invoices
GET    /v1/invoices/{id}               - Get invoice
GET    /v1/invoices/statistics         - Invoice statistics
GET    /v1/invoices/overdue            - Overdue invoices
POST   /v1/invoices                    - Create (admin/manager)
PUT    /v1/invoices/{id}               - Update (admin/manager)
POST   /v1/invoices/{id}/mark-paid     - Mark as paid (admin/manager)
POST   /v1/invoices/{id}/send          - Send invoice (admin/manager)
POST   /v1/invoices/{id}/cancel        - Cancel (admin/manager)
DELETE /v1/invoices/{id}               - Delete (admin/manager)
POST   /v1/invoices/repayment/{id}/generate - Generate from repayment (admin/manager)
POST   /v1/invoices/repayment/{id}/send-reminder - Send reminder (admin/manager)
GET    /v1/invoices/repayment/{id}/reminders - Get reminders (admin/manager)
```

### 7.11 Files & Containers

```
GET    /v1/files                       - List files
POST   /v1/files/upload                - Upload file
GET    /v1/files/{id}/download         - Download file
DELETE /v1/files/{id}                  - Delete file
POST   /v1/files/{id}/verify           - Verify (admin/manager)
```

### 7.12 Messaging

```
GET    /v1/messages/conversations      - List conversations
POST   /v1/messages/conversations      - Create conversation
GET    /v1/messages/conversations/{id} - Get conversation
POST   /v1/messages/conversations/{id}/messages - Send message
POST   /v1/messages/conversations/{id}/read - Mark as read
POST   /v1/messages/conversations/{id}/archive - Archive
POST   /v1/messages/conversations/{id}/reactivate - Reactivate
GET    /v1/messages/unread-count       - Unread count
GET    /v1/messages/search-users       - Search users
```

### 7.13 Notifications

```
GET    /v1/notifications               - List notifications
GET    /v1/notifications/unread-count  - Unread count
POST   /v1/notifications/{id}/read     - Mark as read
POST   /v1/notifications/mark-all-read - Mark all read
DELETE /v1/notifications/{id}          - Delete notification
DELETE /v1/notifications/clear-read    - Clear read notifications
```

### 7.14 Reports & Analytics

```
GET    /v1/reports/dashboard           - Dashboard overview
GET    /v1/reports/investments/analytics - Investment analytics
GET    /v1/reports/repayments/analytics - Repayment analytics
GET    /v1/reports/plants/analytics    - Plant analytics
GET    /v1/reports/monthly/{year}/{month} - Monthly report
GET    /v1/reports/investments/{id}/performance - Investment performance
GET    /v1/reports/cohort-analysis     - Cohort analysis (admin/manager)
GET    /v1/reports/trend-analysis      - Trend analysis (admin/manager)
GET    /v1/reports/comparative-analysis - Comparison (admin/manager)
GET    /v1/reports/multi-dimensional   - Multi-dimensional (admin/manager)
GET    /v1/reports/forecast            - Forecasting (admin/manager)
POST   /v1/reports/advanced-export     - Export (admin/manager)
POST   /v1/reports/investments/export  - Export investments (admin/manager)
GET    /v1/reports/download/{file}     - Download export (admin/manager)
```

### 7.15 Activity Logs

```
GET    /v1/activity-logs               - List activity logs
GET    /v1/activity-logs/statistics    - Statistics
GET    /v1/activity-logs/timeline      - Timeline view
GET    /v1/activity-logs/recent        - Recent activities
GET    /v1/activity-logs/{id}          - Get single activity
GET    /v1/activity-logs/model/{type}/{id} - Activities for model
GET    /v1/activity-logs/user/{id}     - Activities by user
POST   /v1/activity-logs/export        - Export (admin)
```

### 7.16 Settings

```
GET    /v1/settings/public             - Public settings
GET    /v1/settings                    - All settings (admin)
GET    /v1/settings/{group}/{key}      - Get single setting (admin)
POST   /v1/settings                    - Create (admin)
PUT    /v1/settings/{group}/{key}      - Update (admin)
DELETE /v1/settings/{group}/{key}      - Delete (admin)
POST   /v1/settings/bulk-update        - Bulk update (admin)
POST   /v1/settings/reset              - Reset to default (admin)
```

### 7.17 Campaigns

```
GET    /v1/campaigns                   - List campaigns
GET    /v1/campaigns/active            - Active campaigns
GET    /v1/campaigns/{id}              - Get campaign
GET    /v1/campaigns/statistics        - Statistics
POST   /v1/campaigns/{id}/validate-code - Validate code
POST   /v1/campaigns/{id}/apply        - Apply campaign (auth)
POST   /v1/campaigns                   - Create (admin)
PUT    /v1/campaigns/{id}              - Update (admin)
DELETE /v1/campaigns/{id}              - Delete (admin)
```

### 7.18 Web Info (Content)

```
GET    /v1/web-info/published          - Published content
GET    /v1/web-info/featured           - Featured content
GET    /v1/web-info/categories         - Categories list
GET    /v1/web-info/slug/{slug}        - Get by slug
GET    /v1/web-info                    - List all (admin)
GET    /v1/web-info/{id}               - Get by ID (admin)
POST   /v1/web-info                    - Create (admin)
PUT    /v1/web-info/{id}               - Update (admin)
DELETE /v1/web-info/{id}               - Delete (admin)
POST   /v1/web-info/{id}/toggle-publish - Publish/unpublish (admin)
GET    /v1/web-info/statistics         - Statistics (admin)
```

### 7.19 Extras (Add-ons)

```
GET    /v1/extras/active               - Get active extras
GET    /v1/extras                      - List extras
GET    /v1/extras/{id}                 - Get extra details
GET    /v1/extras/{id}/usage           - Usage statistics
POST   /v1/extras                      - Create (admin)
PUT    /v1/extras/{id}                 - Update (admin)
DELETE /v1/extras/{id}                 - Delete (admin)
POST   /v1/extras/{id}/toggle-active   - Toggle active (admin)
```

### 7.20 Projects (Public Showcase)

```
GET    /v1/projects                    - List projects
GET    /v1/projects/{id}               - Get project details
GET    /v1/projects/statistics         - Statistics
GET    /v1/projects/featured           - Featured projects
GET    /v1/projects/by-location        - Projects by location
GET    /v1/projects/opportunities      - Investment opportunities
```

### 7.21 PDF Generation

```
GET    /v1/pdf/languages               - Available languages
GET    /v1/pdf/investments/{id}/contract - Investment contract
GET    /v1/pdf/investments/{id}/repayment-schedule - Repayment schedule
```

### 7.22 Languages

```
GET    /v1/languages                   - All active languages
GET    /v1/languages/default           - Default language
GET    /v1/languages/me                - User's preferences (auth)
PUT    /v1/languages/me                - Update preferences (auth)
```

### 7.23 Admin User Management

```
POST   /v1/admin/users/search          - Search users
GET    /v1/admin/users/{id}            - Get user details
POST   /v1/admin/users                 - Create user
PUT    /v1/admin/users/{id}            - Update user
DELETE /v1/admin/users/{id}            - Delete user
POST   /v1/admin/users/{id}/send-welcome-email - Send welcome email
POST   /v1/admin/users/{id}/avatar     - Update avatar
DELETE /v1/admin/users/{id}/avatar     - Delete avatar
GET    /v1/admin/dashboard             - Admin dashboard stats
```

### 7.24 Admin User Details (Tabbed)

```
GET    /v1/admin/users/{id}/overview   - All tabs data
GET    /v1/admin/users/{id}/account    - Account info
GET    /v1/admin/users/{id}/documents  - Documents
GET    /v1/admin/users/{id}/investments - Investments
GET    /v1/admin/users/{id}/power-plants - Solar plants
GET    /v1/admin/users/{id}/billing    - Billing/SEPA
GET    /v1/admin/users/{id}/activity   - Activity timeline
```

### 7.25 Admin Document Verification

```
GET    /v1/admin/documents/pending     - Pending documents
GET    /v1/admin/documents/rejected    - Rejected documents
GET    /v1/admin/documents/statistics  - Statistics
GET    /v1/admin/documents/users/{id}/status - User verification status
POST   /v1/admin/documents/files/{id}/verify - Verify document
POST   /v1/admin/documents/files/{id}/reject - Reject document
```

### 7.26 Health Check

```
GET    /health                         - API health status
```

---

## 8. STATE MANAGEMENT (PINIA)

### Frontend Stores

**Location:** `/Frontend/src/store/`

**Modules:**
- `app/navbar.js` - Navigation/header state
- `app/index.js` - App-level state
- `app-config/index.js` - Configuration settings
- `vertical-menu/index.js` - Sidebar menu state
- `index.js` - Root store initialization

### FrontendUser Stores

**Location:** `/FrontendUser/src/store/`

Similar structure with customer-specific state

### Common Store Concerns

- User authentication state
- Current user data
- Navigation/menu visibility
- Application configuration
- Language/locale settings
- Theme preferences

---

## 9. KEY SERVICES & BUSINESS LOGIC

### Service Classes (in `/api/app/Services/`)

1. **ContractGeneratorService**
   - Generates investment contracts
   - Creates PDFs with contract terms

2. **DocumentRequirementService**
   - Defines required documents per user type
   - Tracks verification status
   - Calculates completion percentage

3. **EncryptionService**
   - Encrypts sensitive data
   - Decrypts for display

4. **MailService**
   - Sends emails (verification, reminders, notifications)
   - Uses Laravel's mail system

5. **NotificationService**
   - Creates user notifications
   - Manages notification delivery

6. **OtpService**
   - Generates OTP codes
   - Validates OTP
   - Manages OTP expiration

7. **RepaymentCalculatorService**
   - Calculates repayment schedules
   - Generates repayment plans
   - Computes interest and principal

8. **ReportService**
   - Generates various reports
   - Performs analytics calculations
   - Exports data

---

## 10. DATABASE OPTIMIZATION OPPORTUNITIES

### Current Schema Issues

1. **Redundant Data in User Table**
   - Customer-related fields mixed with user fields
   - Solution: Use customer_profiles (already implemented!)

2. **Record Status Field (rs)**
   - Using integer (0=active, 99=deleted) instead of proper soft deletes
   - Most tables already use Laravel's soft deletes
   - Recommendation: Standardize on soft deletes

3. **Missing Indexes**
   - Some frequently queried relationships lack indexes
   - Recommendation: Add compound indexes for common filters:
     ```sql
     -- Missing indexes:
     ALTER TABLE investments ADD INDEX idx_user_plant (user_id, solar_plant_id);
     ALTER TABLE invoices ADD INDEX idx_user_invoice_date (user_id, invoice_date);
     ALTER TABLE activity_logs ADD INDEX idx_subject_user (subject_type, subject_id, user_id);
     ```

4. **Large JSON Columns**
   - `preferences`, `monthly_forecast`, `repayment_calculation`, `conditions`, `meta` are JSON
   - These will slow down queries when searching
   - Recommendation: Extract frequently-searched fields to separate columns or tables

5. **No Full-Text Indexes**
   - Content searches like web_info, documents may be slow
   - Recommendation: Add FULLTEXT indexes:
     ```sql
     ALTER TABLE web_infos ADD FULLTEXT idx_content_search (title, content);
     ALTER TABLE solar_plants ADD FULLTEXT idx_plant_search (title, description, location);
     ```

6. **N+1 Query Problems**
   - Controllers use eager loading with `with()` which is good
   - Recommendation: Monitor for missing eager loads in queries

7. **No Database Views**
   - Complex analytics queries run in PHP
   - Recommendation: Create views for common reports:
     ```sql
     CREATE VIEW investment_summary AS
     SELECT i.id, i.user_id, i.solar_plant_id, 
            SUM(ir.amount) as total_paid,
            COUNT(CASE WHEN ir.status = 'pending' THEN 1 END) as pending_count,
            ...
     FROM investments i
     LEFT JOIN investment_repayments ir ON i.id = ir.investment_id;
     ```

8. **Foreign Key Constraints**
   - Not all foreign keys have constraints (some use `cascadeOnDelete`, others `nullOnDelete`)
   - Recommendation: Ensure consistent cascading strategy

9. **No Partitioning**
   - Large tables like activity_logs, investment_repayments could benefit from time-based partitioning
   - Recommendation: Consider partitioning by year for tables older than 2 years

10. **Missing Columns**
    - **created_by_id** missing from several resource tables (campaign, extras, settings)
    - **updated_by_id** missing from audit trail

---

## 11. AUTHENTICATION & AUTHORIZATION FLOW

### Registration & Login Flow

```
1. User submits registration (name, email, password)
   ↓
2. Validation & uniqueness check
   ↓
3. Hash password & create user record
   ↓
4. Assign default role ('customer')
   ↓
5. Send email verification notification
   ↓
6. Generate API token (Sanctum)
   ↓
7. Return token to frontend
   ↓
8. Frontend stores token in localStorage
   ↓
9. Frontend includes token in all subsequent requests:
   Authorization: Bearer {token}
```

### Authorization Check Flow

```
API Request with token
   ↓
Authenticate middleware (auth:sanctum)
   - Validates token in personal_access_tokens
   - Sets Auth::user()
   ↓
Role middleware (if present)
   - Checks user's role via roles table
   - Compares with allowed roles
   ↓
If authorized: Proceed to controller
If not: Return 403 Forbidden

Role Check Examples:
- role:admin                  → Only admin
- role:admin|manager         → Admin OR Manager
- role:admin|manager|user    → Any of these roles
```

### Email Verification Flow

```
1. User registers
2. VerifyEmail notification sent with signed URL
3. User clicks link in email
4. Public endpoint verifies signature
5. Sets email_verified_at timestamp
6. Can now access customer features
```

### OTP Authentication Flow

```
1. User requests OTP (POST /v1/otp/send)
2. OtpService generates 6-digit code
3. Code stored in otp_codes table with expiration
4. SMS/Email sent to user
5. User submits OTP (POST /v1/otp/verify)
6. Service validates code & expiration
7. If valid: Returns API token or marks as verified
```

---

## 12. KEY ARCHITECTURAL PATTERNS

### RESTful API Design
- Resource-based URLs
- Standard HTTP methods (GET, POST, PUT, DELETE)
- JSON request/response format
- Pagination with page/per_page parameters
- Filtering via query parameters

### Repository Pattern (Implicit)
- Controllers use Eloquent models directly
- Models encapsulate database queries
- Scopes used for common filters (e.g., `->active()`)

### Factory Pattern
- UserFactory, PlanFactory for test data
- Seeders for database initialization

### Service Layer Pattern
- Business logic in dedicated services
- Controllers call services, not direct database access
- Example: RepaymentCalculatorService, ContractGeneratorService

### Observer Pattern (Not visible but Laravel-native)
- Model events (creating, created, updating, updated)
- Automatic activity logging via observers

### Middleware Chain
- Multiple middleware per route group
- Auth → Role checking → SetLocale → Business logic

---

## 13. IMPORTANT FILES REFERENCE

### Controllers (27 API controllers)
- `AuthController.php` - Authentication logic
- `SolarPlantController.php` - Plant CRUD (285 lines)
- `InvestmentController.php` - Investment management
- `AdminController.php` - Admin dashboard & user management
- `AdminUserDetailController.php` - Detailed user view
- `AdminDocumentController.php` - Document verification
- `InvoiceController.php` - Invoice management
- `ReportController.php` - Analytics & reports
- `ActivityLogController.php` - Audit trail
- `MessagingController.php` - Chat system
- `NotificationController.php` - Notifications
- `FileController.php` - File management
- `LanguageController.php` - Language preferences
- `SettingsController.php` - System settings
- `CampaignController.php` - Campaign management
- `WebInfoController.php` - Content management
- `ProjectController.php` - Project showcase
- And 10+ more...

### Models (24 models)
- `User.php` - Core user model with extensive relationships
- `SolarPlant.php` - Solar plant with financial tracking
- `Investment.php` - Investment tracking with repayments
- `CustomerProfile.php` - Extended customer data
- `Invoice.php` - Auto-numbered invoices
- `InvestmentRepayment.php` - Installment tracking
- `Conversation.php` - Messaging with pivot data
- `Message.php` - Individual messages
- `File.php` - File metadata and verification
- `FileContainer.php` - Polymorphic file organization
- `Campaign.php` - Promotional campaigns
- `WebInfo.php` - Website content
- `Language.php` - System languages
- `Setting.php` - Configuration key-value pairs
- And 10+ more...

### Migrations (30 total)
- Create core tables (users, sessions, cache, jobs)
- Create roles/permissions (Spatie)
- Create domain tables (2025_11_12_*)
- Create supporting tables
- Create messaging/conversations
- Create campaigns, invoices, web content
- Migration for customer profile separation
- Add metadata and file type columns

### Configuration Files
- `config/roles.php` - Role definitions
- `config/auth.php` - Authentication setup
- `config/sanctum.php` - API token configuration

### Routes
- `routes/api.php` - All API v1 endpoints (416 lines)
- `routes/web.php` - Web routes (minimal)
- `routes/console.php` - Artisan commands

---

## 14. DEPLOYMENT & ENVIRONMENT

### Environment Variables (from .env)
- APP_NAME, APP_ENV, APP_DEBUG, APP_URL
- DB_CONNECTION, DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD
- MAIL_DRIVER, MAIL_FROM_ADDRESS, MAIL_FROM_NAME
- SANCTUM_STATEFUL_DOMAINS
- FRONTEND_URL (for CORS)

### Server Requirements
- PHP 8.2+
- MySQL 5.7+ or MariaDB
- Node.js for frontend build

### Build Process
```bash
# Backend
composer install
php artisan migrate
php artisan db:seed

# Frontend
cd Frontend && npm install && npm run build
cd FrontendUser && npm install && npm run build
```

---

## 15. SUMMARY: RECOMMENDED OPTIMIZATION AREAS

1. **Database**: Add missing indexes, standardize soft deletes
2. **Caching**: Implement Redis for frequently accessed data
3. **Search**: Add full-text indexes for content
4. **Reporting**: Use database views instead of PHP calculations
5. **Security**: Audit field encryption, validate all inputs
6. **Performance**: Monitor N+1 queries, use pagination
7. **Testing**: Expand test coverage (only CLI tests found)
8. **Documentation**: API documentation (OpenAPI/Swagger)
9. **Monitoring**: Add error tracking (Sentry), performance monitoring
10. **Scalability**: Consider queue system for heavy operations

