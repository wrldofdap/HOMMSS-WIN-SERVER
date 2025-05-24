# 🚀 HOMMSS Ubuntu Apache Deployment Package
## Complete Migration Solution for AWS Ubuntu with Cloudflare

This deployment package contains everything you need to migrate your HOMMSS Laravel e-commerce platform from Windows to Ubuntu Apache on AWS, with full Cloudflare integration.

---

## 📦 **PACKAGE CONTENTS**

### **🔧 Configuration Files**
- **`apache-vhost.conf`** - Production-ready Apache virtual host configuration
- **`.env.production`** - Complete production environment template
- **`php-fpm-pool.conf`** - Optimized PHP-FPM pool configuration
- **`mysql-optimization.cnf`** - MySQL performance optimization settings

### **🚀 Deployment Scripts**
- **`deploy.sh`** - Complete automated deployment script
- **`quick-start.sh`** - One-command deployment for quick setup
- **`security-hardening.sh`** - Advanced security configuration script

### **📚 Documentation**
- **`MIGRATION-CHECKLIST.md`** - Step-by-step migration guide
- **`README.md`** - This file with complete instructions

---

## ⚡ **QUICK START (Recommended)**

### **Option 1: One-Command Deployment**
```bash
# Upload this entire deployment folder to your Ubuntu server
scp -r deployment/ubuntu-apache ubuntu@your-server-ip:/tmp/

# SSH into your server
ssh ubuntu@your-server-ip

# Run the quick start script
cd /tmp/ubuntu-apache
sudo bash quick-start.sh
```

### **Option 2: Manual Step-by-Step**
```bash
# Follow the detailed migration checklist
cat MIGRATION-CHECKLIST.md
```

---

## 🎯 **WHAT THIS DEPLOYMENT PROVIDES**

### **✅ Complete Server Setup**
- **Ubuntu 22.04 LTS** optimized configuration
- **Apache 2.4** with security modules enabled
- **PHP 8.2** with all required extensions
- **MySQL 8.0** with performance optimization
- **SSL/TLS** with Cloudflare Origin Certificate support

### **✅ Security Hardening**
- **Fail2Ban** protection against brute force attacks
- **ModSecurity** with OWASP Core Rule Set
- **UFW Firewall** properly configured
- **File permissions** secured according to best practices
- **Security headers** implemented
- **Antivirus scanning** with ClamAV

### **✅ Performance Optimization**
- **PHP-FPM** optimized for e-commerce workloads
- **Apache modules** configured for performance
- **MySQL** tuned for Laravel applications
- **Caching** enabled (OPcache, browser caching)
- **Compression** enabled (gzip/deflate)

### **✅ Monitoring & Maintenance**
- **Automated backups** with encryption
- **Log monitoring** and alerting
- **Security monitoring** scripts
- **Automatic security updates**
- **Health check** endpoints

---

## 🔧 **CUSTOMIZATION GUIDE**

### **Domain Configuration**
1. **Update `apache-vhost.conf`**:
   ```bash
   sed -i 's/yourdomain.com/your-actual-domain.com/g' apache-vhost.conf
   ```

2. **Update `.env.production`**:
   ```bash
   sed -i 's/yourdomain.com/your-actual-domain.com/g' .env.production
   ```

### **Database Configuration**
1. **Update database credentials** in `.env.production`
2. **Modify MySQL settings** in `mysql-optimization.cnf` if needed
3. **Adjust PHP-FPM pool** settings in `php-fpm-pool.conf` based on server resources

### **Security Configuration**
1. **Update email addresses** in security scripts
2. **Customize Fail2Ban** rules in `security-hardening.sh`
3. **Adjust firewall rules** if needed

---

## 📋 **PREREQUISITES**

### **AWS Instance Requirements**
- **OS**: Ubuntu 22.04 LTS
- **Instance Type**: t3.medium or larger (recommended: t3.large)
- **Storage**: 20GB+ SSD
- **Security Groups**: HTTP (80), HTTPS (443), SSH (22)

### **Domain & DNS**
- Domain connected to Cloudflare
- Cloudflare SSL/TLS mode: **Full (strict)**
- DNS A record ready to update

### **Local Requirements**
- SSH access to Ubuntu server
- SCP/SFTP capability for file upload
- Basic command line knowledge

---

## 🚀 **DEPLOYMENT PROCESS**

