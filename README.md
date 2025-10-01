# Goitom Finance — Financial Management for Habesha Freelancers

Modern financieel platform voor Habesha freelancers en ondernemers, gebouwd met Laravel 11+, Inertia/Vue 3 en Tailwind. Light/Dark/Auto thema, premium UI en focus op facturatie, uitgaven en rapportage.

## 🧭 Executive Overview / Vision

Goitom Finance biedt world‑class tools met een culture‑first ervaring. Kernwaarden: eenvoud, betrouwbaarheid, meertaligheid, en snelle betalingen.

## 👤 Personas & User Stories

- **Freelancer (Sara, 28)**
  - Needs to send invoices fast, track who paid, and manage tax deductions.
  - “As a freelancer, I want to generate a professional invoice in < 1 minute so I look credible and get paid faster.”

- **Small Business Owner (Yohannes, 40)**
  - Requires project‑based invoicing, expense management, and team visibility.
  - “As a business owner, I want a dashboard that shows expenses vs income, so I can make quick financial decisions.”

- **Consultant (Mimi, 35)**
  - Wants multi‑language invoices, secure record keeping, and tax‑ready reports.
  - “As a consultant, I want to send invoices in Amharic, Dutch, or English, so I can serve clients internationally.”

## 🏗️ System Architecture (High‑level)

```text
+------------------------+        HTTPS        +--------------------+
|        Browser         |  <---------------->  |   Laravel Router   |
|  (Inertia + Vue 3)     |                     +---------+----------+
+-----------+------------+                               |
            | Inertia responses (SSR/JSON page props)    |
            v                                            v
+-----------+------------+                      +--------+---------+
|   Inertia Adapter      |                      |  Controllers     |
|   (inertia-laravel)    |                      |  + Policies      |
+-----------+------------+                      +--------+---------+
            | Eloquent / Services                         |
            v                                             v
+-----------+------------+                      +--------+---------+
|     Services           |<-------------------->|   Models/ORM     |
|  (Invoice/Dashboard/   |   Domain logic       | (Eloquent)       |
|   Expense, etc.)       |                      +--------+---------+
+-----------+------------+                               |
            |                                              
            v                                              
+-----------+------------+                      +--------+---------+
|   MySQL (RDBMS)        |<-- indexes -->       |   Redis (cache)  |
+------------------------+                      +------------------+
            ^                                              |
            | PDF/Email                                   |
            |                                              v
      +-----+------+                              +-------+-------+
      |  DomPDF     |                              |  Audit Logs   |
      |  Mailer     |----------------------------->| storage/logs  |
      +------------ +                              +---------------+
```

## 🎨 Design & UX

**Tokens & theming**
- Class‑based dark mode (`dark:`) + ThemeToggle met `auto|light|dark` en persistente keuze.
- Design tokens: brand/ink kleuren, `shadow-card/cardStrong`, radius xl.

**UX patterns**
- Glasachtige topbar/side bar, sticky sectiekoppen, focus‑visible ringen, skip‑link.
- Actieve nav als “pill” met merkcontrast en chevron.

## 🔒 Security & Compliance

- **GDPR readiness**: scheiding van data, audit trails, exporteerbaarheid.
- **Encryption**: TLS in transit; database/volumes voorbereid op at‑rest encryptie (infra afhankelijk).
- **RBAC**: policies per resource; uitbreidbaar naar rollen/permissions.
- **Rate limiting**: middleware voor misbruikpreventie.
- **2FA/MFA (roadmap)**: gepland via Laravel Fortify/Third‑party authenticator.

## 🔌 API (Future‑ready)

Planned versioned API voor integraties:

```http
POST /api/v1/invoices            # create invoice
GET  /api/v1/invoices/{id}       # fetch invoice
GET  /api/v1/clients/{id}        # fetch client
GET  /api/v1/dashboard/stats     # summary metrics
```

