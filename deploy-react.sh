#!/bin/bash
set -e  # Exit immediately if any command fails

# ── CONFIGURATION ────────────────────────────────────────────
LIGHTSAIL_IP="3.149.116.102"  # AWS Lightsail instance IP
PEM_PATH="$HOME/.ssh/LightsailDefaultKey-us-east-2.pem"  # SSH key for secure connection
REMOTE_USER="bitnami"  # Default user for Bitnami WordPress instances

# Local paths
LOCAL_BUILD_DIR="app/public/wp-content/themes/kuku-child/react-build"  # Output directory from Vite build
LOCAL_FUNCTIONS_PHP="app/public/wp-content/themes/kuku-child/functions.php"  # WordPress theme PHP file

# Remote paths
REMOTE_TEMP="/home/bitnami/react-build-temp"              # Temporary upload directory on the server
REMOTE_DEST="/opt/bitnami/wordpress/wp-content/themes/kuku-child/react-build"  # Final destination
REMOTE_FUNCTIONS_PATH="/opt/bitnami/wordpress/wp-content/themes/kuku-child/functions.php"
# ────────────────────────────────────────────────────────────

echo "🐳 Building React app inside Docker..."
./docker-build-react.sh  # Build the frontend (must match package.json config)

echo "🚀  Uploading build to Lightsail…"
rsync -az --delete -e "ssh -i $PEM_PATH" \
      "$LOCAL_BUILD_DIR/" \
      "$REMOTE_USER@$LIGHTSAIL_IP:$REMOTE_TEMP/"  # Upload the Vite output to a temporary folder

echo "📦  Uploading latest functions.php to Lightsail…"
scp -i "$PEM_PATH" "$LOCAL_FUNCTIONS_PHP" \
    "$REMOTE_USER@$LIGHTSAIL_IP:/home/bitnami/functions.php"  # Upload WordPress PHP file separately

echo "🔁  Moving build and PHP files into place on server…"
ssh -i "$PEM_PATH" "$REMOTE_USER@$LIGHTSAIL_IP" << EOF
  sudo rsync -az --delete "$REMOTE_TEMP/" "$REMOTE_DEST/"       # Move React build into WordPress theme folder
  sudo mv /home/bitnami/functions.php "$REMOTE_FUNCTIONS_PATH"  # Replace functions.php with the new one
  rm -rf "$REMOTE_TEMP"                                          # Clean up temp files
  echo "✅ Deployment complete!"
EOF
