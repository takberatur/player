<template>
  <div class="prose prose-slate dark:prose-invert max-w-none">
    <h1>Requirements</h1>
    <p>
      This section lists the system and software requirements to deploy Forge
      Player. The requirements align with the provided deployment scripts:
      <code>deployment/docker/install_docker.sh</code> and
      <code>deployment/native/setup_ubuntu.sh</code>.
    </p>

    <h2>Supported OS</h2>
    <ul>
      <li>Ubuntu 22.04 LTS or Ubuntu 24.04 LTS (64-bit)</li>
    </ul>

    <h2>Server Resources</h2>
    <ul>
      <li>CPU: 1 vCPU or more (2+ recommended)</li>
      <li>RAM: 2 GB minimum (4 GB+ recommended)</li>
      <li>Disk: 10 GB free space minimum</li>
      <li>Network: Stable internet connection</li>
      <li>Privileges: Root or sudo access</li>
    </ul>

    <h2>Native Setup Requirements</h2>
    <p>Installed via <code>deployment/native/setup_ubuntu.sh</code>:</p>
    <ul>
      <li>Nginx (web server)</li>
      <li>
        PHP 8.4 (FPM, CLI) and extensions:
        <ul>
          <li>php8.4-fpm, php8.4-cli, php8.4-common</li>
          <li>php8.4-mysql, php8.4-zip, php8.4-gd, php8.4-mbstring</li>
          <li>php8.4-curl, php8.4-xml, php8.4-bcmath</li>
        </ul>
      </li>
      <li>Composer (PHP dependency manager)</li>
      <li>Node.js 20.x and npm</li>
      <li>Supervisor (optional for background workers)</li>
      <li>Python 3, pip, ffmpeg</li>
      <li>yt-dlp (download helper)</li>
      <li>
        Puppeteer/Chromium system libraries:
        <ul>
          <li>libnss3, libatk1.0-0, libatk-bridge2.0-0, libcups2, libgbm1</li>
          <li>libasound2, libpangocairo-1.0-0, libxss1, libgtk-3-0</li>
          <li>fonts-liberation, xdg-utils, and other related dependencies</li>
        </ul>
      </li>
    </ul>

    <h3>PHP Configuration</h3>
    <p>Updated by the script (if php.ini is found):</p>
    <ul>
      <li><code>post_max_size = 500M</code></li>
      <li><code>upload_max_filesize = 500M</code></li>
      <li><code>max_file_uploads = 50</code></li>
    </ul>

    <h3>Permissions</h3>
    <ul>
      <li>Project ownership: <code>www-data:www-data</code></li>
      <li>
        Writable directories: <code>storage/</code> and
        <code>bootstrap/cache/</code> (chmod 775)
      </li>
    </ul>

    <h3>Environment</h3>
    <ul>
      <li>Prepare <code>.env</code> from <code>.env.example</code></li>
      <li>Configure DB settings (MySQL recommended)</li>
      <li>Run <code>php artisan key:generate</code>, then migrations/seeds</li>
      <li>Create storage symlink: <code>php artisan storage:link</code></li>
    </ul>

    <h2>Docker Setup Requirements</h2>
    <p>Installed via <code>deployment/docker/install_docker.sh</code>:</p>
    <ul>
      <li>Docker CE</li>
      <li>Docker Compose plugin</li>
      <li>Ability to run <code>docker compose</code> commands</li>
    </ul>

    <h3>Containers</h3>
    <p>Defined in <code>deployment/docker/docker-compose.yml</code>:</p>
    <ul>
      <li>
        <strong>app</strong>: PHP 8.4 FPM container, working dir
        <code>/var/www/html</code>
      </li>
      <li>
        <strong>web</strong>: Nginx container, exposes host port
        <code>8000</code> → container port <code>80</code>
      </li>
      <li>
        <strong>db</strong>: MySQL 8.0 with environment variables and a
        persistent volume
      </li>
      <li>Shared network: <code>forge-network</code></li>
      <li>App volume: project folder mounted to <code>/var/www/html</code></li>
    </ul>

    <h3>Environment (Docker)</h3>
    <ul>
      <li>Copy <code>.env</code> from <code>.env.example</code></li>
      <li>
        Use <code>DB_HOST=db</code> to connect to MySQL service inside the
        compose network
      </li>
      <li>
        Run <code>docker compose up -d --build</code> from the compose file
      </li>
      <li>
        Inside the <code>app</code> container, run composer/npm setup and
        Laravel commands if needed:
        <ul>
          <li><code>composer install</code></li>
          <li><code>npm install && npm run build</code></li>
          <li><code>php artisan key:generate</code></li>
          <li><code>php artisan migrate --force</code></li>
          <li><code>php artisan storage:link</code></li>
        </ul>
      </li>
    </ul>

    <h2>DNS & SSL</h2>
    <ul>
      <li>Point your domain’s DNS A record to the server IP</li>
      <li>SSL/TLS can be added via Nginx and Certbot (native) or via proxy</li>
    </ul>
  </div>
</template>
