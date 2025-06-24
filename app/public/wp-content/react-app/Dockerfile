# ─── Stage 1: Build ─────────────────────────────
FROM node:18 AS builder

WORKDIR /app
COPY . .

# ✅ Install rsync BEFORE build
RUN apt-get update && apt-get install -y rsync

RUN npm ci
RUN npm run build

# ✅ Copy to /app/output (not nested inside `build`)
RUN mkdir /app/output && cp -r build/* /app/output/
