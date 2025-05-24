# 🚀 HOMMSS Migration Checklist: Windows to Ubuntu Apache
## Complete Migration Guide with Cloudflare Integration

This checklist ensures a smooth migration from your current Windows setup to Ubuntu Apache on AWS.

---

## 📋 **PRE-MIGRATION CHECKLIST**

### **✅ Domain & DNS Preparation**
- [ ] Domain connected to Cloudflare ✅ (Already done)
- [ ] Cloudflare SSL/TLS mode set to **Full (strict)**
- [ ] DNS A record ready to point to new AWS instance
- [ ] Backup current DNS settings

### **✅ AWS Instance Preparation**
- [ ] Ubuntu 22.04 LTS instance launched
- [ ] Security groups configured (HTTP:80, HTTPS:443, SSH:22)
- [ ] Elastic IP assigned (recommended)
- [ ] SSH key pair created and downloaded

### **✅ Backup Current System**
- [ ] Database backup created
- [ ] Website files backed up
- [ ] Configuration files saved
- [ ] SSL certificates backed up (if any)

---

## 🛠️ **MIGRATION STEPS**

### **Step 1: Server Setup (30 minutes)**
```bash
# Connect to your Ubuntu instance
ssh -i your-key.pem ubuntu@your-server-ip

# Run the deployment script
sudo bash deploy.sh
```

**What this does:**
- ✅ Installs Apache, PHP 8.2, MySQL
- ✅ Configures Apache modules
- ✅ Sets up security hardening
- ✅ Creates database and user

### **Step 2: SSL Certificate Setup (15 minutes)**
```bash
# Get Cloudflare Origin Certificate
# 1. Go to Cloudflare Dashboard > SSL/TLS > Origin Server
# 2. Create Certificate (15 years validity)
# 3. Upload to server:

sudo nano /etc/ssl/certs/cloudflare-origin.pem
# Paste certificate content

sudo nano /etc/ssl/private/cloudflare-origin.key
# Paste private key content

# Set permissions
sudo chmod 644 /etc/ssl/certs/cloudflare-origin.pem
sudo chmod 600 /etc/ssl/private/cloudflare-origin.key
```

### **Step 3: Project Deployment (20 minutes)**
```bash
# Upload your HOMMSS project
# Option 1: Git clone
cd /var/www
sudo git clone https://github.com/yourusername/HOMMSS-PHP.git hommss

# Option 2: SCP upload
scp -r -i your-key.pem /path/to/HOMMSS-PHP ubuntu@your-server-ip:/tmp/
sudo mv /tmp/HOMMSS-PHP/* /var/www/hommss/

# Set ownership and permissions
sudo chown -R www-data:www-data /var/www/hommss
sudo chmod -R 755 /var/www/hommss
```

### **Step 4: Environment Configuration (10 minutes)**
```bash
cd /var/www/hommss

# Copy production environment
sudo -u www-data cp deployment/ubuntu-apache/.env.production .env

# Edit with your settings
sudo nano .env

# Generate new application key
sudo -u www-data php artisan key:generate
```

**Critical .env Updates:**
```bash
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_DATABASE=hommss_production
DB_USERNAME=hommss_user
DB_PASSWORD=your_secure_password

FORCE_HTTPS=true
HSTS_ENABLED=true
SECURE_COOKIES=true
```

### **Step 5: Database Migration (15 minutes)**
```bash
# Install dependencies
sudo -u www-data composer install --optimize-autoloader --no-dev

# Run migrations
sudo -u www-data php artisan migrate --force

# Seed database (if needed)
sudo -u www-data php artisan db:seed --force

# Create storage link
sudo -u www-data php artisan storage:link

# Cache configuration
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
```

### **Step 6: Apache Configuration (10 minutes)**
```bash
# Copy virtual host configuration
sudo cp deployment/ubuntu-apache/apache-vhost.conf /etc/apache2/sites-available/hommss.conf

# Update domain name in config
sudo sed -i 's/yourdomain.com/your-actual-domain.com/g' /etc/apache2/sites-available/hommss.conf

# Enable site
sudo a2ensite hommss.conf
sudo a2dissite 000-default.conf

# Test and restart
sudo apache2ctl configtest
sudo systemctl restart apache2
```

### **Step 7: Security Hardening (20 minutes)**
```bash
# Run security hardening script
sudo bash deployment/ubuntu-apache/security-hardening.sh

# Configure firewall
sudo ufw allow ssh
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw --force enable
```

### **Step 8: DNS Cutover (5 minutes)**
```bash
# In Cloudflare Dashboard:
# 1. Update A record to point to new server IP
# 2. Wait for DNS propagation (usually 1-5 minutes with Cloudflare)
```

---

## 🧪 **TESTING CHECKLIST**

