<?php

/**
 * Dashboard Repository
 *
 * This repository class handles all database queries and data access operations
 * for the dashboard statistics functionality. It follows the Repository Pattern
 * to separate data access logic from business logic, providing a clean and
 * maintainable architecture.
 *
 * Responsibilities:
 * - Execute complex database queries for statistics
 * - Aggregate data from multiple tables
 * - Optimize queries for performance
 * - Handle raw SQL queries when needed
 * - Provide data transformation methods
 * - Cache frequently accessed queries
 *
 * Benefits:
 * - Centralized data access logic
 * - Easier testing and mocking
 * - Improved query optimization
 * - Better code organization
 * - Reusable query methods
 *
 * Performance Optimization:
 * - Uses eager loading to prevent N+1 queries
 * - Implements query result caching
 * - Utilizes database indexing effectively
 * - Batch operations for large datasets
 * - Raw queries for complex aggregations
 *
 * @author Hanaya Shop Development Team
 * @version 2.0
 * @package App\Repositories
 */

namespace App\Repositories;

use App\Models\Order\Order;
use App\Models\Order\OrderDetail;
use App\Models\Product\Product;
use App\Models\Product\Category;
use App\Models\Product\Review;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

/**
 * Dashboard Repository Class
 *
 * Provides optimized database query methods for dashboard statistics
 * and analytics functionality.
 */
class DashboardRepository
{
    /**
     * Get Total Revenue for Date Range
     *
     * Calculates the total revenue from completed and shipped orders
     * within the specified date range.
     *
     * @param Carbon $startDate Start date for calculation
     * @param Carbon $endDate End date for calculation
     * @param array $statuses Order statuses to include (default: completed, shipped, processing)
     * @return float Total revenue amount
     */
    public function getTotalRevenue(
        Carbon $startDate,
        Carbon $endDate,
        array $statuses = ['completed', 'shipped', 'processing']
    ): float {
        return Order::whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('status', $statuses)
            ->sum('total_price') ?? 0.0;
    }

    /**
     * Get Revenue Grouped by Date
     *
     * Returns revenue data grouped by date with order counts.
     * Useful for generating revenue charts and time-series visualizations.
     *
     * @param Carbon $startDate Start date
     * @param Carbon $endDate End date
     * @param string $groupBy Grouping format (day|week|month)
     * @return Collection Revenue data grouped by date
     */
    public function getRevenueByDate(
        Carbon $startDate,
        Carbon $endDate,
        string $groupBy = 'day'
    ): Collection {
        $dateFormat = match($groupBy) {
            'week' => '%Y-%u',
            'month' => '%Y-%m',
            'year' => '%Y',
            default => '%Y-%m-%d'
        };

        return Order::whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('status', ['completed', 'shipped', 'processing'])
            ->select(
                DB::raw("DATE_FORMAT(created_at, '{$dateFormat}') as date"),
                DB::raw('SUM(total_price) as total_revenue'),
                DB::raw('COUNT(*) as order_count'),
                DB::raw('AVG(total_price) as average_order_value')
            )
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();
    }

