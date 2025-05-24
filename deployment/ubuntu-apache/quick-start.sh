#!/bin/bash

# HOMMSS E-Commerce Quick Start Script
# One-command deployment for Ubuntu Apache

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
NC='\033[0m'

# ASCII Art Banner
echo -e "${PURPLE}"
cat << "EOF"
 _   _  ___  __  __ __  __ _____ _____ 
| | | |/ _ \|  \/  |  \/  /  ___/  ___|
| |_| | | | | .  . | .  . \ `--.\ `--. 
|  _  | | | | |\/| | |\/| |`--. \`--. \
| | | | |_| | |  | | |  | /\__/ /\__/ /
\_| |_/\___/\_|  |_\_|  |_\____/\____/ 

E-Commerce Platform - Ubuntu Apache Deployment
EOF
echo -e "${NC}"

print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

print_header() {
    echo -e "\n${PURPLE}=== $1 ===${NC}\n"
}

# Check if running as root
if [[ $EUID -ne 0 ]]; then
    print_error "This script must be run as root (use sudo)"
    exit 1
fi

print_header "HOMMSS E-Commerce Quick Start Deployment"

# Get user inputs
print_status "Please provide the following information:"

read -p "Enter your domain name (e.g., example.com): " DOMAIN_NAME
read -p "Enter database name [hommss_production]: " DB_NAME
DB_NAME=${DB_NAME:-hommss_production}
read -p "Enter database username [hommss_user]: " DB_USER
DB_USER=${DB_USER:-hommss_user}
read -s -p "Enter database password: " DB_PASS
echo
read -p "Enter your email for notifications: " ADMIN_EMAIL
read -p "Enter Git repository URL (or press Enter to skip): " GIT_REPO

print_header "Starting Deployment Process"

# Step 1: System Update and Dependencies
print_status "Installing system dependencies..."
apt update && apt upgrade -y

# Install Apache
apt install apache2 -y

# Install PHP 8.2
apt install software-properties-common -y
add-apt-repository ppa:ondrej/php -y
apt update
apt install php8.2 php8.2-fpm php8.2-mysql php8.2-xml php8.2-gd \
            php8.2-curl php8.2-mbstring php8.2-zip php8.2-intl \
            php8.2-bcmath php8.2-soap php8.2-readline -y

# Install MySQL
apt install mysql-server -y

# Install additional tools
apt install unzip curl wget git fail2ban ufw -y

# Install Composer
if ! command -v composer &> /dev/null; then
    curl -sS https://getcomposer.org/installer | php
    mv composer.phar /usr/local/bin/composer
    chmod +x /usr/local/bin/composer
fi

# Install Node.js
if ! command -v node &> /dev/null; then
    curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
    apt install nodejs -y
fi

print_success "Dependencies installed"

# Step 2: Configure Apache
print_status "Configuring Apache..."
a2enmod rewrite ssl headers deflate expires proxy_fcgi setenvif
a2enconf php8.2-fpm

# Create virtual host
cat > /etc/apache2/sites-available/hommss.conf << EOF
<VirtualHost *:80>
    ServerName $DOMAIN_NAME
    ServerAlias www.$DOMAIN_NAME
    DocumentRoot /var/www/hommss/public
    
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
    
    ErrorLog \${APACHE_LOG_DIR}/hommss_error.log
    CustomLog \${APACHE_LOG_DIR}/hommss_access.log combined
</VirtualHost>

<VirtualHost *:443>
    ServerName $DOMAIN_NAME
    ServerAlias www.$DOMAIN_NAME
    DocumentRoot /var/www/hommss/public
    
    SSLEngine on
    SSLCertificateFile /etc/ssl/certs/cloudflare-origin.pem
    SSLCertificateKeyFile /etc/ssl/private/cloudflare-origin.key
    
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains; preload"
    
    <Directory /var/www/hommss/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    <FilesMatch \.php$>
        SetHandler "proxy:unix:/var/run/php/php8.2-fpm.sock|fcgi://localhost"
    </FilesMatch>
    
    ErrorLog \${APACHE_LOG_DIR}/hommss_ssl_error.log
    CustomLog \${APACHE_LOG_DIR}/hommss_ssl_access.log combined
</VirtualHost>
EOF

a2ensite hommss.conf
a2dissite 000-default.conf

print_success "Apache configured"

# Step 3: Setup Database
print_status "Setting up database..."
mysql -u root << EOF
CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';
GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';
FLUSH PRIVILEGES;
EOF

print_success "Database configured"

# Step 4: Deploy Application
print_status "Deploying application..."
mkdir -p /var/www/hommss

if [ ! -z "$GIT_REPO" ]; then
    git clone $GIT_REPO /var/www/hommss
else
    print_warning "Please upload your HOMMSS project files to /var/www/hommss/"
    read -p "Press Enter after uploading the files..."
fi

cd /var/www/hommss

# Set ownership
chown -R www-data:www-data /var/www/hommss
chmod -R 755 /var/www/hommss

# Install dependencies
sudo -u www-data composer install --optimize-autoloader --no-dev
sudo -u www-data npm install
sudo -u www-data npm run build

# Setup environment
if [ -f "deployment/ubuntu-apache/.env.production" ]; then
    sudo -u www-data cp deployment/ubuntu-apache/.env.production .env
else
    sudo -u www-data cp .env.example .env
fi

# Update .env with user inputs
sed -i "s/yourdomain.com/$DOMAIN_NAME/g" .env
sed -i "s/hommss_production/$DB_NAME/g" .env
sed -i "s/hommss_user/$DB_USER/g" .env
sed -i "s/your_secure_database_password_here/$DB_PASS/g" .env
sed -i "s/admin@yourdomain.com/$ADMIN_EMAIL/g" .env

# Generate key
sudo -u www-data php artisan key:generate

# Set permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
chmod 600 .env

print_success "Application deployed"

# Step 5: Laravel Setup
print_status "Configuring Laravel..."
sudo -u www-data php artisan migrate --force
sudo -u www-data php artisan storage:link
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache

print_success "Laravel configured"

# Step 6: Security Setup
print_status "Configuring security..."

# Configure Fail2Ban
cat > /etc/fail2ban/jail.local << 'EOF'
[DEFAULT]
bantime = 3600
findtime = 600
maxretry = 5

[sshd]
enabled = true

[apache-auth]
enabled = true
port = http,https
logpath = %(apache_error_log)s

[apache-badbots]
enabled = true
port = http,https
logpath = %(apache_access_log)s
EOF

# Configure UFW
ufw default deny incoming
ufw default allow outgoing
ufw allow ssh
ufw allow 80/tcp
ufw allow 443/tcp
ufw --force enable

print_success "Security configured"

# Step 7: Start Services
print_status "Starting services..."
systemctl restart apache2
systemctl restart php8.2-fpm
systemctl restart mysql
systemctl restart fail2ban

systemctl enable apache2
systemctl enable php8.2-fpm
systemctl enable mysql
systemctl enable fail2ban

print_success "Services started"

# Step 8: SSL Certificate Setup
print_header "SSL Certificate Setup Required"
print_warning "Please complete the SSL setup:"
print_warning "1. Go to Cloudflare Dashboard > SSL/TLS > Origin Server"
print_warning "2. Create Certificate (15 years validity)"
print_warning "3. Copy certificate to: /etc/ssl/certs/cloudflare-origin.pem"
print_warning "4. Copy private key to: /etc/ssl/private/cloudflare-origin.key"
print_warning "5. Set permissions:"
print_warning "   sudo chmod 644 /etc/ssl/certs/cloudflare-origin.pem"
print_warning "   sudo chmod 600 /etc/ssl/private/cloudflare-origin.key"

read -p "Press Enter after setting up SSL certificates..."

# Test Apache configuration
apache2ctl configtest
systemctl restart apache2

# Final tests
print_header "Running Final Tests"

# Test services
if systemctl is-active --quiet apache2; then
    print_success "Apache is running"
else
    print_error "Apache is not running"
fi

if systemctl is-active --quiet php8.2-fpm; then
    print_success "PHP-FPM is running"
else
    print_error "PHP-FPM is not running"
fi

if systemctl is-active --quiet mysql; then
    print_success "MySQL is running"
else
    print_error "MySQL is not running"
fi

# Test Laravel
cd /var/www/hommss
if sudo -u www-data php artisan --version > /dev/null 2>&1; then
    print_success "Laravel application is working"
else
    print_error "Laravel application has issues"
fi

print_header "Deployment Complete!"

print_success "HOMMSS E-Commerce has been successfully deployed!"
print_status "Your website should be accessible at: https://$DOMAIN_NAME"

print_warning "Next Steps:"
print_warning "1. Update DNS A record to point to this server"
print_warning "2. Test website functionality"
print_warning "3. Configure payment gateways"
print_warning "4. Set up monitoring"

print_status "Useful commands:"
echo "  - Check logs: sudo tail -f /var/log/apache2/hommss_ssl_error.log"
echo "  - Restart services: sudo systemctl restart apache2 php8.2-fpm mysql"
echo "  - Laravel commands: cd /var/www/hommss && sudo -u www-data php artisan [command]"

print_success "Deployment completed successfully! 🎉"
