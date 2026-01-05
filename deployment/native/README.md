# Native Deployment Guide (Ubuntu + Nginx) via FTP

This guide is intended for users who wish to upload the project using FTP (FileZilla / WinSCP) and run the automated installation script.

## Local Preparation (Your PC/Laptop)

1.  **Prepare Project Files**:
    Ensure your project files are ready. You **DO NOT** need to upload the following folders as they will be re-generated on the server:
    - `node_modules/` (Will be re-installed on the server)
    - `vendor/` (Will be re-installed on the server)
    - `.git/` (Not required)
    - `.env` (Will be created from `.env.example`)

    **Tip**: It is recommended to compress the entire project folder into a `player.zip` file (excluding the folders mentioned above) for faster upload.

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

## Step 2: Run Installation Script

1.  Enter the project folder via SSH Terminal:

    ```bash
    cd /var/www/player
    ```

2.  Grant execution permission to the setup script:

    ```bash
    chmod +x deployment/native/setup_ubuntu.sh
    ```

3.  Run the script as root (sudo):

    ```bash
    sudo ./deployment/native/setup_ubuntu.sh
    ```

4.  **Follow On-Screen Instructions**:
    - Enter your domain name (e.g., `player.mydomain.com`).
    - The script will automatically:
      - Update system & install PHP 8.2, Nginx, Node.js 20, Python, etc.
      - Install Puppeteer dependencies (Chromium libs).
      - Install Composer & NPM dependencies.
      - Setup Nginx config & SSL (basic).
      - Set folder permissions.

## Step 3: Verification

1.  Open your browser and access your domain.
2.  If a 500/Permission error occurs, re-run the permission command:
    ```bash
    sudo chown -R www-data:www-data /var/www/player
    sudo chmod -R 775 /var/www/player/storage
    ```

---

**Additional Notes:**

- **Database**: This script installs the `php8.2-mysql` driver but does not automatically create a MySQL database. Ensure you have created a MySQL database and user, then update the `.env` file after installation is complete.
  ```bash
  nano .env
  # Edit DB_DATABASE, DB_USERNAME, DB_PASSWORD
  ```
  Then run migration manually if needed:
  ```bash
  php artisan migrate
  ```
