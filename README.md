# PHP Docker Playground

A simple Docker setup for testing PHP projects.

## Quick Start

1. **Build and run with Docker Compose:**
   ```bash
   docker-compose up --build
   ```

2. **Or build and run manually:**
   ```bash
   docker build -t php-playground -f docker/Dockerfile .
   docker run -p 8080:80 -v $(pwd)/web:/var/www/html php-playground
   ```

3. **Access your PHP app:**
   Open your browser and go to: http://localhost:8080

## Features

- PHP 8.2 with Apache (matches DreamHost shared hosting)
- MySQL 8.0 database server
- phpMyAdmin for database management
- Common PHP extensions: mysqli, pdo_mysql, gd, zip, opcache
- Apache configured for .htaccess support (AllowOverride All)
- Volume mounting for live code changes (no rebuild needed)
- Simple and minimal setup

## Project Structure

```
.
├── docker/
│   ├── Dockerfile      # PHP Apache container definition (DreamHost-matched)
│   └── .dockerignore  # Files to ignore in Docker build
├── docker-compose.yml # Easy container management
└── web/               # Your PHP files go here (git repo for deployment)
    ├── .github/
    │   └── workflows/
    │       └── deploy-dreamhost.yml  # GitHub Actions deployment workflow
    ├── index.php      # Sample PHP file
    └── DEPLOY.md      # Deployment setup instructions
```

## Matching DreamHost Environment

This Docker setup is configured to closely match DreamHost's shared hosting:
- **PHP Extensions**: mysqli, pdo_mysql, gd, zip, opcache (common on DreamHost)
- **Apache**: Configured with .htaccess support (AllowOverride All)
- **PHP Version**: 8.2 (you can change this in the Dockerfile if DreamHost uses a different version)

**To compare environments:**
1. Visit `http://localhost:8080/phpinfo.php` in your Docker container
2. Upload `phpinfo.php` to your DreamHost server
3. Compare the outputs to see any differences

## Deployment to DreamHost

This project includes GitHub Actions workflow for automatic deployment:

1. Initialize git in the `web/` folder: `cd web && git init`
2. Push to GitHub (see `web/DEPLOY.md` for details)
3. Configure GitHub Secrets with your DreamHost credentials
4. Push to `main` branch → automatic deployment!

See `web/DEPLOY.md` for complete setup instructions.

## Database Connection

MySQL is available with these credentials:

- **Host:** `mysql` (from PHP container) or `localhost` (from host machine)
- **Port:** `3407` (exposed to host, container uses 3306 internally)
- **Database:** `playground`
- **Root User:** `root` / `rootpassword`
- **Regular User:** `playground_user` / `playground_password`

**Test the connection:**
Visit http://localhost:9090/test-db.php to test your database connection.

**Connect from PHP:**
```php
$pdo = new PDO(
    "mysql:host=mysql;dbname=playground;charset=utf8mb4",
    "root",
    "rootpassword"
);
```

**Connect from host machine (using MySQL client):**
```bash
mysql -h 127.0.0.1 -P 3407 -u root -prootpassword playground
```

**Access phpMyAdmin:**
Visit http://localhost:9091 to access phpMyAdmin web interface.

Login credentials:
- **Server:** `mysql` (or leave default)
- **Username:** `root`
- **Password:** `rootpassword`

Or use the regular user:
- **Username:** `playground_user`
- **Password:** `playground_password`

## Tips

- Edit files in the `web/` directory and refresh your browser - changes are live!
- Stop the container: `docker compose down`
- View logs: `docker compose logs -f`
- View MySQL logs: `docker compose logs mysql`
- View phpMyAdmin logs: `docker compose logs phpmyadmin`
- Rebuild after Dockerfile changes: `docker compose up --build`
- MySQL data persists in a Docker volume (won't be lost when container stops)
- Access phpMyAdmin at http://localhost:9091 for easy database management