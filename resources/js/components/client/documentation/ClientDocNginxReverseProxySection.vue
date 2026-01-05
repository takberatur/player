<template>
  <div class="prose prose-slate dark:prose-invert max-w-none space-y-6">
    <div>
      <h1 class="text-3xl font-bold text-foreground">
        Nginx Reverse Proxy Setup
      </h1>
      <p class="mt-2 text-muted-foreground">
        Learn how to configure Nginx on your host machine to serve your
        Dockerized application using a domain name and SSL.
      </p>
    </div>

    <div class="prose dark:prose-invert max-w-none">
      <div class="rounded-lg border bg-card p-6">
        <h3 class="mt-0 text-xl font-semibold">Why use a Reverse Proxy?</h3>
        <p>
          While your Docker container exposes the application on a specific port
          (e.g., <code>8000</code>), you typically want users to access it via a
          standard domain name (like <code>yourdomain.com</code>) on port 80/443
          without specifying a port number. A Reverse Proxy handles this traffic
          forwarding and manages SSL certificates.
        </p>
      </div>

      <h3>1. Install Nginx on Host</h3>
      <p>If you haven't installed Nginx on your Ubuntu host yet, run:</p>
      <pre><code class="language-bash">sudo apt update
sudo apt install nginx -y</code></pre>

      <h3>2. Create Configuration File</h3>
      <p>
        Create a new configuration file for your site in
        <code>/etc/nginx/sites-available/</code>. Replace
        <code>forge-player</code> with your app name.
      </p>
      <pre><code class="language-bash">sudo nano /etc/nginx/sites-available/forge-player</code></pre>

      <p>
        Paste the following configuration, adjusting the
        <strong>server_name</strong> and <strong>proxy_pass</strong> port to
        match your setup:
      </p>

      <pre><code class="language-nginx">server {
    listen 80;
    server_name yourdomain.com; # Change this to your domain

    # Max upload size (adjust as needed for video uploads)
    client_max_body_size 100M;

    location / {
        # Forward traffic to the Docker Container
        # Make sure port 8000 matches your APP_PORT in .env
        proxy_pass http://127.0.0.1:8000; 
        
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_cache_bypass $http_upgrade;
        
        # Standard Headers
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}</code></pre>

      <h3>3. Enable the Site</h3>
      <p>Create a symbolic link to enable the configuration:</p>
      <pre><code class="language-bash">sudo ln -s /etc/nginx/sites-available/forge-player /etc/nginx/sites-enabled/</code></pre>

      <h3>4. Unlink Default Site</h3>
      <pre><code class="language-bash">sudo unlink /etc/nginx/sites-enabled/default</code></pre>

      <h3>5. Test and Restart Nginx</h3>
      <p>Verify the configuration syntax and restart Nginx:</p>
      <pre><code class="language-bash">sudo nginx -t
sudo systemctl restart nginx</code></pre>

      <h3>6. Setup SSL with Certbot (Recommended)</h3>
      <p>Secure your site with a free SSL certificate from Let's Encrypt:</p>
      <pre><code class="language-bash"># Install Certbot
sudo apt install certbot python3-certbot-nginx -y

# Obtain and install certificate
sudo certbot --nginx -d yourdomain.com</code></pre>
      <p>
        Certbot will automatically update your Nginx configuration to use HTTPS.
      </p>
    </div>
  </div>
</template>
