# Solar Planning Application

A comprehensive platform for managing solar plant investments, built with Laravel 12 and Vue 3.

## Overview

Solar Planning is a modern web application that facilitates investment management for solar energy projects. The platform connects investors with solar plant owners, manages investment contracts, tracks repayments, and provides comprehensive reporting and analytics.

### Key Features

#### Investment Management
- 💰 **Investment Tracking** - Create and manage investments in solar plants
- 📊 **Automated Calculations** - Interest and repayment calculations using multiple methods (annuity, linear, bullet)
- 📅 **Flexible Repayment Schedules** - Monthly, quarterly, or annual repayment intervals
- ✅ **Investment Verification** - Multi-step verification workflow for admins

#### Solar Plant Management
- ☀️ **Plant Registry** - Comprehensive solar plant database with technical specifications
- 📈 **Capacity Tracking** - Monitor nominal power, expected production, and installation details
- 🏗️ **Status Management** - Track plants from planning through operational status
- 💼 **Funding Progress** - Real-time funding goal tracking

#### Document Management
- 📄 **Contract Generation** - Automated PDF investment contract generation
- 📑 **Repayment Schedules** - Professional PDF schedules with payment breakdowns
- 📎 **File Uploads** - Secure file storage with role-based access control
- ✓ **Document Verification** - Admin/manager file verification workflow

#### Financial Operations
- 💳 **Repayment Processing** - Automated repayment tracking and management
- 📧 **Payment Notifications** - Automated reminders and confirmation emails
- 📊 **Interest Calculations** - Accurate interest and principal allocation
- ⏰ **Overdue Tracking** - Automatic identification of late payments

#### Reporting & Analytics
- 📊 **Dashboard Statistics** - Real-time overview of investments, plants, and repayments
- 📈 **Investment Analytics** - Detailed breakdowns by status, time period, and investor
- 💰 **Financial Reports** - Monthly reports with comprehensive metrics
- 🎯 **Performance Scoring** - Investment performance ratings (0-100)
- 📥 **CSV Exports** - Export data for external analysis

#### User Management
- 👥 **Role-Based Access** - Admin, Manager, and Customer roles with granular permissions
- 🔐 **Authentication** - Laravel Sanctum-based API authentication
- ✉️ **Email Verification** - Secure account verification workflow
- 🔑 **Password Reset** - Secure password recovery with token validation
- 📱 **OTP Authentication** - Optional OTP-based login (planned)

#### Audit & Compliance
- 📝 **Activity Logging** - Complete audit trail using Spatie Activity Log
- 🔍 **Search & Filter** - Advanced activity log filtering and search
- 📊 **Statistics** - Activity statistics and reporting
- 🔒 **Data Privacy** - Soft deletes and GDPR-compliant data management

#### Notifications
- 📧 **Email Notifications** - Automated emails for all key events
- 🔔 **In-App Notifications** - Real-time notification system
- ⚙️ **Preferences** - User-configurable notification settings
- 📮 **Email Templates** - Professional, branded email templates

## Technology Stack

### Backend
- **Framework**: Laravel 12 (PHP 8.2+)
- **Database**: PostgreSQL 14+
- **Authentication**: Laravel Sanctum
- **Authorization**: Spatie Laravel Permission
- **Activity Logging**: Spatie Laravel Activity Log
- **PDF Generation**: Laravel DomPDF (Barryvdh)
- **Email**: Laravel Mail with queue support

### Frontend
- **Framework**: Vue 3.5.20 (Composition API)
- **State Management**: Pinia 2.3.3
- **UI Components**: PrimeVue 4.3.7
- **Routing**: Vue Router 4.5.1
- **HTTP Client**: Axios 1.7.10
- **Build Tool**: Vite 6.0.7
- **Type Safety**: TypeScript

### Development Tools
- **Code Quality**: ESLint, Prettier
- **Testing**: PHPUnit (backend), Vitest (frontend)
- **Version Control**: Git
- **Package Management**: Composer (PHP), npm (Node.js)

## Project Structure

