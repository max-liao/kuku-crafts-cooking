# ─── Stage 1: Build ─────────────────────────────
FROM node:18 AS builder

WORKDIR /app
COPY . .

# Install rsync for use in post-build step
RUN apt-get update && apt-get install -y rsync

RUN npm ci
RUN npm run build

# ─── Stage 2: (optional) Serve with Nginx or just export ───────
# FROM nginx:alpine
# COPY --from=builder /app/build /usr/share/nginx/html
# EXPOSE 80
# CMD ["nginx", "-g", "daemon off;"]
