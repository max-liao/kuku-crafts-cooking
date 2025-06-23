# Use an official Node image to build the app
FROM node:18 AS builder

WORKDIR /app
RUN apt-get update && apt-get install -y rsync

COPY . .

RUN npm install && npm run build

# Serve using Nginx
FROM nginx:alpine
COPY --from=builder /app/build /usr/share/nginx/html

EXPOSE 80
CMD ["nginx", "-g", "daemon off;"]
