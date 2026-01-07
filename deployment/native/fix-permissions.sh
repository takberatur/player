#!/bin/bash
# Fix Laravel Permissions
# Run as root (sudo)

if [ "$EUID" -ne 0 ]; then
  echo "Please run as root (sudo)"
  exit 1
fi

echo "--- Fixing Permissions ---"
CURRENT_DIR=$(pwd)
echo "Project Dir: $CURRENT_DIR"

# 1. Set ownership to www-data (standard web user)
chown -R www-data:www-data "$CURRENT_DIR"

# 2. Reset base permissions
# Files -> 644 (Read/Write)
find "$CURRENT_DIR" -type f -exec chmod 644 {} \;
# Directories -> 755 (Read/Write/Execute-Traverse)
find "$CURRENT_DIR" -type d -exec chmod 755 {} \;

# 3. Restore executable permissions for binaries and scripts
chmod +x "$CURRENT_DIR/artisan"
chmod +x "$CURRENT_DIR/deployment/native/"*.sh

# Important: Fix node_modules permissions recursively (fix EACCES on esbuild/vite)
if [ -d "$CURRENT_DIR/node_modules" ]; then
    echo "Restoring node_modules permissions..."
    chmod -R 755 "$CURRENT_DIR/node_modules"
fi

# 4. Give write access to storage and cache for www-data
chmod -R 775 "$CURRENT_DIR/storage"
chmod -R 775 "$CURRENT_DIR/bootstrap/cache"

# 5. Clear caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear

echo "--- Permissions Fixed ---"
echo "You can now run 'npm run build:ssr' again."
