#!/bin/bash
# Fix Rumble Scraper dependencies and config
# Run this script as root (sudo)

if [ "$EUID" -ne 0 ]; then
  echo "Please run as root (sudo)"
  exit 1
fi

echo "--- 1. Checking Puppeteer System Dependencies ---"
apt-get update
apt-get install -y libxshmfence-dev libgbm-dev wget unzip fontconfig locales libc6 libcairo2 libcups2 libdbus-1-3 libexpat1 libfontconfig1 libgcc1 libgdk-pixbuf2.0-0 libglib2.0-0 libgtk-3-0 libnspr4 libpango-1.0-0 libpangocairo-1.0-0 libstdc++6 libx11-6 libx11-xcb1 libxcb1 libxcomposite1 libxcursor1 libxdamage1 libxext6 libxfixes3 libxi6 libxrandr2 libxrender1 libxss1 libxtst6 ca-certificates fonts-liberation libnss3 lsb-release xdg-utils libvips-dev

echo "--- 2. Installing Google Chrome Stable (if missing) ---"
if ! command -v google-chrome &> /dev/null; then
    wget -c https://dl.google.com/linux/direct/google-chrome-stable_current_amd64.deb
    dpkg -i google-chrome-stable_current_amd64.deb || apt-get install -f -y
    rm google-chrome-stable_current_amd64.deb
fi

CHROME_PATH=$(which google-chrome)
if [ -z "$CHROME_PATH" ]; then
    CHROME_PATH=$(which google-chrome-stable)
fi

if [ -z "$CHROME_PATH" ]; then
    echo "Error: Google Chrome not found even after installation attempt."
    exit 1
fi

echo "Chrome found at: $CHROME_PATH"

echo "--- 3. Configuring .env ---"
# Check if .env exists
if [ -f .env ]; then
    if grep -q "VITE_RUMBLE_PUPPETEER_EXECUTABLE_PATH=" .env; then
        sed -i "s|^VITE_RUMBLE_PUPPETEER_EXECUTABLE_PATH=.*|VITE_RUMBLE_PUPPETEER_EXECUTABLE_PATH=$CHROME_PATH|" .env
    else
        echo "" >> .env
        echo "VITE_RUMBLE_PUPPETEER_EXECUTABLE_PATH=$CHROME_PATH" >> .env
    fi
    echo ".env updated with VITE_RUMBLE_PUPPETEER_EXECUTABLE_PATH=$CHROME_PATH"
else
    echo "Warning: .env file not found in current directory."
fi

echo "--- 4. Verify Node.js ---"
NODE_PATH=$(which node)
echo "Node found at: $NODE_PATH"

echo "--- 5. Clearing Cache ---"
if [ -f artisan ]; then
    php artisan config:clear
    php artisan cache:clear
    php artisan optimize:clear
else
    echo "artisan not found, skipping cache clear."
fi

echo "--- Fix Complete ---"
echo "Please retry fetching a Rumble video."
