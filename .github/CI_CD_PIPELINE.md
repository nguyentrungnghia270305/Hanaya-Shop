# CI/CD Pipeline Documentation

## 📋 Overview

This document provides comprehensive documentation for the Hanaya Shop CI/CD pipeline implemented using GitHub Actions. The pipeline ensures code quality, security, and reliable deployments to production.

## 🎯 Pipeline Goals

1. **Automated Testing**: Run comprehensive tests on every commit
2. **Code Quality**: Enforce coding standards and static analysis
3. **Security**: Scan for vulnerabilities and malicious code
4. **Docker Build Gating**: Validate Docker images before deployment
5. **Production Deployment**: Deploy only after all CI checks pass
6. **Automatic Rollback**: Revert to previous version on deployment failure
7. **Notifications**: Alert team members about deployment status

## 🔄 Workflow Stages

### Stage 1: Debug Information (Always Runs)

**Job**: `debug-info`

**Purpose**: Collect comprehensive environment information for troubleshooting

**Steps**:
- Display workflow context (branch, commit, actor)
- Show system information (OS, CPU, memory, disk)
- Display Docker information and versions
- List GitHub Actions environment variables
- Show changed files in the commit
- Display available language runtimes (PHP, Node)
- Show network configuration
- List installed tools and versions
- Check for CI configuration files
- Display project structure

**When it runs**: On every workflow execution, regardless of outcome

**Duration**: ~30 seconds

---

### Stage 2: Code Quality Checks

**Job**: `code-quality`

**Purpose**: Validate code syntax and dependencies

**Dependencies**: `debug-info`

**Steps**:
1. **Checkout code**: Clone the repository
2. **Setup PHP**: Install PHP 8.2 with required extensions
3. **Cache dependencies**: Cache Composer packages for faster builds
4. **Install dependencies**: Run `composer install`
5. **Check PHP syntax**: Validate all PHP files
6. **Validate composer.json**: Ensure composer file is valid
7. **Security audit**: Check for known vulnerabilities in dependencies

**Failure conditions**:
- PHP syntax errors
- Invalid composer.json
- Critical security vulnerabilities

**Duration**: ~1-2 minutes

---

### Stage 3: Static Analysis

**Job**: `static-analysis`

**Purpose**: Perform deep code analysis and style checking

**Dependencies**: `debug-info`

**Steps**:
1. **PHPStan Analysis**: Static analysis at level 5
2. **Psalm Analysis**: Additional static analysis (if configured)
3. **Laravel Pint**: Check code style compliance
4. **PHP CS Fixer**: Check PSR-12 compliance
5. **PHP_CodeSniffer**: Validate coding standards
6. **Code Complexity Analysis**: Analyze code complexity metrics
7. **Dead Code Detection**: Find unused private methods

**Tools Used**:
- PHPStan (Level 5)
- Psalm
- Laravel Pint
- PHP CS Fixer
- PHP_CodeSniffer (PSR-12)
- PHPMetrics

**Failure conditions**:
- Critical static analysis errors
- Severe code style violations

**Duration**: ~2-3 minutes

---

### Stage 4: Security Scanning

**Job**: `security`

**Purpose**: Comprehensive security analysis

**Dependencies**: `debug-info`

**Steps**:
1. **Composer Security Audit**: Check for known vulnerabilities
2. **Malicious Code Patterns**: Scan for dangerous functions (eval, exec, system, shell_exec, passthru)
3. **TruffleHog Secret Scanning**: Detect hardcoded secrets
4. **OWASP Dependency Check**: Analyze dependencies for CVEs
5. **Configuration Exposure Check**: Ensure .env is not committed
6. **File Permissions Analysis**: Check for insecure permissions

**Security Tools**:
- Composer Audit
- TruffleHog
- OWASP Dependency-Check 8.4.0

**Artifacts Generated**:
- `security-audit.json`
- `dependency-check-report/`

**Failure conditions**:
- Critical or high severity vulnerabilities
- Hardcoded secrets detected
- Exposed configuration files

