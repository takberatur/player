#!/bin/bash


PROJECT_PATH="/path/to/your/project"
PHP_PATH="/usr/local/bin/php" # Or /usr/bin/php, check in cPanel > Server Information

# Navigate to project directory
cd $PROJECT_PATH

# Check if SSR process is running
# Using pgrep -f to find processes containing string "inertia:start-ssr"
if ! pgrep -f "inertia:start-ssr" > /dev/null
then
    # If not found, restart SSR
    echo "[$(date)] SSR is down. Restarting..." >> storage/logs/ssr-watcher.log

    # Run command in background with nohup
    nohup $PHP_PATH artisan inertia:start-ssr > /dev/null 2>&1 &

    echo "[$(date)] SSR successfully started." >> storage/logs/ssr-watcher.log
else
    # Optional: Log heartbeat (can be disabled to avoid log overflow)
    # echo "[$(date)] SSR is running normally." >> storage/logs/ssr-watcher.log
    echo "SSR Running."
fi
