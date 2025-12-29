<?php

/**
 * Dashboard API Routes
 *
 * This file defines all API routes for the dashboard statistics functionality.
 * It provides RESTful endpoints for accessing comprehensive business intelligence,
 * analytics, and statistics for the Hanaya Shop e-commerce application.
 *
 * Route Organization:
 * - All routes are prefixed with '/api/dashboard'
 * - Routes require authentication via Sanctum
 * - Admin-only routes are protected with admin middleware
 * - Rate limiting applied to prevent abuse
 * - CORS enabled for cross-origin requests
 *
 * Authentication:
 * - Bearer token authentication required
 * - Token passed in Authorization header
 * - Example: Authorization: Bearer {token}
 *
 * Rate Limiting:
 * - 60 requests per minute per user
 * - Throttle key: 'api'
 * - Can be customized in RouteServiceProvider
 *
 * Available Endpoints:
 * - GET /api/dashboard/overview - Overview statistics
 * - GET /api/dashboard/revenue - Revenue analytics
 * - GET /api/dashboard/orders - Order statistics
 * - GET /api/dashboard/products - Product performance
 * - GET /api/dashboard/customers - Customer analytics
 * - GET /api/dashboard/top-products - Best sellers
 * - GET /api/dashboard/low-stock - Inventory alerts
 * - GET /api/dashboard/recent-orders - Latest orders
 * - GET /api/dashboard/growth - Growth metrics
 * - GET /api/dashboard/categories - Category performance
 * - POST /api/dashboard/clear-cache - Clear statistics cache
 *
 * @author Hanaya Shop Development Team
 * @version 2.0
 */

use App\Http\Controllers\Api\DashboardStatisticsController;
use Illuminate\Support\Facades\Route;

/**
 * Dashboard Statistics API Routes
 *
 * All routes require authentication and are rate-limited.
 * Some routes require admin privileges for sensitive data access.
 */