```
solarApp/
├── api/                          # Laravel Backend
│   ├── app/
│   │   ├── Console/             # CLI commands and scheduled jobs
│   │   ├── Http/
│   │   │   ├── Controllers/Api/ # API controllers
│   │   │   └── Middleware/      # Custom middleware
│   │   ├── Mail/                # Email templates (Mailable classes)
│   │   ├── Models/              # Eloquent models
│   │   └── Services/            # Business logic services
│   ├── config/                  # Configuration files
│   ├── database/
│   │   ├── migrations/          # Database migrations
│   │   └── seeders/             # Database seeders
│   ├── resources/
│   │   └── views/
│   │       ├── emails/          # Blade email templates
│   │       └── pdfs/            # PDF templates
│   ├── routes/
│   │   └── api.php              # API routes
│   └── storage/                 # File storage
│
├── frontend/                     # Vue 3 Frontend
│   ├── src/
│   │   ├── components/          # Reusable Vue components
│   │   ├── composables/         # Composition API composables
│   │   ├── layouts/             # Layout components
│   │   ├── router/              # Vue Router configuration
│   │   ├── services/            # API service layer
│   │   ├── stores/              # Pinia stores
│   │   ├── types/               # TypeScript type definitions
│   │   ├── utils/               # Utility functions
│   │   └── views/               # Page components
│   ├── public/                  # Static assets
│   └── package.json             # Frontend dependencies
│
├── INSTALLATION.md              # Detailed installation guide
├── IMPLEMENTATION_STATUS.md     # Implementation progress tracker
└── README.md                    # This file
```

## Quick Start

### Prerequisites

- PHP 8.2+
- Composer
- Node.js 18+
- PostgreSQL 14+
- Git

### Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd solarApp
   ```

2. **Backend Setup**
   ```bash
   cd api
   composer install
   composer require barryvdh/laravel-dompdf spatie/laravel-permission spatie/laravel-activitylog
   cp .env.example .env
   php artisan key:generate
   # Configure your database in .env
   php artisan migrate
   php artisan db:seed
   ```

3. **Frontend Setup**
   ```bash
   cd ../frontend
   npm install
   cp .env.example .env
   # Configure API URL in .env
   ```

4. **Run Development Servers**
   ```bash
   # Terminal 1 - Backend
   cd api && php artisan serve

   # Terminal 2 - Queue Worker
   cd api && php artisan queue:work

   # Terminal 3 - Frontend
   cd frontend && npm run dev
   ```

5. **Access the Application**
   - Frontend: http://localhost:5173
   - Backend API: http://localhost:8000/api/v1

For detailed setup instructions, see [INSTALLATION.md](INSTALLATION.md)

## API Documentation

### Base URL
```
http://localhost:8000/api/v1
```

### Authentication
The API uses Laravel Sanctum for authentication. Include the token in requests:
```http
Authorization: Bearer {your-token}
```

### Key Endpoints

#### Authentication
- `POST /register` - Register new user
- `POST /login` - User login
- `POST /logout` - User logout
- `GET /user` - Get authenticated user

#### Solar Plants
- `GET /solar-plants` - List all solar plants
- `POST /solar-plants` - Create new plant (admin/manager)
- `GET /solar-plants/{id}` - Get plant details
- `PUT /solar-plants/{id}` - Update plant (admin/manager)
- `GET /solar-plants/statistics` - Get plant statistics

#### Investments
- `GET /investments` - List investments
- `POST /investments` - Create investment
- `GET /investments/{id}` - Get investment details
- `POST /investments/{id}/verify` - Verify investment (admin/manager)
- `GET /investments/{id}/repayments` - Get repayment schedule

#### Repayments
- `GET /repayments/statistics` - Repayment statistics
- `GET /repayments/overdue` - List overdue repayments
- `GET /repayments/upcoming` - Upcoming repayments
- `POST /repayments/{id}/mark-paid` - Mark as paid (admin/manager)

#### Reports
- `GET /reports/dashboard` - Dashboard overview
- `GET /reports/investments/analytics` - Investment analytics
- `GET /reports/repayments/analytics` - Repayment analytics
- `GET /reports/monthly/{year}/{month}` - Monthly report
- `POST /reports/investments/export` - Export to CSV (admin/manager)

#### Files
- `POST /files/upload` - Upload file
- `GET /files` - List files in container
- `GET /files/{id}/download` - Download file
- `POST /files/{id}/verify` - Verify file (admin/manager)

#### Activity Logs
- `GET /activity-logs` - List activity logs
- `GET /activity-logs/statistics` - Activity statistics
- `GET /activity-logs/model/{type}/{id}` - Logs for specific model

#### Settings
- `GET /settings/public` - Public settings (no auth required)
- `GET /settings` - All settings (authenticated)
- `PUT /settings/{group}/{key}` - Update setting (admin)
- `POST /settings/bulk-update` - Bulk update (admin)

## User Roles & Permissions

### Admin
- Full system access
- User management
- All CRUD operations
- Settings management
- System configuration

### Manager
- Manage assigned solar plants
- Verify investments for managed plants
- View and manage repayments
- Access reports and analytics
- File verification

### Customer
- View available solar plants
- Create investments
- View own investments and repayments
- Upload documents
- Access personal dashboard

## Email Notifications

The system sends automated emails for:
- ✉️ Welcome emails for new users
- 📝 Investment confirmation
- ✅ Investment verification
- 📄 Contract ready for download
- 💰 Repayment confirmation
- ⏰ Repayment reminders (7 days before due date)
- ⚠️ Overdue payment notifications
- 📊 Monthly investment reports

## Scheduled Jobs

The application includes automated background jobs:
- **Daily 2:00 AM** - Check for overdue repayments
- **Daily 9:00 AM** - Send repayment reminders
- **1st of month 8:00 AM** - Send monthly reports
- **Weekly Sunday 3:00 AM** - Cleanup old activity logs
- **Monthly 1st 4:00 AM** - Prune soft deleted records

Configure cron:
```bash
* * * * * cd /path/to/api && php artisan schedule:run >> /dev/null 2>&1
```

## Development

### Code Style

**Backend (Laravel)**
```bash
# Run PHP CS Fixer
./vendor/bin/php-cs-fixer fix

