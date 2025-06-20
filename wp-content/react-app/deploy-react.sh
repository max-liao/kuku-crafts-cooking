#!/bin/bash
set -e

# ── CONFIG ────────────────────────────────────────────────
LIGHTSAIL_IP="3.149.116.102"
PEM_PATH="$HOME/.ssh/LightsailDefaultKey-us-east-2.pem"
REMOTE_USER="bitnami"

LOCAL_BUILD_DIR="../themes/kuku-child/react-build"          # where Vite writes
LOCAL_FUNCTIONS_PHP="../themes/kuku-child/functions.php"    # ← add this
REMOTE_TEMP="/home/bitnami/react-build-temp"                # no sudo needed
REMOTE_DEST="/opt/bitnami/wordpress/wp-content/themes/kuku-child/react-build"
REMOTE_FUNCTIONS_PATH="/opt/bitnami/wordpress/wp-content/themes/kuku-child/functions.php"
# ──────────────────────────────────────────────────────────

echo "🛠  Building React app with Vite…"
npm run build   # make sure your package.json has: "build": "vite build"

echo "🚀  Uploading build to Lightsail…"
rsync -az --delete -e "ssh -i $PEM_PATH" \
      "$LOCAL_BUILD_DIR/" \
      "$REMOTE_USER@$LIGHTSAIL_IP:$REMOTE_TEMP/"

echo "📦  Uploading latest functions.php to Lightsail…"
scp -i "$PEM_PATH" "$LOCAL_FUNCTIONS_PHP" \
    "$REMOTE_USER@$LIGHTSAIL_IP:/home/bitnami/functions.php"

echo "🔁  Moving build and PHP files into place on server…"
ssh -i "$PEM_PATH" "$REMOTE_USER@$LIGHTSAIL_IP" << EOF
  sudo rsync -az --delete "$REMOTE_TEMP/" "$REMOTE_DEST/"
  sudo mv /home/bitnami/functions.php "$REMOTE_FUNCTIONS_PATH"
  rm -rf "$REMOTE_TEMP"
  echo "✅ Deployment complete!"
EOF