### **Phase 1: Preparation (10 minutes)**
1. Launch Ubuntu 22.04 LTS instance on AWS
2. Configure security groups
3. Upload deployment files to server
4. Prepare Cloudflare Origin Certificate

### **Phase 2: Server Setup (30 minutes)**
1. Run deployment script
2. Configure Apache virtual host
3. Set up SSL certificates
4. Configure database

### **Phase 3: Application Deployment (20 minutes)**
1. Upload/clone HOMMSS project
2. Install dependencies
3. Configure environment
4. Run migrations

### **Phase 4: Security Hardening (20 minutes)**
1. Run security hardening script
2. Configure firewall
3. Set up monitoring
4. Test security measures

### **Phase 5: DNS Cutover (5 minutes)**
1. Update DNS A record in Cloudflare
2. Test website functionality
3. Verify SSL certificate

**Total Time: ~85 minutes**

---

## 🧪 **TESTING CHECKLIST**

### **✅ Basic Functionality**
```bash
# Test website loading
curl -I https://yourdomain.com

# Test HTTP to HTTPS redirect
curl -I http://yourdomain.com

# Test Apache status
sudo systemctl status apache2

# Test PHP-FPM status
sudo systemctl status php8.2-fpm

# Test MySQL status
sudo systemctl status mysql
```

### **✅ Security Verification**
```bash
# Check security headers
curl -I https://yourdomain.com | grep -E "(X-|Strict-Transport)"

# Test SSL rating
# Visit: https://www.ssllabs.com/ssltest/

# Check Fail2Ban status
sudo fail2ban-client status

# Verify firewall
sudo ufw status
```

### **✅ Laravel Application**
```bash
cd /var/www/hommss

# Test Laravel
sudo -u www-data php artisan --version

# Check database connection
sudo -u www-data php artisan tinker
# Then: DB::connection()->getPdo();

# Test backup system
sudo -u www-data php artisan app:backup-database --filename=test-backup
```

---

## 🔧 **TROUBLESHOOTING**

### **Common Issues**

#### **Apache Won't Start**
```bash
# Check configuration
sudo apache2ctl configtest

# Check error logs
sudo tail -f /var/log/apache2/error.log

# Verify virtual host
sudo apache2ctl -S
```

#### **SSL Certificate Issues**
```bash
# Check certificate files
sudo ls -la /etc/ssl/certs/cloudflare-origin.pem
sudo ls -la /etc/ssl/private/cloudflare-origin.key

# Verify certificate
sudo openssl x509 -in /etc/ssl/certs/cloudflare-origin.pem -text -noout
```

#### **Database Connection Failed**
```bash
# Test MySQL connection
mysql -u hommss_user -p hommss_production

# Check MySQL logs
sudo tail -f /var/log/mysql/error.log

# Verify user permissions
mysql -u root -p -e "SHOW GRANTS FOR 'hommss_user'@'localhost';"
```

#### **Permission Denied Errors**
```bash
# Fix ownership
sudo chown -R www-data:www-data /var/www/hommss

# Fix permissions
sudo chmod -R 755 /var/www/hommss
sudo chmod -R 775 storage bootstrap/cache
sudo chmod 600 .env
```

---

## 📞 **SUPPORT**

### **Log Files to Check**
- **Apache**: `/var/log/apache2/hommss_ssl_error.log`
- **PHP-FPM**: `/var/log/php8.2-fpm.log`
- **MySQL**: `/var/log/mysql/error.log`
- **Laravel**: `/var/www/hommss/storage/logs/laravel.log`
- **Security**: `/var/log/hommss-security.log`

### **Useful Commands**
```bash
# Restart all services
sudo systemctl restart apache2 php8.2-fpm mysql

# Monitor logs in real-time
sudo tail -f /var/log/apache2/hommss_ssl_access.log

# Check system resources
htop
df -h
free -h

# Laravel maintenance
cd /var/www/hommss
sudo -u www-data php artisan cache:clear
sudo -u www-data php artisan config:cache
```

---

## 🎉 **SUCCESS!**

Once deployment is complete, your HOMMSS e-commerce platform will be:

- ✅ **Running on Ubuntu Apache** with optimal performance
- ✅ **Secured with enterprise-grade** security measures
- ✅ **Protected by Cloudflare** SSL and DDoS protection
- ✅ **Monitored and backed up** automatically
- ✅ **Ready for production** traffic

**Your website will be live at: `https://yourdomain.com`** 🚀

---

*This deployment package ensures a smooth, secure, and professional migration from Windows to Ubuntu Apache on AWS.*