Authenticatie: **Bearer tokens (Sanctum personal access tokens)**, rate‑limited, JSON responses.

## 🚀 Features

### Core (MVP)
- **Invoice Management**: Create, edit, and track invoices with automatic calculations
- **Client Management**: Organize and manage client information
- **Project Tracking**: Monitor project progress and associate invoices
- **Expense Tracking**: Record and categorize business expenses
- **Payment Processing**: Track payments and outstanding amounts
- **Financial Dashboard**: Real-time overview of business finances

### Advisory Pillars
- **Financial Planning**
  Comprehensive wealth management strategies tailored to your unique financial goals and cultural values.

- **Investment Management**
  Expert investment strategies that align with your risk tolerance and long-term financial objectives.

- **Tax Optimization**
  Strategic tax planning to minimize your tax burden while maximizing your wealth accumulation potential.

- **Estate Planning**
  Comprehensive estate planning to ensure your legacy is preserved and passed down according to your wishes.

### Advanced / V2+
- **Multi-language Support**: English, Dutch, and Amharic
- **PDF Generation**: Professional invoice PDFs with company branding
- **Email Integration**: Send invoices directly to clients
- **Audit Logging**: Complete audit trail for financial operations
- **Performance Optimization**: Cached dashboard statistics and database indexes
- **Security Hardening**: Rate limiting and comprehensive validation
 - **Time Tracking** (urenregistratie) gelinkt aan projecten
 - **Custom Invoice Fields**
 - **Subscriptions/Recurring Invoices**
 - **Digital Archive (Documents)**

## 🛠 Technology Stack

### Backend
- **Laravel 12**: PHP framework with modern features
- **MySQL/PostgreSQL**: Robust database support
- **Redis**: Caching and session storage
- **Laravel Sanctum**: API authentication

### Frontend
- **Vue.js 3**: Modern reactive frontend
- **Inertia.js**: SPA-like experience without API complexity
- **Tailwind CSS**: Utility-first CSS framework
- **Vite**: Fast build tool and development server

### Additional Tools
- **DomPDF**: PDF generation for invoices
- **Laravel Breeze**: Authentication scaffolding
- **PHPUnit**: Comprehensive testing suite

## 📋 Requirements

- PHP 8.3+
- Composer
- Node.js 20.19+ (aanbevolen 22.12+) en npm
- MySQL 8.0+ of PostgreSQL 13+
- Redis (optioneel, voor caching)

## 🚀 Installation

### 1. Clone the Repository
```bash
git clone https://github.com/your-username/habesha-finance-platform.git
cd habesha-finance-platform
```

### 2. Install Dependencies
```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

> Tip: gebruik nvm om Node 22 te installeren als Vite klaagt over Node-versie
> ```bash
> # macOS zsh
> curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.7/install.sh | bash
> export NVM_DIR="$HOME/.nvm" && . "$NVM_DIR/nvm.sh"
> nvm install 22.12.0 && nvm use 22.12.0
> ```

### 3. Environment Setup
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```


### 5. Run Migrations
```bash
# Create database tables
php artisan migrate

# Seed initial data (optional)
php artisan db:seed
```

### 6. Build & Run
```bash
# Development (Vite + Laravel)
php artisan serve --port=8010  # of vrij poort indien 8000 bezet
npm run dev                 # Vite dev server (hot reload)

# Production build
npm run build
```

## 🧪 Testing

The project includes comprehensive test coverage for all core functionality:

```bash
# Run all tests
php artisan test

# Run specific test suites
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit

# Run tests with coverage
php artisan test --coverage
```

### Test Coverage
- **Authentication**: Login, registration, password reset
- **Invoice Management**: CRUD operations, calculations, PDF generation
- **Expense Tracking**: CRUD operations, categorization
- **Dashboard**: Statistics, caching, data isolation
- **Security**: Authorization, validation, audit logging

## 🔧 Configuration

