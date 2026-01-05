FROM php:8.3-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    gnupg \
    libnss3 \
    libatk1.0-0 \
    libatk-bridge2.0-0 \
    libcups2 \
    libgbm1 \
    libasound2 \
    libpangocairo-1.0-0 \
    libxss1 \
    libgtk-3-0 \
    libxshmfence1 \
    libglu1 \
    fonts-liberation \
    libappindicator3-1 \
    xdg-utils \
    libxcomposite1 \
    libxcursor1 \
    libxdamage1 \
    libxi6 \
    libxtst6 \
    libxrandr2 \
    libdbus-1-3 \
    libx11-xcb1 \
    python3 \
    python3-pip \
    ffmpeg \
    wget \
    --no-install-recommends

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install yt-dlp
RUN curl -L https://github.com/yt-dlp/yt-dlp/releases/latest/download/yt-dlp -o /usr/local/bin/yt-dlp \
    && chmod a+rx /usr/local/bin/yt-dlp

# Install Google Chrome
RUN wget -q -O google-chrome.deb https://dl.google.com/linux/direct/google-chrome-stable_current_amd64.deb \
    && apt-get install -y ./google-chrome.deb \
    && rm google-chrome.deb

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Configure PHP upload limits
RUN echo "upload_max_filesize = 100M" > /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size = 100M" >> /usr/local/etc/php/conf.d/uploads.ini

# Install Node.js (Version 20)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Fix Git ownership issue
RUN git config --global --add safe.directory /var/www/html

# Copy application code
COPY . /var/www/html

# Install Dependencies
# RUN composer install --optimize-autoloader --no-dev
ENV PUPPETEER_SKIP_CHROMIUM_DOWNLOAD=true
ENV PUPPETEER_EXECUTABLE_PATH=/usr/bin/google-chrome
# Fix for Chrome crashpad_handler: --database is required
ENV XDG_CONFIG_HOME=/var/www/.local
ENV XDG_CACHE_HOME=/var/www/.local
ENV HOME=/var/www

# Install composer dependencies first (needed for wayfinder)
# Use --no-scripts to prevent pre-uninstall scripts from failing due to missing vendor/autoload.php or other issues
RUN composer install --optimize-autoloader --no-dev --no-scripts

# Run manual commands to ensure everything is set up correctly
RUN composer dump-autoload --optimize
RUN php artisan package:discover --ansi

# Generate application key if it doesn't exist
RUN php artisan key:generate --force --ansi

# Cache configuration
RUN php artisan config:cache

# Update npm to latest version
RUN npm install -g npm@latest

# Build temporary client to ensure everything is set up correctly
RUN npm install && npm run build

# The Manually generate wayfinder routes
RUN php artisan wayfinder:generate --with-form --ansi

# Then build frontend with SSR
RUN npm run build:ssr

# Set permission for storage and bootstrap cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Create .local directory for www-data (fix for Puppeteer permission issue)
RUN mkdir -p /var/www/.local/share/applications \
    && chown -R www-data:www-data /var/www/.local

# Copy Entrypoint
COPY deployment/docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Expose port 9000
EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]
