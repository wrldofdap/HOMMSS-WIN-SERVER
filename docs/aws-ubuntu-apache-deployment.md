# 🚀 HOMMSS AWS Ubuntu Apache Deployment Guide
## Complete Migration from Windows to Ubuntu Server

This guide will help you deploy your HOMMSS Laravel e-commerce platform on an Ubuntu AWS instance with Apache, while maintaining all security features and functionality.

---

## 📋 **PREREQUISITES**

### **AWS Ubuntu Instance Requirements**
- **Ubuntu 22.04 LTS** (recommended)
- **Minimum**: t3.medium (2 vCPU, 4GB RAM)
- **Recommended**: t3.large (2 vCPU, 8GB RAM) for production
- **Storage**: 20GB+ SSD
- **Security Groups**: HTTP (80), HTTPS (443), SSH (22)

### **Domain & SSL Setup**
- ✅ Domain connected to Cloudflare
- ✅ Cloudflare SSL/TLS encryption mode: **Full (strict)**
- ✅ DNS A record pointing to AWS instance IP

---

## 🛠️ **STEP 1: SERVER PREPARATION**

### **Update System & Install Dependencies**
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install Apache
sudo apt install apache2 -y

# Install PHP 8.2 and extensions
sudo apt install software-properties-common -y
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install php8.2 php8.2-fpm php8.2-mysql php8.2-xml php8.2-gd php8.2-curl php8.2-mbstring php8.2-zip php8.2-intl php8.2-bcmath -y

# Install MySQL
sudo apt install mysql-server -y

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Node.js and NPM
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install nodejs -y

# Install Git
sudo apt install git -y

# Install additional tools
sudo apt install unzip curl wget -y
```

### **Configure Apache Modules**
```bash
# Enable required Apache modules
sudo a2enmod rewrite
sudo a2enmod ssl
sudo a2enmod headers
sudo a2enmod deflate
sudo a2enmod expires

# Enable PHP-FPM
sudo a2enmod proxy_fcgi setenvif
sudo a2enconf php8.2-fpm

# Restart Apache
sudo systemctl restart apache2
```

---

## 🔧 **STEP 2: PROJECT DEPLOYMENT**

### **Upload Project Files**
```bash
# Create web directory
sudo mkdir -p /var/www/hommss

# Upload your project (use SCP, SFTP, or Git)
# Option 1: Using Git (recommended)
cd /var/www
sudo git clone https://github.com/yourusername/HOMMSS-PHP.git hommss

# Option 2: Upload via SCP from your local machine
# scp -r /path/to/HOMMSS-PHP ubuntu@your-server-ip:/tmp/
# sudo mv /tmp/HOMMSS-PHP/* /var/www/hommss/

# Set ownership
sudo chown -R www-data:www-data /var/www/hommss
sudo chmod -R 755 /var/www/hommss
```

### **Install Dependencies**
```bash
cd /var/www/hommss

# Install PHP dependencies
sudo -u www-data composer install --optimize-autoloader --no-dev

# Install Node dependencies and build assets
sudo -u www-data npm install
sudo -u www-data npm run build

# Set proper permissions
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R www-data:www-data storage bootstrap/cache
```

---

## 🗄️ **STEP 3: DATABASE SETUP**

### **Configure MySQL**
```bash
# Secure MySQL installation
sudo mysql_secure_installation

# Create database and user
sudo mysql -u root -p
```

```sql
-- Create database
CREATE DATABASE hommss_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create dedicated user
CREATE USER 'hommss_user'@'localhost' IDENTIFIED BY 'your_secure_password_here';
GRANT ALL PRIVILEGES ON hommss_production.* TO 'hommss_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### **Configure Environment**
```bash
cd /var/www/hommss

# Copy environment file
sudo -u www-data cp .env.example .env.production
sudo -u www-data mv .env.production .env

# Generate application key
sudo -u www-data php artisan key:generate

# Edit environment file
sudo nano .env
```

---

## ⚙️ **STEP 4: ENVIRONMENT CONFIGURATION**

