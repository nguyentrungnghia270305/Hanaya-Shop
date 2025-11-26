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