    /**
     * Get Revenue by Category
     *
     * Calculates revenue contribution from each product category.
     * Useful for category performance analysis and inventory decisions.
     *
     * @param Carbon $startDate Start date
     * @param Carbon $endDate End date
     * @return Collection Revenue breakdown by category
     */
    public function getRevenueByCategory(Carbon $startDate, Carbon $endDate): Collection
    {
        return Category::select('categories.id', 'categories.name')
            ->join('products', 'categories.id', '=', 'products.category_id')
            ->join('order_details', 'products.id', '=', 'order_details.product_id')
            ->join('orders', 'order_details.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->whereIn('orders.status', ['completed', 'shipped', 'processing'])
            ->groupBy('categories.id', 'categories.name')
            ->select(
                'categories.id',
                'categories.name',
                DB::raw('SUM(order_details.quantity * order_details.price) as total_revenue'),
                DB::raw('SUM(order_details.quantity) as total_quantity'),
                DB::raw('COUNT(DISTINCT orders.id) as order_count')
            )
            ->orderBy('total_revenue', 'desc')
            ->get();
    }

    /**
     * Get Revenue by Payment Method
     *
     * Analyzes revenue distribution across different payment methods.
     *
     * @param Carbon $startDate Start date
     * @param Carbon $endDate End date
     * @return Collection Revenue by payment method
     */
    public function getRevenueByPaymentMethod(Carbon $startDate, Carbon $endDate): Collection
    {
        return DB::table('orders')
            ->join('payments', 'orders.id', '=', 'payments.order_id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->whereIn('orders.status', ['completed', 'shipped', 'processing'])
            ->select(
                'payments.payment_method',
                DB::raw('SUM(orders.total_price) as total_revenue'),
                DB::raw('COUNT(orders.id) as order_count'),
                DB::raw('AVG(orders.total_price) as average_value')
            )
            ->groupBy('payments.payment_method')
            ->get();
    }

    /**
     * Get Order Count by Status
     *
     * Returns order counts grouped by order status.
     *
     * @param Carbon $startDate Start date
     * @param Carbon $endDate End date
     * @return Collection Order counts by status
     */
    public function getOrderCountByStatus(Carbon $startDate, Carbon $endDate): Collection
    {
        return Order::whereBetween('created_at', [$startDate, $endDate])
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();
    }

    /**
     * Get Orders with Details
     *
     * Retrieves orders with related user, address, and order details.
     *
     * @param Carbon $startDate Start date
     * @param Carbon $endDate End date
     * @param string|null $status Optional status filter
     * @param int $limit Maximum number of orders to return
     * @return Collection Orders with relationships
     */
    public function getOrdersWithDetails(
        Carbon $startDate,
        Carbon $endDate,
        ?string $status = null,
        int $limit = 100
    ): Collection {
        $query = Order::with(['user', 'address', 'orderDetails.product.category'])
            ->whereBetween('created_at', [$startDate, $endDate]);

        if ($status) {
            $query->where('status', $status);
        }

        return $query->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get Average Order Fulfillment Time
     *
     * Calculates the average time taken to fulfill orders (from created to completed).
     *
     * @param Carbon $startDate Start date
     * @param Carbon $endDate End date
     * @return float Average fulfillment time in days
     */
    public function getAverageFulfillmentTime(Carbon $startDate, Carbon $endDate): float
    {
        $result = DB::table('orders')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'completed')
            ->whereNotNull('updated_at')
            ->select(DB::raw('AVG(TIMESTAMPDIFF(DAY, created_at, updated_at)) as avg_days'))
            ->first();

        return $result->avg_days ?? 0.0;
    }

    /**
     * Get Orders by Hour of Day
     *
     * Analyzes order distribution throughout the day to identify peak hours.
     *
     * @param Carbon $startDate Start date
     * @param Carbon $endDate End date
     * @return Collection Orders grouped by hour
     */
    public function getOrdersByHourOfDay(Carbon $startDate, Carbon $endDate): Collection
    {
        return Order::whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw('HOUR(created_at) as hour'),
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(total_price) as total_revenue')
            )
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();
    }

    /**
     * Get Top Selling Products
     *
     * Returns products with highest sales volume or revenue.
     *
     * @param Carbon $startDate Start date
     * @param Carbon $endDate End date
     * @param string $metric Sorting metric (quantity|revenue)
     * @param int $limit Number of products to return
     * @return Collection Top selling products
     */
    public function getTopSellingProducts(
        Carbon $startDate,
        Carbon $endDate,
        string $metric = 'quantity',
        int $limit = 10
    ): Collection {
        $query = Product::select('products.*')
            ->join('order_details', 'products.id', '=', 'order_details.product_id')
            ->join('orders', 'order_details.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->whereIn('orders.status', ['completed', 'shipped', 'processing'])
            ->with('category');

        if ($metric === 'revenue') {
            $query->select(
                'products.*',
                DB::raw('SUM(order_details.quantity * order_details.price) as total_revenue'),
                DB::raw('SUM(order_details.quantity) as total_quantity')
            )
            ->groupBy('products.id')
            ->orderBy('total_revenue', 'desc');
        } else {
            $query->select(
                'products.*',
                DB::raw('SUM(order_details.quantity) as total_quantity'),
                DB::raw('SUM(order_details.quantity * order_details.price) as total_revenue')
            )
            ->groupBy('products.id')
            ->orderBy('total_quantity', 'desc');
        }

        return $query->limit($limit)->get();
    }

    /**
     * Get Low Stock Products
     *
     * Returns products with inventory below specified threshold.
     *
     * @param int $threshold Stock quantity threshold
     * @param int|null $categoryId Optional category filter
     * @return Collection Low stock products
     */
    public function getLowStockProducts(int $threshold = 10, ?int $categoryId = null): Collection
    {
        $query = Product::with('category')
            ->where('stock_quantity', '<=', $threshold)
            ->where('stock_quantity', '>', 0);

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        return $query->orderBy('stock_quantity', 'asc')->get();
    }

    /**
     * Get Out of Stock Products
     *
     * Returns products with zero inventory.
     *
     * @param int|null $categoryId Optional category filter
     * @return Collection Out of stock products
     */
    public function getOutOfStockProducts(?int $categoryId = null): Collection
    {
        $query = Product::with('category')
            ->where('stock_quantity', '<=', 0);

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        return $query->get();
    }

    /**
     * Get Product Inventory Summary
     *
     * Provides summary statistics about product inventory status.
     *
     * @return array Inventory summary
     */
    public function getInventorySummary(): array
    {
        return [
            'total_products' => Product::count(),
            'in_stock' => Product::where('stock_quantity', '>', 10)->count(),
            'low_stock' => Product::whereBetween('stock_quantity', [1, 10])->count(),
            'out_of_stock' => Product::where('stock_quantity', '<=', 0)->count(),
            'total_inventory_value' => Product::select(DB::raw('SUM(stock_quantity * price) as total'))
                ->first()
                ->total ?? 0
        ];
    }

    /**
     * Get Customer Statistics
     *
     * Retrieves comprehensive customer statistics and segmentation.
     *
     * @param Carbon $startDate Start date
     * @param Carbon $endDate End date
     * @return array Customer statistics
     */
    public function getCustomerStatistics(Carbon $startDate, Carbon $endDate): array
    {
        $totalCustomers = User::where('role', 'user')->count();
        
        $newCustomers = User::where('role', 'user')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $activeCustomers = User::where('role', 'user')
            ->whereHas('orders', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->count();

        $customersWithOrders = User::where('role', 'user')
            ->has('orders')
            ->count();

        $repeatCustomers = User::where('role', 'user')
            ->whereHas('orders', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }, '>=', 2)
            ->count();

        return [
            'total_customers' => $totalCustomers,
            'new_customers' => $newCustomers,
            'active_customers' => $activeCustomers,
            'customers_with_orders' => $customersWithOrders,
            'repeat_customers' => $repeatCustomers,
            'repeat_customer_rate' => $activeCustomers > 0 
                ? ($repeatCustomers / $activeCustomers) * 100 
                : 0
        ];
    }

    /**
     * Get Top Customers by Revenue
     *
     * Returns customers with highest total purchase value.
     *
     * @param Carbon $startDate Start date
     * @param Carbon $endDate End date
     * @param int $limit Number of customers to return
     * @return Collection Top customers
     */
    public function getTopCustomers(Carbon $startDate, Carbon $endDate, int $limit = 10): Collection
    {
        return User::select('users.*')
            ->join('orders', 'users.id', '=', 'orders.user_id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->whereIn('orders.status', ['completed', 'shipped'])
            ->groupBy('users.id')
            ->select(
                'users.id',
                'users.name',
                'users.email',
                DB::raw('COUNT(orders.id) as total_orders'),
                DB::raw('SUM(orders.total_price) as total_spent'),
                DB::raw('AVG(orders.total_price) as average_order_value')
            )
            ->orderBy('total_spent', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get Customer Lifetime Value
     *
     * Calculates average customer lifetime value based on all orders.
     *
     * @return float Average customer lifetime value
     */
    public function getCustomerLifetimeValue(): float
    {
        $result = DB::table('users')
            ->join('orders', 'users.id', '=', 'orders.user_id')
            ->where('users.role', 'user')
            ->whereIn('orders.status', ['completed', 'shipped'])
            ->select(DB::raw('AVG(order_total) as avg_ltv'))
            ->from(DB::raw('(SELECT user_id, SUM(total_price) as order_total FROM orders WHERE status IN ("completed", "shipped") GROUP BY user_id) as user_totals'))
            ->first();

        return $result->avg_ltv ?? 0.0;
    }

    /**
     * Get Customer Retention Rate
     *
     * Calculates percentage of customers who made repeat purchases.
     *
     * @param Carbon $startDate Start date
     * @param Carbon $endDate End date
     * @return float Retention rate percentage
     */
    public function getCustomerRetentionRate(Carbon $startDate, Carbon $endDate): float
    {
        $previousPeriod = [
            'start' => $startDate->copy()->subDays($startDate->diffInDays($endDate) + 1),
            'end' => $startDate->copy()->subDay()
        ];

        $customersInPreviousPeriod = User::where('role', 'user')
            ->whereHas('orders', function ($query) use ($previousPeriod) {
                $query->whereBetween('created_at', [$previousPeriod['start'], $previousPeriod['end']]);
            })
            ->pluck('id');

        if ($customersInPreviousPeriod->isEmpty()) {
            return 0.0;
        }

        $retainedCustomers = User::where('role', 'user')
            ->whereIn('id', $customersInPreviousPeriod)
            ->whereHas('orders', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->count();

        return ($retainedCustomers / $customersInPreviousPeriod->count()) * 100;
    }

    /**
     * Get Category Performance Metrics
     *
     * Analyzes performance of each product category.
     *
     * @param Carbon $startDate Start date
     * @param Carbon $endDate End date
     * @return Collection Category performance data
     */
    public function getCategoryPerformance(Carbon $startDate, Carbon $endDate): Collection
    {
        return Category::withCount(['products'])
            ->with(['products' => function ($query) {
                $query->select('id', 'category_id', 'price', 'stock_quantity', 'view_count');
            }])
            ->get()
            ->map(function ($category) use ($startDate, $endDate) {
                // Calculate sales for this category
                $salesData = DB::table('order_details')
                    ->join('products', 'order_details.product_id', '=', 'products.id')
                    ->join('orders', 'order_details.order_id', '=', 'orders.id')
                    ->where('products.category_id', $category->id)
                    ->whereBetween('orders.created_at', [$startDate, $endDate])
                    ->whereIn('orders.status', ['completed', 'shipped', 'processing'])
                    ->select(
                        DB::raw('SUM(order_details.quantity) as total_sales'),
                        DB::raw('SUM(order_details.quantity * order_details.price) as total_revenue'),
                        DB::raw('COUNT(DISTINCT orders.id) as order_count')
                    )
                    ->first();

                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'product_count' => $category->products_count,
                    'total_sales' => $salesData->total_sales ?? 0,
                    'total_revenue' => $salesData->total_revenue ?? 0,
                    'order_count' => $salesData->order_count ?? 0,
                    'average_product_price' => $category->products->avg('price') ?? 0,
                    'total_views' => $category->products->sum('view_count') ?? 0
                ];
            });
    }

    /**
     * Get Product Reviews Statistics
     *
     * Analyzes product reviews and ratings.
     *
     * @param Carbon $startDate Start date
     * @param Carbon $endDate End date
     * @return array Review statistics
     */
    public function getReviewStatistics(Carbon $startDate, Carbon $endDate): array
    {
        $totalReviews = Review::whereBetween('created_at', [$startDate, $endDate])->count();
        
        $averageRating = Review::whereBetween('created_at', [$startDate, $endDate])
            ->avg('rating') ?? 0;

        $ratingDistribution = Review::whereBetween('created_at', [$startDate, $endDate])
            ->select('rating', DB::raw('COUNT(*) as count'))
            ->groupBy('rating')
            ->orderBy('rating', 'desc')
            ->pluck('count', 'rating')
            ->toArray();

        return [
            'total_reviews' => $totalReviews,
            'average_rating' => round($averageRating, 2),
            'rating_distribution' => $ratingDistribution
        ];
    }

    /**
     * Get Sales Trend Data
     *
     * Returns sales trend data for forecasting and analysis.
     *
     * @param Carbon $startDate Start date
     * @param Carbon $endDate End date
     * @param string $interval Grouping interval (day|week|month)
     * @return Collection Sales trend data
     */
    public function getSalesTrend(Carbon $startDate, Carbon $endDate, string $interval = 'day'): Collection
    {
        $dateFormat = match($interval) {
            'week' => '%Y-%u',
            'month' => '%Y-%m',
            default => '%Y-%m-%d'
        };

        return Order::whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('status', ['completed', 'shipped', 'processing'])
            ->select(
                DB::raw("DATE_FORMAT(created_at, '{$dateFormat}') as period"),
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(total_price) as revenue'),
                DB::raw('AVG(total_price) as avg_order_value')
            )
            ->groupBy('period')
            ->orderBy('period')
            ->get();
    }

    /**
     * Get Conversion Funnel Data
     *
     * Analyzes customer journey from visitor to purchaser.
     *
     * @param Carbon $startDate Start date
     * @param Carbon $endDate End date
     * @return array Conversion funnel metrics
     */
    public function getConversionFunnel(Carbon $startDate, Carbon $endDate): array
    {
        $totalVisitors = User::whereBetween('created_at', [$startDate, $endDate])->count();
        
        $usersWithCart = DB::table('carts')
            ->join('users', 'carts.user_id', '=', 'users.id')
            ->whereBetween('carts.created_at', [$startDate, $endDate])
            ->distinct('carts.user_id')
            ->count('carts.user_id');

        $usersWithOrders = Order::whereBetween('created_at', [$startDate, $endDate])
            ->distinct('user_id')
            ->count('user_id');

        $completedOrders = Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'completed')
            ->count();

        return [
            'visitors' => $totalVisitors,
            'add_to_cart' => $usersWithCart,
            'checkout' => $usersWithOrders,
            'completed' => $completedOrders,
            'conversion_rate' => $totalVisitors > 0 
                ? ($completedOrders / $totalVisitors) * 100 
                : 0
        ];
    }

    /**
     * Get Order Value Distribution
     *
     * Analyzes distribution of order values across different ranges.
     *
     * @param Carbon $startDate Start date
     * @param Carbon $endDate End date
     * @return Collection Order value distribution
     */
    public function getOrderValueDistribution(Carbon $startDate, Carbon $endDate): Collection
    {
        return DB::table('orders')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('status', ['completed', 'shipped', 'processing'])
            ->select(
                DB::raw('CASE 
                    WHEN total_price < 100000 THEN "0-100K"
                    WHEN total_price < 300000 THEN "100K-300K"
                    WHEN total_price < 500000 THEN "300K-500K"
                    WHEN total_price < 1000000 THEN "500K-1M"
                    ELSE "1M+"
                END as value_range'),
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(total_price) as total_revenue')
            )
            ->groupBy('value_range')
            ->get();
    }

    /**
     * Get Cancellation Analysis
     *
     * Analyzes cancelled orders and cancellation patterns.
     *
     * @param Carbon $startDate Start date
     * @param Carbon $endDate End date
     * @return array Cancellation analysis
     */
    public function getCancellationAnalysis(Carbon $startDate, Carbon $endDate): array
    {
        $totalOrders = Order::whereBetween('created_at', [$startDate, $endDate])->count();
        $cancelledOrders = Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'cancelled')
            ->count();

        $cancellationRate = $totalOrders > 0 ? ($cancelledOrders / $totalOrders) * 100 : 0;

        $lostRevenue = Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'cancelled')
            ->sum('total_price');

        return [
            'total_cancelled' => $cancelledOrders,
            'cancellation_rate' => round($cancellationRate, 2),
            'lost_revenue' => $lostRevenue
        ];
    }
}
