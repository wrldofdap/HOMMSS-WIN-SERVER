#!/bin/bash

# HOMMSS E-Commerce Deployment Script for Ubuntu Apache
# This script automates the deployment process

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
PROJECT_NAME="hommss"
WEB_ROOT="/var/www/$PROJECT_NAME"
APACHE_SITES_DIR="/etc/apache2/sites-available"
SSL_CERT_DIR="/etc/ssl/certs"
SSL_KEY_DIR="/etc/ssl/private"
BACKUP_DIR="/var/backups/hommss"

# Functions
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

check_root() {
    if [[ $EUID -ne 0 ]]; then
        print_error "This script must be run as root (use sudo)"
        exit 1
    fi
}

check_ubuntu() {
    if ! grep -q "Ubuntu" /etc/os-release; then
        print_error "This script is designed for Ubuntu systems"
        exit 1
    fi
}

install_dependencies() {
    print_status "Installing system dependencies..."
    
    # Update system
    apt update && apt upgrade -y
    
    # Install Apache
    apt install apache2 -y
    
    # Install PHP 8.2 and extensions
    apt install software-properties-common -y
    add-apt-repository ppa:ondrej/php -y
    apt update
    apt install php8.2 php8.2-fpm php8.2-mysql php8.2-xml php8.2-gd \
                php8.2-curl php8.2-mbstring php8.2-zip php8.2-intl \
                php8.2-bcmath php8.2-soap php8.2-readline -y
    
    # Install MySQL
    apt install mysql-server -y
    
    # Install additional tools
    apt install unzip curl wget git -y
    
    # Install Composer
    if ! command -v composer &> /dev/null; then
        curl -sS https://getcomposer.org/installer | php
        mv composer.phar /usr/local/bin/composer
        chmod +x /usr/local/bin/composer
    fi
    
    # Install Node.js and NPM
    if ! command -v node &> /dev/null; then
        curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
        apt install nodejs -y
    fi
    
    print_success "Dependencies installed successfully"
}

configure_apache() {
    print_status "Configuring Apache..."
    
    # Enable required modules
    a2enmod rewrite ssl headers deflate expires proxy_fcgi setenvif
    a2enconf php8.2-fpm
    
    # Copy virtual host configuration
    if [ -f "apache-vhost.conf" ]; then
        cp apache-vhost.conf $APACHE_SITES_DIR/$PROJECT_NAME.conf
        print_success "Virtual host configuration copied"
    else
        print_error "apache-vhost.conf not found in current directory"
        exit 1
    fi
    
    # Enable site
    a2ensite $PROJECT_NAME.conf
    a2dissite 000-default.conf
    
    # Test configuration
    apache2ctl configtest
    
    print_success "Apache configured successfully"
}

setup_ssl() {
    print_status "Setting up SSL certificates..."
    
    # Create SSL directories
    mkdir -p $SSL_CERT_DIR $SSL_KEY_DIR
    
    print_warning "Please upload your Cloudflare Origin Certificate to:"
    print_warning "Certificate: $SSL_CERT_DIR/cloudflare-origin.pem"
    print_warning "Private Key: $SSL_KEY_DIR/cloudflare-origin.key"
    
    read -p "Press Enter after uploading the SSL certificates..."
    
    # Set proper permissions
    if [ -f "$SSL_CERT_DIR/cloudflare-origin.pem" ]; then
        chmod 644 $SSL_CERT_DIR/cloudflare-origin.pem
        chown root:root $SSL_CERT_DIR/cloudflare-origin.pem
        print_success "SSL certificate permissions set"
    else
        print_error "SSL certificate not found at $SSL_CERT_DIR/cloudflare-origin.pem"
    fi
    
    if [ -f "$SSL_KEY_DIR/cloudflare-origin.key" ]; then
        chmod 600 $SSL_KEY_DIR/cloudflare-origin.key
        chown root:root $SSL_KEY_DIR/cloudflare-origin.key
        print_success "SSL private key permissions set"
    else
        print_error "SSL private key not found at $SSL_KEY_DIR/cloudflare-origin.key"
    fi
}

setup_database() {
    print_status "Setting up database..."
    
    # Secure MySQL installation
    print_warning "Please run mysql_secure_installation manually after this script"
    
    # Get database credentials
    read -p "Enter database name [hommss_production]: " DB_NAME
    DB_NAME=${DB_NAME:-hommss_production}
    
    read -p "Enter database username [hommss_user]: " DB_USER
    DB_USER=${DB_USER:-hommss_user}
    
    read -s -p "Enter database password: " DB_PASS
    echo
    
    # Create database and user
    mysql -u root -p << EOF
CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';
GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';
FLUSH PRIVILEGES;
EOF
    
    print_success "Database setup completed"
}

