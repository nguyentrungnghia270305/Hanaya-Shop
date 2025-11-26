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