# Run PHPStan
./vendor/bin/phpstan analyse
```

**Frontend (Vue)**
```bash
# Lint
npm run lint

# Format
npm run format
```

### Testing

**Backend Tests**
```bash
cd api
php artisan test
php artisan test --coverage
```

**Frontend Tests**
```bash
cd frontend
npm run test:unit
npm run test:coverage
```

### Building for Production

**Backend**
```bash
cd api
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

**Frontend**
```bash
cd frontend
npm run build
```

## Migration from Legacy System

This application replaces the old Quarkus (Java 11) + Vue 2 system. Key improvements:

### Architecture Changes
- ✅ Unified authentication (Laravel Sanctum vs. Keycloak)
- ✅ Single frontend application (Vue 3 vs. 2 separate Vue 2 apps)
- ✅ Modern tech stack (Laravel 12, Vue 3, TypeScript)
- ✅ Improved database schema with UUIDs
- ✅ Enhanced security and RBAC

### Feature Enhancements
- ✅ Automated PDF contract generation
- ✅ Comprehensive activity logging
- ✅ Advanced reporting and analytics
- ✅ File management with verification workflow
- ✅ Scheduled background jobs
- ✅ Email notification system
- ✅ Modern, responsive UI with PrimeVue

## Implementation Status

✅ **Completed** (100%)
- Backend API with all controllers
- Database migrations and models
- Authentication and authorization
- Solar plant management
- Investment management
- Repayment processing
- File management system
- PDF generation (contracts, schedules)
- Email notification system
- Reporting and analytics
- Activity logging
- Settings management
- Scheduled jobs
- Frontend application (all views)
- API integration
- Documentation

See [IMPLEMENTATION_STATUS.md](IMPLEMENTATION_STATUS.md) for detailed breakdown.

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is proprietary software. All rights reserved.

## Support

For issues, questions, or contributions:
- 📧 Email: support@solarplanning.com
- 📚 Documentation: See [INSTALLATION.md](INSTALLATION.md)
- 🐛 Report bugs: Create a GitHub issue

---

**Built with ❤️ by the Solar Planning Team**