deploy_application() {
    print_status "Deploying application..."
    
    # Create web directory
    mkdir -p $WEB_ROOT
    
    # Get project source
    read -p "Enter Git repository URL (or press Enter to skip): " GIT_REPO
    
    if [ ! -z "$GIT_REPO" ]; then
        # Clone from Git
        git clone $GIT_REPO $WEB_ROOT
    else
        print_warning "Please upload your project files to $WEB_ROOT"
        read -p "Press Enter after uploading the project files..."
    fi
    
    # Set ownership
    chown -R www-data:www-data $WEB_ROOT
    chmod -R 755 $WEB_ROOT
    
    # Navigate to project directory
    cd $WEB_ROOT
    
    # Install PHP dependencies
    sudo -u www-data composer install --optimize-autoloader --no-dev
    
    # Install Node dependencies and build assets
    sudo -u www-data npm install
    sudo -u www-data npm run build
    
    # Set up environment file
    if [ -f ".env.production" ]; then
        sudo -u www-data cp .env.production .env
    else
        sudo -u www-data cp .env.example .env
    fi
    
    # Generate application key
    sudo -u www-data php artisan key:generate
    
    # Set proper permissions
    chmod -R 775 storage bootstrap/cache
    chown -R www-data:www-data storage bootstrap/cache
    
    print_success "Application deployed successfully"
}

configure_laravel() {
    print_status "Configuring Laravel..."
    
    cd $WEB_ROOT
    
    # Run migrations
    sudo -u www-data php artisan migrate --force
    
    # Seed database (optional)
    read -p "Do you want to seed the database? (y/N): " SEED_DB
    if [[ $SEED_DB =~ ^[Yy]$ ]]; then
        sudo -u www-data php artisan db:seed --force
    fi
    
    # Create storage link
    sudo -u www-data php artisan storage:link
    
    # Cache configuration
    sudo -u www-data php artisan config:cache
    sudo -u www-data php artisan route:cache
    sudo -u www-data php artisan view:cache
    
    print_success "Laravel configured successfully"
}

setup_backups() {
    print_status "Setting up automated backups..."
    
    # Create backup directory
    mkdir -p $BACKUP_DIR
    chown www-data:www-data $BACKUP_DIR
    
    # Add cron job for daily backups
    CRON_JOB="0 2 * * * cd $WEB_ROOT && php artisan app:backup-database --filename=daily-backup-\$(date +\\%Y\\%m\\%d) >> /var/log/hommss-backup.log 2>&1"
    
    # Add to www-data crontab
    (crontab -u www-data -l 2>/dev/null; echo "$CRON_JOB") | crontab -u www-data -
    
    print_success "Automated backups configured"
}

setup_firewall() {
    print_status "Configuring firewall..."
    
    # Install and configure UFW
    apt install ufw -y
    
    # Default policies
    ufw default deny incoming
    ufw default allow outgoing
    
    # Allow SSH, HTTP, and HTTPS
    ufw allow ssh
    ufw allow 80/tcp
    ufw allow 443/tcp
    
    # Enable firewall
    ufw --force enable
    
    print_success "Firewall configured"
}

restart_services() {
    print_status "Restarting services..."
    
    systemctl restart apache2
    systemctl restart php8.2-fpm
    systemctl restart mysql
    
    # Enable services to start on boot
    systemctl enable apache2
    systemctl enable php8.2-fpm
    systemctl enable mysql
    
    print_success "Services restarted and enabled"
}

run_tests() {
    print_status "Running deployment tests..."
    
    cd $WEB_ROOT
    
    # Test Apache status
    if systemctl is-active --quiet apache2; then
        print_success "Apache is running"
    else
        print_error "Apache is not running"
    fi
    
    # Test PHP-FPM status
    if systemctl is-active --quiet php8.2-fpm; then
        print_success "PHP-FPM is running"
    else
        print_error "PHP-FPM is not running"
    fi
    
    # Test MySQL status
    if systemctl is-active --quiet mysql; then
        print_success "MySQL is running"
    else
        print_error "MySQL is not running"
    fi
    
    # Test Laravel application
    if sudo -u www-data php artisan --version > /dev/null 2>&1; then
        print_success "Laravel application is working"
    else
        print_error "Laravel application has issues"
    fi
    
    print_success "Deployment tests completed"
}

main() {
    print_status "Starting HOMMSS E-Commerce deployment..."
    
    # Pre-flight checks
    check_root
    check_ubuntu
    
    # Main deployment steps
    install_dependencies
    configure_apache
    setup_ssl
    setup_database
    deploy_application
    configure_laravel
    setup_backups
    setup_firewall
    restart_services
    run_tests
    
    print_success "Deployment completed successfully!"
    print_status "Your HOMMSS E-Commerce platform should now be accessible at your domain"
    print_warning "Don't forget to:"
    print_warning "1. Update your .env file with production settings"
    print_warning "2. Configure your payment gateway credentials"
    print_warning "3. Set up monitoring and alerts"
    print_warning "4. Test all functionality thoroughly"
}

# Run main function
main "$@"
