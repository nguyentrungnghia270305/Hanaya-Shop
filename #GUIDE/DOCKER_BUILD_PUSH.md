# Docker Build & Push Guide

## Table of Contents
- [Docker Build \& Push Guide](#docker-build--push-guide)
  - [Table of Contents](#table-of-contents)
  - [Overview](#overview)
  - [Prerequisites](#prerequisites)
    - [System Requirements](#system-requirements)
  - [Docker Image Structure](#docker-image-structure)
    - [Base Images](#base-images)
  - [Building Docker Images](#building-docker-images)
    - [Development Build](#development-build)
    - [Production Build](#production-build)
    - [Build Arguments](#build-arguments)
  - [Tagging Strategies](#tagging-strategies)
    - [Semantic Versioning](#semantic-versioning)
    - [Environment-Based Tags](#environment-based-tags)
    - [Git-Based Tags](#git-based-tags)
  - [Pushing to Container Registry](#pushing-to-container-registry)
    - [Docker Hub](#docker-hub)
    - [GitHub Container Registry](#github-container-registry)
    - [Private Registry](#private-registry)
  - [Multi-Stage Builds](#multi-stage-builds)
    - [Stage 1: Dependencies](#stage-1-dependencies)
    - [Stage 2: Build Assets](#stage-2-build-assets)
    - [Stage 3: Production](#stage-3-production)
  - [Optimization Tips](#optimization-tips)
    - [Reduce Image Size](#reduce-image-size)
    - [Layer Caching](#layer-caching)
    - [Build Cache](#build-cache)
    - [Security Scanning](#security-scanning)
  - [Troubleshooting](#troubleshooting)
    - [Common Build Issues](#common-build-issues)
      - [Issue: Build fails due to network timeout](#issue-build-fails-due-to-network-timeout)
      - [Issue: Out of disk space](#issue-out-of-disk-space)
      - [Issue: Layer caching not working](#issue-layer-caching-not-working)
    - [Permission Issues](#permission-issues)
    - [Debugging Failed Builds](#debugging-failed-builds)
  - [CI/CD Integration](#cicd-integration)
    - [GitHub Actions](#github-actions)
    - [GitLab CI/CD](#gitlab-cicd)
    - [Jenkins Pipeline](#jenkins-pipeline)
  - [Best Practices](#best-practices)
    - [Image Naming](#image-naming)
    - [Version Control](#version-control)
    - [Security](#security)
    - [Documentation](#documentation)
  - [Additional Resources](#additional-resources)

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

### Security Scanning

Scan images for vulnerabilities before pushing:

```bash
# Using Docker Scout
docker scout cves hanaya-shop:latest

# Using Trivy
trivy image hanaya-shop:latest

# Using Snyk
snyk container test hanaya-shop:latest
```

## Troubleshooting

### Common Build Issues

#### Issue: Build fails due to network timeout

```bash
# Increase Docker build timeout
docker build --network host -t hanaya-shop:latest .

# Or use a mirror registry
docker build --build-arg COMPOSER_MIRROR=https://mirrors.aliyun.com/composer/ .
```

#### Issue: Out of disk space

```bash
# Clean up unused images and containers
docker system prune -af

# Remove build cache
docker builder prune -af

# Check disk usage
docker system df
```

#### Issue: Layer caching not working

```bash
# Disable cache and rebuild
docker build --no-cache -t hanaya-shop:latest .

# Or use specific cache source
docker build --cache-from hanaya-shop:cache -t hanaya-shop:latest .
```

### Permission Issues

```bash
# Fix permission denied errors
sudo chown -R $USER:$USER .

# Run with correct user in container
docker run --user $(id -u):$(id -g) hanaya-shop:latest
```

### Debugging Failed Builds

```bash
# Run intermediate stage for debugging
docker build --target dependencies -t debug-image .
docker run -it debug-image sh

# Check build logs
docker build --progress=plain -t hanaya-shop:latest . 2>&1 | tee build.log
```

## CI/CD Integration

### GitHub Actions

Create `.github/workflows/docker-build-push.yml`:

```yaml
name: Docker Build and Push

on:
  push:
    branches: [ main, develop ]
    tags: [ 'v*' ]
  pull_request:
    branches: [ main ]

env:
  REGISTRY: ghcr.io
  IMAGE_NAME: ${{ github.repository }}

jobs:
  build:
    runs-on: ubuntu-latest
    permissions:
      contents: read
      packages: write

    steps:
    - name: Checkout repository
      uses: actions/checkout@v4

    - name: Set up Docker Buildx
      uses: docker/setup-buildx-action@v3

    - name: Log in to Container registry
      if: github.event_name != 'pull_request'
      uses: docker/login-action@v3
      with:
        registry: ${{ env.REGISTRY }}
        username: ${{ github.actor }}
        password: ${{ secrets.GITHUB_TOKEN }}

    - name: Extract metadata
      id: meta
      uses: docker/metadata-action@v5
      with:
        images: ${{ env.REGISTRY }}/${{ env.IMAGE_NAME }}
        tags: |
          type=ref,event=branch
          type=ref,event=pr
          type=semver,pattern={{version}}
          type=semver,pattern={{major}}.{{minor}}
          type=sha

    - name: Build and push Docker image
      uses: docker/build-push-action@v5
      with:
        context: .
        push: ${{ github.event_name != 'pull_request' }}
        tags: ${{ steps.meta.outputs.tags }}
        labels: ${{ steps.meta.outputs.labels }}
        cache-from: type=gha
        cache-to: type=gha,mode=max
```

### GitLab CI/CD

Create `.gitlab-ci.yml`:

```yaml
variables:
  DOCKER_DRIVER: overlay2
  DOCKER_TLS_CERTDIR: "/certs"
  IMAGE_TAG: $CI_REGISTRY_IMAGE:$CI_COMMIT_REF_SLUG

stages:
  - build
  - push
  - deploy

build:
  stage: build
  image: docker:latest
  services:
    - docker:dind
  before_script:
    - docker login -u $CI_REGISTRY_USER -p $CI_REGISTRY_PASSWORD $CI_REGISTRY
  script:
    - docker build -t $IMAGE_TAG .
    - docker push $IMAGE_TAG
  only:
    - main
    - develop
    - tags

push-latest:
  stage: push
  image: docker:latest
  services:
    - docker:dind
  before_script:
    - docker login -u $CI_REGISTRY_USER -p $CI_REGISTRY_PASSWORD $CI_REGISTRY
  script:
    - docker pull $IMAGE_TAG
    - docker tag $IMAGE_TAG $CI_REGISTRY_IMAGE:latest
    - docker push $CI_REGISTRY_IMAGE:latest
  only:
    - main
```

### Jenkins Pipeline

Create `Jenkinsfile`:

```groovy
pipeline {
    agent any
    
    environment {
        DOCKER_REGISTRY = 'ghcr.io'
        IMAGE_NAME = 'hanaya-shop'
        DOCKER_CREDENTIALS = credentials('docker-registry-credentials')
    }
    
    stages {
        stage('Checkout') {
            steps {
                checkout scm
            }
        }
        
        stage('Build') {
            steps {
                script {
                    docker.build("${DOCKER_REGISTRY}/${IMAGE_NAME}:${env.BUILD_NUMBER}")
                }
            }
        }
        
        stage('Test') {
            steps {
                script {
                    docker.image("${DOCKER_REGISTRY}/${IMAGE_NAME}:${env.BUILD_NUMBER}").inside {
                        sh 'php artisan test'
                    }
                }
            }
        }
        
        stage('Push') {
            when {
                branch 'main'
            }
            steps {
                script {
                    docker.withRegistry("https://${DOCKER_REGISTRY}", 'docker-registry-credentials') {
                        docker.image("${DOCKER_REGISTRY}/${IMAGE_NAME}:${env.BUILD_NUMBER}").push()
                        docker.image("${DOCKER_REGISTRY}/${IMAGE_NAME}:${env.BUILD_NUMBER}").push('latest')
                    }
                }
            }
        }
    }
    
    post {
        always {
            cleanWs()
        }
    }
}
```

## Best Practices

### Image Naming

Follow consistent naming conventions:

```
[registry/][username/]repository[:tag]

Examples:
- hanaya-shop:latest
- ghcr.io/nguyentrungnghia270305/hanaya-shop:v1.0.0
- registry.example.com/team/hanaya-shop:staging
```

### Version Control

- Tag all production images with semantic versions
- Use commit SHA for traceability
- Keep development images separate from production

### Security

1. **Don't store secrets in images**:
   ```bash
   # Use build secrets (BuildKit)
   docker build --secret id=env,src=.env.production .
   ```

2. **Run as non-root user**:
   ```dockerfile
   USER www-data
   ```

3. **Scan regularly**:
   ```bash
   docker scout cves --only-severity critical,high hanaya-shop:latest
   ```

### Documentation

Include these labels in your Dockerfile:

```dockerfile
LABEL org.opencontainers.image.title="Hanaya Shop"
LABEL org.opencontainers.image.description="E-commerce platform for flower shop"
LABEL org.opencontainers.image.version="1.0.0"
LABEL org.opencontainers.image.authors="Nguyen Trung Nghia <email@example.com>"
LABEL org.opencontainers.image.source="https://github.com/nguyentrungnghia270305/Hanaya-Shop"
LABEL org.opencontainers.image.licenses="MIT"
```

## Additional Resources

- [Docker Documentation](https://docs.docker.com/)
- [Docker Best Practices](https://docs.docker.com/develop/dev-best-practices/)
- [Dockerfile Reference](https://docs.docker.com/engine/reference/builder/)
- [Docker Compose Documentation](https://docs.docker.com/compose/)
- [Container Registry Documentation](https://docs.github.com/en/packages/working-with-a-github-packages-registry/working-with-the-container-registry)

---

*Last updated: November 2025*
