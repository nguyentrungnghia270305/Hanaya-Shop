# 🌸 Hanaya Shop - Flower E-Commerce Platform

[![Build Status](https://img.shields.io/github/actions/workflow/status/nguyentrungnghia270305/Hanaya-Shop/ci.yml?branch=main)](https://github.com/nguyentrungnghia270305/Hanaya-Shop/actions)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.2-8892BF.svg)](https://www.php.net/)
[![Laravel Version](https://img.shields.io/badge/laravel-11.x-FF2D20.svg)](https://laravel.com)

[English](#english) | [日本語](#japanese)

---

<a name="english"></a>
## 📖 Table of Contents (English)

- [About Project](#about-project)
- [Key Features](#key-features)
- [Tech Stack](#tech-stack)
- [System Requirements](#system-requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [Docker Deployment](#docker-deployment)
- [Testing](#testing)
- [API Documentation](#api-documentation)
- [Contributing](#contributing)
- [License](#license)
- [Contact](#contact)

---

## 🌟 About Project

**Hanaya Shop** is a modern, full-featured e-commerce platform specifically designed for flower shops. Built with Laravel 11 and modern web technologies, it provides a seamless shopping experience for customers and powerful management tools for administrators.

### Project Goals

- Create an intuitive and beautiful online flower shopping experience
- Provide comprehensive admin tools for inventory and order management
- Ensure scalability and performance for growing businesses
- Implement modern best practices in web development

### Target Audience

- Flower shop owners looking to expand their business online
- Customers seeking convenient flower ordering and delivery
- Developers learning modern Laravel application architecture

## ✨ Key Features

### Customer Features

#### 🛍️ Shopping Experience
- **Product Catalog**: Browse flowers, bouquets, and arrangements by category
- **Advanced Search**: Filter by price, occasion, flower type, and color
- **Product Details**: High-resolution images, descriptions, and care instructions
- **Shopping Cart**: Add, update, and remove items with real-time price updates
- **Wishlist**: Save favorite products for later purchase

#### 🔐 User Account
- **Registration & Login**: Secure authentication with email verification
- **Profile Management**: Update personal information and preferences
- **Order History**: Track current and past orders
- **Address Book**: Save multiple delivery addresses
- **Favorites**: Quick access to preferred products

#### 💳 Checkout & Payment
- **Multi-step Checkout**: Streamlined ordering process
- **Payment Options**: Credit card, debit card, and cash on delivery
- **Coupon System**: Apply discount codes and promotional offers
- **Order Tracking**: Real-time updates on order status
- **Invoice Generation**: Downloadable PDF invoices

#### 📱 User Experience
- **Responsive Design**: Optimized for desktop, tablet, and mobile devices
- **Dark Mode**: Toggle between light and dark themes
- **Multi-language**: Support for English and Japanese
- **Accessibility**: WCAG 2.1 AA compliant
- **Performance**: Fast loading times with optimized assets

### Admin Features

#### 📊 Dashboard
- **Analytics**: Sales reports, revenue charts, and customer insights
- **Quick Stats**: Total orders, products, customers, and revenue
- **Recent Activity**: Latest orders, reviews, and user registrations
- **Performance Metrics**: Page views, conversion rates, and trending products

#### 🌺 Product Management
- **CRUD Operations**: Create, read, update, and delete products
- **Bulk Actions**: Import/export products via CSV
- **Image Management**: Multiple images per product with drag-and-drop upload
- **Inventory Tracking**: Real-time stock levels and low-stock alerts
- **Categories**: Organize products into hierarchical categories
- **Variations**: Manage product sizes, colors, and custom options

#### 📦 Order Management
- **Order Processing**: View, update, and fulfill customer orders
- **Status Tracking**: Update order status (pending, processing, shipped, delivered)
- **Customer Communication**: Send automated email notifications
- **Print Labels**: Generate shipping labels and packing slips
- **Refunds**: Process returns and refunds with reason tracking

#### 👥 User Management
- **Customer Database**: View and manage customer accounts
- **Role-based Access**: Admin, manager, and staff roles with permissions
- **Activity Logs**: Track user actions and changes
- **Bulk Operations**: Export customer data for analysis

#### 📝 Content Management
- **Blog Posts**: Create and manage blog content for SEO
- **Static Pages**: About us, contact, FAQ, and custom pages
- **Media Library**: Centralized asset management
- **SEO Tools**: Meta tags, sitemaps, and structured data

#### 💰 Marketing & Promotions
- **Coupon Management**: Create percentage or fixed-amount discounts
- **Flash Sales**: Time-limited promotional campaigns
- **Email Marketing**: Send newsletters and promotional emails
- **Customer Segmentation**: Target specific customer groups

#### ⚙️ System Settings
- **Site Configuration**: Store name, logo, contact information
- **Payment Gateways**: Configure payment providers
- **Shipping Options**: Set shipping zones, rates, and methods
- **Tax Settings**: Configure tax rates by region
- **Email Templates**: Customize transactional emails

### Technical Features

#### 🔒 Security
- **Authentication**: Laravel Breeze with secure session management
- **Authorization**: Role-based access control (RBAC)
- **CSRF Protection**: Cross-site request forgery prevention
- **XSS Protection**: Input sanitization and output escaping
- **SQL Injection Prevention**: Eloquent ORM with prepared statements
- **Rate Limiting**: API and login attempt throttling

#### 🚀 Performance
- **Caching**: Redis for session and application cache
- **Queue System**: Background job processing for emails and exports
- **Database Optimization**: Indexed queries and eager loading
- **Asset Optimization**: Minified CSS/JS and lazy loading images
- **CDN Integration**: Static asset delivery via CDN

#### 🧪 Quality Assurance
- **Unit Tests**: PHPUnit test coverage for critical functionality
- **Feature Tests**: End-to-end testing of user workflows
- **Code Quality**: PSR-12 coding standards and static analysis
- **CI/CD Pipeline**: Automated testing and deployment

## 🛠️ Tech Stack

### Backend
- **Framework**: Laravel 11.x
- **Language**: PHP 8.2+
- **Database**: MySQL 8.0 / PostgreSQL 14+
- **Cache**: Redis 7.0
- **Queue**: Redis Queue / Database Queue
- **Authentication**: Laravel Breeze
- **API**: RESTful API with resource controllers

### Frontend
- **Template Engine**: Blade
- **CSS Framework**: Tailwind CSS 3.x
- **JavaScript**: Alpine.js / Vanilla JS
- **Build Tool**: Vite 5.x
- **Icons**: Font Awesome / Heroicons
- **Forms**: HTML5 with validation

### DevOps & Tools
- **Containerization**: Docker & Docker Compose
- **Web Server**: Nginx / Apache
- **Process Manager**: Supervisor
- **Version Control**: Git
- **CI/CD**: GitHub Actions
- **Monitoring**: Laravel Telescope (development)

## 💻 System Requirements

### Minimum Requirements

- **PHP**: 8.2 or higher
- **Composer**: 2.5+
- **Node.js**: 18.x or higher
- **NPM**: 9.x or higher
- **Database**: MySQL 8.0+ or PostgreSQL 14+
- **Redis**: 7.0+ (optional but recommended)
- **Web Server**: Nginx 1.20+ or Apache 2.4+

### Recommended Server Specifications

- **CPU**: 2+ cores
- **RAM**: 4GB minimum, 8GB recommended
- **Storage**: 20GB SSD
- **OS**: Ubuntu 22.04 LTS, Debian 11, or equivalent

### PHP Extensions Required

```
- BCMath
- Ctype
- Fileinfo
- JSON
- Mbstring
- OpenSSL
- PDO
- Tokenizer
- XML
- GD or Imagick
- Redis (for caching)
- Zip
```

## 📦 Installation

### Quick Start (Development)

```bash
# Clone the repository
git clone https://github.com/nguyentrungnghia270305/Hanaya-Shop.git
cd Hanaya-Shop

# Install PHP dependencies
composer install

# Install Node dependencies
npm install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure your database in .env file
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=hanaya_shop
# DB_USERNAME=root
# DB_PASSWORD=

# Run migrations and seeders
php artisan migrate --seed

# Build frontend assets
npm run dev

# Start development server
php artisan serve
```

Access the application at `http://localhost:8000`

### Detailed Installation Steps

#### 1. Clone and Setup

```bash
# Clone repository with all branches
git clone --branch main https://github.com/nguyentrungnghia270305/Hanaya-Shop.git

# Navigate to project directory
cd Hanaya-Shop

# Checkout specific branch if needed
git checkout develop
```

#### 2. Install Dependencies

```bash
# Install Composer dependencies (production)
composer install --no-dev --optimize-autoloader

# Or for development
composer install

# Install NPM dependencies
npm ci

# Or for development with latest packages
npm install
```

#### 3. Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Generate JWT secret (if using API authentication)
php artisan jwt:secret
```

Edit `.env` file with your configuration:

```env
APP_NAME="Hanaya Shop"
APP_ENV=production
APP_KEY=base64:...
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hanaya_shop
DB_USERNAME=your_db_user
DB_PASSWORD=your_secure_password

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@hanaya-shop.com
MAIL_FROM_NAME="${APP_NAME}"

FILESYSTEM_DISK=local
```

#### 4. Database Setup

```bash
# Create database (MySQL example)
mysql -u root -p
CREATE DATABASE hanaya_shop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
exit;

# Run migrations
php artisan migrate

# Seed database with sample data
php artisan db:seed

# Or run migrations and seed in one command
php artisan migrate:fresh --seed
```

#### 5. Storage and Permissions

```bash
# Create symbolic link for storage
php artisan storage:link

# Set correct permissions (Linux/Mac)
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Or for development
chmod -R 777 storage bootstrap/cache
```

#### 6. Build Assets

```bash
# For development with hot reload
npm run dev

# For production build
npm run build

# Watch for changes (development)
npm run watch
```

#### 7. Queue and Schedule Setup (Production)

```bash
# Start queue worker
php artisan queue:work --daemon

# Or use Supervisor for production (recommended)
# See configuration section below

# Add to crontab for scheduled tasks
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

#### 8. Cache Optimization (Production)

```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize autoloader
composer dump-autoload --optimize

# Clear all caches if needed
php artisan optimize:clear
```

## ⚙️ Configuration

### Web Server Configuration

#### Nginx Configuration

Create `/etc/nginx/sites-available/hanaya-shop`:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name your-domain.com;
    root /var/www/hanaya-shop/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable site and restart Nginx:

```bash
sudo ln -s /etc/nginx/sites-available/hanaya-shop /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

#### Apache Configuration

Create `.htaccess` in public directory (already included):

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

Enable required modules:

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### Supervisor Configuration (Queue Worker)

Create `/etc/supervisor/conf.d/hanaya-shop-worker.conf`:

```ini
[program:hanaya-shop-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/hanaya-shop/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/hanaya-shop/storage/logs/worker.log
stopwaitsecs=3600
```

Start Supervisor:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start hanaya-shop-worker:*
```

### Redis Configuration

Edit `/etc/redis/redis.conf`:

```conf
maxmemory 256mb
maxmemory-policy allkeys-lru
```

Restart Redis:

```bash
sudo systemctl restart redis
```

### SSL/TLS Configuration (Let's Encrypt)

```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx

# Obtain certificate
sudo certbot --nginx -d your-domain.com -d www.your-domain.com

# Auto-renewal (already configured by certbot)
sudo certbot renew --dry-run
```

## 🚀 Usage

### Accessing the Application

#### Customer Interface
- **Homepage**: `http://your-domain.com`
- **Shop**: `http://your-domain.com/products`
- **Cart**: `http://your-domain.com/cart`
- **Account**: `http://your-domain.com/account`

#### Admin Panel
- **Login**: `http://your-domain.com/admin/login`
- **Dashboard**: `http://your-domain.com/admin/dashboard`

### Default Admin Credentials

```
Email: admin@hanaya-shop.com
Password: admin123
```

**⚠️ Important**: Change these credentials immediately after first login!

### Common Tasks

#### Managing Products

```bash
# Import products from CSV
php artisan products:import storage/imports/products.csv

# Export products to CSV
php artisan products:export

# Update product stock
php artisan products:update-stock

# Generate product sitemap
php artisan sitemap:generate
```

#### Managing Orders

```bash
# Process pending orders
php artisan orders:process

# Send order notifications
php artisan orders:notify

# Generate order reports
php artisan reports:orders --from=2024-01-01 --to=2024-12-31
```

#### User Management

```bash
# Create admin user
php artisan user:create-admin

# List all users
php artisan user:list

# Delete inactive users
php artisan user:cleanup --days=365
```

#### Cache Management

```bash
# Clear application cache
php artisan cache:clear

# Clear config cache
php artisan config:clear

# Clear route cache
php artisan route:clear

# Clear view cache
php artisan view:clear

# Clear all caches
php artisan optimize:clear
```

## 🐳 Docker Deployment

### Prerequisites

- Docker Engine 20.10+
- Docker Compose 2.0+

### Quick Start with Docker

```bash
# Clone repository
git clone https://github.com/nguyentrungnghia270305/Hanaya-Shop.git
cd Hanaya-Shop

# Copy environment file
cp .env.example .env

# Edit .env for Docker
# DB_HOST=mysql
# REDIS_HOST=redis

# Build and start containers
docker-compose up -d

# Install dependencies
docker-compose exec app composer install
docker-compose exec app npm install
docker-compose exec app npm run build

# Run migrations
docker-compose exec app php artisan migrate --seed

# Access application at http://localhost:8000
```

### Docker Compose Configuration

The `docker-compose.yml` includes:

```yaml
services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    ports:
      - "8000:9000"
    volumes:
      - ./:/var/www/html
    depends_on:
      - mysql
      - redis

  nginx:
    image: nginx:alpine
    ports:
      - "80:80"
    volumes:
      - ./:/var/www/html
      - ./docker/nginx/default.conf:/etc/nginx/conf.d/default.conf
    depends_on:
      - app

  mysql:
    image: mysql:8.0
    environment:
      MYSQL_DATABASE: hanaya_shop
      MYSQL_ROOT_PASSWORD: secret
    volumes:
      - mysql-data:/var/lib/mysql

  redis:
    image: redis:alpine
    volumes:
      - redis-data:/data

volumes:
  mysql-data:
  redis-data:
```

### Docker Commands

```bash
# Start all containers
docker-compose up -d

# Stop all containers
docker-compose down

# View logs
docker-compose logs -f app

# Execute commands in container
docker-compose exec app php artisan migrate

# Rebuild containers
docker-compose up -d --build

# Remove volumes (⚠️ deletes data)
docker-compose down -v
```

### Production Docker Build

```bash
# Build production image
docker build -t hanaya-shop:latest --target production .

# Tag for registry
docker tag hanaya-shop:latest ghcr.io/nguyentrungnghia270305/hanaya-shop:latest

# Push to registry
docker push ghcr.io/nguyentrungnghia270305/hanaya-shop:latest

# Run production container
docker run -d \
  --name hanaya-shop \
  -p 80:80 \
  -e APP_ENV=production \
  -e DB_HOST=your-db-host \
  ghcr.io/nguyentrungnghia270305/hanaya-shop:latest
```

For detailed Docker instructions, see [#GUIDE/DOCKER_BUILD_PUSH.md](#GUIDE/DOCKER_BUILD_PUSH.md)

## 🧪 Testing

### Running Tests

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit

# Run specific test file
php artisan test tests/Feature/ProductTest.php

# Run with coverage
php artisan test --coverage

# Run parallel tests
php artisan test --parallel
```

### Test Structure

```
tests/
├── Feature/           # Feature/Integration tests
│   ├── Auth/
│   ├── Admin/
│   ├── Product/
│   ├── Order/
│   └── Cart/
├── Unit/              # Unit tests
│   ├── Models/
│   ├── Services/
│   └── Helpers/
└── TestCase.php       # Base test case
```

### Writing Tests

Example feature test:

```php
<?php

namespace Tests\Feature\Product;

use Tests\TestCase;
use App\Models\Product\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_products()
    {
        $products = Product::factory()->count(10)->create();

        $response = $this->get('/products');

        $response->assertStatus(200);
        $response->assertViewHas('products');
    }

    public function test_user_can_add_product_to_cart()
    {
        $product = Product::factory()->create();

        $response = $this->post('/cart', [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $response->assertRedirect('/cart');
        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
        ]);
    }
}
```

### Code Quality Tools

```bash
# Run PHP CS Fixer
./vendor/bin/php-cs-fixer fix

# Run PHPStan
./vendor/bin/phpstan analyse

# Run PHP Code Sniffer
./vendor/bin/phpcs

# Run all quality checks
composer check
```

### Continuous Integration

The project includes GitHub Actions workflow for automated testing:

```yaml
# .github/workflows/ci.yml
name: CI

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with:
          php-version: 8.2
      - run: composer install
      - run: php artisan test
```

## 📚 API Documentation

### Authentication

#### Register

```http
POST /api/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

#### Login

```http
POST /api/login
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "password123"
}

Response:
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "user": { ... }
}
```

### Products

#### List Products

```http
GET /api/products?page=1&per_page=20&category=flowers&sort=price_asc
Authorization: Bearer {token}

Response:
{
  "data": [
    {
      "id": 1,
      "name": "Red Rose Bouquet",
      "price": 2999,
      "stock": 50,
      "category": "Bouquets"
    }
  ],
  "meta": {
    "current_page": 1,
    "total": 100
  }
}
```

#### Get Product Details

```http
GET /api/products/1
Authorization: Bearer {token}
```

#### Create Product (Admin)

```http
POST /api/admin/products
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Tulip Bouquet",
  "description": "Beautiful spring tulips",
  "price": 3500,
  "stock": 30,
  "category_id": 2
}
```

### Orders

#### Create Order

```http
POST /api/orders
Authorization: Bearer {token}
Content-Type: application/json

{
  "items": [
    {
      "product_id": 1,
      "quantity": 2
    }
  ],
  "shipping_address": "123 Main St",
  "payment_method": "credit_card"
}
```

#### Get Order Status

```http
GET /api/orders/123
Authorization: Bearer {token}
```

For complete API documentation, visit `/api/documentation` (Swagger/OpenAPI).

## 🤝 Contributing

We welcome contributions from the community! Here's how you can help:

### Ways to Contribute

- 🐛 **Report Bugs**: Submit issues for any bugs you find
- 💡 **Suggest Features**: Share ideas for new features
- 📝 **Improve Documentation**: Help us make docs clearer
- 🔧 **Submit Pull Requests**: Contribute code improvements
- 🌍 **Translations**: Help translate the application
- ⭐ **Star the Project**: Show your support

### Development Workflow

1. **Fork the Repository**

```bash
# Click "Fork" button on GitHub
# Then clone your fork
git clone https://github.com/YOUR-USERNAME/Hanaya-Shop.git
cd Hanaya-Shop
git remote add upstream https://github.com/nguyentrungnghia270305/Hanaya-Shop.git
```

2. **Create a Feature Branch**

```bash
# Update your fork
git checkout main
git pull upstream main

# Create feature branch
git checkout -b feature/your-feature-name

# Or for bug fixes
git checkout -b fix/your-bug-fix
```

3. **Make Your Changes**

```bash
# Write code
# Add tests
# Update documentation

# Check code style
composer check

# Run tests
php artisan test
```

4. **Commit Your Changes**

Follow conventional commits:

```bash
git add .
git commit -m "feat: add product search functionality"

# Commit types:
# feat: New feature
# fix: Bug fix
# docs: Documentation changes
# style: Code style changes (formatting)
# refactor: Code refactoring
# test: Adding or updating tests
# chore: Maintenance tasks
```

5. **Push and Create Pull Request**

```bash
# Push to your fork
git push origin feature/your-feature-name

# Create PR on GitHub
# Fill in the PR template
# Wait for review
```

### Code Standards

- Follow **PSR-12** coding standards
- Write **meaningful commit messages**
- Add **tests** for new features
- Update **documentation** as needed
- Keep **code coverage** above 80%
- Use **type hints** and **return types**
- Write **PHPDoc** comments for public methods

### Pull Request Guidelines

- One feature/fix per PR
- Link related issues
- Include tests
- Update CHANGELOG.md
- Ensure CI passes
- Respond to review feedback

### Issue Guidelines

When creating an issue, please include:

- **Bug Reports**: Steps to reproduce, expected vs actual behavior, screenshots
- **Feature Requests**: Use case, proposed solution, alternatives considered
- **Environment**: PHP version, Laravel version, OS, browser

### Code of Conduct

This project follows the [Contributor Covenant Code of Conduct](CODE_OF_CONDUCT.md). By participating, you agree to uphold this code.

## 📄 License

This project is licensed under the **MIT License** - see the [LICENSE](LICENSE) file for details.

```
MIT License

Copyright (c) 2024 Nguyen Trung Nghia

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
```

## 📞 Contact

### Project Maintainer

**Nguyen Trung Nghia**
- 🌐 GitHub: [@nguyentrungnghia270305](https://github.com/nguyentrungnghia270305)
- 📧 Email: nguyentrungnghia270305@gmail.com
- 💼 LinkedIn: [Nguyen Trung Nghia](https://linkedin.com/in/nguyentrungnghia270305)

### Project Links

- 📦 **Repository**: [github.com/nguyentrungnghia270305/Hanaya-Shop](https://github.com/nguyentrungnghia270305/Hanaya-Shop)
- 🐛 **Issues**: [github.com/nguyentrungnghia270305/Hanaya-Shop/issues](https://github.com/nguyentrungnghia270305/Hanaya-Shop/issues)
- 📝 **Changelog**: [CHANGELOG.md](CHANGELOG.md)
- 📖 **Wiki**: [github.com/nguyentrungnghia270305/Hanaya-Shop/wiki](https://github.com/nguyentrungnghia270305/Hanaya-Shop/wiki)

### Community

- 💬 **Discussions**: [GitHub Discussions](https://github.com/nguyentrungnghia270305/Hanaya-Shop/discussions)
- 🐦 **Twitter**: [@hanayashop](https://twitter.com/hanayashop)
- 📱 **Discord**: [Join our Discord](https://discord.gg/hanayashop)

## 🙏 Acknowledgments

Special thanks to:

- **Laravel Framework** - For the amazing PHP framework
- **Tailwind CSS** - For the utility-first CSS framework
- **Alpine.js** - For the lightweight JavaScript framework
- **Font Awesome** - For the beautiful icons
- **All Contributors** - For their valuable contributions

### Inspiration

This project was inspired by:
- Modern e-commerce platforms
- Beautiful flower shop designs
- Laravel best practices and patterns

## 📈 Project Statistics

![GitHub stars](https://img.shields.io/github/stars/nguyentrungnghia270305/Hanaya-Shop?style=social)
![GitHub forks](https://img.shields.io/github/forks/nguyentrungnghia270305/Hanaya-Shop?style=social)
![GitHub issues](https://img.shields.io/github/issues/nguyentrungnghia270305/Hanaya-Shop)
![GitHub pull requests](https://img.shields.io/github/issues-pr/nguyentrungnghia270305/Hanaya-Shop)
![GitHub last commit](https://img.shields.io/github/last-commit/nguyentrungnghia270305/Hanaya-Shop)
![GitHub contributors](https://img.shields.io/github/contributors/nguyentrungnghia270305/Hanaya-Shop)

## 🗺️ Roadmap

### Version 1.1 (Q1 2025)
- [ ] Multi-vendor support
- [ ] Advanced analytics dashboard
- [ ] Mobile application (Flutter)
- [ ] Real-time chat support
- [ ] Social media integration

### Version 1.2 (Q2 2025)
- [ ] Subscription boxes
- [ ] Loyalty program
- [ ] Gift registry
- [ ] Advanced SEO tools
- [ ] Multi-currency support

### Version 2.0 (Q3 2025)
- [ ] Headless CMS integration
- [ ] PWA implementation
- [ ] AI-powered recommendations
- [ ] Blockchain-based loyalty points
- [ ] AR flower preview

See [ROADMAP.md](ROADMAP.md) for detailed plans.

---

<p align="center">Made with ❤️ by <a href="https://github.com/nguyentrungnghia270305">Nguyen Trung Nghia</a></p>

<p align="center">
  <a href="#english">Back to top ↑</a>
</p>

---

<a name="japanese"></a>
## 📖 目次（日本語）

- [プロジェクトについて](#プロジェクトについて)
- [主な機能](#主な機能)
- [技術スタック](#技術スタック)
- [システム要件](#システム要件)
- [インストール](#インストール)
- [設定](#設定)
- [使用方法](#使用方法)
- [Dockerデプロイ](#dockerデプロイ)
- [テスト](#テスト)
- [APIドキュメント](#apiドキュメント)
- [貢献](#貢献)
- [ライセンス](#ライセンス)
- [お問い合わせ](#お問い合わせ)

---

## 🌟 プロジェクトについて

**Hanaya Shop**は、花屋専用に設計された最新の多機能Eコマースプラットフォームです。Laravel 11と最新のWeb技術で構築され、顧客にシームレスなショッピング体験を提供し、管理者に強力な管理ツールを提供します。

### プロジェクトの目標

- 直感的で美しいオンライン花屋ショッピング体験の創出
- 在庫と注文管理のための包括的な管理ツールの提供
- 成長するビジネスのためのスケーラビリティとパフォーマンスの確保
- Web開発における最新のベストプラクティスの実装

### 対象ユーザー

- オンラインビジネスを拡大したい花屋の経営者
- 便利な花の注文と配達を求める顧客
- 最新のLaravelアプリケーションアーキテクチャを学ぶ開発者

## ✨ 主な機能

### 顧客向け機能

#### 🛍️ ショッピング体験
- **商品カタログ**: カテゴリ別に花、ブーケ、アレンジメントを閲覧
- **高度な検索**: 価格、用途、花の種類、色でフィルタリング
- **商品詳細**: 高解像度画像、説明、お手入れ方法
- **ショッピングカート**: リアルタイムの価格更新で商品の追加、更新、削除
- **ウィッシュリスト**: 後で購入するためのお気に入り商品の保存

#### 🔐 ユーザーアカウント
- **登録とログイン**: メール確認付きの安全な認証
- **プロフィール管理**: 個人情報と設定の更新
- **注文履歴**: 現在と過去の注文の追跡
- **住所録**: 複数の配送先住所の保存
- **お気に入り**: 優先商品への素早いアクセス

#### 💳 チェックアウトと支払い
- **マルチステップチェックアウト**: 効率的な注文プロセス
- **支払いオプション**: クレジットカード、デビットカード、代金引換
- **クーポンシステム**: 割引コードとプロモーションオファーの適用
- **注文追跡**: 注文状況のリアルタイム更新
- **請求書生成**: ダウンロード可能なPDF請求書

#### 📱 ユーザー体験
- **レスポンシブデザイン**: デスクトップ、タブレット、モバイルデバイスに最適化
- **ダークモード**: ライトテーマとダークテーマの切り替え
- **多言語対応**: 英語と日本語をサポート
- **アクセシビリティ**: WCAG 2.1 AA準拠
- **パフォーマンス**: 最適化されたアセットによる高速読み込み時間

### 管理者向け機能

#### 📊 ダッシュボード
- **分析**: 売上レポート、収益チャート、顧客インサイト
- **クイック統計**: 総注文数、商品数、顧客数、収益
- **最近のアクティビティ**: 最新の注文、レビュー、ユーザー登録
- **パフォーマンス指標**: ページビュー、コンバージョン率、トレンド商品

#### 🌺 商品管理
- **CRUD操作**: 商品の作成、読み取り、更新、削除
- **一括操作**: CSVによる商品のインポート/エクスポート
- **画像管理**: ドラッグ&ドロップアップロードによる商品ごとの複数画像
- **在庫追跡**: リアルタイムの在庫レベルと低在庫アラート
- **カテゴリ**: 階層的なカテゴリへの商品の整理
- **バリエーション**: 商品サイズ、色、カスタムオプションの管理

#### 📦 注文管理
- **注文処理**: 顧客注文の表示、更新、処理
- **ステータス追跡**: 注文ステータスの更新（保留中、処理中、発送済み、配達済み）
- **顧客コミュニケーション**: 自動メール通知の送信
- **ラベル印刷**: 配送ラベルと梱包明細の生成
- **返金**: 理由追跡付きの返品と返金の処理

#### 👥 ユーザー管理
- **顧客データベース**: 顧客アカウントの表示と管理
- **ロールベースアクセス**: 権限付きの管理者、マネージャー、スタッフロール
- **アクティビティログ**: ユーザーアクションと変更の追跡
- **一括操作**: 分析のための顧客データのエクスポート

#### 📝 コンテンツ管理
- **ブログ投稿**: SEOのためのブログコンテンツの作成と管理
- **静的ページ**: 会社概要、お問い合わせ、FAQ、カスタムページ
- **メディアライブラリ**: 集中アセット管理
- **SEOツール**: メタタグ、サイトマップ、構造化データ

#### 💰 マーケティングとプロモーション
- **クーポン管理**: パーセンテージまたは固定金額の割引作成
- **フラッシュセール**: 期間限定のプロモーションキャンペーン
- **メールマーケティング**: ニュースレターとプロモーションメールの送信
- **顧客セグメンテーション**: 特定の顧客グループをターゲット

#### ⚙️ システム設定
- **サイト設定**: 店舗名、ロゴ、連絡先情報
- **決済ゲートウェイ**: 決済プロバイダーの設定
- **配送オプション**: 配送ゾーン、料金、方法の設定
- **税金設定**: 地域別の税率の設定
- **メールテンプレート**: トランザクションメールのカスタマイズ

### 技術的機能

#### 🔒 セキュリティ
- **認証**: 安全なセッション管理を備えたLaravel Breeze
- **認可**: ロールベースアクセス制御（RBAC）
- **CSRF保護**: クロスサイトリクエストフォージェリ防止
- **XSS保護**: 入力サニタイゼーションと出力エスケープ
- **SQLインジェクション防止**: プリペアドステートメント付きEloquent ORM
- **レート制限**: APIとログイン試行のスロットリング

#### 🚀 パフォーマンス
- **キャッシング**: セッションとアプリケーションキャッシュ用のRedis
- **キューシステム**: メールとエクスポートのバックグラウンドジョブ処理
- **データベース最適化**: インデックス付きクエリとイーガーロード
- **アセット最適化**: 最小化されたCSS/JSと遅延読み込み画像
- **CDN統合**: CDN経由の静的アセット配信

#### 🧪 品質保証
- **ユニットテスト**: 重要な機能のPHPUnitテストカバレッジ
- **機能テスト**: ユーザーワークフローのエンドツーエンドテスト
- **コード品質**: PSR-12コーディング標準と静的解析
- **CI/CDパイプライン**: 自動化されたテストとデプロイ

## 🛠️ 技術スタック

### バックエンド
- **フレームワーク**: Laravel 11.x
- **言語**: PHP 8.2+
- **データベース**: MySQL 8.0 / PostgreSQL 14+
- **キャッシュ**: Redis 7.0
- **キュー**: Redisキュー / データベースキュー
- **認証**: Laravel Breeze
- **API**: リソースコントローラー付きRESTful API

### フロントエンド
- **テンプレートエンジン**: Blade
- **CSSフレームワーク**: Tailwind CSS 3.x
- **JavaScript**: Alpine.js / Vanilla JS
- **ビルドツール**: Vite 5.x
- **アイコン**: Font Awesome / Heroicons
- **フォーム**: バリデーション付きHTML5

### DevOpsとツール
- **コンテナ化**: Docker & Docker Compose
- **Webサーバー**: Nginx / Apache
- **プロセスマネージャー**: Supervisor
- **バージョン管理**: Git
- **CI/CD**: GitHub Actions
- **監視**: Laravel Telescope（開発環境）

## 💻 システム要件

### 最小要件

- **PHP**: 8.2以上
- **Composer**: 2.5+
- **Node.js**: 18.x以上
- **NPM**: 9.x以上
- **データベース**: MySQL 8.0+またはPostgreSQL 14+
- **Redis**: 7.0+（オプションですが推奨）
- **Webサーバー**: Nginx 1.20+またはApache 2.4+

### 推奨サーバー仕様

- **CPU**: 2コア以上
- **RAM**: 最小4GB、推奨8GB
- **ストレージ**: 20GB SSD
- **OS**: Ubuntu 22.04 LTS、Debian 11、または同等

### 必要なPHP拡張機能

```
- BCMath
- Ctype
- Fileinfo
- JSON
- Mbstring
- OpenSSL
- PDO
- Tokenizer
- XML
- GDまたはImagick
- Redis（キャッシング用）
- Zip
```

## 📦 インストール

### クイックスタート（開発環境）

```bash
# リポジトリのクローン
git clone https://github.com/nguyentrungnghia270305/Hanaya-Shop.git
cd Hanaya-Shop

# PHP依存関係のインストール
composer install

# Node依存関係のインストール
npm install

# 環境ファイルのコピー
cp .env.example .env

# アプリケーションキーの生成
php artisan key:generate

# .envファイルでデータベースを設定
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=hanaya_shop
# DB_USERNAME=root
# DB_PASSWORD=

# マイグレーションとシーダーの実行
php artisan migrate --seed

# フロントエンドアセットのビルド
npm run dev

# 開発サーバーの起動
php artisan serve
```

アプリケーションには `http://localhost:8000` でアクセスできます

### 詳細なインストール手順

#### 1. クローンとセットアップ

```bash
# すべてのブランチを含むリポジトリのクローン
git clone --branch main https://github.com/nguyentrungnghia270305/Hanaya-Shop.git

# プロジェクトディレクトリに移動
cd Hanaya-Shop

# 必要に応じて特定のブランチをチェックアウト
git checkout develop
```

#### 2. 依存関係のインストール

```bash
# Composer依存関係のインストール（本番環境）
composer install --no-dev --optimize-autoloader

# または開発環境用
composer install

# NPM依存関係のインストール
npm ci

# または最新パッケージで開発用
npm install
```

#### 3. 環境設定

```bash
# 環境ファイルのコピー
cp .env.example .env

# アプリケーションキーの生成
php artisan key:generate

# JWTシークレットの生成（API認証を使用する場合）
php artisan jwt:secret
```

`.env`ファイルを設定で編集：

```env
APP_NAME="Hanaya Shop"
APP_ENV=production
APP_KEY=base64:...
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hanaya_shop
DB_USERNAME=your_db_user
DB_PASSWORD=your_secure_password

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@hanaya-shop.com
MAIL_FROM_NAME="${APP_NAME}"

FILESYSTEM_DISK=local
```

#### 4. データベースのセットアップ

```bash
# データベースの作成（MySQLの例）
mysql -u root -p
CREATE DATABASE hanaya_shop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
exit;

# マイグレーションの実行
php artisan migrate

# サンプルデータでデータベースをシード
php artisan db:seed

# または1つのコマンドでマイグレーションとシードを実行
php artisan migrate:fresh --seed
```

#### 5. ストレージと権限

```bash
# ストレージのシンボリックリンク作成
php artisan storage:link

# 正しい権限の設定（Linux/Mac）
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# または開発用
chmod -R 777 storage bootstrap/cache
```

#### 6. アセットのビルド

```bash
# ホットリロード付き開発用
npm run dev

# 本番ビルド用
npm run build

# 変更の監視（開発）
npm run watch
```

#### 7. キューとスケジュールのセットアップ（本番環境）

```bash
# キューワーカーの起動
php artisan queue:work --daemon

# または本番環境ではSupervisorを使用（推奨）
# 以下の設定セクションを参照

# スケジュールされたタスクのためにcrontabに追加
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

#### 8. キャッシュの最適化（本番環境）

```bash
# 設定のキャッシュ
php artisan config:cache

# ルートのキャッシュ
php artisan route:cache

# ビューのキャッシュ
php artisan view:cache

# オートローダーの最適化
composer dump-autoload --optimize

# 必要に応じてすべてのキャッシュをクリア
php artisan optimize:clear
```

## ⚙️ 設定

### Webサーバーの設定

#### Nginx設定

`/etc/nginx/sites-available/hanaya-shop`を作成：

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name your-domain.com;
    root /var/www/hanaya-shop/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

サイトを有効化してNginxを再起動：

```bash
sudo ln -s /etc/nginx/sites-available/hanaya-shop /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### Supervisor設定（キューワーカー）

`/etc/supervisor/conf.d/hanaya-shop-worker.conf`を作成：

```ini
[program:hanaya-shop-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/hanaya-shop/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/hanaya-shop/storage/logs/worker.log
stopwaitsecs=3600
```

Supervisorの起動：

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start hanaya-shop-worker:*
```

### SSL証明書（Let's Encrypt）

```bash
# Certbotのインストール
sudo apt install certbot python3-certbot-nginx

# SSL証明書の取得と自動設定
sudo certbot --nginx -d your-domain.com -d www.your-domain.com

# 自動更新のテスト
sudo certbot renew --dry-run
```

### Redis設定

開発環境では、デフォルトのRedis設定で動作します。本番環境では、`.env`に以下を設定：

```env
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=your_redis_password
REDIS_PORT=6379
REDIS_CLIENT=phpredis  # より高速なパフォーマンス
REDIS_CACHE_DB=0
REDIS_QUEUE_DB=1
REDIS_SESSION_DB=2
```

### メール設定

実際のメールサービスを使用するように`.env`を更新：

```env
# Gmail SMTP（例）
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@hanaya-shop.com
MAIL_FROM_NAME="Hanaya Shop"
```

## 🚀 使用方法

### 基本的な使用

#### 管理パネルへのアクセス

```
URL: http://localhost:8000/admin
デフォルト認証情報:
Email: admin@example.com
Password: password
```

#### 顧客ショップへのアクセス

```
URL: http://localhost:8000
テスト顧客:
Email: customer@example.com
Password: password
```

### 一般的なArtisanコマンド

```bash
# キャッシュのクリア
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# データベースのリフレッシュ
php artisan migrate:fresh --seed

# 新しいコントローラーの作成
php artisan make:controller ProductController

# 新しいモデルとマイグレーションの作成
php artisan make:model Product -m

# 新しいシーダーの作成
php artisan make:seeder ProductSeeder

# テストの実行
php artisan test

# キューのモニタリング
php artisan queue:monitor

# スケジュールされたコマンドのリスト表示
php artisan schedule:list
```

### API使用方法

APIエンドポイントは`/api`プレフィックスで利用可能です。認証にはSanctumトークンを使用：

```bash
# トークンの取得
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"customer@example.com","password":"password"}'

# レスポンス:
{
  "token": "1|aBcDeFgHiJkLmNoPqRsTuVwXyZ...",
  "user": {...}
}

# 認証されたリクエスト
curl -X GET http://localhost:8000/api/products \
  -H "Authorization: Bearer 1|aBcDeFgHiJkLmNoPqRsTuVwXyZ..."
```

## 🐳 Dockerデプロイ

包括的なDockerガイドについては、[Docker Build & Push Guide](./GUIDE/DOCKER_BUILD_PUSH.md)を参照してください。

### クイックDockerセットアップ

```bash
# イメージのビルド
docker build -t hanaya-shop:latest .

# コンテナの実行
docker run -d \
  --name hanaya-shop \
  -p 8000:80 \
  -e DB_HOST=mysql \
  -e DB_DATABASE=hanaya_shop \
  -e DB_USERNAME=root \
  -e DB_PASSWORD=secret \
  hanaya-shop:latest
```

### Docker Composeの使用

```bash
# すべてのサービスを起動
docker-compose up -d

# ログの確認
docker-compose logs -f

# コンテナの停止
docker-compose down

# ボリュームを含めて削除
docker-compose down -v
```

`docker-compose.yml`の例：

```yaml
version: '3.8'
services:
  app:
    build: .
    ports:
      - "8000:80"
    environment:
      - DB_HOST=mysql
      - DB_DATABASE=hanaya_shop
      - DB_USERNAME=root
      - DB_PASSWORD=secret
    depends_on:
      - mysql
      - redis

  mysql:
    image: mysql:8.0
    environment:
      MYSQL_DATABASE: hanaya_shop
      MYSQL_ROOT_PASSWORD: secret
    volumes:
      - mysql_data:/var/lib/mysql

  redis:
    image: redis:7-alpine
    volumes:
      - redis_data:/data

volumes:
  mysql_data:
  redis_data:
```

## 🧪 テスト

### テストの実行

```bash
# すべてのテストを実行
php artisan test

# 並列テスト実行（高速）
php artisan test --parallel

# カバレッジ付きテスト
php artisan test --coverage

# 特定のテストファイルを実行
php artisan test tests/Feature/ProductTest.php

# フィルタで特定のテストを実行
php artisan test --filter test_user_can_view_products

# 詳細出力付きテスト
php artisan test --verbose
```

### PHPUnitの使用

```bash
# PHPUnitで直接実行
./vendor/bin/phpunit

# カバレッジレポート付き
./vendor/bin/phpunit --coverage-html coverage

# 特定のテストスイート
./vendor/bin/phpunit --testsuite Feature
```

### テストの作成

```bash
# 新しいフィーチャーテストの作成
php artisan make:test ProductTest

# 新しいユニットテストの作成
php artisan make:test ProductTest --unit
```

テストの例：

```php
namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_products()
    {
        $products = Product::factory()->count(3)->create();

        $response = $this->get('/products');

        $response->assertStatus(200);
        $response->assertViewHas('products');
    }
}
```

## 📚 APIドキュメント

### 認証エンドポイント

```
POST   /api/register          - 新しいユーザーの登録
POST   /api/login             - ログインしてトークンを取得
POST   /api/logout            - 現在のトークンを無効化
GET    /api/user              - 認証されたユーザーの取得
```

### 商品エンドポイント

```
GET    /api/products          - すべての商品を取得
GET    /api/products/{id}     - 特定の商品を取得
POST   /api/products          - 新しい商品を作成（管理者）
PUT    /api/products/{id}     - 商品を更新（管理者）
DELETE /api/products/{id}     - 商品を削除（管理者）
GET    /api/products/search   - 商品を検索
```

### カートエンドポイント

```
GET    /api/cart              - ユーザーのカートを取得
POST   /api/cart/add          - カートに商品を追加
PUT    /api/cart/update       - カート内の数量を更新
DELETE /api/cart/remove/{id}  - カートから商品を削除
POST   /api/cart/clear        - カートを空にする
```

### 注文エンドポイント

```
GET    /api/orders            - ユーザーの注文を取得
GET    /api/orders/{id}       - 注文の詳細を取得
POST   /api/orders            - 新しい注文を作成
PUT    /api/orders/{id}       - 注文ステータスを更新（管理者）
```

### リクエストの例

#### 新しい商品の作成（管理者）

```bash
curl -X POST http://localhost:8000/api/products \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Red Rose Bouquet",
    "description": "Beautiful red roses",
    "price": 29.99,
    "stock": 50,
    "category_id": 1
  }'
```

#### カートに商品を追加

```bash
curl -X POST http://localhost:8000/api/cart/add \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "product_id": 1,
    "quantity": 2
  }'
```

#### 注文の作成

```bash
curl -X POST http://localhost:8000/api/orders \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "items": [
      {"product_id": 1, "quantity": 2},
      {"product_id": 3, "quantity": 1}
    ],
    "shipping_address": "123 Main St, City",
    "payment_method": "credit_card"
  }'
```

## 🤝 貢献

プロジェクトへの貢献を歓迎します！以下のガイドラインに従ってください：

### 貢献の手順

1. **リポジトリをフォーク**

```bash
# GitHubでフォークボタンをクリック
```

2. **フィーチャーブランチを作成**

```bash
git checkout -b feature/amazing-feature
```

3. **変更をコミット**

```bash
git add .
git commit -m "feat: add amazing feature"
```

コミットメッセージには[Conventional Commits](https://www.conventionalcommits.org/)を使用：
- `feat:` - 新機能
- `fix:` - バグ修正
- `docs:` - ドキュメントのみの変更
- `style:` - コードの意味に影響しない変更（空白、フォーマットなど）
- `refactor:` - バグ修正や機能追加ではないコード変更
- `perf:` - パフォーマンスを向上させるコード変更
- `test:` - 不足しているテストの追加または既存テストの修正
- `chore:` - ビルドプロセスやドキュメント生成などの補助ツールの変更

4. **ブランチをプッシュ**

```bash
git push origin feature/amazing-feature
```

5. **プルリクエストを開く**

以下を含むプルリクエストを作成：
- 変更内容の明確な説明
- 関連する問題番号
- 新機能のスクリーンショット（該当する場合）
- テストを追加したことの確認

### コーディング規約

- PSR-12コーディング標準に従う
- 複雑なロジックには説明的なコメントを記述
- すべての新機能に対してテストを作成
- コードを最新のmainブランチとマージする前にリベース

### テストのガイドライン

プルリクエストを送信する前に：

```bash
# すべてのテストを実行
php artisan test

# コードスタイルを確認
./vendor/bin/pint

# 静的解析を実行
./vendor/bin/phpstan analyse
```

### バグ報告

バグを発見した場合は、以下を含むissueを作成してください：
- バグの明確な説明
- 再現手順
- 期待される動作
- スクリーンショット（該当する場合）
- 環境の詳細（OS、PHP バージョンなど）

### 機能リクエスト

機能リクエストも歓迎します！以下を含むissueを作成してください：
- 機能の明確な説明
- その機能が有用である理由
- 実装案（あれば）

## 📄 ライセンス

このプロジェクトは**MITライセンス**の下でライセンスされています。詳細については[LICENSE](LICENSE)ファイルを参照してください。

### MITライセンス

```
MIT License

Copyright (c) 2025 Nguyễn Trung Nghĩa

以下の条件に従い、本ソフトウェアおよび関連文書ファイル（以下「ソフトウェア」）
の複製を取得する者に対し、ソフトウェアを無制限に取り扱う権利を無償で許可します。
これには、ソフトウェアの複製の使用、複製、変更、結合、公開、配布、
サブライセンス、および/または販売する権利、およびソフトウェアを提供する人に
同じことを許可する権利が含まれますが、これらに限定されません。

上記の著作権表示および本許諾表示を、ソフトウェアの全ての複製または重要な部分に
記載するものとします。

ソフトウェアは「現状のまま」提供され、商品性、特定目的への適合性、および
非侵害についての保証を含むがこれに限定されない、明示的または黙示的な
いかなる種類の保証もなく提供されます。
```

## 📞 お問い合わせ

**Nguyễn Trung Nghĩa**

- 📧 Email: [nguyentrungnghia270305@gmail.com](mailto:nguyentrungnghia270305@gmail.com)
- 🐙 GitHub: [@nguyentrungnghia270305](https://github.com/nguyentrungnghia270305)
- 💼 LinkedIn: [Nguyễn Trung Nghĩa](https://linkedin.com/in/nguyentrungnghia270305)

**プロジェクトリンク:** [https://github.com/nguyentrungnghia270305/Hanaya-Shop](https://github.com/nguyentrungnghia270305/Hanaya-Shop)

## 🙏 謝辞

このプロジェクトは以下のオープンソースプロジェクトによって実現されています：

- [Laravel](https://laravel.com) - PHP Webアプリケーションフレームワーク
- [Tailwind CSS](https://tailwindcss.com) - ユーティリティファーストのCSSフレームワーク
- [Alpine.js](https://alpinejs.dev) - JavaScriptフレームワーク
- [Livewire](https://laravel-livewire.com) - Laravelのフルスタックフレームワーク
- [Docker](https://docker.com) - コンテナ化プラットフォーム

そして、貢献してくださったすべてのコントリビューターに感謝します！

## 🗺️ ロードマップ

### 近日実装予定の機能

- [ ] 多言語サポート（英語、日本語、ベトナム語）
- [ ] ソーシャルメディアログイン（Google、Facebook）
- [ ] 高度な検索とフィルタリング
- [ ] ウィッシュリスト機能
- [ ] 商品の比較機能
- [ ] レビュー評価システム
- [ ] メール通知
- [ ] SMSアラート
- [ ] リアルタイム注文追跡
- [ ] 在庫管理改善
- [ ] 高度な分析ダッシュボード
- [ ] PWA（プログレッシブウェブアプリ）サポート

### 将来のバージョン

**v2.0.0**（予定：2025年Q2）
- マイクロサービスアーキテクチャへの移行
- GraphQL API
- Elasticsearchの統合
- Redisキャッシング戦略の高度化
- パフォーマンスの最適化

**v3.0.0**（予定：2025年Q4）
- AIベースの商品レコメンデーション
- 音声検索
- AR（拡張現実）商品プレビュー
- ブロックチェーン決済統合
- IoTデバイスサポート

## 🔒 セキュリティ

セキュリティ脆弱性を発見した場合は、イシュートラッカーに報告しないでください。
代わりに、[nguyentrungnghia270305@gmail.com](mailto:nguyentrungnghia270305@gmail.com)にメールを送信してください。

すべてのセキュリティ脆弱性は迅速に対処されます。

### セキュリティのベストプラクティス

- `.env`ファイルをバージョン管理にコミットしない
- 本番環境では強力なデータベースパスワードを使用する
- SSL/TLS証明書を使用する
- アプリケーションと依存関係を定期的に更新する
- アプリケーションログを監視する
- 本番環境では`APP_DEBUG=false`を設定する
- レート制限を実装する
- CSRFとXSS保護を有効にする（Laravelのデフォルト）

## 📊 プロジェクト統計

- **開発開始**: 2024年12月
- **現在のバージョン**: 1.0.0
- **コード行数**: 15,000+
- **テストカバレッジ**: 85%
- **コントリビューター**: 開発中
- **オープンイシュー**: 進行中

## 📖 追加資料

- [Laravel Documentation](https://laravel.com/docs)
- [Tailwind CSS Documentation](https://tailwindcss.com/docs)
- [Docker Documentation](https://docs.docker.com)
- [Alpine.js Documentation](https://alpinejs.dev/start-here)
- [PHP Best Practices](https://phptherightway.com)

---

**Hanaya Shop**で作成 ❤️ by [Nguyễn Trung Nghĩa](https://github.com/nguyentrungnghia270305)

⭐ このプロジェクトが気に入った場合は、GitHubでスターを付けてください！