### Cache Configuration
The application uses Redis for caching dashboard statistics:
```env
CACHE_STORE=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### Mail Configuration
Configure email settings for invoice delivery:
```env
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
```

### Logging Configuration
Audit logs are stored separately for compliance:
```env
LOG_AUDIT_DAYS=90
```

## 🏗 Architecture

### Service Layer Pattern
Business logic is extracted into service classes:
- `DashboardService`: Handles dashboard statistics and caching
- `InvoiceService`: Manages invoice calculations and operations
- `ExpenseService`: Handles expense management logic

### Form Request Validation
Comprehensive validation using Laravel Form Requests:
- `StoreInvoiceRequest`: Invoice creation validation
- `UpdateInvoiceRequest`: Invoice update validation
- `StoreExpenseRequest`: Expense creation validation

### Security Middleware
- `RateLimitMiddleware`: Prevents abuse with rate limiting
- `AuditLogMiddleware`: Logs all financial operations

## 📊 Performance Optimizations

### Database Indexes
Strategic indexes on frequently queried fields:
- User-based queries (`user_id`, `status`)
- Date-based queries (`due_date`, `expense_date`)
- Foreign key relationships

### Caching Strategy
- Dashboard statistics cached for 5 minutes
- Cache invalidation on data changes
- Redis-based caching for production

### Query Optimization
- Eager loading relationships
- Database aggregation queries
- Reduced N+1 query problems

## 🔒 Security Features

### Authentication & Authorization
- Laravel Sanctum for API authentication
- Policy-based authorization
- CSRF protection
- Password hashing

### Rate Limiting
- 60 requests per minute per IP
- Configurable limits for different endpoints
- Graceful error handling

### Audit Logging
- Complete audit trail for financial operations
- User activity tracking
- IP address and user agent logging
- 90-day log retention

### Input Validation
- Comprehensive form request validation
- SQL injection prevention
- XSS protection
- File upload security

## 🌍 Internationalization

The platform supports multiple languages:
- **English**: Default language
- **Dutch**: Complete translation
- **Amharic**: Ethiopian language support

Language files are located in `lang/` directory with structured translations for all UI elements.

## 📈 Monitoring & Logging

### Application Logs
- Standard Laravel logs in `storage/logs/laravel.log`
- Daily log rotation
- Configurable log levels

### Audit Logs
- Financial operations logged to `storage/logs/audit.log`
- Structured JSON format
- 90-day retention policy

### Error Handling
- Comprehensive error handling
- User-friendly error messages
- Detailed logging for debugging

## 🚀 Deployment

### Production Checklist
- [ ] Set `APP_ENV=production`
- [ ] Configure production database
- [ ] Set up Redis for caching
- [ ] Configure email settings
- [ ] Set up SSL certificates
- [ ] Configure web server (Nginx/Apache)
- [ ] Set up monitoring and logging
- [ ] Run `php artisan config:cache`
- [ ] Run `php artisan route:cache`
- [ ] Run `php artisan view:cache`

### Docker Deployment
```bash
# Build production image
docker build -t habesha-finance .

# Run with docker-compose
docker-compose up -d
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Development Guidelines
- Follow PSR-12 coding standards
- Write tests for new features
- Update documentation
- Use meaningful commit messages

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🆘 Support

For support and questions:
- Create an issue on GitHub
- Email: support@habeshafinance.com
- Documentation: [docs.habeshafinance.com](https://docs.habeshafinance.com)

## 🗺 Roadmap & Requirements

### Upcoming Features
- [ ] Recurring invoices
- [ ] Advanced reporting
- [ ] Mobile app
- [ ] API for third-party integrations
- [ ] Multi-currency support
- [ ] Tax calculation automation
- [ ] Client portal
- [ ] Automated payment reminders

Zie ook: `docs/Requirements.md` voor de volledige features, prioriteiten en roadmap.

---

**Built with ❤️ for the Habesha community**