**Duration**: ~3-5 minutes

---

### Stage 5: Automated Tests

**Job**: `tests`

**Purpose**: Run comprehensive test suite with coverage

**Dependencies**: `debug-info`, `code-quality`

**Services**:
- MySQL 8.0 (database)
- Redis 7 (caching)

**Steps**:
1. **Setup environment**: Configure PHP, MySQL, Redis
2. **Install dependencies**: Composer packages
3. **Prepare Laravel**: Copy .env, generate key, run migrations
4. **Run unit tests**: Execute Unit test suite with coverage (min 80%)
5. **Run feature tests**: Execute Feature test suite in parallel
6. **Generate coverage report**: Create HTML and XML coverage reports
7. **Upload artifacts**: Store coverage reports for 30 days
8. **Comment on PR**: Post coverage summary on pull requests

**Test Configuration**:
- Minimum coverage: 80%
- Parallel execution: Enabled
- Coverage formats: HTML, XML, Clover

**Artifacts Generated**:
- `coverage-html/` - HTML coverage report
- `coverage.xml` - Clover XML coverage report

**Failure conditions**:
- Test failures
- Coverage below 80%
- Database connection issues

**Duration**: ~3-4 minutes

---

### Stage 6: Docker Build & Validation

**Job**: `docker-build`

**Purpose**: Build, validate, and scan Docker image

**Dependencies**: `code-quality`, `tests`

**Steps**:
1. **Setup Docker Buildx**: Configure build environment
2. **Login to registry**: Authenticate with GitHub Container Registry
3. **Extract metadata**: Generate tags and labels
4. **Cache Docker layers**: Use build cache for faster builds
5. **Test build**: Build image without pushing
6. **Validate image**: 
   - Check image exists
   - Inspect image details
   - Test container startup
   - Check container logs
7. **Trivy security scan**: Scan for vulnerabilities
8. **Build and push**: Push validated image to registry

**Docker Tags Generated**:
- `latest` (main branch only)
- `{branch}-{sha}` (all branches)
- `{version}` (semantic version tags)

**Security Scanning**:
- Tool: Aqua Security Trivy
- Severity levels: CRITICAL, HIGH
- Output: SARIF format uploaded to GitHub Security

**Artifacts Generated**:
- Docker image in GHCR
- `trivy-results.sarif` - Security scan results

**Failure conditions**:
- Build failures
- Container fails to start
- Critical/high vulnerabilities in image

**Duration**: ~5-10 minutes

---

### Stage 7: Production Deployment

**Job**: `deploy`

**Purpose**: Deploy validated application to production

**Dependencies**: `code-quality`, `static-analysis`, `tests`, `security`, `docker-build`

**Conditions**:
- Only runs on `main` branch
- Only runs on `push` events
- All CI checks must pass

**Environment**: `production` with URL: https://hanaya-shop.com

**Steps**:
1. **Verify CI checks**: Confirm all previous jobs passed
2. **Download image info**: Get validated Docker image details
3. **Setup kubectl**: Configure Kubernetes CLI
4. **Configure AWS credentials**: Authenticate with AWS (if applicable)
5. **Pre-deployment health check**: Verify production is healthy
6. **Create backup**: Tag current production image as backup
7. **Deploy to production**: 
   - Pull new image
   - Update deployment
   - Wait for rollout
8. **Wait for stabilization**: Monitor deployment progress
9. **Post-deployment health check**: Verify new version is healthy
10. **Run smoke tests**:
    - Test homepage
    - Test API endpoints
    - Test admin panel
11. **Update deployment status**: Log deployment summary

**Health Check Endpoints**:
- `/health` - Application health
- `/` - Homepage
- `/api/products` - API test
- `/admin` - Admin panel

**Timeout**: 10 minutes

**Failure conditions**:
- Pre-deployment health check fails
- Deployment timeout
- Post-deployment health check fails
- Smoke tests fail

**Duration**: ~5-7 minutes

