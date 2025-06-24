#!/bin/bash

set -e

# Name for the image we'll build
IMAGE_NAME="kuku-react-builder"

# Docker build context: real path to the React app folder
CONTEXT_PATH="app/public/wp-content/react-app"

# Path where the React build output will be extracted on the host
OUTPUT_DIR="app/public/wp-content/themes/kuku-child/react-build"

echo "🛠️ Building Docker image for React app..."
docker build -t "$IMAGE_NAME" "$CONTEXT_PATH"

echo "📦 Creating a container from the image (without running it)..."
CONTAINER_ID=$(docker create "$IMAGE_NAME")

echo "📁 Preparing build output directory..."
mkdir -p "$OUTPUT_DIR"

echo "📤 Copying build output from container to host..."
docker cp "$CONTAINER_ID":/app/output/. "$OUTPUT_DIR"

echo "🧹 Cleaning up temporary container..."
docker rm "$CONTAINER_ID"

echo "✅ Dockerized build complete."
