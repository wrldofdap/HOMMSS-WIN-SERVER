# ⚡ Quick Demo Commands - HOMMSS Presentation

## 🚀 **Essential Commands for Live Demo**

### **1. Start Application**
```bash
# Start the development server
php artisan serve --host=0.0.0.0 --port=8000

# Alternative with specific port
php artisan serve --port=8080
```

### **2. Monitor System**
```bash
# Watch OTP generation in real-time
tail -f storage/logs/laravel.log | grep "OTP"

# Monitor all authentication events
tail -f storage/logs/laravel.log | grep "LOGIN\|OTP\|AUTH"

# Check application status
php artisan about
```

### **3. Security Demonstrations**
```bash
# Test SQL injection protection
php artisan security:test-sql-injection

# Test email security
php artisan email:test-security --send

# Run deployment readiness test
php artisan app:deployment-test --quick
```

### **4. Backup System Demo**
```bash
# Create backup (PHP method - works everywhere)
php artisan app:php-backup-database --filename=presentation-demo

# Simple backup (with fallback to PHP)
php artisan app:simple-backup-database --filename=demo-backup

# List existing backups
ls -la storage/app/backups/
```

### **5. Cache Management**
```bash
# Clear all caches
php artisan config:clear && php artisan route:clear && php artisan view:clear

# Rebuild caches for production
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

---

## 🎯 **Demo URLs**

### **Public Pages**
- **Homepage:** http://localhost:8000
- **Shop:** http://localhost:8000/shop
- **Product Details:** http://localhost:8000/product/{id}
- **Cart:** http://localhost:8000/cart
- **Checkout:** http://localhost:8000/checkout

### **Authentication**
- **Login:** http://localhost:8000/login
- **Register:** http://localhost:8000/register
- **OTP Verification:** http://localhost:8000/verify-otp

### **Admin Panel**
- **Admin Dashboard:** http://localhost:8000/admin
- **Products:** http://localhost:8000/admin/products
- **Orders:** http://localhost:8000/admin/orders
- **Add Product:** http://localhost:8000/admin/products/add

---

## 👥 **Demo Accounts**

### **Admin Account**
```
Email: admin@demo.com
Password: demo1234
Role: Administrator
```

### **Customer Account**
```
Email: customer@demo.com
Password: demo1234
Role: Customer
```

### **Test Customer Account**
```
Email: test@example.com
Password: demo1234
Role: Customer
```

---

## 🛡️ **Security Test Commands**

### **SQL Injection Tests**
```bash
# Run all SQL injection tests
php artisan security:test-sql-injection

# Test specific endpoints
php artisan security:test-sql-injection --endpoint=search
php artisan security:test-sql-injection --endpoint=login
php artisan security:test-sql-injection --endpoint=contact
```

### **File Upload Security**
```bash
# Test file upload validation (manual)
# 1. Go to admin/products/add
# 2. Try uploading .php file (should fail)
# 3. Try uploading oversized image (should fail)
# 4. Upload valid image (should work)
```

### **Email Security**
```bash
# Test email configuration and security
php artisan email:test-security --send

# Check email settings
php artisan tinker
>>> config('mail')
>>> exit
```

---

## 📊 **System Information Commands**

### **Application Status**
```bash
# Comprehensive system information
php artisan about

# Check environment configuration
php artisan env

# Database status
php artisan migrate:status

# Check routes
php artisan route:list --compact
```

### **Performance Checks**
```bash
# Check database queries (in tinker)
php artisan tinker
>>> DB::enableQueryLog()
>>> User::all()
>>> DB::getQueryLog()
>>> exit

# Check file permissions
ls -la storage/
ls -la bootstrap/cache/
```

---

## 🎬 **Demo Flow Commands**

### **Security-First Demo Flow (6 minutes)**
```bash
# 1. Start application
php artisan serve --host=0.0.0.0 --port=8000

# 2. Start OTP monitoring (separate terminal)
tail -f storage/logs/laravel.log | grep "DEMO OTP"

# 3. OTP Authentication Demo
# - Go to http://localhost:8000/login
# - Login with admin@demo.com / demo1234
# - Watch OTP appear in monitoring terminal
# - Complete OTP verification

# 4. SQL Injection Protection Demo
php artisan security:test-sql-injection

# 5. File Upload Security Demo
# - Go to http://localhost:8000/admin/products/add
# - Try uploading .php file (should fail)
# - Upload valid image (should work)

# 6. Backup System Demo
php artisan app:php-backup-database --filename=presentation-demo
```

### **E-Commerce Features Demo Flow (6 minutes)**
```bash
# Customer Experience (3 min)
# 1. Homepage: http://localhost:8000
# 2. Shop: http://localhost:8000/shop
# 3. Add products to cart
# 4. Checkout with OTP authentication

# Admin Panel (3 min)
# 1. Admin login: admin@demo.com / demo1234
# 2. Dashboard: http://localhost:8000/admin
# 3. Add product: http://localhost:8000/admin/products/add
# 4. Manage orders: http://localhost:8000/admin/orders
```

### **Technical Excellence Demo Flow (4 minutes)**
```bash
# 1. Show deployment readiness
php artisan app:deployment-test --quick

# 2. Check backup schedule status
php artisan app:check-backup-schedule

# 3. Show recent backups
ls -la storage/app/backups/

# 4. Mention AWS production deployment
# Live at: https://hommss.website
```

---

## 🚨 **Emergency Commands**

### **If Application Breaks**
```bash
# Reset everything
php artisan config:clear
php artisan route:clear
php artisan view:clear
composer dump-autoload
php artisan migrate:fresh --seed

# Check for errors
php artisan tinker
>>> User::count()
>>> exit
```

### **If Database Issues**
```bash
# Check database connection
php artisan migrate:status

# Reset database with sample data
php artisan migrate:fresh --seed

# Create admin user manually
php artisan tinker
>>> User::create(['name' => 'Admin', 'email' => 'admin@demo.com', 'password' => Hash::make('demo1234'), 'role' => 'admin'])
>>> exit
```

### **If OTP Not Working**
```bash
# Check email configuration
cat .env | grep MAIL

# Test email manually
php artisan tinker
>>> Mail::raw('Test email', function($mail) { $mail->to('test@example.com')->subject('Test'); })
>>> exit

# Check logs for errors
tail -f storage/logs/laravel.log
```

---

## 📝 **Quick Explanations for Questions**

### **"How does OTP work?"**
```
1. User enters email/password
2. System generates 6-digit random OTP
3. OTP is hashed with SHA-256 and stored in database
4. Plain OTP is sent via email (TLS encrypted)
5. User enters OTP, system verifies hash
6. OTP expires after 10 minutes for security
```

### **"How do you prevent SQL injection?"**
```
1. Use Laravel's Eloquent ORM (automatic parameter binding)
2. Never concatenate user input directly into queries
3. Use prepared statements for raw queries
4. Validate and sanitize all inputs
5. Automated tests verify protection
```

### **"What about performance?"**
```
1. Database queries optimized with Eloquent
2. Caching enabled (config, routes, views)
3. Image optimization for uploads
4. Lazy loading for relationships
5. Database indexing on key fields
```

---

## 🎯 **Success Metrics to Mention**

- **Security Score:** 95%+ deployment readiness
- **Page Load Time:** < 2 seconds average
- **Database Tables:** 12 with proper relationships
- **Code Quality:** PSR-12 compliant, documented
- **Test Coverage:** SQL injection protection 100%
- **Features:** 10+ major features implemented
- **Deployment:** Production-ready on AWS EC2

---

**💡 Tip: Keep this file open during your presentation for quick reference!**
