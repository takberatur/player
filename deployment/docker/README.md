# Docker Deployment Guide via FTP

This guide is intended for users who wish to upload the project using FTP (FileZilla / WinSCP) and run it using Docker.

## Local Preparation (Your PC/Laptop)

1.  **Prepare Project Files**:
    Ensure your project files are ready. You **DO NOT** need to upload the following folders as they will be re-generated inside the container:
    - `node_modules/`
    - `vendor/`
    - `.git/`
    - `.env` (Will be created from `.env.example`)

    **Tip**: Compress the project folder into a `player.zip` file to speed up the upload process.

## Step 1: Upload to Server

1.  Open FileZilla or WinSCP.
2.  Log in to your VPS server.
3.  Upload the project file (or `player.zip`) to the destination folder, for example `/var/www/player`.
    - If using ZIP:

      ```bash
      # Login via Terminal / SSH
      ssh user@ip-server

      # Install unzip if not available
      sudo apt install unzip

      # Unzip file
      cd /var/www
      unzip player.zip -d player
      ```

## Step 2: Install Docker (If Not Installed)

If Docker is not yet installed on your server, you can use the helper script we provide:

```bash
cd /var/www/player
chmod +x deployment/docker/install_docker.sh
sudo ./deployment/docker/install_docker.sh
```

Or install manually according to the official Docker documentation.

## Step 3: Run Container

1.  Enter the project folder:

    ```bash
    cd /var/www/player
    ```

2.  Copy the environment file:

    ```bash
    cp .env.example .env
    ```

3.  Edit `.env` as needed (Database, App URL, etc.):

    ```bash
    nano .env
    ```

    - **IMPORTANT**: For database connection in Docker, use `DB_HOST=db` (matching the service name in docker-compose).

4.  Run Docker Compose (Build & Run):
    ```bash
    # Use the docker-compose file from the deployment/docker folder
    docker compose -f deployment/docker/docker-compose.yml up -d --build
    ```

## Step 4: App Setup (After Container Is Running)

Once the container is running, you need to execute several Laravel commands inside the `app` container:

1.  **Install Dependencies & Build Assets**:
    (Usually covered by Dockerfile, but if missing/failed):

    ```bash
    docker compose -f deployment/docker/docker-compose.yml exec app composer install
    docker compose -f deployment/docker/docker-compose.yml exec app npm install
    docker compose -f deployment/docker/docker-compose.yml exec app npm run build
    ```

2.  **Generate Key & Migrate Database**:

    ```bash
    docker compose -f deployment/docker/docker-compose.yml exec app php artisan key:generate
    docker compose -f deployment/docker/docker-compose.yml exec app php artisan migrate --force
    docker compose -f deployment/docker/docker-compose.yml exec app php artisan storage:link
    ```

3.  **Set Permissions**:
    ```bash
    docker compose -f deployment/docker/docker-compose.yml exec app chown -R www-data:www-data /var/www/html
    ```

## Troubleshooting

- **Web Access**: Open your browser and access `http://your-server-ip`.
- **Error Logs**: Check container logs if issues arise.
  ```bash
  docker compose -f deployment/docker/docker-compose.yml logs -f app
  ```
- **Puppeteer Error**: If the download feature fails, ensure the `PUPPETEER_EXECUTABLE_PATH` environment variable is correctly set in the Dockerfile (usually `/usr/bin/chromium`).
