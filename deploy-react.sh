#!/bin/bash
set -e  # Exit immediately on any error

# ─────────────────────────────────────────────────────────────────────────────
# CONFIGURATION SECTION
# These variables define your build and deployment settings.
# ─────────────────────────────────────────────────────────────────────────────

LIGHTSAIL_IP="3.149.116.102"                 # Public IP of your AWS Lightsail instance
# If $PEM_PATH is already set (e.g. by GitHub Actions), use it; otherwise default:
PEM_PATH="${PEM_PATH:-$HOME/.ssh/lightsail_key.pem}"
REMOTE_USER="bitnami"                       # Default SSH user for Bitnami WordPress instances

# Local project paths
LOCAL_BUILD_DIR="app/public/wp-content/themes/kuku-child/react-build"         # Output of Vite build
LOCAL_FUNCTIONS_PHP="app/public/wp-content/themes/kuku-child/functions.php"   # Local WordPress functions.php

# Remote server paths
REMOTE_TEMP="/home/bitnami/react-build-temp"                                   # Temp folder to receive files
REMOTE_DEST="/opt/bitnami/wordpress/wp-content/themes/kuku-child/react-build" # Final folder for React build
REMOTE_FUNCTIONS_PATH="/opt/bitnami/wordpress/wp-content/themes/kuku-child/functions.php"

# ─────────────────────────────────────────────────────────────────────────────
# BUILD STEP (Runs Vite inside Docker)
# ─────────────────────────────────────────────────────────────────────────────

echo "🐳 Building React app inside Docker..."
./docker-build-react.sh

# Sanity check to ensure the build succeeded
if [ ! -f "$LOCAL_BUILD_DIR/index.html" ]; then
  echo "❌ Build failed: index.html not found in $LOCAL_BUILD_DIR"
  exit 1
fi

# ─────────────────────────────────────────────────────────────────────────────
# SYNC BUILD FOLDER TO REMOTE TEMP DIRECTORY
# ─────────────────────────────────────────────────────────────────────────────

echo "🚀 Uploading React build to Lightsail..."
rsync -az --delete -e "ssh -o StrictHostKeyChecking=no -i $PEM_PATH" \
      "$LOCAL_BUILD_DIR/" \
      "$REMOTE_USER@$LIGHTSAIL_IP:$REMOTE_TEMP/"

# ─────────────────────────────────────────────────────────────────────────────
# UPLOAD UPDATED PHP FILE
# ─────────────────────────────────────────────────────────────────────────────

echo "📦 Uploading updated functions.php..."
scp -o StrictHostKeyChecking=no -i "$PEM_PATH" \
    "$LOCAL_FUNCTIONS_PHP" \
    "$REMOTE_USER@$LIGHTSAIL_IP:/home/bitnami/functions.php"

# ─────────────────────────────────────────────────────────────────────────────
# APPLY CHANGES ON THE REMOTE SERVER
# ─────────────────────────────────────────────────────────────────────────────

echo "🔁 Finalizing deployment on Lightsail..."
ssh -o StrictHostKeyChecking=no -i "$PEM_PATH" "$REMOTE_USER@$LIGHTSAIL_IP" << EOF
  # Sync build to destination
  sudo rsync -az --delete "$REMOTE_TEMP/" "$REMOTE_DEST/"

  # Move updated functions.php into place
  sudo mv /home/bitnami/functions.php "$REMOTE_FUNCTIONS_PATH"

  # Clean up temporary files
  rm -rf "$REMOTE_TEMP"

  echo "✅ Deployment complete!"
EOF
