<template>
  <div class="prose prose-slate dark:prose-invert max-w-none">
    <h1>Native Deployment Guide (Ubuntu + Nginx)</h1>
    <p>
      This guide provides a step-by-step walkthrough for deploying Forge Player
      directly on an Ubuntu server using Nginx. We provide an automated script
      to handle system dependencies, Nginx configuration, and application setup.
    </p>

    <div class="not-prose my-6 rounded-lg border bg-muted/50 p-4">
      <h3 class="flex items-center gap-2 font-semibold">
        <span class="text-primary">ℹ️</span> Note
      </h3>
      <p class="mt-2 text-sm text-muted-foreground">
        This guide assumes you are deploying to a fresh
        <strong>Ubuntu 22.04 LTS</strong> or <strong>24.04 LTS</strong> server
        with root access.
      </p>
    </div>

    <h2>Step 1: Local Preparation</h2>
    <p>
      Before uploading your project to the server, prepare your local files. You
      <strong>should not</strong> upload heavy folders that will be re-generated
      on the server.
    </p>
    <p>
      <strong>Exclude the following folders/files:</strong>
    </p>
    <ul>
      <li><code>node_modules/</code></li>
      <li><code>vendor/</code></li>
      <li><code>.git/</code></li>
      <li>
        <code>.env</code> (Will be created from <code>.env.example</code>)
      </li>
    </ul>
    <p>
      <strong>Tip:</strong> Compress your project folder into a
      <code>player.zip</code> file for faster uploading.
    </p>

    <h2>Step 2: Upload to Server</h2>
    <p>
      Upload your project files to your VPS using an FTP client like FileZilla
      or WinSCP. A common destination is <code>/var/www/player</code>.
    </p>
    <img
      src="/images/docs/ftp-upload.png"
      alt="Upload Files via FileZilla"
      class="rounded-lg border shadow-sm"
    />
    <p>If you uploaded a zip file, unzip it on the server:</p>
    <pre><code class="language-bash"># Login to your server
ssh user@your-server-ip

# Install unzip if needed
sudo apt install unzip

# Navigate and unzip
cd /var/www
unzip player.zip -d player
cd player</code></pre>

    <h2>Step 3: Run Installation Script</h2>
    <p>
      We provide a comprehensive script that handles system updates, software
      installation (PHP, Nginx, Node.js), and project configuration.
    </p>
    <ol>
      <li>
        <p>Grant execution permission to the script:</p>
        <pre><code class="language-bash">chmod +x deployment/native/setup_ubuntu.sh</code></pre>
      </li>
      <li>
        <p>Run the script as root:</p>
        <pre><code class="language-bash">sudo ./deployment/native/setup_ubuntu.sh</code></pre>
      </li>
    </ol>
    <p>
      <strong>Follow the on-screen instructions:</strong>
    </p>
    <ul>
      <li>
        Enter your <strong>Domain Name</strong> (e.g.,
        <code>player.yourdomain.com</code>) when prompted.
      </li>
      <li>
        The script will automatically install PHP 8.4, Nginx, Node.js 20,
        Python, and other dependencies.
      </li>
      <li>
        It will also attempt to run <code>composer install</code> and
        <code>npm install</code>.
      </li>
    </ul>
    <img
      src="/images/docs/docker-install-script-output.png"
      alt="Native Install Script Output"
      class="rounded-lg border shadow-sm"
    />

    <h2>Step 4: Database Configuration</h2>
    <p>
      The installation script installs the MySQL driver but
      <strong>does not create the database</strong>. You need to do this
      manually.
    </p>
    <ol>
      <li>
        <p>Log in to MySQL (if installed locally) or your database server:</p>
        <pre><code class="language-bash">mysql -u root -p</code></pre>
      </li>
      <li>
        <p>Create a database and user:</p>
        <pre><code class="language-sql">CREATE DATABASE forge_player;
CREATE USER 'forge'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON forge_player.* TO 'forge'@'localhost';
FLUSH PRIVILEGES;
EXIT;</code></pre>
      </li>
      <li>
        <p>Edit the <code>.env</code> file to update database credentials:</p>
        <pre><code class="language-bash">nano .env</code></pre>
        <pre><code class="language-ini">DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=forge_player
DB_USERNAME=forge
DB_PASSWORD=your_secure_password</code></pre>
      </li>
      <li>
        <p>Run migrations manually (if skipped during script execution):</p>
        <pre><code class="language-bash">php artisan migrate --force</code></pre>
      </li>
    </ol>

    <h2>Step 5: Verification & Permissions</h2>
    <p>
      Open your browser and visit your domain. If you see the application,
      congratulations!
    </p>
    <p>
      <strong>Fixing 500 Errors / Permission Issues:</strong>
    </p>
    <p>
      If you encounter a "Server Error" or permission denied error, run these
      commands to ensure the web server owns the files:
    </p>
    <pre><code class="language-bash"># Set ownership to www-data user
sudo chown -R www-data:www-data /var/www/player

# Set permissions for storage and cache
sudo chmod -R 775 /var/www/player/storage
sudo chmod -R 775 /var/www/player/bootstrap/cache</code></pre>

    <h2>Step 6: Complete Installation</h2>
    <p>
      Congratulations! Your Forge Player installation is complete. You can
      access the application at <code>http://yourdomain.com</code> (or your
      configured port).
    </p>
    <pre><code class="language-bash"># Admin login
admin@example.com
admin123

# Editor login
editor@editor.com
editor123
     </code></pre>

    <h2>Troubleshooting</h2>
    <ul>
      <li>
        <strong>Nginx Logs:</strong> Check
        <code>/var/log/nginx/error.log</code> for web server errors.
      </li>
      <li>
        <strong>Laravel Logs:</strong> Check
        <code>storage/logs/laravel.log</code> for application errors.
      </li>
      <li>
        <strong>Restart Services:</strong>
        <pre><code class="language-bash">sudo systemctl restart nginx
sudo systemctl restart php8.4-fpm</code></pre>
      </li>
    </ul>
  </div>
</template>