Route::prefix('dashboard')
    ->middleware(['auth:sanctum', 'throttle:api'])
    ->name('api.dashboard.')
    ->group(function () {

        /**
         * Dashboard Overview Endpoint
         *
         * GET /api/dashboard/overview
         *
         * Retrieves comprehensive dashboard statistics including revenue,
         * orders, customers, products, and key performance metrics.
         *
         * Query Parameters:
         * - period: string (today|week|month|year|custom) - Default: month
         * - start_date: date (Y-m-d) - Required if period=custom
         * - end_date: date (Y-m-d) - Required if period=custom
         * - compare: boolean - Include comparison with previous period
         * - cache: boolean - Use cached data - Default: true
         *
         * Response Example:
         * {
         *   "success": true,
         *   "data": {
         *     "revenue": {...},
         *     "orders": {...},
         *     "customers": {...},
         *     "products": {...}
         *   }
         * }
         *
         * Permissions: Authenticated users
         */
        Route::get('/overview', [DashboardStatisticsController::class, 'overview'])
            ->name('overview');

        /**
         * Revenue Statistics Endpoint
         *
         * GET /api/dashboard/revenue
         *
         * Provides detailed revenue analysis including time-series data,
         * category breakdown, payment method analysis, and forecasting.
         *
         * Query Parameters:
         * - period: string (day|week|month|quarter|year) - Default: month
         * - start_date: date (Y-m-d)
         * - end_date: date (Y-m-d)
         * - group_by: string (day|week|month) - Time series grouping
         * - include_forecast: boolean - Include revenue forecast
         *
         * Response Example:
         * {
         *   "success": true,
         *   "data": {
         *     "total_revenue": 5000000,
         *     "growth_rate": 18.5,
         *     "time_series": [...],
         *     "by_category": [...]
         *   }
         * }
         *
         * Permissions: Admin only
         */
        Route::get('/revenue', [DashboardStatisticsController::class, 'revenueStatistics'])
            ->middleware('admin')
            ->name('revenue');

        /**
         * Order Statistics Endpoint
         *
         * GET /api/dashboard/orders
         *
         * Provides comprehensive order analytics including status distribution,
         * fulfillment rates, order trends, and customer ordering patterns.
         *
         * Query Parameters:
         * - period: string (today|week|month|year) - Default: month
         * - status: string (pending|processing|shipped|completed|cancelled)
         * - include_details: boolean - Include detailed breakdown
         *
         * Response Example:
         * {
         *   "success": true,
         *   "data": {
         *     "total_orders": 1580,
         *     "status_breakdown": {...},
         *     "fulfillment_rate": 92.5
         *   }
         * }
         *
         * Permissions: Authenticated users
         */
        Route::get('/orders', [DashboardStatisticsController::class, 'orderStatistics'])
            ->name('orders');

        /**
         * Product Statistics Endpoint
         *
         * GET /api/dashboard/products
         *
         * Analyzes product performance including sales, views, conversion rates,
         * inventory status, and product popularity metrics.
         *
         * Query Parameters:
         * - period: string (today|week|month|year) - Default: month
         * - category_id: integer - Filter by category
         * - sort: string (sales|views|revenue|stock) - Default: sales
         * - limit: integer (1-100) - Number of products - Default: 20
         *
         * Response Example:
         * {
         *   "success": true,
         *   "data": {
         *     "summary": {...},
         *     "top_products": [...],
         *     "inventory_status": {...}
         *   }
         * }
         *
         * Permissions: Authenticated users
         */
        Route::get('/products', [DashboardStatisticsController::class, 'productStatistics'])
            ->name('products');

        /**
         * Customer Analytics Endpoint
         *
         * GET /api/dashboard/customers
         *
         * Provides customer behavior analysis, segmentation, lifetime value,
         * retention rates, and customer acquisition metrics.
         *
         * Query Parameters:
         * - period: string (today|week|month|year) - Default: month
         * - segment: string (all|new|active|inactive|vip) - Default: all
         *
         * Response Example:
         * {
         *   "success": true,
         *   "data": {
         *     "summary": {...},
         *     "customer_lifetime_value": 5000000,
         *     "customer_segments": [...],
         *     "top_customers": [...]
         *   }
         * }
         *
         * Permissions: Admin only
         */
        Route::get('/customers', [DashboardStatisticsController::class, 'customerAnalytics'])
            ->middleware('admin')
            ->name('customers');

        /**
         * Top Products Endpoint
         *
         * GET /api/dashboard/top-products
         *
         * Returns list of best-selling products with sales metrics,
         * revenue contribution, and performance trends.
         *
         * Query Parameters:
         * - period: string (today|week|month|year) - Default: month
         * - limit: integer (1-50) - Number of products - Default: 10
         * - metric: string (revenue|quantity|views) - Ranking metric
         *
         * Response Example:
         * {
         *   "success": true,
         *   "data": [
         *     {
         *       "id": 1,
         *       "name": "Product Name",
         *       "total_sales": 150,
         *       "total_revenue": 3000000
         *     }
         *   ]
         * }
         *
         * Permissions: Authenticated users
         */
        Route::get('/top-products', [DashboardStatisticsController::class, 'topProducts'])
            ->name('top-products');

        /**
         * Low Stock Products Endpoint
         *
         * GET /api/dashboard/low-stock
         *
         * Returns products with low inventory levels requiring restock attention.
         * Helps with inventory management and prevents stockouts.
         *
         * Query Parameters:
         * - threshold: integer (0-1000) - Stock threshold - Default: 10
         * - category_id: integer - Filter by category
         *
         * Response Example:
         * {
         *   "success": true,
         *   "data": [
         *     {
         *       "id": 5,
         *       "name": "Product Name",
         *       "stock_quantity": 3,
         *       "status": "low_stock",
         *       "recommended_restock": 50
         *     }
         *   ]
         * }
         *
         * Permissions: Authenticated users
         */
        Route::get('/low-stock', [DashboardStatisticsController::class, 'lowStockProducts'])
            ->name('low-stock');

        /**
         * Recent Orders Endpoint
         *
         * GET /api/dashboard/recent-orders
         *
         * Returns latest orders with details for real-time monitoring.
         * Useful for order management and customer service.
         *
         * Query Parameters:
         * - limit: integer (1-100) - Number of orders - Default: 20
         * - status: string - Filter by order status
         *
         * Response Example:
         * {
         *   "success": true,
         *   "data": [
         *     {
         *       "id": 1234,
         *       "customer_name": "John Doe",
         *       "total_price": 500000,
         *       "status": "processing",
         *       "created_at": "2024-01-31T10:30:00Z"
         *     }
         *   ]
         * }
         *
         * Permissions: Authenticated users
         */
        Route::get('/recent-orders', [DashboardStatisticsController::class, 'recentOrders'])
            ->name('recent-orders');

        /**
         * Growth Metrics Endpoint
         *
         * GET /api/dashboard/growth
         *
         * Calculates various growth metrics including revenue growth,
         * customer growth, order growth, and market expansion indicators.
         *
         * Query Parameters:
         * - period: string (week|month|quarter|year) - Default: month
         * - compare_period: string (previous|last_year) - Default: previous
         *
         * Response Example:
         * {
         *   "success": true,
         *   "data": {
         *     "revenue_growth": {
         *       "rate": 18.5,
         *       "current": 5000000,
         *       "previous": 4200000,
         *       "trend": "up"
         *     },
         *     "order_growth": {...},
         *     "customer_growth": {...}
         *   }
         * }
         *
         * Permissions: Authenticated users
         */
        Route::get('/growth', [DashboardStatisticsController::class, 'growthMetrics'])
            ->name('growth');

        /**
         * Category Performance Endpoint
         *
         * GET /api/dashboard/categories
         *
         * Analyzes performance metrics for each product category including
         * sales distribution, revenue contribution, and growth trends.
         *
         * Query Parameters:
         * - period: string (today|week|month|year) - Default: month
         *
         * Response Example:
         * {
         *   "success": true,
         *   "data": [
         *     {
         *       "id": 1,
         *       "name": "Soap Flower",
         *       "product_count": 45,
         *       "total_sales": 320,
         *       "total_revenue": 8000000
         *     }
         *   ]
         * }
         *
         * Permissions: Authenticated users
         */
        Route::get('/categories', [DashboardStatisticsController::class, 'categoryPerformance'])
            ->name('categories');

        /**
         * Clear Cache Endpoint
         *
         * POST /api/dashboard/clear-cache
         *
         * Clears all cached dashboard statistics to force fresh data retrieval.
         * Useful after major data updates or for troubleshooting.
         *
         * Response Example:
         * {
         *   "success": true,
         *   "data": {
         *     "cleared": true
         *   },
         *   "message": "Dashboard cache cleared successfully"
         * }
         *
         * Permissions: Admin only
         */
        Route::post('/clear-cache', [DashboardStatisticsController::class, 'clearCache'])
            ->middleware('admin')
            ->name('clear-cache');

        /**
         * Export Statistics Endpoint (Future Enhancement)
         *
         * POST /api/dashboard/export
         *
         * Exports dashboard statistics in various formats (CSV, Excel, PDF).
         * Query Parameters:
         * - format: string (csv|excel|pdf) - Export format
         * - period: string - Data period
         * - type: string - Statistics type to export
         */
        // Route::post('/export', [DashboardStatisticsController::class, 'export'])
        //     ->middleware('admin')
        //     ->name('export');

        /**
         * Scheduled Reports Endpoint (Future Enhancement)
         *
         * GET /api/dashboard/reports
         * POST /api/dashboard/reports
         *
         * Manages scheduled dashboard reports sent via email.
         */
        // Route::get('/reports', [DashboardStatisticsController::class, 'listReports'])
        //     ->middleware('admin')
        //     ->name('reports.index');
        // Route::post('/reports', [DashboardStatisticsController::class, 'createReport'])
        //     ->middleware('admin')
        //     ->name('reports.create');

        /**
         * Real-time Metrics Endpoint (Future Enhancement)
         *
         * GET /api/dashboard/realtime
         *
         * Provides real-time metrics using WebSocket or Server-Sent Events.
         * - Current active users
         * - Live order notifications
         * - Real-time revenue updates
         */
        // Route::get('/realtime', [DashboardStatisticsController::class, 'realtime'])
        //     ->middleware('admin')
        //     ->name('realtime');

        /**
         * Comparison Analysis Endpoint (Future Enhancement)
         *
         * POST /api/dashboard/compare
         *
         * Compares metrics between two custom date ranges.
         * Query Parameters:
         * - period1_start: date
         * - period1_end: date
         * - period2_start: date
         * - period2_end: date
         */
        // Route::post('/compare', [DashboardStatisticsController::class, 'compare'])
        //     ->middleware('admin')
        //     ->name('compare');
    });

