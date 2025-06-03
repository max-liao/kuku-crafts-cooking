# Kuku-Crafts-Cooking: Containerized WordPress Dev Environment

This project is a fully containerized WordPress development environment for rapid prototyping of custom themes, plugins, and React-based integrations.

---

## Stack Overview

- **WordPress**: PHP 8.2 + Apache, containerized via Docker.
- **MySQL**: Persistent database storage.
- **React App**: Integrated in `wp-content/` for modern UI components.
- **Custom Plugin**: `kuku-pinecone-of-the-day` plugin serves a daily pine cone image via REST API (`/wp-json/kuku/v1/pinecone`).
- **Local Assets**: Pine cone images stored locally in `wp-content/themes/kuku-child/assets/pinecones/`.

---

## Getting Started

### Prerequisites

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) or Docker Engine installed.

### Running Locally

1. Clone this repo (if not already):

   ```bash
   git clone https://github.com/max-liao/kuku-crafts-cooking.git
   cd kuku-crafts-cooking/app/public
   ```

2. Start the containers:

   ```bash
   docker compose up -d
   ```

3. Visit your site:

   ```
   http://localhost:8000
   ```

4. Complete the WordPress install by setting your site title, admin user, and password.

---

### Additional Features

- **Live React Integration**: React app in `wp-content/react-app` for dynamic front-end.
- **Custom Plugin**: Daily random pine cone images, served via REST API.
- **Query Monitor**: Use the Query Monitor plugin for real-time DB query profiling.
- **Local-only asset delivery**: Pine cone images served from local static assets for full control.

---

### Development Best Practices

- Only `wp-content/` and `docker-compose.yml` are tracked in Git.
- WP core files (`wp-admin/`, `wp-includes/`, etc.) are managed as external dependencies (not versioned here).
- Local image assets are stored for speed and control.
- PHP logs and Query Monitor for debugging and performance profiling.

---

### Summary

This project demonstrates:

- Containerized WordPress dev environment.
- Plugin + REST API extension.
- React + WordPress synergy.
- Secure, repeatable, version-controlled workflow.

Happy coding!