### **Production .env Settings**
```bash
# Application Settings
APP_NAME="HOMMSS E-Commerce"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hommss_production
DB_USERNAME=hommss_user
DB_PASSWORD=your_secure_password_here

# Security Settings (IMPORTANT!)
FORCE_HTTPS=true
HSTS_ENABLED=true
SECURE_COOKIES=true
UPGRADE_INSECURE_REQUESTS=true

# Session Configuration
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=strict

# Cache & Queue
CACHE_STORE=database
QUEUE_CONNECTION=database

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=hommss666@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="hommss666@gmail.com"
MAIL_FROM_NAME="${APP_NAME}"

# Backup Configuration
BACKUP_PASSWORD=your_secure_backup_password
ADMIN_EMAIL=admin@yourdomain.com

# Payment Gateways (Update with production keys)
STRIPE_KEY=pk_live_your_stripe_key
STRIPE_SECRET=sk_live_your_stripe_secret
PAYMONGO_PUBLIC_KEY=pk_live_your_paymongo_key
PAYMONGO_SECRET_KEY=sk_live_your_paymongo_secret

# MySQL Binary Path for Ubuntu
MYSQL_DUMP_BINARY_PATH="/usr/bin"
```

---

## 🌐 **STEP 5: APACHE VIRTUAL HOST CONFIGURATION**

### **Create Virtual Host File**
```bash
sudo nano /etc/apache2/sites-available/hommss.conf
```

```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com
    DocumentRoot /var/www/hommss/public
    
    # Redirect all HTTP to HTTPS
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
    
    ErrorLog ${APACHE_LOG_DIR}/hommss_error.log
    CustomLog ${APACHE_LOG_DIR}/hommss_access.log combined
</VirtualHost>

<VirtualHost *:443>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com
    DocumentRoot /var/www/hommss/public
    
    # SSL Configuration (Cloudflare Origin Certificate)
    SSLEngine on
    SSLCertificateFile /etc/ssl/certs/cloudflare-origin.pem
    SSLCertificateKeyFile /etc/ssl/private/cloudflare-origin.key
    
    # Security Headers
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
    Header always set Permissions-Policy "geolocation=(), microphone=(), camera=()"
    
    # HSTS Header (handled by Laravel middleware, but backup)
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains; preload"
    
    # Directory Configuration
    <Directory /var/www/hommss/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
        
        # Additional security
        <Files ".env">
            Require all denied
        </Files>
        
        <Files "*.log">
            Require all denied
        </Files>
    </Directory>
    
    # Deny access to sensitive directories
    <Directory /var/www/hommss/storage>
        Require all denied
    </Directory>
    
    <Directory /var/www/hommss/bootstrap/cache>
        Require all denied
    </Directory>
    
    # PHP-FPM Configuration
    <FilesMatch \.php$>
        SetHandler "proxy:unix:/var/run/php/php8.2-fpm.sock|fcgi://localhost"
    </FilesMatch>
    
    # Logging
    ErrorLog ${APACHE_LOG_DIR}/hommss_ssl_error.log
    CustomLog ${APACHE_LOG_DIR}/hommss_ssl_access.log combined
</VirtualHost>
```

### **Enable Site and Restart Apache**
```bash
# Enable the site
sudo a2ensite hommss.conf

# Disable default site
sudo a2dissite 000-default.conf

# Test configuration
sudo apache2ctl configtest

# Restart Apache
sudo systemctl restart apache2
```

---

## 🔒 **STEP 6: SSL CERTIFICATE SETUP**

### **Option 1: Cloudflare Origin Certificate (Recommended)**
```bash
# Create SSL directory
sudo mkdir -p /etc/ssl/cloudflare

# Upload Cloudflare Origin Certificate
# 1. Go to Cloudflare Dashboard > SSL/TLS > Origin Server
# 2. Create Certificate (15 years validity)
# 3. Copy certificate content to:
sudo nano /etc/ssl/certs/cloudflare-origin.pem

# 4. Copy private key content to:
sudo nano /etc/ssl/private/cloudflare-origin.key

# Set proper permissions
sudo chmod 644 /etc/ssl/certs/cloudflare-origin.pem
sudo chmod 600 /etc/ssl/private/cloudflare-origin.key
sudo chown root:root /etc/ssl/certs/cloudflare-origin.pem
sudo chown root:root /etc/ssl/private/cloudflare-origin.key
```

