# 🔐 GitHub Secrets Required

## 📋 Danh sách Secrets cần thiết

Để workflows chạy được, bạn **BẮT BUỘC** phải thêm các secrets sau vào GitHub repository.

### 1️⃣ DockerHub Credentials (BẮT BUỘC cho Deploy)

#### DOCKERHUB_USERNAME
- **Value**: `assassincreed2k1`
- **Sử dụng trong**: staging-deploy.yml, enhanced-production-deploy.yml, production-deploy.yml
- **Mục đích**: Login vào DockerHub để push images

#### DOCKERHUB_TOKEN
- **Value**: `<your-dockerhub-access-token>`
- **Sử dụng trong**: staging-deploy.yml, enhanced-production-deploy.yml, production-deploy.yml  
- **Mục đích**: Xác thực với DockerHub

**Cách lấy DockerHub Token**:
1. Login vào https://hub.docker.com
2. Account Settings → Security → Access Tokens
3. Click "New Access Token"
4. Description: `GitHub Actions - Hanaya Shop`
5. Permissions: **Read & Write**
6. Generate và **copy token ngay** (chỉ hiện 1 lần!)

### 2️⃣ Production Server Credentials (TÙY CHỌN - Chỉ cần khi deploy lên server thật)

#### PRODUCTION_SSH_KEY
- **Value**: Private SSH key để kết nối server production
- **Sử dụng trong**: enhanced-production-deploy.yml, production-deploy.yml
- **Mục đích**: SSH vào server để deploy

**Cách tạo SSH key**:
```bash
# Trên máy local
ssh-keygen -t ed25519 -C "github-actions-deploy" -f ~/.ssh/hanaya-deploy

# Copy private key
cat ~/.ssh/hanaya-deploy
# → Paste vào GitHub Secret PRODUCTION_SSH_KEY

# Copy public key vào server
ssh-copy-id -i ~/.ssh/hanaya-deploy.pub user@production-server
```

#### PRODUCTION_HOST
- **Value**: IP hoặc domain của production server
- **Example**: `203.0.113.10` hoặc `www.hanayashop.com`
- **Sử dụng trong**: enhanced-production-deploy.yml, production-deploy.yml

#### PRODUCTION_USER
- **Value**: Username SSH để login vào server
- **Example**: `ubuntu`, `root`, `deploy`
- **Sử dụng trong**: enhanced-production-deploy.yml, production-deploy.yml

## 🚀 Cách thêm Secrets vào GitHub

### Bước 1: Vào Settings
```
https://github.com/nguyentrungnghia270305/Hanaya-Shop/settings/secrets/actions
```

### Bước 2: Click "New repository secret"

### Bước 3: Thêm từng secret

**Ví dụ thêm DOCKERHUB_USERNAME**:
```
Name: DOCKERHUB_USERNAME
Secret: assassincreed2k1
```

**Ví dụ thêm DOCKERHUB_TOKEN**:
```
Name: DOCKERHUB_TOKEN
Secret: dckr_pat_... (token bạn vừa tạo)
```

### Bước 4: Verify

Sau khi thêm xong, vào lại Settings → Secrets:
- ✅ DOCKERHUB_USERNAME (Updated X ago)
- ✅ DOCKERHUB_TOKEN (Updated X ago)

## 📊 Workflow Requirements

### Staging Deploy (develop branch)
**Secrets cần thiết**:
- ✅ DOCKERHUB_USERNAME
- ✅ DOCKERHUB_TOKEN

**Secrets tùy chọn**:
- ⏭️ Không cần SSH credentials (chỉ build và push image)

### Production Deploy (main branch)
**Secrets cần thiết**:
- ✅ DOCKERHUB_USERNAME
- ✅ DOCKERHUB_TOKEN

**Secrets tùy chọn** (nếu deploy lên server):
- ⏭️ PRODUCTION_SSH_KEY
- ⏭️ PRODUCTION_HOST  
- ⏭️ PRODUCTION_USER

