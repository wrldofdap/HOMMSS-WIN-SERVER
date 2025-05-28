# HOMMSS E-Commerce Platform

## Enterprise-Grade Secure E-Commerce Solution

[![Laravel](https://img.shields.io/badge/Laravel-11.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.3+-blue.svg)](https://php.net)
[![Security](https://img.shields.io/badge/Security-Enterprise%20Grade-green.svg)](#security-features)
[![License](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)

A **production-ready e-commerce platform** built with Laravel, featuring **enterprise-grade security**, **military-level encryption**, and **professional backup systems** for selling home and construction products.

## PBL Presentation Ready

**For PBL Presentation:** All presentation guides are in `docs/presentation-guides/`
- **[Start Here: Presentation Index](docs/presentation-guides/00-PRESENTATION-INDEX.md)** - Master guide
- **[Complete Presentation Guide](docs/presentation-guides/PBL-PRESENTATION-GUIDE.md)** - 15-20 min guide  
- **[Quick Demo Commands](docs/presentation-guides/QUICK-DEMO-COMMANDS.md)** - Essential commands
- **[Presentation Checklist](docs/presentation-guides/PRESENTATION-CHECKLIST.md)** - Step-by-step checklist

**Demo Accounts:** admin@demo.com / demo1234 • customer@demo.com / demo1234

---

## Quick Start for Demo

```bash
# Start the application
php artisan serve --host=0.0.0.0 --port=8000

# Monitor OTP in logs
tail -f storage/logs/laravel.log | grep "OTP"

# Test security features
php artisan security:test-sql-injection
```

---

## Security Features

### Authentication & Authorization
- **SHA-256 Hashed OTP Storage** - Secure two-factor authentication
- **Timing Attack Protection** - Using `hash_equals()` for secure comparisons
- **Rate Limiting** - Brute force protection (5 attempts per minute)
- **Role-Based Access Control** - Admin/User separation with logging
- **Session Encryption** - All session data encrypted

### Infrastructure Security
- **Security Headers Middleware** - XSS, Clickjacking, MIME-type protection
- **HTTPS Enforcement** - Automatic HTTP to HTTPS redirection
- **CSRF Protection** - Cross-site request forgery prevention
- **Content Security Policy** - Enhanced CSP for payment gateways

### Payment Security
- **Real Payment Gateways** - Stripe and PayMongo integration
- **PCI Compliance Ready** - Secure payment processing
- **Webhook Security** - Signature verification for callbacks
- **Transaction Logging** - Complete audit trail

### File Upload Security
- **Content Validation** - Actual file content checking (not just MIME)
- **EXIF Data Stripping** - Privacy protection for images
- **Secure Filename Generation** - Cryptographically secure names
- **Size & Dimension Limits** - Malicious upload prevention

### Backup & Data Protection
- **AES-256-CBC Encryption** - Military-grade backup encryption
- **SHA-256 Integrity Verification** - Backup corruption detection
- **Password-Protected Backups** - Secure backup access
- **Automated Daily Backups** - Scheduled data protection
- **Email Notifications** - Backup success/failure alerts

---

## Technical Architecture

### Core Technologies
- **Framework**: Laravel 11.x (Latest)
- **PHP Version**: 8.3+ (Modern PHP)
- **Database**: MySQL 8.0+ with optimized queries
- **Frontend**: Bootstrap 5 + JavaScript
- **Security**: Custom middleware + Laravel security features
- **Payments**: Stripe + PayMongo integration
- **Backup**: Custom encryption system

### Project Structure
```
HOMMSS/
├── app/
│   ├── Http/
│   │   ├── Controllers/     # Business logic
│   │   └── Middleware/      # Security layers
│   ├── Models/              # Data models
│   ├── Services/            # Business services
│   └── Console/Commands/    # Custom artisan commands
├── config/                  # Configuration files
├── database/
│   ├── migrations/          # Database schema
│   └── seeders/             # Demo data
├── resources/views/         # UI templates
├── storage/app/backups/     # Secure backups
└── docs/                    # Documentation
```

---

## Installation & Setup

### Requirements
- **PHP 8.3+** with extensions: OpenSSL, PDO, Mbstring, Tokenizer, XML, Ctype, JSON
- **MySQL 8.0+** or MariaDB 10.3+
- **Composer** (PHP dependency manager)
- **Node.js 18+** and NPM (for frontend assets)

### Quick Setup
```bash
# 1. Clone the repository
git clone https://github.com/wrldofdap/HOMMSS-WIN-SERVER.git
cd HOMMSS-WIN-SERVER

# 2. Install dependencies
composer install
npm install

# 3. Environment setup
cp .env.example .env
php artisan key:generate

# 4. Database setup
php artisan migrate
php artisan db:seed

# 5. Build assets
npm run build

# 6. Storage setup
php artisan storage:link

# 7. Start application
php artisan serve
```

---

## Demo Walkthrough

### Security Features Demo

#### 1. Authentication Security
```bash
# Test OTP system
1. Go to http://localhost:8000/login
2. Enter: admin@demo.com / demo1234
3. Check logs for OTP: tail -f storage/logs/laravel.log
4. Look for "DEMO OTP Generated" entry
5. Enter the 6-digit OTP to complete login
```

#### 2. SQL Injection Protection
```bash
# Test security protection
php artisan security:test-sql-injection
```

#### 3. Backup Security
```bash
# Create encrypted backup
php artisan app:php-backup-database --filename=presentation-demo

# Verify backup exists
ls -la storage/app/backups/
```

### E-Commerce Features Demo

#### 1. Customer Journey
```bash
# Complete shopping flow
1. Browse products: http://localhost:8000/shop
2. Add to cart and proceed to checkout
3. Complete order with OTP authentication
4. View order history
```

#### 2. Admin Management
```bash
# Admin operations
1. Login: admin@demo.com / demo1234
2. Manage products: /admin/products
3. Process orders: /admin/orders
4. View dashboard: /admin
```

---

## Documentation

### PBL Presentation Guides
All presentation materials are in `docs/presentation-guides/`
- [Presentation Index](docs/presentation-guides/00-PRESENTATION-INDEX.md) - Complete guide index
- [Complete Presentation Guide](docs/presentation-guides/PBL-PRESENTATION-GUIDE.md) - Full 15-20 min guide
- [Quick Demo Commands](docs/presentation-guides/QUICK-DEMO-COMMANDS.md) - Essential commands
- [Presentation Checklist](docs/presentation-guides/PRESENTATION-CHECKLIST.md) - Step-by-step checklist

### Security Documentation
- [Complete Security Implementation](docs/SECURITY_IMPLEMENTATION_COMPLETE.md)
- [Security Setup Guide](docs/SECURITY_SETUP_GUIDE.md)
- [SQL Injection Testing Guide](docs/SQL-INJECTION-TESTING-GUIDE.md)
- [Email TLS Security Setup](docs/EMAIL-TLS-SECURITY.md)

### Deployment Documentation
- [AWS Ubuntu Apache Deployment](docs/aws-ubuntu-apache-deployment.md)
- [Database Backup & Restore Guide](docs/database-backup-restore.md)

### Complete Documentation Index
**[See docs/README.md](docs/README.md) for complete documentation index**

---

## Project Achievements

### Technical Excellence
- **Enterprise-Grade Security** - Military-level encryption and protection
- **Production-Ready Code** - Professional Laravel architecture
- **Complete Feature Set** - Full e-commerce functionality
- **Modern Tech Stack** - Latest Laravel 11 with PHP 8.3+
- **Comprehensive Testing** - Security and functionality validation

### Security Achievements
- **Zero SQL Injection Vulnerabilities** - Proper query parameterization
- **XSS Protection** - Complete input sanitization and output encoding
- **CSRF Protection** - All forms protected against cross-site requests
- **Session Security** - Encrypted sessions with secure configuration
- **File Upload Security** - Content validation and malicious file detection

### Business Value
- **Real-World Applicable** - Can handle actual business operations
- **Scalable Architecture** - Designed for growth and expansion
- **Maintainable Code** - Clean, documented, and organized
- **Security Compliant** - Meets industry security standards
- **Professional Quality** - Enterprise-level implementation

---

## Support & Contact

### Contact Information
- **Email**: hommss666@gmail.com
- **Project**: HOMMSS E-Commerce Platform
- **Live Demo**: https://hommss.website
- **Repository**: https://github.com/wrldofdap/HOMMSS-WIN-SERVER

### Getting Help
1. Check the documentation in the `docs/` directory
2. Review the security guides for implementation details
3. Check Laravel logs in `storage/logs/laravel.log`
4. Verify environment configuration in `.env`

---

## License

This project is open-sourced software licensed under the [MIT license](LICENSE).

---

## Acknowledgments

### Special Thanks
- **Laravel Framework** - For providing an excellent foundation
- **Security Community** - For best practices and standards
- **Open Source Contributors** - For making this possible

### Final Note

*HOMMSS represents more than just code - it's a demonstration of **professional software development**, **enterprise security thinking**, and **production-ready architecture**. This project showcases the kind of security implementation and code quality expected in **Fortune 500 companies**.*

*Built with Security in Mind*

---

*© 2025 HOMMSS E-Commerce Platform. All rights reserved.*