/**
 * Public Dashboard Routes (No Authentication Required)
 *
 * These routes provide limited public statistics for marketing
 * and transparency purposes. Data is heavily cached and sanitized.
 */
Route::prefix('public/dashboard')
    ->middleware(['throttle:public'])
    ->name('api.public.dashboard.')
    ->group(function () {

        /**
         * Public Statistics Endpoint
         *
         * GET /api/public/dashboard/stats
         *
         * Provides basic public statistics such as:
         * - Total number of products
         * - Number of categories
         * - General platform metrics
         *
         * No sensitive business data is exposed.
         */
        // Route::get('/stats', [PublicDashboardController::class, 'publicStats'])
        //     ->name('stats');

        /**
         * Product Catalog Stats
         *
         * GET /api/public/dashboard/catalog
         *
         * Public product catalog statistics for marketing purposes.
         */
        // Route::get('/catalog', [PublicDashboardController::class, 'catalogStats'])
        //     ->name('catalog');
    });

/**
 * Webhook Endpoints for Dashboard Updates
 *
 * These endpoints receive webhooks from external services
 * to update dashboard statistics in real-time.
 */
Route::prefix('webhooks/dashboard')
    ->middleware(['webhook.signature'])
    ->name('webhooks.dashboard.')
    ->group(function () {

        /**
         * Order Webhook
         *
         * POST /api/webhooks/dashboard/order
         *
         * Receives order update webhooks to refresh statistics.
         */
        // Route::post('/order', [DashboardWebhookController::class, 'orderUpdate'])
        //     ->name('order');

        /**
         * Payment Webhook
         *
         * POST /api/webhooks/dashboard/payment
         *
         * Receives payment update webhooks to update revenue stats.
         */
        // Route::post('/payment', [DashboardWebhookController::class, 'paymentUpdate'])
        //     ->name('payment');
    });

