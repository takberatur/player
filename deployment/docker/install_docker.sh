#!/bin/bash

# Script Install Docker & Docker Compose on Ubuntu 22.04
# Run as root or sudo

set -e

echo "Installing Docker..."

# Add Docker's official GPG key:
apt-get update
apt-get install -y ca-certificates curl
install -m 0755 -d /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/ubuntu/gpg -o /etc/apt/keyrings/docker.asc
chmod a+r /etc/apt/keyrings/docker.asc

# Add the repository to Apt sources:
echo \
  "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.asc] https://download.docker.com/linux/ubuntu \
  $(. /etc/os-release && echo "$VERSION_CODENAME") stable" | \
  tee /etc/apt/sources.list.d/docker.list > /dev/null
apt-get update

# Install Docker packages
apt-get install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin

echo "Docker installed successfully!"
docker --version
docker compose version

echo "Next steps:"
echo "1. Ensure you are in the project root folder (where .env is located)"
echo "2. Run: 'docker compose --env-file .env -f deployment/docker/docker-compose.yml up -d --build'"
echo "3. Run migrations: 'docker compose --env-file .env -f deployment/docker/docker-compose.yml exec app php artisan migrate'"
