#!/bin/bash

# HOMMSS Security Hardening Script for Ubuntu Apache
# This script implements additional security measures

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

print_status() {
    echo -e "${BLUE}[SECURITY]${NC} $1"
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

# Check if running as root
if [[ $EUID -ne 0 ]]; then
    print_error "This script must be run as root (use sudo)"
    exit 1
fi

print_status "Starting security hardening for HOMMSS..."

# 1. Install and configure Fail2Ban
print_status "Installing and configuring Fail2Ban..."
apt install fail2ban -y

# Create Fail2Ban configuration for Apache
cat > /etc/fail2ban/jail.local << 'EOF'
[DEFAULT]
bantime = 3600
findtime = 600
maxretry = 5
backend = auto
usedns = warn
logencoding = auto
enabled = false
mode = normal
filter = %(__name__)s[mode=%(mode)s]

[sshd]
enabled = true
port = ssh
logpath = %(sshd_log)s
backend = %(sshd_backend)s

[apache-auth]
enabled = true
port = http,https
logpath = %(apache_error_log)s

[apache-badbots]
enabled = true
port = http,https
logpath = %(apache_access_log)s
bantime = 86400
maxretry = 1

[apache-noscript]
enabled = true
port = http,https
logpath = %(apache_access_log)s
maxretry = 6

[apache-overflows]
enabled = true
port = http,https
logpath = %(apache_error_log)s
maxretry = 2

[apache-nohome]
enabled = true
port = http,https
logpath = %(apache_error_log)s
maxretry = 2

[apache-botsearch]
enabled = true
port = http,https
logpath = %(apache_error_log)s
maxretry = 2

[hommss-admin]
enabled = true
port = http,https
logpath = /var/log/apache2/hommss_ssl_access.log
filter = hommss-admin
maxretry = 3
bantime = 7200
EOF

# Create custom filter for admin protection
cat > /etc/fail2ban/filter.d/hommss-admin.conf << 'EOF'
[Definition]
failregex = ^<HOST> .* "(GET|POST) /admin.*" (401|403|404) .*$
ignoreregex =
EOF

systemctl enable fail2ban
systemctl restart fail2ban

print_success "Fail2Ban configured"

# 2. Configure ModSecurity
print_status "Installing and configuring ModSecurity..."
apt install libapache2-mod-security2 -y
a2enmod security2

# Copy default configuration
cp /etc/modsecurity/modsecurity.conf-recommended /etc/modsecurity/modsecurity.conf

# Enable ModSecurity
sed -i 's/SecRuleEngine DetectionOnly/SecRuleEngine On/' /etc/modsecurity/modsecurity.conf

# Download OWASP Core Rule Set
cd /tmp
wget https://github.com/coreruleset/coreruleset/archive/v3.3.4.tar.gz
tar -xzf v3.3.4.tar.gz
mv coreruleset-3.3.4 /etc/modsecurity/crs
cp /etc/modsecurity/crs/crs-setup.conf.example /etc/modsecurity/crs/crs-setup.conf

# Configure ModSecurity for Apache
cat > /etc/apache2/mods-enabled/security2.conf << 'EOF'
<IfModule mod_security2.c>
    SecRuleEngine On
    SecRequestBodyAccess On
    SecRule REQUEST_HEADERS:Content-Type "text/xml" \
         "id:'200000',phase:1,t:none,t:lowercase,pass,nolog,ctl:requestBodyProcessor=XML"
    SecRequestBodyLimit 13107200
    SecRequestBodyNoFilesLimit 131072
    SecRequestBodyInMemoryLimit 131072
    SecRequestBodyLimitAction Reject
    SecRule REQBODY_ERROR "!@eq 0" \
    "id:'200001', phase:2,t:none,log,deny,status:400,msg:'Failed to parse request body.',logdata:'%{reqbody_error_msg}',severity:2"
    SecRule MULTIPART_STRICT_ERROR "!@eq 0" \
    "id:'200002',phase:2,t:none,log,deny,status:400, \
    msg:'Multipart request body failed strict validation: \
    PE %{REQBODY_PROCESSOR_ERROR}, \
    BQ %{MULTIPART_BOUNDARY_QUOTED}, \
    BW %{MULTIPART_BOUNDARY_WHITESPACE}, \
    DB %{MULTIPART_DATA_BEFORE}, \
    DA %{MULTIPART_DATA_AFTER}, \
    HF %{MULTIPART_HEADER_FOLDING}, \
    LF %{MULTIPART_LF_LINE}, \
    SM %{MULTIPART_MISSING_SEMICOLON}, \
    IQ %{MULTIPART_INVALID_QUOTING}, \
    IP %{MULTIPART_INVALID_PART}, \
    IH %{MULTIPART_INVALID_HEADER_FOLDING}, \
    FL %{MULTIPART_FILE_LIMIT_EXCEEDED}'"

    SecRule MULTIPART_UNMATCHED_BOUNDARY "!@eq 0" \
    "id:'200003',phase:2,t:none,log,deny,status:44"
    SecPcreMatchLimit 1000
    SecPcreMatchLimitRecursion 1000
    SecRule TX:/^MSC_/ "!@streq 0" \
            "id:'200004',phase:2,t:none,deny,msg:'ModSecurity internal error flagged: %{MATCHED_VAR_NAME}'"
    SecResponseBodyAccess On
    SecResponseBodyMimeType text/plain text/html text/xml
    SecResponseBodyLimit 524288
    SecResponseBodyLimitAction ProcessPartial
    SecTmpDir /tmp/
    SecDataDir /tmp/
    IncludeOptional /etc/modsecurity/*.conf
    Include /etc/modsecurity/crs/crs-setup.conf
    Include /etc/modsecurity/crs/rules/*.conf
</IfModule>
EOF

print_success "ModSecurity configured"

# 3. Secure file permissions
print_status "Setting secure file permissions..."

# Set proper ownership
chown -R www-data:www-data /var/www/html/HOMMS-PHP
find /var/www/html/HOMMS-PHP -type d -exec chmod 755 {} \;
find /var/www/html/HOMMS-PHP -type f -exec chmod 644 {} \;

# Secure sensitive directories
chmod 750 /var/www/html/HOMMS-PHP/storage
chmod 750 /var/www/html/HOMMS-PHP/bootstrap/cache
chmod 600 /var/www/html/HOMMS-PHP/.env

# Make sure uploads directory is secure
if [ -d "/var/www/html/HOMMS-PHP/public/uploads" ]; then
    find /var/www/html/HOMMS-PHP/public/uploads -name "*.php" -delete
    echo "Options -ExecCGI" > /var/www/html/HOMMS-PHP/public/uploads/.htaccess
    echo "AddHandler cgi-script .php .pl .py .jsp .asp .sh .cgi" >> /var/www/html/HOMMS-PHP/public/uploads/.htaccess
fi

print_success "File permissions secured"

# 4. Configure system security
print_status "Configuring system security..."

# Disable unnecessary services
systemctl disable avahi-daemon 2>/dev/null || true
systemctl disable cups 2>/dev/null || true
systemctl disable bluetooth 2>/dev/null || true

# Configure kernel parameters
cat >> /etc/sysctl.conf << 'EOF'

# HOMMSS Security Hardening
# Network security
net.ipv4.conf.default.rp_filter=1
net.ipv4.conf.all.rp_filter=1
net.ipv4.conf.all.accept_redirects=0
net.ipv6.conf.all.accept_redirects=0
net.ipv4.conf.all.send_redirects=0
net.ipv4.conf.all.accept_source_route=0
net.ipv6.conf.all.accept_source_route=0
net.ipv4.conf.all.log_martians=1
net.ipv4.icmp_echo_ignore_broadcasts=1
net.ipv4.icmp_ignore_bogus_error_responses=1
net.ipv4.tcp_syncookies=1
net.ipv4.conf.all.rp_filter=1
net.ipv4.conf.default.rp_filter=1

# Memory protection
kernel.exec-shield=1
kernel.randomize_va_space=2
EOF

sysctl -p

print_success "System security configured"

# 5. Configure log monitoring
print_status "Setting up log monitoring..."

# Create log monitoring script
cat > /usr/local/bin/hommss-security-monitor.sh << 'EOF'
#!/bin/bash

# HOMMSS Security Monitoring Script
LOG_FILE="/var/log/hommss-security.log"
ADMIN_EMAIL="admin@yourdomain.com"

# Check for suspicious activities
check_failed_logins() {
    FAILED_LOGINS=$(grep "authentication failure" /var/log/auth.log | tail -10)
    if [ ! -z "$FAILED_LOGINS" ]; then
        echo "$(date): Failed login attempts detected" >> $LOG_FILE
        echo "$FAILED_LOGINS" >> $LOG_FILE
    fi
}

check_apache_errors() {
    APACHE_ERRORS=$(grep "error" /var/log/apache2/hommss_ssl_error.log | tail -10)
    if [ ! -z "$APACHE_ERRORS" ]; then
        echo "$(date): Apache errors detected" >> $LOG_FILE
        echo "$APACHE_ERRORS" >> $LOG_FILE
    fi
}

check_disk_space() {
    DISK_USAGE=$(df / | awk 'NR==2{print $5}' | sed 's/%//')
    if [ $DISK_USAGE -gt 90 ]; then
        echo "$(date): Disk space critical: ${DISK_USAGE}%" >> $LOG_FILE
        echo "Disk space critical on HOMMSS server: ${DISK_USAGE}%" | mail -s "HOMMSS Alert: Disk Space" $ADMIN_EMAIL
    fi
}

# Run checks
check_failed_logins
check_apache_errors
check_disk_space
EOF

chmod +x /usr/local/bin/hommss-security-monitor.sh

# Add to crontab
(crontab -l 2>/dev/null; echo "*/15 * * * * /usr/local/bin/hommss-security-monitor.sh") | crontab -

print_success "Log monitoring configured"

# 6. Install and configure ClamAV
print_status "Installing antivirus protection..."
apt install clamav clamav-daemon -y

# Update virus definitions
freshclam

# Configure daily scan
cat > /usr/local/bin/hommss-virus-scan.sh << 'EOF'
#!/bin/bash

SCAN_DIR="/var/www/hommss"
LOG_FILE="/var/log/hommss-virus-scan.log"
ADMIN_EMAIL="admin@yourdomain.com"

echo "$(date): Starting virus scan" >> $LOG_FILE
clamscan -r $SCAN_DIR --log=$LOG_FILE --infected --remove

if [ $? -ne 0 ]; then
    echo "Virus detected on HOMMSS server" | mail -s "HOMMSS Alert: Virus Detected" $ADMIN_EMAIL
fi
EOF

chmod +x /usr/local/bin/hommss-virus-scan.sh

# Add to crontab for daily scan at 3 AM
(crontab -l 2>/dev/null; echo "0 3 * * * /usr/local/bin/hommss-virus-scan.sh") | crontab -

print_success "Antivirus protection configured"

# 7. Configure automatic updates
print_status "Configuring automatic security updates..."
apt install unattended-upgrades -y

cat > /etc/apt/apt.conf.d/50unattended-upgrades << 'EOF'
Unattended-Upgrade::Allowed-Origins {
    "${distro_id}:${distro_codename}-security";
    "${distro_id}ESMApps:${distro_codename}-apps-security";
    "${distro_id}ESM:${distro_codename}-infra-security";
};

Unattended-Upgrade::Package-Blacklist {
};

Unattended-Upgrade::DevRelease "false";
Unattended-Upgrade::Remove-Unused-Dependencies "true";
Unattended-Upgrade::Automatic-Reboot "false";
Unattended-Upgrade::Automatic-Reboot-Time "02:00";
Unattended-Upgrade::Mail "admin@yourdomain.com";
Unattended-Upgrade::MailOnlyOnError "true";
EOF

systemctl enable unattended-upgrades

print_success "Automatic updates configured"

# 8. Restart services
print_status "Restarting services..."
systemctl restart apache2
systemctl restart fail2ban
systemctl restart clamav-daemon

print_success "Security hardening completed!"

print_warning "Additional manual steps required:"
print_warning "1. Update admin email addresses in configuration files"
print_warning "2. Configure mail server for alerts"
print_warning "3. Review and customize ModSecurity rules"
print_warning "4. Set up external monitoring"
print_warning "5. Test all security measures"

print_status "Security hardening summary:"
echo "✅ Fail2Ban installed and configured"
echo "✅ ModSecurity with OWASP rules enabled"
echo "✅ File permissions secured"
echo "✅ System security hardened"
echo "✅ Log monitoring configured"
echo "✅ Antivirus protection installed"
echo "✅ Automatic security updates enabled"
