#!/bin/sh
set -e

# Configure PHP Limits
echo "Configuring PHP limits..."
{
    echo "post_max_size = 500M"
    echo "upload_max_filesize = 500M"
    echo "max_file_uploads = 50"
    echo "memory_limit = 512M"
} > /usr/local/etc/php/conf.d/99-custom-limits.ini

# Ensure permissions are correct
echo "Setting permissions..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Install dependencies if vendor folder is missing (happens when volume overwrites image content)
if [ ! -d "vendor" ]; then
    echo "Vendor folder not found. Running composer install..."
    composer install --optimize-autoloader --no-dev
fi

# Fix Storage Link
if [ -f artisan ]; then
    echo "Creating storage symlink..."
    rm -rf public/storage
    php artisan storage:link
fi

# Execute the main command
exec "$@"