### Test Suite (develop, feature/*, hotfix/*)
**Secrets cần thiết**:
- ⏭️ Không cần secrets nào!

## ⚠️ Lưu ý quan trọng

### 1. Token Security
- ❌ **KHÔNG BAO GIỜ** commit token vào code
- ❌ **KHÔNG BAO GIỜ** chia sẻ token công khai
- ✅ **CHỈ** thêm vào GitHub Secrets
- ✅ Regenerate token định kỳ (3-6 tháng)

### 2. Token Expiration
- DockerHub tokens có thể set expiration
- Nếu token hết hạn, workflows sẽ fail
- Cần regenerate và update GitHub Secret

### 3. SSH Key Security
- Private key **KHÔNG BAO GIỜ** được commit
- Public key phải được thêm vào server (authorized_keys)
- Nên dùng key riêng cho CI/CD, không dùng personal key

## 🧪 Test Secrets

Sau khi thêm secrets, test bằng cách:

### 1. Test DockerHub Login
Push một commit nhỏ lên develop:
```bash
git commit --allow-empty -m "test: Trigger staging deploy to test DockerHub login"
git push origin develop
```

Check workflow tại: https://github.com/nguyentrungnghia270305/Hanaya-Shop/actions

Xem step "Login to DockerHub":
- ✅ Should see: "Login Succeeded"
- ❌ Nếu fail: Check lại DOCKERHUB_USERNAME và DOCKERHUB_TOKEN

### 2. Test SSH Connection (nếu có)
Workflow sẽ tự test SSH connection trong bước "Setup SSH Key Authentication"

## 🔧 Troubleshooting

### Error: "Error loading credentials - not logged in"
**Nguyên nhân**: Thiếu DOCKERHUB_USERNAME hoặc DOCKERHUB_TOKEN
**Giải pháp**: Thêm cả 2 secrets vào GitHub

### Error: "Error: Username and password required"  
**Nguyên nhân**: DOCKERHUB_TOKEN sai hoặc expired
**Giải pháp**: Regenerate token mới và update secret

### Error: "Permission denied (publickey)"
**Nguyên nhân**: SSH key không đúng hoặc chưa được thêm vào server
**Giải pháp**: 
1. Check PRODUCTION_SSH_KEY format (phải là private key đầy đủ)
2. Verify public key đã được thêm vào server

### Error: "Host key verification failed"
**Nguyên nhân**: Server chưa được trust
**Giải pháp**: Workflow đã tự động xử lý với `ssh-keyscan`

## 📝 Checklist

Trước khi deploy, đảm bảo:

### Staging Deploy (Tối thiểu)
- [ ] DOCKERHUB_USERNAME đã thêm
- [ ] DOCKERHUB_TOKEN đã thêm
- [ ] Token có Read & Write permissions
- [ ] Repository assassincreed2k1/hanaya-shop-exe tồn tại trên DockerHub

### Production Deploy (Đầy đủ)
- [ ] Tất cả secrets của Staging Deploy
- [ ] PRODUCTION_SSH_KEY đã thêm (nếu deploy lên server)
- [ ] PRODUCTION_HOST đã thêm (nếu deploy lên server)
- [ ] PRODUCTION_USER đã thêm (nếu deploy lên server)
- [ ] SSH public key đã được thêm vào server
- [ ] Server có Docker và docker-compose installed
- [ ] Directory /opt/hanaya-shop tồn tại trên server

## 🎯 Current Status

✅ **Bootstrap/cache error**: Fixed in workflows
✅ **YAML syntax errors**: Fixed
✅ **Docker configuration**: Updated
⏳ **GitHub Secrets**: Cần thêm DOCKERHUB credentials

**Next step**: Thêm DOCKERHUB_USERNAME và DOCKERHUB_TOKEN → Workflows sẽ chạy!