### **Option 2: Let's Encrypt (Alternative)**
```bash
# Install Certbot
sudo apt install certbot python3-certbot-apache -y

# Get certificate (only if not using Cloudflare proxy)
sudo certbot --apache -d yourdomain.com -d www.yourdomain.com
```

---

## 🚀 **STEP 7: FINALIZE DEPLOYMENT**

### **Run Laravel Setup Commands**
```bash
cd /var/www/hommss

# Run migrations
sudo -u www-data php artisan migrate --force

# Seed database (if needed)
sudo -u www-data php artisan db:seed --force

# Create storage link
sudo -u www-data php artisan storage:link

# Clear and cache configuration
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache

# Set final permissions
sudo chmod -R 755 /var/www/hommss
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R www-data:www-data /var/www/hommss
```

### **Configure Automatic Backups**
```bash
# Add to crontab for automated backups
sudo crontab -e

# Add this line for daily backups at 2 AM
0 2 * * * cd /var/www/hommss && php artisan app:backup-database --filename=daily-backup-$(date +\%Y\%m\%d) >> /var/log/hommss-backup.log 2>&1
```

---

## ✅ **STEP 8: TESTING & VERIFICATION**

### **Test Website Functionality**
```bash
# Check Apache status
sudo systemctl status apache2

# Check PHP-FPM status
sudo systemctl status php8.2-fpm

# Check MySQL status
sudo systemctl status mysql

# Test website
curl -I https://yourdomain.com

# Check Laravel logs
tail -f /var/www/hommss/storage/logs/laravel.log
```

### **Security Verification**
```bash
# Test security headers
curl -I https://yourdomain.com

# Test HTTPS redirect
curl -I http://yourdomain.com

# Check file permissions
ls -la /var/www/hommss/
```

---

## 🔧 **TROUBLESHOOTING**

### **Common Issues & Solutions**

#### **Permission Issues**
```bash
# Fix ownership
sudo chown -R www-data:www-data /var/www/hommss

# Fix permissions
sudo chmod -R 755 /var/www/hommss
sudo chmod -R 775 storage bootstrap/cache
```

#### **Apache Issues**
```bash
# Check Apache error logs
sudo tail -f /var/log/apache2/error.log

# Check site-specific logs
sudo tail -f /var/log/apache2/hommss_error.log
```

#### **PHP Issues**
```bash
# Check PHP-FPM logs
sudo tail -f /var/log/php8.2-fpm.log

# Restart PHP-FPM
sudo systemctl restart php8.2-fpm
```

#### **Database Issues**
```bash
# Check MySQL status
sudo systemctl status mysql

# Test database connection
cd /var/www/hommss
sudo -u www-data php artisan tinker
# Then run: DB::connection()->getPdo();
```

---

## 🎯 **CLOUDFLARE CONFIGURATION**

### **Recommended Cloudflare Settings**
1. **SSL/TLS**: Full (strict)
2. **Always Use HTTPS**: On
3. **HSTS**: Enabled
4. **Security Level**: Medium
5. **Bot Fight Mode**: On
6. **WAF**: Custom rules for admin protection

### **Cloudflare Page Rules**
```
Rule 1: yourdomain.com/admin/*
- Security Level: High
- Cache Level: Bypass

Rule 2: yourdomain.com/*
- SSL: Full (strict)
- Always Use HTTPS: On
```

---

## 🏆 **DEPLOYMENT COMPLETE!**

Your HOMMSS e-commerce platform is now successfully deployed on Ubuntu with Apache! 

### **What's Been Achieved:**
- ✅ **Secure HTTPS** with Cloudflare integration
- ✅ **Production-ready** Laravel configuration
- ✅ **Apache optimization** with security headers
- ✅ **Database security** with dedicated user
- ✅ **Automated backups** configured
- ✅ **All security features** maintained

### **Next Steps:**
1. Test all e-commerce functionality
2. Configure payment gateways with production keys
3. Set up monitoring and alerts
4. Create regular backup verification
5. Implement additional security monitoring

**Your website should now be live at: https://yourdomain.com** 🎉
