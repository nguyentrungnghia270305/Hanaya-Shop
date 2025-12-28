<?php

/**
 * Dashboard Statistics API Controller
 *
 * This controller provides RESTful API endpoints for comprehensive dashboard statistics
 * and analytics for the Hanaya Shop e-commerce application. It serves real-time business
 * intelligence data for mobile apps, SPAs, and third-party integrations.
 *
 * Features:
 * - Real-time revenue and sales statistics
 * - Order analytics and trends
 * - Customer behavior insights
 * - Product performance metrics
 * - Inventory management statistics
 * - Time-series data for charts
 * - Comparative period analysis
 * - Top performing products and categories
 * - User engagement metrics
 * - Financial KPIs and growth indicators
 *
 * API Endpoints:
 * - GET /api/dashboard/overview - Overall statistics summary
 * - GET /api/dashboard/revenue - Revenue analytics with time-series
 * - GET /api/dashboard/orders - Order statistics and status breakdown
 * - GET /api/dashboard/products - Product performance metrics
 * - GET /api/dashboard/customers - Customer analytics and segmentation
 * - GET /api/dashboard/trends - Historical trends and forecasting
 * - GET /api/dashboard/top-products - Best selling products
 * - GET /api/dashboard/low-stock - Inventory alerts
 * - GET /api/dashboard/recent-orders - Latest order activity
 * - GET /api/dashboard/growth - Growth rate calculations
 *
 * Response Format:
 * All endpoints return JSON with consistent structure:
 * {
 *     "success": true,
 *     "data": {...},
 *     "meta": {...},
 *     "timestamp": "2024-01-01T00:00:00.000000Z"
 * }
 *
 * Error Handling:
 * - 200: Success with data
 * - 400: Bad request with validation errors
 * - 401: Unauthorized access
 * - 403: Forbidden - Insufficient permissions
 * - 500: Internal server error
 *
 * Authentication:
 * - Requires Bearer token authentication
 * - Admin role required for sensitive data
 * - Rate limiting applied per user
 *
 * @author Hanaya Shop Development Team
 * @version 2.0
 * @package App\Http\Controllers\Api
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\DashboardRequest;
use App\Http\Resources\Api\DashboardOverviewResource;
use App\Http\Resources\Api\RevenueStatisticsResource;
use App\Http\Resources\Api\OrderStatisticsResource;
use App\Http\Resources\Api\ProductStatisticsResource;
use App\Http\Resources\Api\CustomerStatisticsResource;
use App\Services\DashboardStatisticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Dashboard Statistics API Controller Class
 *
 * Handles all dashboard statistics API requests with comprehensive
 * data aggregation, caching strategies, and error handling.
 */
class DashboardStatisticsController extends Controller
{
    /**
     * Dashboard Statistics Service Instance
     *
     * Service layer handling business logic for statistics calculation,
     * data aggregation, and complex analytics operations.
     *
     * @var DashboardStatisticsService
     */
    protected $statisticsService;

    /**
     * Cache Time-to-Live in Seconds
     *
     * Default cache duration for dashboard statistics to improve
     * performance and reduce database load. Set to 5 minutes.
     *
     * @var int
     */
    protected $cacheTTL = 300;

    /**
     * Constructor - Inject Dependencies
     *
     * Initializes the controller with required service dependencies
     * using Laravel's dependency injection container.
     *
     * @param DashboardStatisticsService $statisticsService Statistics service instance
     */
    public function __construct(DashboardStatisticsService $statisticsService)
    {
        $this->statisticsService = $statisticsService;
        
        // Apply authentication middleware to all routes
        $this->middleware('auth:sanctum');
        
        // Apply admin middleware for sensitive endpoints
        $this->middleware('admin')->only([
            'revenueStatistics',
            'financialMetrics',
            'customerAnalytics'
        ]);
    }

