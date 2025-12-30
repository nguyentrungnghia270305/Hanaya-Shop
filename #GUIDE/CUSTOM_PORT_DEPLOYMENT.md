# 🚀 Custom Port Deployment Configuration

## 📋 Deployment Strategy

Dự án này sẽ deploy lên **cùng server** nhưng **port khác** thay vì www.hanayashop.com:
- **Main production**: Port 80 (dự án khác đang chạy)
- **Hanaya Shop**: Port 8080 (hoặc port tùy chọn)

## 🐳 DockerHub Configuration

**Repository**: `assassincreed2k1/hanaya-shop-exe`
**Token**: Xem hướng dẫn lấy token trong [GITHUB_SECRETS_REQUIRED.md](./GITHUB_SECRETS_REQUIRED.md)

## 🔧 Required GitHub Secrets

### DockerHub (BẮT BUỘC)
```
DOCKERHUB_USERNAME: assassincreed2k1
DOCKERHUB_TOKEN: <your-dockerhub-access-token>
```

Xem cách lấy token: [GITHUB_SECRETS_REQUIRED.md](./GITHUB_SECRETS_REQUIRED.md)

### Production Server (Cho custom port deployment)
```
PRODUCTION_SSH_KEY: <your-ssh-private-key>
PRODUCTION_HOST: <your-server-ip-or-domain>
PRODUCTION_USER: <ssh-username>
HANAYA_PORT: 8080  # Custom port cho Hanaya Shop
```

## 📦 Docker Compose Configuration

File: `/opt/hanaya-shop/docker-compose.yml` trên server

```yaml
version: '3.8'

services:
  hanaya-app:
    image: assassincreed2k1/hanaya-shop-exe:latest
    container_name: hanaya-app
    restart: unless-stopped
    ports:
      - "8080:80"  # Map port 8080 external -> 80 internal
    environment:
      - APP_ENV=production
      - APP_DEBUG=false
      - APP_KEY=${APP_KEY}
      - DB_HOST=${DB_HOST}
      - DB_PORT=${DB_PORT}
      - DB_DATABASE=${DB_DATABASE}
      - DB_USERNAME=${DB_USERNAME}
      - DB_PASSWORD=${DB_PASSWORD}
    volumes:
      - ./storage:/var/www/html/storage
      - ./.env:/var/www/html/.env
    networks:
      - hanaya-network

networks:
  hanaya-network:
    driver: bridge
```

## 🌐 Access URLs

### Development
```
http://localhost:8080
```

### Production
```
http://<your-server-ip>:8080
# hoặc
http://<your-domain>:8080
```

### Staging (nếu có)
```
http://<your-server-ip>:8081
```

## 🔄 Deployment Script

File: `/opt/hanaya-shop/scripts/update-image.sh`

```bash
#!/bin/bash
set -e

echo "🚀 Updating Hanaya Shop on custom port..."

# Pull latest image
docker pull assassincreed2k1/hanaya-shop-exe:latest

# Stop and remove old container
docker-compose down

# Start new container
docker-compose up -d

# Wait for startup
sleep 10

# Health check
if curl -f http://localhost:8080/health > /dev/null 2>&1; then
    echo "✅ Hanaya Shop is healthy on port 8080!"
else
    echo "⚠️ Health check failed, check logs:"
    docker logs hanaya-app --tail 50
fi
```

## 🔐 Security Considerations

### Firewall Rules
```bash
# Allow port 8080 for Hanaya Shop
sudo ufw allow 8080/tcp
sudo ufw reload
```

### Nginx Reverse Proxy (Optional)
Nếu muốn dùng subdomain thay vì port:

```nginx
# /etc/nginx/sites-available/hanaya.conf
server {
    listen 80;
    server_name hanaya.yourdomain.com;

    location / {
        proxy_pass http://localhost:8080;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

## 📊 Server Setup Steps

### 1. Create Project Directory
```bash
ssh user@your-server
sudo mkdir -p /opt/hanaya-shop/{scripts,storage}
sudo chown -R $USER:$USER /opt/hanaya-shop
cd /opt/hanaya-shop
```

### 2. Create .env File
```bash
cat > .env << 'EOF'
APP_NAME="Hanaya Shop"
APP_ENV=production
APP_KEY=base64:your-generated-key-here
APP_DEBUG=false
APP_TIMEZONE=UTC
APP_URL=http://your-server-ip:8080

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=hanaya_shop
DB_USERNAME=hanaya_user
DB_PASSWORD=your-secure-password

# Add other environment variables...
EOF
```

### 3. Create docker-compose.yml
```bash
# Copy the configuration above
```

### 4. Create Update Script
```bash
chmod +x scripts/update-image.sh
```

### 5. Setup SSH Key
```bash
# On your local machine
ssh-keygen -t ed25519 -C "github-actions-hanaya" -f ~/.ssh/hanaya-deploy

# Copy public key to server
ssh-copy-id -i ~/.ssh/hanaya-deploy.pub user@your-server

# Add private key to GitHub Secrets as PRODUCTION_SSH_KEY
cat ~/.ssh/hanaya-deploy
```

## 🧪 Testing Deployment

### Local Test
```bash
# Pull image
docker pull assassincreed2k1/hanaya-shop-exe:latest

# Run on port 8080
docker run -d -p 8080:80 \
  -e APP_ENV=production \
  -e APP_KEY=base64:test-key \
  --name hanaya-test \
  assassincreed2k1/hanaya-shop-exe:latest

# Test
curl http://localhost:8080
```

### Server Test
```bash
# SSH to server
ssh user@your-server

# Navigate to project
cd /opt/hanaya-shop

# Pull and run
docker-compose pull
docker-compose up -d

# Check logs
docker-compose logs -f

# Test health
curl http://localhost:8080/health
```

## 🎯 Workflow Configuration

Workflows đã được cấu hình để:
- Build image và push lên `assassincreed2k1/hanaya-shop-exe`
- Deploy lên server với port 8080
- Health check trên custom port

## 📝 Notes

1. **Port 80 conflict**: Dự án khác đang dùng → Hanaya dùng port 8080
2. **Database**: Có thể share MySQL server, dùng database riêng
3. **Storage**: Volume mount để persistent data
4. **Logs**: `docker logs hanaya-app` để debug
5. **Updates**: CI/CD tự động pull image mới và restart

## 🚨 Troubleshooting

### Port already in use
```bash
# Check what's using port 8080
sudo lsof -i :8080
sudo netstat -tulpn | grep 8080

# Stop conflicting service or choose different port
```

### Container won't start
```bash
# Check logs
docker logs hanaya-app

# Check Docker daemon
sudo systemctl status docker

# Check disk space
df -h
```

### Can't access from outside
```bash
# Check firewall
sudo ufw status
sudo ufw allow 8080/tcp

# Check Docker port binding
docker ps
docker port hanaya-app
```