---

### Stage 8: Automatic Rollback

**Job**: `rollback`

**Purpose**: Automatically revert to previous version on deployment failure

**Dependencies**: `deploy`

**Conditions**:
- Only runs if `deploy` job fails
- Only runs on `main` branch

**Steps**:
1. **Initiate rollback**: Log rollback start
2. **Login to registry**: Authenticate with container registry
3. **Locate backup image**: Find most recent backup tag
4. **Restore backup**: 
   - Pull backup image
   - Tag as latest
   - Push restored image
5. **Redeploy previous version**: Trigger deployment rollout
6. **Verify rollback**: Health check on rolled-back version
7. **Create GitHub issue**: Document rollback for investigation
8. **Rollback summary**: Log rollback details

**Backup Strategy**:
- Backups tagged with format: `backup-YYYYMMDD-HHMMSS`
- Stored in GitHub Container Registry
- Automatically created before each deployment

**GitHub Issue Created**:
- Title: `🔄 Deployment Rollback - {commit}`
- Labels: `deployment`, `rollback`, `critical`
- Body: Contains failure details and investigation steps

**Failure conditions**:
- No backup image found
- Rollback deployment fails
- Health check fails after rollback

**Duration**: ~5-7 minutes

---

### Stage 9: Notifications

**Job**: `notify`

**Purpose**: Send notifications about deployment status

**Dependencies**: `deploy`, `rollback`

**Conditions**: Always runs (success or failure)

**Notification Channels**:

#### 1. Slack Notification
- **Trigger**: Always
- **Content**:
  - Status emoji (✅/🔄/❌)
  - Repository and branch
  - Commit SHA and message
  - Author and workflow link
  - Color-coded based on status
- **Required Secret**: `SLACK_WEBHOOK_URL`

#### 2. Email Notification
- **Trigger**: Always
- **Format**: HTML email
- **Content**:
  - Deployment status
  - Repository details
  - Commit information
  - Links to workflow and commit
- **Required Secrets**:
  - `MAIL_USERNAME`
  - `MAIL_PASSWORD`
  - `NOTIFICATION_EMAIL`

#### 3. GitHub Deployment Event
- **Trigger**: On successful deployment
- **Purpose**: Track deployment in GitHub UI
- **Environment**: Production

#### 4. Deployment Status Update
- **Trigger**: Always
- **Purpose**: Update deployment status in GitHub
- **States**: `success`, `failure`
- **Includes**: Log URL and environment URL

**Status Messages**:
- ✅ Success: "Deployment completed successfully"
- 🔄 Rollback: "Deployment failed, rollback completed"
- ❌ Failure: "Deployment and rollback failed"

**Duration**: ~30-60 seconds

---

## 🔧 Configuration

### Required Secrets

Add these secrets in GitHub repository settings:

```
# Container Registry
GITHUB_TOKEN (automatically provided)

# AWS (if using AWS deployment)
AWS_ACCESS_KEY_ID
AWS_SECRET_ACCESS_KEY
AWS_REGION
AWS_ACCOUNT_ID

# Notifications
SLACK_WEBHOOK_URL
MAIL_USERNAME
MAIL_PASSWORD
NOTIFICATION_EMAIL
```

### Environment Variables

Configured in workflow file:

```yaml
PHP_VERSION: '8.2'
NODE_VERSION: '18'
DOCKER_REGISTRY: ghcr.io
IMAGE_NAME: ${{ github.repository }}
DEPLOY_TIMEOUT: 600
```

### Branch Protection Rules

Recommended settings for `main` branch:

- [x] Require status checks to pass before merging
  - [x] code-quality
  - [x] static-analysis
  - [x] tests
  - [x] security
  - [x] docker-build
- [x] Require branches to be up to date before merging
- [x] Require linear history
- [x] Include administrators

---

## 🚀 Usage

### Trigger Workflow

#### Automatic Triggers

1. **Push to main or dev**:
   ```bash
   git push origin main
   ```

