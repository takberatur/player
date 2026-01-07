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
apt-get install -y php8.4 php8.4-fpm php8.4-cli php8.4-common php8.4-mysql php8.4-zip php8.4-gd php8.4-mbstring php8.4-curl php8.4-xml php8.4-bcmath

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
apt-get install -y libxshmfence-dev libgbm-dev wget unzip fontconfig locales libc6 libcairo2 libcups2 libdbus-1-3 libexpat1 libfontconfig1 libgcc1 libgdk-pixbuf2.0-0 libglib2.0-0 libgtk-3-0 libnspr4 libpango-1.0-0 libpangocairo-1.0-0 libstdc++6 libx11-6 libx11-xcb1 libxcb1 libxcomposite1 libxcursor1 libxdamage1 libxext6 libxfixes3 libxi6 libxrandr2 libxrender1 libxss1 libxtst6 ca-certificates fonts-liberation libnss3 lsb-release xdg-utils libvips-dev

# Install Google Chrome
echo "Installing Google Chrome Stable..."
wget -c https://dl.google.com/linux/direct/google-chrome-stable_current_amd64.deb
apt-get update
dpkg -i google-chrome-stable_current_amd64.deb || apt-get install -f -y
rm google-chrome-stable_current_amd64.deb

# Configure PHP Limits
echo "Configuring PHP limits..."
PHP_INI="/etc/php/8.4/fpm/php.ini"
if [ -f "$PHP_INI" ]; then
    sed -i 's/^post_max_size.*/post_max_size = 500M/' "$PHP_INI"
    sed -i 's/^upload_max_filesize.*/upload_max_filesize = 500M/' "$PHP_INI"
    sed -i 's/^max_file_uploads.*/max_file_uploads = 50/' "$PHP_INI"
    echo "PHP limits updated in $PHP_INI"

    # Restart PHP-FPM to apply changes
    systemctl restart php8.4-fpm
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

    client_max_body_size 100M;

    index index.php;

    charset utf-8;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
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

# Configure APP_URL, ASSET_URL, SESSION_DOMAIN
echo "Configuring environment variables..."
# Extract root domain (last two parts) for SESSION_DOMAIN
SESSION_DOMAIN=".$(echo $DOMAIN_NAME | rev | cut -d. -f1-2 | rev)"
echo "Setting SESSION_DOMAIN to $SESSION_DOMAIN"

sed -i "s|^APP_URL=.*|APP_URL=https://$DOMAIN_NAME|" .env
sed -i "s|^ASSET_URL=.*|ASSET_URL=https://$DOMAIN_NAME|" .env
sed -i "s|^VITE_APP_URL=.*|VITE_APP_URL=https://$DOMAIN_NAME|" .env
sed -i "s|^SESSION_DOMAIN=.*|SESSION_DOMAIN=$SESSION_DOMAIN|" .env

echo "--- INSTALL MYSQL ---"
apt-get install -y mysql-server
systemctl enable mysql
systemctl start mysql
read -p "Enter MySQL Database Name [forge_player]: " DB_NAME
DB_NAME=${DB_NAME:-forge_player}
read -p "Enter MySQL Username [forge]: " DB_USER
DB_USER=${DB_USER:-forge}
read -p "Enter MySQL Password [password]: " DB_PASS
DB_PASS=${DB_PASS:-password}
mysql -e "CREATE DATABASE IF NOT EXISTS \`$DB_NAME\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -e "CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED WITH mysql_native_password BY '$DB_PASS';"
mysql -e "GRANT ALL PRIVILEGES ON \`$DB_NAME\`.* TO '$DB_USER'@'localhost'; FLUSH PRIVILEGES;"
sed -i "s/^DB_HOST=.*/DB_HOST=127.0.0.1/" .env
sed -i "s/^DB_DATABASE=.*/DB_DATABASE=$DB_NAME/" .env
sed -i "s/^DB_USERNAME=.*/DB_USERNAME=$DB_USER/" .env
sed -i "s/^DB_PASSWORD=.*/DB_PASSWORD=$DB_PASS/" .env
read -p "Enable remote MySQL access? (y/n) " MYSQL_REMOTE
read -p "MySQL Port [3306]: " MYSQL_PORT
MYSQL_PORT=${MYSQL_PORT:-3306}
BIND_ADDR="127.0.0.1"
if [[ "$MYSQL_REMOTE" =~ ^[Yy]$ ]]; then
  read -p "MySQL Bind Address [0.0.0.0]: " MYSQL_BIND
  BIND_ADDR=${MYSQL_BIND:-0.0.0.0}
  mysql -e "CREATE USER IF NOT EXISTS '$DB_USER'@'%' IDENTIFIED WITH mysql_native_password BY '$DB_PASS';"
  mysql -e "GRANT ALL PRIVILEGES ON \`$DB_NAME\`.* TO '$DB_USER'@'%'; FLUSH PRIVILEGES;"