### **✅ Basic Functionality**
- [ ] Website loads at https://yourdomain.com
- [ ] HTTP redirects to HTTPS
- [ ] Admin panel accessible (/admin)
- [ ] User registration/login works
- [ ] OTP system functional

### **✅ E-Commerce Features**
- [ ] Product browsing works
- [ ] Shopping cart functional
- [ ] Checkout process works
- [ ] Payment gateways configured
- [ ] Order management working

### **✅ Security Verification**
```bash
# Test security headers
curl -I https://yourdomain.com

# Check SSL rating
# Visit: https://www.ssllabs.com/ssltest/

# Verify file permissions
ls -la /var/www/hommss/
```

### **✅ Performance Testing**
```bash
# Check Apache status
sudo systemctl status apache2

# Check PHP-FPM status
sudo systemctl status php8.2-fpm

# Check MySQL status
sudo systemctl status mysql

# Monitor logs
sudo tail -f /var/log/apache2/hommss_ssl_access.log
```

---

## 🔧 **POST-MIGRATION TASKS**

### **✅ Production Configuration**
- [ ] Update payment gateway keys to production
- [ ] Configure email settings
- [ ] Set up monitoring alerts
- [ ] Configure backup schedule
- [ ] Update Google OAuth credentials

### **✅ Cloudflare Optimization**
```bash
# Recommended Cloudflare settings:
# 1. SSL/TLS: Full (strict)
# 2. Always Use HTTPS: On
# 3. HSTS: Enabled
# 4. Security Level: Medium
# 5. Bot Fight Mode: On
# 6. Caching Level: Standard
```

### **✅ Monitoring Setup**
```bash
# Set up log monitoring
sudo tail -f /var/log/apache2/hommss_ssl_error.log

# Monitor security events
sudo tail -f /var/log/hommss-security.log

# Check backup status
sudo -u www-data php artisan app:backup-database --filename=test-backup
```

---

## 🚨 **TROUBLESHOOTING GUIDE**

### **Common Issues & Solutions**

#### **🔴 Website Not Loading**
```bash
# Check Apache status
sudo systemctl status apache2

# Check error logs
sudo tail -f /var/log/apache2/error.log

# Verify virtual host
sudo apache2ctl -S
```

#### **🔴 SSL Certificate Issues**
```bash
# Verify certificate files
sudo ls -la /etc/ssl/certs/cloudflare-origin.pem
sudo ls -la /etc/ssl/private/cloudflare-origin.key

# Check certificate validity
sudo openssl x509 -in /etc/ssl/certs/cloudflare-origin.pem -text -noout
```

#### **🔴 Database Connection Issues**
```bash
# Test database connection
cd /var/www/hommss
sudo -u www-data php artisan tinker
# Then run: DB::connection()->getPdo();

# Check MySQL status
sudo systemctl status mysql

# Review database logs
sudo tail -f /var/log/mysql/error.log
```

#### **🔴 Permission Issues**
```bash
# Fix ownership
sudo chown -R www-data:www-data /var/www/hommss

# Fix permissions
sudo chmod -R 755 /var/www/hommss
sudo chmod -R 775 storage bootstrap/cache
sudo chmod 600 .env
```

#### **🔴 Laravel Application Issues**
```bash
# Clear all caches
sudo -u www-data php artisan cache:clear
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan route:clear
sudo -u www-data php artisan view:clear

# Check Laravel logs
sudo tail -f /var/www/hommss/storage/logs/laravel.log
```

---

## 📞 **SUPPORT CONTACTS**

### **Emergency Contacts**
- **Technical Issues**: Check Laravel logs first
- **Server Issues**: Check Apache/MySQL logs
- **Security Issues**: Review Fail2Ban logs
- **DNS Issues**: Check Cloudflare dashboard

### **Useful Commands**
```bash
# Restart all services
sudo systemctl restart apache2 php8.2-fpm mysql

# Check all service status
sudo systemctl status apache2 php8.2-fpm mysql

# Monitor real-time logs
sudo tail -f /var/log/apache2/hommss_ssl_access.log

# Check disk space
df -h

# Check memory usage
free -h

# Check running processes
top
```

---

## 🎉 **MIGRATION COMPLETE!**

### **✅ What You've Achieved:**
- ✅ **Secure Ubuntu Apache deployment**
- ✅ **Cloudflare SSL integration**
- ✅ **Production-ready configuration**
- ✅ **Enhanced security hardening**
- ✅ **Automated backup system**
- ✅ **Performance optimization**

### **🚀 Next Steps:**
1. **Monitor performance** for 24-48 hours
2. **Test all e-commerce functionality**
3. **Configure production payment gateways**
4. **Set up external monitoring**
5. **Create disaster recovery plan**

**Your HOMMSS e-commerce platform is now live on Ubuntu Apache with enterprise-grade security!** 🎊

---

*Migration completed successfully! Your website should now be faster, more secure, and ready for production traffic.*
