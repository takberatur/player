#!/bin/bash

# Script Full Install for Ubuntu 22.04 / 24.04 (Native Setup)
# Run this script as root (sudo) in the project folder that has been cloned (or will be setup)

set -e

# Check Root
if [ "$EUID" -ne 0 ]; then
  echo "Please run as root (sudo)"
  exit 1
fi

# Detect Current Directory
CURRENT_DIR=$(pwd)
echo "Current directory: $CURRENT_DIR"

# Input Domain Name
read -p "Enter Domain Name (e.g., player.com): " DOMAIN_NAME
if [ -z "$DOMAIN_NAME" ]; then
    echo "Domain name can't be empty!"
    exit 1
fi

echo "--- 1. UPDATE SYSTEM ---"
apt-get update && apt-get upgrade -y

echo "--- 2. INSTALL DEPENDENCIES (PHP, Node, System) ---"
apt-get install -y git curl zip unzip software-properties-common supervisor nginx
add-apt-repository ppa:ondrej/php -y
apt-get update
apt-get install -y php8.3 php8.3-fpm php8.3-cli php8.3-common php8.3-mysql php8.3-zip php8.3-gd php8.3-mbstring php8.3-curl php8.3-xml php8.3-bcmath

# Install Composer
if ! command -v composer &> /dev/null; then
    echo "Installing Composer..."
    curl -sS https://getcomposer.org/installer | php
    mv composer.phar /usr/local/bin/composer
fi

# Install Node.js 20
if ! command -v node &> /dev/null; then
    echo "Installing Node.js..."
    curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
    apt-get install -y nodejs
fi

# Install Python & yt-dlp
echo "Installing Python & yt-dlp..."
apt-get install -y python3 python3-pip ffmpeg
if [ ! -f /usr/local/bin/yt-dlp ]; then
    curl -L https://github.com/yt-dlp/yt-dlp/releases/latest/download/yt-dlp -o /usr/local/bin/yt-dlp
    chmod a+rx /usr/local/bin/yt-dlp
fi

# Install Puppeteer Dependencies
echo "Installing Puppeteer system dependencies..."
apt-get install -y ca-certificates fonts-liberation libasound2 libatk-bridge2.0-0 libatk1.0-0 libc6 libcairo2 libcups2 libdbus-1-3 libexpat1 libfontconfig1 libgbm1 libgcc1 libglib2.0-0 libgtk-3-0 libnspr4 libnss3 libpango-1.0-0 libpangocairo-1.0-0 libstdc++6 libx11-6 libx11-xcb1 libxcb1 libxcomposite1 libxcursor1 libxdamage1 libxext6 libxfixes3 libxi6 libxrandr2 libxrender1 libxss1 libxtst6 lsb-release wget xdg-utils

# Configure PHP Limits
echo "Configuring PHP limits..."
PHP_INI="/etc/php/8.3/fpm/php.ini"
if [ -f "$PHP_INI" ]; then
    sed -i 's/^post_max_size.*/post_max_size = 500M/' "$PHP_INI"
    sed -i 's/^upload_max_filesize.*/upload_max_filesize = 500M/' "$PHP_INI"
    sed -i 's/^max_file_uploads.*/max_file_uploads = 50/' "$PHP_INI"
    echo "PHP limits updated in $PHP_INI"

    # Restart PHP-FPM to apply changes
    systemctl restart php8.3-fpm
else
    echo "Warning: $PHP_INI not found. Please update PHP limits manually."
fi

echo "--- 3. SETUP NGINX ---"
NGINX_CONF="/etc/nginx/sites-available/$DOMAIN_NAME"
# Copy config template (assume file is in deployment/native folder relative to script, or script is in root project folder)
# We will create a new config file dynamically
cat > "$NGINX_CONF" <<EOF
server {
    listen 80;
    server_name $DOMAIN_NAME;
    root $CURRENT_DIR/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF

echo "Nginx config created at $NGINX_CONF"

# Enable Site
ln -sf "$NGINX_CONF" /etc/nginx/sites-enabled/

# Remove default if exists
if [ -f /etc/nginx/sites-enabled/default ]; then
    unlink /etc/nginx/sites-enabled/default
fi

# Test & Reload Nginx
nginx -t
systemctl reload nginx

echo "--- 4. SETUP PERMISSIONS & PROJECT ---"
# Set Ownership
chown -R www-data:www-data "$CURRENT_DIR"

# Set Permissions
chmod -R 775 "$CURRENT_DIR/storage"
chmod -R 775 "$CURRENT_DIR/bootstrap/cache"

# User www-data needs to be able to write, but we are root now.
# We will run composer and npm as the current user (if sudo) or better, run as the owner of the files?
# Running composer/npm as root is generally discouraged but common in simple deploy scripts.
# We will allow root for now.

# Setup .env
if [ ! -f .env ]; then
    echo "Copying .env.example to .env..."
    cp .env.example .env
fi

# Install App Dependencies
echo "Running Composer Install..."
composer install --no-interaction --prefer-dist --optimize-autoloader

echo "Running NPM Install & Build..."
npm install -g npm@latest
npm install
npm run build:ssr

# Generate Key
if ! grep -q "APP_KEY=base64" .env; then
    php artisan key:generate
fi

# Storage Link
rm -rf public/storage
php artisan storage:link

# Migration
read -p "Run database migration? (y/n) " RUN_MIGRATE
if [[ "$RUN_MIGRATE" =~ ^[Yy]$ ]]; then
    php artisan migrate --force
fi

# Seed Database
read -p "Run database seeder? (y/n) " RUN_SEED
if [[ "$RUN_SEED" =~ ^[Yy]$ ]]; then
    php artisan db:seed --force
fi

# Ensure permissions again (in case composer/npm changed ownership of new files)
chown -R www-data:www-data "$CURRENT_DIR"
chmod -R 775 "$CURRENT_DIR/storage"
chmod -R 775 "$CURRENT_DIR/bootstrap/cache"

echo "--- INSTALLATION COMPLETE ---"
echo "Your app is deployed at http://$DOMAIN_NAME"
echo "Ensure your DNS points to this server IP."