fi
cat > /etc/mysql/mysql.conf.d/forge-player.cnf <<EOF
[mysqld]
bind-address = $BIND_ADDR
port = $MYSQL_PORT
character-set-server = utf8mb4
collation-server = utf8mb4_unicode_ci
default_authentication_plugin = mysql_native_password
sql_mode = ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION
EOF
sed -i "s/^DB_PORT=.*/DB_PORT=$MYSQL_PORT/" .env
systemctl restart mysql

read -p "Configure UFW firewall? (y/n) " CFG_UFW
if [[ "$CFG_UFW" =~ ^[Yy]$ ]]; then
  apt-get install -y ufw
  read -p "SSH Port [22]: " SSH_PORT
  SSH_PORT=${SSH_PORT:-22}
  ufw default deny incoming
  ufw default allow outgoing
  ufw allow ${SSH_PORT}/tcp
  ufw allow 443/tcp
  if [[ "$MYSQL_REMOTE" =~ ^[Yy]$ ]]; then
    read -p "Allow MySQL from CIDR/IP (leave empty to allow any): " MYSQL_CIDR
    if [ -n "$MYSQL_CIDR" ]; then
      ufw allow from "$MYSQL_CIDR" to any port "$MYSQL_PORT" proto tcp
    else
      ufw allow ${MYSQL_PORT}/tcp
    fi
  fi
  ufw --force enable
fi

echo "Running Composer Install..."
export COMPOSER_ALLOW_SUPERUSER=1
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

echo "--- 5. SETUP SSL (Let's Encrypt) ---"
read -p "Setup SSL with Let's Encrypt? (y/n) " SETUP_SSL
if [[ "$SETUP_SSL" =~ ^[Yy]$ ]]; then
  if ! command -v certbot >/dev/null 2>&1; then
    apt-get install -y certbot python3-certbot-nginx
  fi
  UFW_ACTIVE=$(ufw status | grep -i "Status: active" || true)
  if [ -n "$UFW_ACTIVE" ]; then
    ufw allow 80/tcp
  fi
  read -p "Admin Email for Let's Encrypt: " LE_EMAIL
  certbot --nginx -d "$DOMAIN_NAME" --non-interactive --agree-tos -m "$LE_EMAIL" --redirect || true
  systemctl reload nginx
  if [ -n "$UFW_ACTIVE" ]; then
    ufw delete allow 80/tcp || ufw deny 80/tcp
  fi
  read -p "Check SSL renewal status? (y/n) " CHECK_RENEWAL
  if [[ "$CHECK_RENEWAL" =~ ^[Yy]$ ]]; then
    sudo systemctl status certbot.timer || true
    sudo certbot renew --dry-run || true
  fi
fi

echo "--- INSTALLATION COMPLETE ---"
echo "Your app is deployed at http://$DOMAIN_NAME"
echo "Ensure your DNS points to this server IP."
