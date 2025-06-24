#!/bin/bash
set -e

echo "🐳 Building React app inside Docker..."

docker build -t kuku-react-builder ./app/public/wp-content/react-app

echo "📦 Running container to extract build to react-build/..."

docker run --rm \
  -v "$PWD/app/public/wp-content/themes/kuku-child/react-build:/app/build" \
  kuku-react-builder

echo "✅ Dockerized build complete."