2. **Pull Request to main or dev**:
   ```bash
   git push origin feature/my-feature
   # Create PR on GitHub
   ```

3. **Push to feature branch**:
   ```bash
   git push origin feature/my-feature
   ```

#### Manual Trigger

1. Go to Actions tab in GitHub
2. Select "CI/CD Pipeline"
3. Click "Run workflow"
4. Choose:
   - Branch
   - Environment (staging/production)
   - Skip tests (true/false)

### Monitoring Workflow

1. **GitHub Actions UI**:
   - Navigate to repository → Actions tab
   - View running/completed workflows
   - Click workflow for detailed logs

2. **Slack Notifications**:
   - Automatic notifications on completion
   - Color-coded status messages
   - Direct links to workflow runs

3. **Email Notifications**:
   - HTML formatted emails
   - Complete deployment details
   - Quick access links

### Debugging Failed Workflows

1. **Check debug-info job**:
   - View environment details
   - Check system resources
   - Verify configuration files

2. **Review failed job logs**:
   - Expand failed step
   - Check error messages
   - Look for stack traces

3. **Download artifacts**:
   - Coverage reports
   - Security scan results
   - Test output

4. **Re-run failed jobs**:
   - Click "Re-run failed jobs"
   - Or "Re-run all jobs"

---

## 📊 Pipeline Metrics

### Expected Durations

| Job | Duration | Can Fail Pipeline |
|-----|----------|-------------------|
| debug-info | 30s | No |
| code-quality | 1-2 min | Yes |
| static-analysis | 2-3 min | Soft |
| security | 3-5 min | Yes |
| tests | 3-4 min | Yes |
| docker-build | 5-10 min | Yes |
| deploy | 5-7 min | Yes |
| rollback | 5-7 min | Yes |
| notify | 30-60s | No |

**Total Pipeline Duration**: ~20-40 minutes (with all jobs)

### Success Criteria

- ✅ All tests pass with >80% coverage
- ✅ No critical security vulnerabilities
- ✅ Docker image builds successfully
- ✅ Container starts and runs healthy
- ✅ Deployment completes within timeout
- ✅ Post-deployment health checks pass
- ✅ Smoke tests pass

---

## 🛠️ Maintenance

### Updating PHP Version

1. Edit workflow file:
   ```yaml
   env:
     PHP_VERSION: '8.3'  # Update version
   ```

2. Update Docker base image in `Dockerfile`

3. Test in feature branch first

### Adding New Tests

1. Create test file in `tests/`
2. Tests automatically discovered
3. No workflow changes needed

### Modifying Deployment Strategy

1. Edit `deploy` job steps
2. Update deployment commands
3. Test in staging first

### Adding New Notification Channels

1. Add new step in `notify` job
2. Configure required secrets
3. Test with manual workflow dispatch

---

## 🔒 Security Best Practices

1. **Never commit secrets**: Use GitHub Secrets
2. **Rotate credentials regularly**: Update secrets quarterly
3. **Review security scan results**: Act on vulnerabilities
4. **Keep dependencies updated**: Run `composer update` regularly
5. **Monitor deployment logs**: Check for anomalies
6. **Use signed commits**: Enable commit signature verification
7. **Enable branch protection**: Prevent force pushes to main

---

## 📞 Support

For issues with the CI/CD pipeline:

1. Check workflow logs
2. Review this documentation
3. Create issue with `ci/cd` label
4. Contact DevOps team

---

## 📝 Changelog

### Version 1.0.0 (Current)

- ✅ Complete CI/CD pipeline implementation
- ✅ Docker build gating
- ✅ Comprehensive testing
- ✅ Security scanning
- ✅ Automatic rollback
- ✅ Multi-channel notifications
- ✅ Full documentation

---

**Last Updated**: December 2025  
**Maintained by**: Nguyễn Trung Nghĩa  
**Repository**: [Hanaya-Shop](https://github.com/nguyentrungnghia270305/Hanaya-Shop)
