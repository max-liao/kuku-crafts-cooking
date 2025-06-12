#!/bin/bash

# === CONFIG ===
LIGHTSAIL_IP="3.149.116.102"
PEM_PATH="$HOME/.ssh/LightsailDefaultKey-us-east-2.pem"
REMOTE_USER="bitnami"
REMOTE_TEMP="/home/bitnami/react-build-temp"
REMOTE_DEST="/opt/bitnami/wordpress/wp-content/themes/kuku-child/react-build"

# === STEP 1: Build React ===
echo "🛠️  Building React app..."
npm run build || { echo "❌ React build failed"; exit 1; }

# === STEP 2: Upload to temp folder ===
echo "🚀 Uploading build to $REMOTE_USER@$LIGHTSAIL_IP..."
scp -i "$PEM_PATH" -r ./build "$REMOTE_USER@$LIGHTSAIL_IP:$REMOTE_TEMP" || {
  echo "❌ SCP upload failed"; exit 1;
}

# === STEP 3: Replace old build on server ===
echo "🔁 Deploying build on remote server..."
ssh -i "$PEM_PATH" "$REMOTE_USER@$LIGHTSAIL_IP" << EOF
  sudo rm -rf "$REMOTE_DEST"/*
  sudo cp -r "$REMOTE_TEMP"/* "$REMOTE_DEST"/
  rm -rf "$REMOTE_TEMP"
  echo "✅ Deployment complete!"
EOF
