# Docker Build & Push Guide

## Table of Contents
- [Overview](#overview)
- [Prerequisites](#prerequisites)
- [Docker Image Structure](#docker-image-structure)
- [Building Docker Images](#building-docker-images)
- [Tagging Strategies](#tagging-strategies)
- [Pushing to Container Registry](#pushing-to-container-registry)
- [Multi-Stage Builds](#multi-stage-builds)
- [Optimization Tips](#optimization-tips)
- [Troubleshooting](#troubleshooting)

## Overview

This guide provides comprehensive instructions for building and pushing Docker images for the Hanaya Shop application. The application uses a containerized architecture with optimized builds for production deployment.

## Prerequisites

Before you begin, ensure you have the following installed:

- Docker Engine 20.10+ or Docker Desktop
- Docker Compose 2.0+
- Access to a container registry (Docker Hub, GitHub Container Registry, or private registry)
- Git for version control

### System Requirements

- **CPU**: 2+ cores recommended
- **RAM**: 4GB minimum, 8GB recommended
- **Disk Space**: 10GB free space for images and layers
- **OS**: Linux, macOS, or Windows with WSL2

## Docker Image Structure

The Hanaya Shop application uses a multi-container architecture:

```
hanaya-shop/
├── app/              # Laravel application container
├── nginx/            # Web server container
├── mysql/            # Database container
└── redis/            # Cache container
```

### Base Images

- **Application**: `php:8.2-fpm-alpine`
- **Web Server**: `nginx:alpine`
- **Database**: `mysql:8.0`
- **Cache**: `redis:alpine`

## Building Docker Images

### Development Build

For local development with hot-reload and debugging enabled:

```bash
# Build all services
docker-compose build

# Build specific service
docker-compose build app

# Build with no cache
docker-compose build --no-cache

# Build with build arguments
docker-compose build --build-arg PHP_VERSION=8.2
```

### Production Build

For optimized production deployment:

```bash
# Build production image
docker build -t hanaya-shop:latest \
  --target production \
  --build-arg APP_ENV=production \
  --build-arg BUILD_DATE=$(date -u +'%Y-%m-%dT%H:%M:%SZ') \
  --build-arg VCS_REF=$(git rev-parse --short HEAD) \
  -f Dockerfile .

# Build with specific PHP version
docker build -t hanaya-shop:php8.2 \
  --build-arg PHP_VERSION=8.2 \
  -f Dockerfile .
```

### Build Arguments

Available build arguments for customization:

| Argument | Description | Default |
|----------|-------------|---------|
| `PHP_VERSION` | PHP version to use | `8.2` |
| `NODE_VERSION` | Node.js version for assets | `18` |
| `APP_ENV` | Application environment | `production` |
| `BUILD_DATE` | Build timestamp | Current date |
| `VCS_REF` | Git commit reference | Current commit |

## Tagging Strategies

### Semantic Versioning

Follow semantic versioning for production releases:

```bash
# Major.Minor.Patch
docker tag hanaya-shop:latest hanaya-shop:1.0.0
docker tag hanaya-shop:latest hanaya-shop:1.0
docker tag hanaya-shop:latest hanaya-shop:1
```

### Environment-Based Tags

```bash
# Development
docker tag hanaya-shop:latest hanaya-shop:dev

# Staging
docker tag hanaya-shop:latest hanaya-shop:staging

# Production
docker tag hanaya-shop:latest hanaya-shop:prod
```

### Git-Based Tags

```bash
# Branch name
docker tag hanaya-shop:latest hanaya-shop:feature-admin-post-management

# Commit SHA
docker tag hanaya-shop:latest hanaya-shop:$(git rev-parse --short HEAD)

# Tag
docker tag hanaya-shop:latest hanaya-shop:v1.0.0
```

## Pushing to Container Registry

### Docker Hub

```bash
# Login to Docker Hub
docker login

# Tag with username
docker tag hanaya-shop:latest username/hanaya-shop:latest

# Push image
docker push username/hanaya-shop:latest

# Push all tags
docker push username/hanaya-shop --all-tags
```

### GitHub Container Registry

```bash
# Login with GitHub token
echo $GITHUB_TOKEN | docker login ghcr.io -u USERNAME --password-stdin

# Tag with GitHub registry
docker tag hanaya-shop:latest ghcr.io/nguyentrungnghia270305/hanaya-shop:latest

# Push to GitHub
docker push ghcr.io/nguyentrungnghia270305/hanaya-shop:latest
```

### Private Registry

```bash
# Login to private registry
docker login registry.example.com

# Tag for private registry
docker tag hanaya-shop:latest registry.example.com/hanaya-shop:latest

# Push to private registry
docker push registry.example.com/hanaya-shop:latest
```

## Multi-Stage Builds

The Dockerfile uses multi-stage builds for optimization:

### Stage 1: Dependencies

```dockerfile
FROM php:8.2-fpm-alpine AS dependencies

# Install system dependencies
RUN apk add --no-cache \
    curl \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    zip \
    unzip

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd
```

### Stage 2: Build Assets

```dockerfile
FROM node:18-alpine AS assets

WORKDIR /app
COPY package*.json ./
RUN npm ci --only=production

COPY resources ./resources
COPY vite.config.js tailwind.config.js postcss.config.js ./
RUN npm run build
```

### Stage 3: Production

```dockerfile
FROM php:8.2-fpm-alpine AS production

WORKDIR /var/www/html

# Copy dependencies from dependencies stage
COPY --from=dependencies /usr/local/lib/php/extensions/ /usr/local/lib/php/extensions/
COPY --from=dependencies /usr/local/etc/php/conf.d/ /usr/local/etc/php/conf.d/

# Copy built assets from assets stage
COPY --from=assets /app/public/build ./public/build

# Copy application code
COPY . .

# Install composer dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
```

## Optimization Tips

### Reduce Image Size

1. **Use Alpine-based images**:
   ```dockerfile
   FROM php:8.2-fpm-alpine
   ```

2. **Multi-stage builds**: Separate build and runtime dependencies

3. **Clean up package managers**:
   ```dockerfile
   RUN apk add --no-cache package \
       && rm -rf /var/cache/apk/*
   ```

4. **Remove development dependencies**:
   ```bash
   composer install --no-dev --optimize-autoloader
   npm ci --only=production
   ```

### Layer Caching

Order Dockerfile instructions from least to most frequently changed:

```dockerfile
# System packages (rarely change)
RUN apk add --no-cache curl libpng-dev

# PHP extensions (rarely change)
RUN docker-php-ext-install pdo_mysql

# Composer dependencies (change occasionally)
COPY composer.json composer.lock ./
RUN composer install --no-dev

# Application code (changes frequently)
COPY . .
```

### Build Cache

```bash
# Enable BuildKit for better caching
export DOCKER_BUILDKIT=1

# Build with cache from registry
docker build --cache-from hanaya-shop:latest -t hanaya-shop:latest .

# Use inline cache
docker build --build-arg BUILDKIT_INLINE_CACHE=1 -t hanaya-shop:latest .
```
