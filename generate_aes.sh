#!/bin/bash

# Generate AES-256-CTR Key and IV
echo "Generating AES-256-CTR credentials..."

KEY=$(openssl rand -base64 32)
IV=$(openssl rand -base64 16)

echo ""
echo "=== Copy these to your code ==="
echo ""
echo "const encryption_key = '$KEY';"
echo "const encryption_iv = '$IV';"
echo ""
echo "=== PHP Format ==="
echo "\$encryption_key = '$KEY';"
echo "\$encryption_iv = '$IV';"
