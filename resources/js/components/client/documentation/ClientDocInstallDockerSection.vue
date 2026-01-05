<template>
  <div class="prose prose-slate dark:prose-invert max-w-none">
    <h1>Docker Deployment Guide</h1>
    <p>
      This guide provides a step-by-step walkthrough for deploying Forge Player
      using Docker. We provide automated scripts to simplify the installation of
      Docker and the initial setup of the application containers.
    </p>

    <div class="not-prose my-6 rounded-lg border bg-muted/50 p-4">
      <h3 class="flex items-center gap-2 font-semibold">
        <span class="text-primary">ℹ️</span> Note
      </h3>
      <p class="mt-2 text-sm text-muted-foreground">
        This guide assumes you are deploying to a fresh
        <strong>Ubuntu 22.04 LTS</strong> server with root access.
      </p>
    </div>

    <h2>Step 1: Local Preparation</h2>
    <p>
      Before uploading your project to the server, prepare your local files to
      ensure a clean deployment. You <strong>should not</strong> upload heavy
      folders that can be regenerated.
    </p>
    <p>
      <strong>Exclude the following folders/files:</strong>
    </p>
    <ul>
      <li><code>node_modules/</code></li>
      <li><code>vendor/</code></li>
      <li><code>.git/</code></li>
      <li>
        <code>.env</code> (You will create this on the server from
        <code>.env.example</code>)
      </li>
    </ul>
    <p>
      <strong>Tip:</strong> Compress your project folder into a
      <code>player.zip</code> file for faster uploading.
    </p>

    <h2>Step 2: Upload to Server</h2>
    <p>
      Upload your project files to your VPS using an FTP client like FileZilla
      or WinSCP, or use SCP via terminal. A common destination is
      <code>/var/www/player</code>.
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

    <h2>Step 3: Install Docker</h2>
    <p>
      We provide a helper script to install Docker and Docker Compose
      automatically.
    </p>
    <ol>
      <li>
        <p>Make the script executable:</p>
        <pre><code class="language-bash">chmod +x deployment/docker/install_docker.sh</code></pre>
      </li>
      <li>
        <p>Run the installation script:</p>
        <pre><code class="language-bash">sudo ./deployment/docker/install_docker.sh</code></pre>
      </li>
    </ol>
    <p>
      This script will install Docker CE, Docker Compose, and necessary system
      dependencies.
    </p>
    <img
      src="/images/docs/docker-install-script-output.png"
      alt="Docker Install Script Output"
      class="rounded-lg border shadow-sm"
    />

    <h2>Step 4: Configure Environment</h2>
    <ol>
      <li>
        <p>Copy the example environment file:</p>
        <pre><code class="language-bash">cp .env.example .env</code></pre>
      </li>
      <li>
        <p>Edit the <code>.env</code> file:</p>
        <pre><code class="language-bash">nano .env</code></pre>
      </li>
      <li>
        <p><strong>Crucial Configuration for Docker:</strong></p>
        <p>
          Update your database host to match the service name in
          <code>docker-compose.yml</code>.
        </p>
        <pre><code class="language-ini"># Database Configuration
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=forge_player
DB_USERNAME=forge
DB_PASSWORD=your_strong_password
DB_ROOT_PASSWORD=another_strong_root_password</code></pre>
      </li>
      <li>
        <p><strong>(Optional) Advanced Multi-App Configuration:</strong></p>
        <p>
          If you plan to deploy multiple applications on the same server, you
          should configure unique ports and service names in your
          <code>.env</code> file to prevent conflicts.
        </p>
        <pre><code class="language-ini"># Unique Service ID for Docker Container Names
APP_SERVICE_ID=forge-player-01

# Web Server Port (Default: 80, Change if using Reverse Proxy)
APP_PORT=8001

# Database Port on Host (Default: 33061, Change if running multiple DBs)
DB_PORT_HOST=33062</code></pre>
      </li>
    </ol>

    <h2>Step 5: Start Containers</h2>
    <p>
      Build and start the application containers using the provided Docker
      Compose file. We explicitly use the <code>--env-file .env</code> flag to
      ensure variables are loaded correctly from the root directory.
    </p>
    <pre><code class="language-bash">docker compose --env-file .env -f deployment/docker/docker-compose.yml up -d --build</code></pre>
    <p>
      Docker will download necessary images (PHP, Nginx, MySQL, etc.) and start
      the services.
    </p>
    <img
      src="/images/docs/docker-compose-up-output.png"
      alt="Docker Compose Up Output"
      class="rounded-lg border shadow-sm"
    />

    <h2>Step 6: Application Setup</h2>
    <p>
      Once the containers are running, you need to install dependencies and run
      migrations inside the <code>app</code> container.
    </p>

    <h3>1. Install Dependencies</h3>
    <pre><code class="language-bash"># Install PHP dependencies
docker compose --env-file .env -f deployment/docker/docker-compose.yml exec app composer install

# Install Node dependencies and build assets
docker compose --env-file .env -f deployment/docker/docker-compose.yml exec app npm install
docker compose --env-file .env -f deployment/docker/docker-compose.yml exec app npm run build

# For SSR mode
docker compose --env-file .env -f deployment/docker/docker-compose.yml exec app npm run build:ssr
</code></pre>

    <h3>2. Database & Keys</h3>
    <pre><code class="language-bash"># Generate App Key
docker compose --env-file .env -f deployment/docker/docker-compose.yml exec app php artisan key:generate

# Run Database Migrations
docker compose --env-file .env -f deployment/docker/docker-compose.yml exec app php artisan migrate --force

# Run Seeder
docker compose --env-file .env -f deployment/docker/docker-compose.yml exec app php artisan db:seed

# Link Storage
docker compose --env-file .env -f deployment/docker/docker-compose.yml exec app php artisan storage:link</code></pre>

    <h3>3. Set Permissions</h3>
    <p>Ensure the web server has correct ownership of the files:</p>
    <pre><code class="language-bash">docker compose --env-file .env -f deployment/docker/docker-compose.yml exec app chown -R www-data:www-data /var/www/html</code></pre>

    <h2>Step 7: Complete Installation</h2>
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
        <strong>View Logs:</strong>
        <pre><code class="language-bash">docker compose --env-file .env -f deployment/docker/docker-compose.yml logs -f app</code></pre>
      </li>
      <li>
        <strong>Stop Containers:</strong>
        <pre><code class="language-bash">docker compose -f deployment/docker/docker-compose.yml down</code></pre>
      </li>
      <li>
        <strong>Reset Containers & Volume:</strong>
        <pre><code class="language-bash">docker compose -f deployment/docker/docker-compose.yml down -v</code></pre>
      </li>
      <li>
        <strong>Puppeteer Issues:</strong> If video downloads fail, ensure
        <code>PUPPETEER_EXECUTABLE_PATH</code> is set correctly in your
        Dockerfile (default is usually <code>/usr/bin/chromium</code>).
      </li>
    </ul>
  </div>
</template>
