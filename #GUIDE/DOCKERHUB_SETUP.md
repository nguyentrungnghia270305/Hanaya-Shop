# 🐳 DockerHub Setup Guide

## DockerHub Configuration

### Image Repository
- **Repository**: `assassincreed2k1/hanaya-shop-exe`
- **Tags**:
  - `latest` - Production version
  - `staging` - Staging version
  - `<git-sha>` - Specific version by commit

### GitHub Secrets Required

Bạn cần thêm 2 secrets vào GitHub repository:

1. **DOCKERHUB_USERNAME**
   - Value: `assassincreed2k1`
   
2. **DOCKERHUB_TOKEN**
   - Value: `<your-dockerhub-access-token>`
   - ⚠️ **Lấy token từ**: https://hub.docker.com/settings/security
   - Click "New Access Token" → Copy token và paste vào GitHub Secret

### Cách thêm GitHub Secrets

1. Vào repository: https://github.com/nguyentrungnghia270305/Hanaya-Shop
2. Settings → Secrets and variables → Actions
3. Click "New repository secret"
4. Thêm 2 secrets:
   ```
   Name: DOCKERHUB_USERNAME
   Secret: assassincreed2k1
   ```
   ```
   Name: DOCKERHUB_TOKEN
   Secret: <your-dockerhub-access-token>
   ```

**Lấy DockerHub Access Token**:
1. Login vào https://hub.docker.com
2. Vào Account Settings → Security
3. Click "New Access Token"
4. Description: "GitHub Actions Hanaya-Shop"
5. Permissions: Read & Write
6. Generate token và copy ngay (chỉ hiện 1 lần)

### Docker Images được tạo

#### Staging Deploy (develop branch)
- `assassincreed2k1/hanaya-shop-exe:staging`
- `assassincreed2k1/hanaya-shop-exe:staging-<git-sha>`

#### Production Deploy (main branch)
- `assassincreed2k1/hanaya-shop-exe:latest`
- `assassincreed2k1/hanaya-shop-exe:<git-sha>`

### Pull Images

```bash
# Pull staging image
docker pull assassincreed2k1/hanaya-shop-exe:staging

# Pull production image
docker pull assassincreed2k1/hanaya-shop-exe:latest

# Pull specific version
docker pull assassincreed2k1/hanaya-shop-exe:<git-sha>
```

### Run Container Locally

```bash
# Run staging
docker run -d -p 80:80 \
  -e APP_ENV=staging \
  -e APP_KEY=base64:your-key-here \
  -e DB_HOST=your-db-host \
  --name hanaya-staging \
  assassincreed2k1/hanaya-shop-exe:staging

# Run production
docker run -d -p 80:80 \
  -e APP_ENV=production \
  -e APP_KEY=base64:your-key-here \
  -e DB_HOST=your-db-host \
  --name hanaya-prod \
  assassincreed2k1/hanaya-shop-exe:latest
```

### Dockerfile Improvements

✅ Fixed bootstrap/cache directory issue:
- Tạo `bootstrap/cache` directory trước khi chạy `composer install`
- Set permissions đúng cho directory

✅ Removed `--no-scripts` flag:
- Cho phép Composer chạy post-install scripts
- Giúp Laravel package discovery hoạt động đúng

### Verification

Sau khi push code lên GitHub:

1. **Check GitHub Actions**:
   - Vào tab "Actions" trong repository
   - Xem workflow đang chạy
   - Đảm bảo "Login to DockerHub" step thành công

2. **Check DockerHub**:
   - Vào https://hub.docker.com/r/assassincreed2k1/hanaya-shop-exe
   - Xem images mới được push
   - Kiểm tra tags và sizes

3. **Test Pull**:
   ```bash
   docker pull assassincreed2k1/hanaya-shop-exe:staging
   docker images | grep hanaya
   ```

## Troubleshooting

### Error: "unauthorized: incorrect username or password"
- Kiểm tra lại DOCKERHUB_USERNAME và DOCKERHUB_TOKEN trong GitHub Secrets
- Token phải là Access Token, không phải password

### Error: "bootstrap/cache directory must be present"
- Đã fix trong Dockerfile mới
- Bootstrap/cache directory được tạo trước khi composer install

### Error: "repository does not exist"
- Đảm bảo repository `assassincreed2k1/hanaya-shop-exe` đã được tạo trên DockerHub
- Hoặc tạo tự động bằng cách push lần đầu

## Next Steps

1. ✅ Thêm GitHub Secrets (DOCKERHUB_USERNAME, DOCKERHUB_TOKEN)
2. ✅ Push code lên develop branch để test staging deploy
3. ✅ Kiểm tra GitHub Actions workflow
4. ✅ Verify image trên DockerHub
5. ✅ Merge vào main để deploy production
