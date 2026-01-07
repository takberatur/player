#!/bin/bash

# Script Setup SSR Mode with PM2 for Ubuntu Native
# Run as root (sudo) inside the project directory

set -e

# Check Root
if [ "$EUID" -ne 0 ]; then
  echo "Please run as root (sudo)"
  exit 1
fi

CURRENT_DIR=$(pwd)
echo "Current directory: $CURRENT_DIR"

# Check if ecosystem.config.json exists
if [ ! -f "ecosystem.config.json" ]; then
    echo "Error: ecosystem.config.json not found in $CURRENT_DIR"
    exit 1
fi

echo "--- 1. INSTALL PM2 ---"
if ! command -v pm2 &> /dev/null; then
    echo "Installing PM2 global..."
    npm install -g pm2
else
    echo "PM2 is already installed."
fi

echo "--- 2. PREPARE SSR BUILD ---"
# Ensure node_modules exists
if [ ! -d "node_modules" ]; then
    echo "Installing dependencies..."
    npm install
fi

echo "Building SSR assets..."
npm run build:ssr

echo "--- 3. SETUP LOGS ---"
mkdir -p "$CURRENT_DIR/storage/logs/pm2"
# Ensure permissions
chmod -R 775 "$CURRENT_DIR/storage"
chown -R www-data:www-data "$CURRENT_DIR/storage"

echo "--- 4. START PM2 ---"
# Delete existing process if exists to allow clean start
pm2 delete forge-player-liv 2>/dev/null || true

# Start PM2
pm2 start ecosystem.config.json

echo "--- 5. SETUP STARTUP ---"
# Generate startup script and execute it
# This part detects the init system and generates the command
# We use '|| true' to avoid failure if already setup
pm2 startup systemd -u root --hp /root 2>/dev/null || pm2 startup

pm2 save

echo "--- SSR SETUP COMPLETE ---"
echo "SSR Service is running on port 13714 (internal)"
echo "Check status: pm2 status"
echo "View logs: pm2 logs forge-player-liv"