/**
 * Dashboard API Health Check
 *
 * GET /api/dashboard/health
 *
 * Checks if the dashboard API is operational.
 * Returns service status and version information.
 */
Route::get('dashboard/health', function () {
    return response()->json([
        'status' => 'operational',
        'service' => 'Dashboard Statistics API',
        'version' => '2.0',
        'timestamp' => now()->toIso8601String(),
        'endpoints' => [
            'overview' => '/api/dashboard/overview',
            'revenue' => '/api/dashboard/revenue',
            'orders' => '/api/dashboard/orders',
            'products' => '/api/dashboard/products',
            'customers' => '/api/dashboard/customers'
        ]
    ]);
})->name('api.dashboard.health');

/**
 * Product CRUD API
 *
 * Lightweight product CRUD endpoints for quick testing.
 */
use App\Http\Controllers\Api\ProductController;

Route::prefix('products')
    ->middleware(['auth:sanctum'])
    ->name('api.products.')
    ->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::post('/', [ProductController::class, 'store'])->name('store');
        Route::get('/{id}', [ProductController::class, 'show'])->name('show');
        Route::put('/{id}', [ProductController::class, 'update'])->name('update');
        Route::delete('/{id}', [ProductController::class, 'destroy'])->name('destroy');

        Route::post('/bulk-delete', [ProductController::class, 'bulkDelete'])->name('bulk-delete');
        Route::post('/{id}/restore', [ProductController::class, 'restore'])->name('restore');
        Route::delete('/{id}/force', [ProductController::class, 'forceDelete'])->name('force-delete');
    });

/**
 * Category CRUD API
 */
use App\Http\Controllers\Api\CategoryController;

Route::prefix('categories')
    ->middleware(['auth:sanctum'])
    ->name('api.categories.')
    ->group(function () {
        Route::get('/', [CategoryController::class, 'index'])->name('index');
        Route::post('/', [CategoryController::class, 'store'])->name('store');
        Route::get('/{id}', [CategoryController::class, 'show'])->name('show');
        Route::put('/{id}', [CategoryController::class, 'update'])->name('update');
        Route::delete('/{id}', [CategoryController::class, 'destroy'])->name('destroy');

        Route::post('/bulk-delete', [CategoryController::class, 'bulkDelete'])->name('bulk-delete');
        Route::post('/{id}/restore', [CategoryController::class, 'restore'])->name('restore');
        Route::delete('/{id}/force', [CategoryController::class, 'forceDelete'])->name('force-delete');
    });