    /**
     * Get Dashboard Overview Statistics
     *
     * Provides comprehensive overview of key business metrics including
     * total revenue, orders, customers, products, and growth indicators.
     *
     * Endpoint: GET /api/dashboard/overview
     *
     * Query Parameters:
     * - period: string (today|week|month|year|custom)
     * - start_date: date (Y-m-d) - Required if period=custom
     * - end_date: date (Y-m-d) - Required if period=custom
     * - compare: boolean - Include comparison with previous period
     * - cache: boolean - Use cached data (default: true)
     *
     * Response Structure:
     * {
     *     "success": true,
     *     "data": {
     *         "revenue": {
     *             "total": 1500000,
     *             "change": 15.5,
     *             "trend": "up"
     *         },
     *         "orders": {
     *             "total": 320,
     *             "change": 12.3,
     *             "pending": 25,
     *             "processing": 45,
     *             "completed": 200,
     *             "cancelled": 50
     *         },
     *         "customers": {
     *             "total": 1250,
     *             "new": 35,
     *             "active": 850,
     *             "change": 8.7
     *         },
     *         "products": {
     *             "total": 450,
     *             "in_stock": 380,
     *             "low_stock": 45,
     *             "out_of_stock": 25
     *         },
     *         "average_order_value": 468750,
     *         "conversion_rate": 3.5,
     *         "period": {
     *             "start": "2024-01-01",
     *             "end": "2024-01-31",
     *             "type": "month"
     *         }
     *     },
     *     "meta": {
     *         "cached": false,
     *         "generated_at": "2024-01-31T23:59:59.000000Z"
     *     }
     * }
     *
     * @param Request $request HTTP request with query parameters
     * @return JsonResponse JSON response with overview statistics
     */
    public function overview(Request $request): JsonResponse
    {
        try {
            // Extract and validate request parameters
            $period = $request->input('period', 'month');
            $useCache = $request->boolean('cache', true);
            $compare = $request->boolean('compare', false);
            
            // Build cache key based on parameters
            $cacheKey = "dashboard.overview.{$period}." . ($compare ? 'compare' : 'simple');
            
            // Attempt to retrieve from cache if enabled
            if ($useCache) {
                $cachedData = Cache::remember($cacheKey, $this->cacheTTL, function () use ($period, $compare) {
                    return $this->statisticsService->getOverviewStatistics($period, $compare);
                });
                
                return $this->successResponse($cachedData, 'Dashboard overview retrieved successfully', true);
            }
            
            // Fetch fresh data without caching
            $statistics = $this->statisticsService->getOverviewStatistics($period, $compare);
            
            return $this->successResponse($statistics, 'Dashboard overview retrieved successfully', false);
            
        } catch (\Exception $e) {
            Log::error('Dashboard overview error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            
            return $this->errorResponse(
                'Failed to retrieve dashboard overview',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Get Revenue Statistics and Analytics
     *
     * Provides detailed revenue analysis including time-series data,
     * revenue breakdown by category, payment methods, and growth trends.
     *
     * Endpoint: GET /api/dashboard/revenue
     *
     * Query Parameters:
     * - period: string (day|week|month|quarter|year)
     * - start_date: date (Y-m-d)
     * - end_date: date (Y-m-d)
     * - group_by: string (day|week|month) - Grouping for time-series
     * - include_forecast: boolean - Include revenue forecast
     *
     * Response Structure:
     * {
     *     "success": true,
     *     "data": {
     *         "total_revenue": 5000000,
     *         "growth_rate": 18.5,
     *         "average_daily_revenue": 166666.67,
     *         "time_series": [
     *             {"date": "2024-01-01", "revenue": 150000, "orders": 32},
     *             {"date": "2024-01-02", "revenue": 175000, "orders": 38}
     *         ],
     *         "by_category": [
     *             {"category": "Soap Flower", "revenue": 2000000, "percentage": 40},
     *             {"category": "Fresh Flower", "revenue": 1500000, "percentage": 30}
     *         ],
     *         "by_payment_method": [
     *             {"method": "COD", "revenue": 3000000, "percentage": 60},
     *             {"method": "Online", "revenue": 2000000, "percentage": 40}
     *         ],
     *         "forecast": {
     *             "next_week": 1200000,
     *             "next_month": 5500000,
     *             "confidence": 0.85
     *         }
     *     }
     * }
     *
     * @param DashboardRequest $request Validated dashboard request
     * @return JsonResponse JSON response with revenue statistics
     */
    public function revenueStatistics(DashboardRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            
            $startDate = $validated['start_date'] ?? Carbon::now()->startOfMonth();
            $endDate = $validated['end_date'] ?? Carbon::now()->endOfMonth();
            $groupBy = $validated['group_by'] ?? 'day';
            $includeForecast = $validated['include_forecast'] ?? false;
            
            // Generate cache key
            $cacheKey = "dashboard.revenue.{$startDate}.{$endDate}.{$groupBy}";
            
            // Get revenue statistics with caching
            $revenueData = Cache::remember($cacheKey, $this->cacheTTL, function () 
                use ($startDate, $endDate, $groupBy, $includeForecast) {
                return $this->statisticsService->getRevenueStatistics(
                    $startDate,
                    $endDate,
                    $groupBy,
                    $includeForecast
                );
            });
            
            return $this->successResponse(
                new RevenueStatisticsResource($revenueData),
                'Revenue statistics retrieved successfully'
            );
            
        } catch (\Exception $e) {
            Log::error('Revenue statistics error: ' . $e->getMessage());
            
            return $this->errorResponse(
                'Failed to retrieve revenue statistics',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Get Order Statistics and Analysis
     *
     * Provides comprehensive order analytics including status distribution,
     * fulfillment rates, order trends, and customer ordering patterns.
     *
     * Endpoint: GET /api/dashboard/orders
     *
     * Query Parameters:
     * - period: string (today|week|month|year)
     * - status: string - Filter by order status
     * - include_details: boolean - Include detailed order breakdown
     *
     * Response Structure:
     * {
     *     "success": true,
     *     "data": {
     *         "total_orders": 1580,
     *         "status_breakdown": {
     *             "pending": 125,
     *             "processing": 230,
     *             "shipped": 180,
     *             "completed": 980,
     *             "cancelled": 65
     *         },
     *         "fulfillment_rate": 92.5,
     *         "cancellation_rate": 4.1,
     *         "average_fulfillment_time": 2.5,
     *         "order_trends": [
     *             {"date": "2024-01-01", "orders": 52, "cancelled": 2},
     *             {"date": "2024-01-02", "orders": 48, "cancelled": 1}
     *         ],
     *         "peak_hours": [
     *             {"hour": 14, "orders": 85},
     *             {"hour": 20, "orders": 72}
     *         ],
     *         "repeat_customer_rate": 35.8
     *     }
     * }
     *
     * @param Request $request HTTP request
     * @return JsonResponse JSON response with order statistics
     */
    public function orderStatistics(Request $request): JsonResponse
    {
        try {
            $period = $request->input('period', 'month');
            $status = $request->input('status');
            $includeDetails = $request->boolean('include_details', true);
            
            $cacheKey = "dashboard.orders.{$period}." . ($status ?? 'all');
            
            $orderStats = Cache::remember($cacheKey, $this->cacheTTL, function () 
                use ($period, $status, $includeDetails) {
                return $this->statisticsService->getOrderStatistics(
                    $period,
                    $status,
                    $includeDetails
                );
            });
            
            return $this->successResponse(
                new OrderStatisticsResource($orderStats),
                'Order statistics retrieved successfully'
            );
            
        } catch (\Exception $e) {
            Log::error('Order statistics error: ' . $e->getMessage());
            
            return $this->errorResponse(
                'Failed to retrieve order statistics',
                500
            );
        }
    }

    /**
     * Get Product Performance Statistics
     *
     * Analyzes product performance including sales, views, conversion rates,
     * inventory status, and product popularity metrics.
     *
     * Endpoint: GET /api/dashboard/products
     *
     * Query Parameters:
     * - period: string
     * - category_id: integer - Filter by category
     * - sort: string (sales|views|revenue|stock)
     * - limit: integer - Number of products to return
     *
     * @param Request $request HTTP request
     * @return JsonResponse JSON response with product statistics
     */
    public function productStatistics(Request $request): JsonResponse
    {
        try {
            $period = $request->input('period', 'month');
            $categoryId = $request->input('category_id');
            $sortBy = $request->input('sort', 'sales');
            $limit = $request->input('limit', 20);
            
            $productStats = $this->statisticsService->getProductStatistics(
                $period,
                $categoryId,
                $sortBy,
                $limit
            );
            
            return $this->successResponse(
                new ProductStatisticsResource($productStats),
                'Product statistics retrieved successfully'
            );
            
        } catch (\Exception $e) {
            Log::error('Product statistics error: ' . $e->getMessage());
            
            return $this->errorResponse(
                'Failed to retrieve product statistics',
                500
            );
        }
    }

    /**
     * Get Customer Analytics and Insights
     *
     * Provides customer behavior analysis, segmentation, lifetime value,
     * retention rates, and customer acquisition metrics.
     *
     * Endpoint: GET /api/dashboard/customers
     *
     * @param Request $request HTTP request
     * @return JsonResponse JSON response with customer analytics
     */
    public function customerAnalytics(Request $request): JsonResponse
    {
        try {
            $period = $request->input('period', 'month');
            $segment = $request->input('segment', 'all');
            
            $customerData = $this->statisticsService->getCustomerAnalytics($period, $segment);
            
            return $this->successResponse(
                new CustomerStatisticsResource($customerData),
                'Customer analytics retrieved successfully'
            );
            
        } catch (\Exception $e) {
            Log::error('Customer analytics error: ' . $e->getMessage());
            
            return $this->errorResponse(
                'Failed to retrieve customer analytics',
                500
            );
        }
    }

    /**
     * Get Top Performing Products
     *
     * Returns list of best-selling products with sales metrics,
     * revenue contribution, and performance trends.
     *
     * Endpoint: GET /api/dashboard/top-products
     *
     * @param Request $request HTTP request
     * @return JsonResponse JSON response with top products
     */
    public function topProducts(Request $request): JsonResponse
    {
        try {
            $period = $request->input('period', 'month');
            $limit = $request->input('limit', 10);
            $metric = $request->input('metric', 'revenue'); // revenue, quantity, views
            
            $topProducts = $this->statisticsService->getTopProducts($period, $limit, $metric);
            
            return $this->successResponse($topProducts, 'Top products retrieved successfully');
            
        } catch (\Exception $e) {
            Log::error('Top products error: ' . $e->getMessage());
            
            return $this->errorResponse('Failed to retrieve top products', 500);
        }
    }

    /**
     * Get Low Stock Products Alert
     *
     * Returns products with low inventory levels requiring restock attention.
     *
     * Endpoint: GET /api/dashboard/low-stock
     *
     * @param Request $request HTTP request
     * @return JsonResponse JSON response with low stock products
     */
    public function lowStockProducts(Request $request): JsonResponse
    {
        try {
            $threshold = $request->input('threshold', 10);
            $categoryId = $request->input('category_id');
            
            $lowStockProducts = $this->statisticsService->getLowStockProducts($threshold, $categoryId);
            
            return $this->successResponse(
                $lowStockProducts,
                'Low stock products retrieved successfully'
            );
            
        } catch (\Exception $e) {
            Log::error('Low stock products error: ' . $e->getMessage());
            
            return $this->errorResponse('Failed to retrieve low stock products', 500);
        }
    }

    /**
     * Get Recent Order Activity
     *
     * Returns latest orders with details for real-time monitoring.
     *
     * Endpoint: GET /api/dashboard/recent-orders
     *
     * @param Request $request HTTP request
     * @return JsonResponse JSON response with recent orders
     */
    public function recentOrders(Request $request): JsonResponse
    {
        try {
            $limit = $request->input('limit', 20);
            $status = $request->input('status');
            
            $recentOrders = $this->statisticsService->getRecentOrders($limit, $status);
            
            return $this->successResponse($recentOrders, 'Recent orders retrieved successfully');
            
        } catch (\Exception $e) {
            Log::error('Recent orders error: ' . $e->getMessage());
            
            return $this->errorResponse('Failed to retrieve recent orders', 500);
        }
    }

    /**
     * Get Growth Metrics and Trends
     *
     * Calculates various growth metrics including revenue growth,
     * customer growth, order growth, and market expansion indicators.
     *
     * Endpoint: GET /api/dashboard/growth
     *
     * @param Request $request HTTP request
     * @return JsonResponse JSON response with growth metrics
     */
    public function growthMetrics(Request $request): JsonResponse
    {
        try {
            $period = $request->input('period', 'month');
            $comparePeriod = $request->input('compare_period', 'previous');
            
            $growthData = $this->statisticsService->getGrowthMetrics($period, $comparePeriod);
            
            return $this->successResponse($growthData, 'Growth metrics retrieved successfully');
            
        } catch (\Exception $e) {
            Log::error('Growth metrics error: ' . $e->getMessage());
            
            return $this->errorResponse('Failed to retrieve growth metrics', 500);
        }
    }

    /**
     * Get Category Performance Analysis
     *
     * Analyzes performance metrics for each product category including
     * sales distribution, revenue contribution, and growth trends.
     *
     * Endpoint: GET /api/dashboard/categories
     *
     * @param Request $request HTTP request
     * @return JsonResponse JSON response with category performance
     */
    public function categoryPerformance(Request $request): JsonResponse
    {
        try {
            $period = $request->input('period', 'month');
            
            $categoryData = $this->statisticsService->getCategoryPerformance($period);
            
            return $this->successResponse(
                $categoryData,
                'Category performance retrieved successfully'
            );
            
        } catch (\Exception $e) {
            Log::error('Category performance error: ' . $e->getMessage());
            
            return $this->errorResponse('Failed to retrieve category performance', 500);
        }
    }

    /**
     * Clear Dashboard Statistics Cache
     *
     * Clears all cached dashboard statistics to force fresh data retrieval.
     * Useful after major data updates or for troubleshooting.
     *
     * Endpoint: POST /api/dashboard/clear-cache
     *
     * @return JsonResponse JSON response confirming cache clear
     */
    public function clearCache(): JsonResponse
    {
        try {
            Cache::tags(['dashboard', 'statistics'])->flush();
            
            return $this->successResponse(
                ['cleared' => true],
                'Dashboard cache cleared successfully'
            );
            
        } catch (\Exception $e) {
            Log::error('Clear cache error: ' . $e->getMessage());
            
            return $this->errorResponse('Failed to clear cache', 500);
        }
    }

    /**
     * Success Response Helper
     *
     * Creates standardized success response structure.
     *
     * @param mixed $data Response data
     * @param string $message Success message
     * @param bool $cached Whether data was retrieved from cache
     * @return JsonResponse Formatted success response
     */
    protected function successResponse($data, string $message = 'Success', bool $cached = false): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'meta' => [
                'cached' => $cached,
                'timestamp' => Carbon::now()->toIso8601String()
            ]
        ], 200);
    }

    /**
     * Error Response Helper
     *
     * Creates standardized error response structure.
     *
     * @param string $message Error message
     * @param int $statusCode HTTP status code
     * @param array $errors Additional error details
     * @return JsonResponse Formatted error response
     */
    protected function errorResponse(string $message, int $statusCode = 400, array $errors = []): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
            'meta' => [
                'timestamp' => Carbon::now()->toIso8601String()
            ]
        ], $statusCode);
    }
}
