<?php

/**
 * Dashboard Statistics API Resource
 *
 * This resource class transforms dashboard statistics data into a consistent
 * JSON response format for API consumers. It provides data transformation,
 * formatting, and serialization for all dashboard-related API endpoints.
 *
 * Features:
 * - Consistent API response structure
 * - Data transformation and formatting
 * - Conditional attribute inclusion
 * - Nested resource relationships
 * - Metadata and pagination support
 * - Localization support
 * - Currency formatting
 * - Date and time formatting
 *
 * Benefits:
 * - Decouples internal data structure from API response
 * - Provides consistent API contract
 * - Easy to modify response format
 * - Supports versioning
 * - Improves API documentation
 *
 * @author Hanaya Shop Development Team
 * @version 2.0
 * @package App\Http\Resources\Api
 */

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

/**
 * Dashboard Overview Resource Class
 *
 * Transforms dashboard overview statistics into JSON format.
 */
class DashboardOverviewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request HTTP request
     * @return array<string, mixed> Transformed data array
     */
    public function toArray(Request $request): array
    {
        return [
            'revenue' => [
                'total' => $this->resource['revenue']['total'] ?? 0,
                'formatted' => $this->formatCurrency($this->resource['revenue']['total'] ?? 0),
                'change_percentage' => $this->resource['revenue']['change'] ?? 0,
                'trend' => $this->resource['revenue']['trend'] ?? 'stable',
                'previous_period' => $this->when(
                    isset($this->resource['revenue']['previous']),
                    $this->resource['revenue']['previous'] ?? 0
                )
            ],
            'orders' => [
                'total' => $this->resource['orders']['total'] ?? 0,
                'change_percentage' => $this->resource['orders']['change'] ?? 0,
                'status_breakdown' => [
                    'pending' => $this->resource['orders']['pending'] ?? 0,
                    'processing' => $this->resource['orders']['processing'] ?? 0,
                    'shipped' => $this->resource['orders']['shipped'] ?? 0,
                    'completed' => $this->resource['orders']['completed'] ?? 0,
                    'cancelled' => $this->resource['orders']['cancelled'] ?? 0
                ]
            ],
            'customers' => [
                'total' => $this->resource['customers']['total'] ?? 0,
                'new' => $this->resource['customers']['new'] ?? 0,
                'active' => $this->resource['customers']['active'] ?? 0,
                'change_percentage' => $this->resource['customers']['change'] ?? 0
            ],
            'products' => [
                'total' => $this->resource['products']['total'] ?? 0,
                'in_stock' => $this->resource['products']['in_stock'] ?? 0,
                'low_stock' => $this->resource['products']['low_stock'] ?? 0,
                'out_of_stock' => $this->resource['products']['out_of_stock'] ?? 0
            ],
            'metrics' => [
                'average_order_value' => $this->resource['metrics']['average_order_value'] ?? 0,
                'average_order_value_formatted' => $this->formatCurrency(
                    $this->resource['metrics']['average_order_value'] ?? 0
                ),
                'conversion_rate' => $this->resource['metrics']['conversion_rate'] ?? 0,
                'fulfillment_rate' => $this->resource['metrics']['fulfillment_rate'] ?? 0
            ],
            'period' => [
                'type' => $this->resource['period']['type'] ?? 'month',
                'start_date' => $this->resource['period']['start'] ?? null,
                'end_date' => $this->resource['period']['end'] ?? null
            ]
        ];
    }

    /**
     * Format currency value.
     *
     * @param float $amount Amount to format
     * @return string Formatted currency string
     */
    private function formatCurrency(float $amount): string
    {
        return number_format($amount, 0, ',', '.') . ' VND';
    }
}

/**
 * Revenue Statistics Resource Class
 *
 * Transforms revenue statistics data into JSON format.
 */
class RevenueStatisticsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request HTTP request
     * @return array<string, mixed> Transformed data array
     */
    public function toArray(Request $request): array
    {
        return [
            'summary' => [
                'total_revenue' => $this->resource['total_revenue'] ?? 0,
                'total_revenue_formatted' => $this->formatCurrency($this->resource['total_revenue'] ?? 0),
                'growth_rate' => $this->resource['growth_rate'] ?? 0,
                'average_daily_revenue' => $this->resource['average_daily_revenue'] ?? 0,
                'average_daily_revenue_formatted' => $this->formatCurrency(
                    $this->resource['average_daily_revenue'] ?? 0
                )
            ],
            'time_series' => $this->transformTimeSeries($this->resource['time_series'] ?? []),
            'by_category' => $this->resource['by_category'] ?? [],
            'by_payment_method' => $this->resource['by_payment_method'] ?? [],
            'forecast' => $this->when(
                isset($this->resource['forecast']),
                $this->resource['forecast'] ?? null
            ),
            'period' => $this->resource['period'] ?? []
        ];
    }

    /**
     * Transform time series data.
     *
     * @param array $timeSeries Time series data
     * @return array Transformed time series
     */
    private function transformTimeSeries(array $timeSeries): array
    {
        return array_map(function ($item) {
            return [
                'date' => $item['date'] ?? null,
                'revenue' => $item['revenue'] ?? 0,
                'revenue_formatted' => $this->formatCurrency($item['revenue'] ?? 0),
                'orders' => $item['orders'] ?? 0
            ];
        }, $timeSeries);
    }

    /**
     * Format currency value.
     *
     * @param float $amount Amount to format
     * @return string Formatted currency string
     */
    private function formatCurrency(float $amount): string
    {
        return number_format($amount, 0, ',', '.') . ' VND';
    }
}

/**
 * Order Statistics Resource Class
 *
 * Transforms order statistics data into JSON format.
 */
class OrderStatisticsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request HTTP request
     * @return array<string, mixed> Transformed data array
     */
    public function toArray(Request $request): array
    {
        return [
            'summary' => [
                'total_orders' => $this->resource['total_orders'] ?? 0,
                'fulfillment_rate' => $this->resource['fulfillment_rate'] ?? 0,
                'cancellation_rate' => $this->resource['cancellation_rate'] ?? 0,
                'average_fulfillment_time' => $this->resource['average_fulfillment_time'] ?? 0,
                'average_fulfillment_time_unit' => 'days',
                'repeat_customer_rate' => $this->resource['repeat_customer_rate'] ?? 0
            ],
            'status_breakdown' => $this->resource['status_breakdown'] ?? [
                'pending' => 0,
                'processing' => 0,
                'shipped' => 0,
                'completed' => 0,
                'cancelled' => 0
            ],
            'order_trends' => $this->when(
                isset($this->resource['order_trends']),
                $this->resource['order_trends'] ?? []
            ),
            'peak_hours' => $this->when(
                isset($this->resource['peak_hours']),
                $this->resource['peak_hours'] ?? []
            ),
            'order_value_distribution' => $this->when(
                isset($this->resource['order_value_distribution']),
                $this->resource['order_value_distribution'] ?? []
            )
        ];
    }
}

/**
 * Product Statistics Resource Class
 *
 * Transforms product statistics data into JSON format.
 */
class ProductStatisticsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request HTTP request
     * @return array<string, mixed> Transformed data array
     */
    public function toArray(Request $request): array
    {
        return [
            'summary' => [
                'total_products' => $this->resource['summary']['total_products'] ?? 0,
                'total_views' => $this->resource['summary']['total_views'] ?? 0,
                'total_sales' => $this->resource['summary']['total_sales'] ?? 0,
                'average_conversion_rate' => $this->resource['summary']['average_conversion_rate'] ?? 0
            ],
            'top_products' => $this->transformProductList($this->resource['top_products'] ?? []),
            'inventory_status' => $this->resource['inventory_status'] ?? [],
            'category_distribution' => $this->resource['category_distribution'] ?? []
        ];
    }

    /**
     * Transform product list.
     *
     * @param array $products Product list
     * @return array Transformed product list
     */
    private function transformProductList(array $products): array
    {
        return array_map(function ($product) {
            return [
                'id' => $product['id'] ?? null,
                'name' => $product['name'] ?? 'Unknown',
                'category' => $product['category'] ?? 'Uncategorized',
                'price' => $product['price'] ?? 0,
                'price_formatted' => $this->formatCurrency($product['price'] ?? 0),
                'stock_quantity' => $product['stock_quantity'] ?? 0,
                'total_sales' => $product['total_sales'] ?? 0,
                'total_revenue' => $product['total_revenue'] ?? 0,
                'total_revenue_formatted' => $this->formatCurrency($product['total_revenue'] ?? 0),
                'view_count' => $product['view_count'] ?? 0,
                'image_url' => $product['image_url'] ?? null
            ];
        }, $products);
    }

    /**
     * Format currency value.
     *
     * @param float $amount Amount to format
     * @return string Formatted currency string
     */
    private function formatCurrency(float $amount): string
    {
        return number_format($amount, 0, ',', '.') . ' VND';
    }
}

/**
 * Customer Statistics Resource Class
 *
 * Transforms customer statistics data into JSON format.
 */
class CustomerStatisticsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request HTTP request
     * @return array<string, mixed> Transformed data array
     */
    public function toArray(Request $request): array
    {
        return [
            'summary' => [
                'total_customers' => $this->resource['summary']['total_customers'] ?? 0,
                'new_customers' => $this->resource['summary']['new_customers'] ?? 0,
                'active_customers' => $this->resource['summary']['active_customers'] ?? 0,
                'retention_rate' => $this->resource['summary']['retention_rate'] ?? 0
            ],
            'customer_lifetime_value' => $this->resource['customer_lifetime_value'] ?? 0,
            'customer_lifetime_value_formatted' => $this->formatCurrency(
                $this->resource['customer_lifetime_value'] ?? 0
            ),
            'customer_segments' => $this->resource['customer_segments'] ?? [],
            'acquisition_cost' => $this->resource['acquisition_cost'] ?? 0,
            'acquisition_cost_formatted' => $this->formatCurrency($this->resource['acquisition_cost'] ?? 0),
            'top_customers' => $this->transformTopCustomers($this->resource['top_customers'] ?? []),
            'customer_activity' => $this->when(
                isset($this->resource['customer_activity']),
                $this->resource['customer_activity'] ?? []
            )
        ];
    }

    /**
     * Transform top customers list.
     *
     * @param array $customers Customer list
     * @return array Transformed customer list
     */
    private function transformTopCustomers(array $customers): array
    {
        return array_map(function ($customer) {
            return [
                'id' => $customer['id'] ?? null,
                'name' => $customer['name'] ?? 'Unknown',
                'email' => $customer['email'] ?? 'N/A',
                'total_orders' => $customer['total_orders'] ?? 0,
                'total_spent' => $customer['total_spent'] ?? 0,
                'total_spent_formatted' => $this->formatCurrency($customer['total_spent'] ?? 0),
                'average_order_value' => $customer['average_order_value'] ?? 0,
                'average_order_value_formatted' => $this->formatCurrency(
                    $customer['average_order_value'] ?? 0
                )
            ];
        }, $customers);
    }

    /**
     * Format currency value.
     *
     * @param float $amount Amount to format
     * @return string Formatted currency string
     */
    private function formatCurrency(float $amount): string
    {
        return number_format($amount, 0, ',', '.') . ' VND';
    }
}

/**
 * Top Products Resource Class
 *
 * Transforms top products data into JSON format.
 */
class TopProductsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request HTTP request
     * @return array<string, mixed> Transformed data array
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'category' => [
                'id' => $this->category->id ?? null,
                'name' => $this->category->name ?? 'Uncategorized'
            ],
            'price' => $this->price,
            'price_formatted' => number_format($this->price, 0, ',', '.') . ' VND',
            'stock_quantity' => $this->stock_quantity,
            'discount_percent' => $this->discount_percent ?? 0,
            'total_sales' => $this->total_sales ?? 0,
            'total_revenue' => $this->total_revenue ?? 0,
            'total_revenue_formatted' => number_format($this->total_revenue ?? 0, 0, ',', '.') . ' VND',
            'view_count' => $this->view_count,
            'image_url' => $this->image_url,
            'created_at' => $this->created_at?->toIso8601String(),
            'links' => [
                'self' => route('api.products.show', $this->id),
                'category' => $this->category ? route('api.categories.show', $this->category->id) : null
            ]
        ];
    }
}

/**
 * Low Stock Products Resource Class
 *
 * Transforms low stock products data into JSON format.
 */
class LowStockProductsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request HTTP request
     * @return array<string, mixed> Transformed data array
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'category' => [
                'id' => $this->category->id ?? null,
                'name' => $this->category->name ?? 'Uncategorized'
            ],
            'stock_quantity' => $this->stock_quantity,
            'status' => $this->stock_quantity == 0 ? 'out_of_stock' : 'low_stock',
            'price' => $this->price,
            'price_formatted' => number_format($this->price, 0, ',', '.') . ' VND',
            'recommended_restock' => max(50, $this->stock_quantity * 5),
            'image_url' => $this->image_url,
            'last_updated' => $this->updated_at?->toIso8601String(),
            'links' => [
                'self' => route('api.products.show', $this->id),
                'update_stock' => route('api.products.update-stock', $this->id)
            ]
        ];
    }
}

/**
 * Recent Orders Resource Class
 *
 * Transforms recent orders data into JSON format.
 */
class RecentOrdersResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request HTTP request
     * @return array<string, mixed> Transformed data array
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => 'ORD-' . str_pad($this->id, 6, '0', STR_PAD_LEFT),
            'customer' => [
                'id' => $this->user->id ?? null,
                'name' => $this->user->name ?? 'Guest',
                'email' => $this->user->email ?? 'N/A'
            ],
            'total_price' => $this->total_price,
            'total_price_formatted' => number_format($this->total_price, 0, ',', '.') . ' VND',
            'status' => $this->status,
            'status_label' => $this->getStatusLabel(),
            'items_count' => $this->orderDetails->count() ?? 0,
            'shipping_address' => $this->address->address ?? 'N/A',
            'created_at' => $this->created_at?->toIso8601String(),
            'created_at_human' => $this->created_at?->diffForHumans(),
            'links' => [
                'self' => route('api.orders.show', $this->id),
                'customer' => $this->user ? route('api.users.show', $this->user->id) : null
            ]
        ];
    }

    /**
     * Get human-readable status label.
     *
     * @return string Status label
     */
    private function getStatusLabel(): string
    {
        return match($this->status) {
            'pending' => 'Pending',
            'processing' => 'Processing',
            'shipped' => 'Shipped',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            default => 'Unknown'
        };
    }
}

/**
 * Growth Metrics Resource Class
 *
 * Transforms growth metrics data into JSON format.
 */
class GrowthMetricsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request HTTP request
     * @return array<string, mixed> Transformed data array
     */
    public function toArray(Request $request): array
    {
        return [
            'revenue_growth' => [
                'rate' => $this->resource['revenue_growth']['rate'] ?? 0,
                'current' => $this->resource['revenue_growth']['current'] ?? 0,
                'current_formatted' => $this->formatCurrency(
                    $this->resource['revenue_growth']['current'] ?? 0
                ),
                'previous' => $this->resource['revenue_growth']['previous'] ?? 0,
                'previous_formatted' => $this->formatCurrency(
                    $this->resource['revenue_growth']['previous'] ?? 0
                ),
                'trend' => $this->resource['revenue_growth']['trend'] ?? 'stable'
            ],
            'order_growth' => [
                'rate' => $this->resource['order_growth']['rate'] ?? 0,
                'current' => $this->resource['order_growth']['current'] ?? 0,
                'previous' => $this->resource['order_growth']['previous'] ?? 0,
                'trend' => $this->resource['order_growth']['trend'] ?? 'stable'
            ],
            'customer_growth' => [
                'rate' => $this->resource['customer_growth']['rate'] ?? 0,
                'current' => $this->resource['customer_growth']['current'] ?? 0,
                'previous' => $this->resource['customer_growth']['previous'] ?? 0,
                'trend' => $this->resource['customer_growth']['trend'] ?? 'stable'
            ]
        ];
    }

    /**
     * Format currency value.
     *
     * @param float $amount Amount to format
     * @return string Formatted currency string
     */
    private function formatCurrency(float $amount): string
    {
        return number_format($amount, 0, ',', '.') . ' VND';
    }
}

/**
 * Category Performance Resource Class
 *
 * Transforms category performance data into JSON format.
 */
class CategoryPerformanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request HTTP request
     * @return array<string, mixed> Transformed data array
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'product_count' => $this->product_count ?? 0,
            'total_sales' => $this->total_sales ?? 0,
            'total_revenue' => $this->total_revenue ?? 0,
            'total_revenue_formatted' => number_format($this->total_revenue ?? 0, 0, ',', '.') . ' VND',
            'average_price' => $this->average_price ?? 0,
            'average_price_formatted' => number_format($this->average_price ?? 0, 0, ',', '.') . ' VND',
            'performance_score' => $this->calculatePerformanceScore(),
            'links' => [
                'self' => route('api.categories.show', $this->id),
                'products' => route('api.categories.products', $this->id)
            ]
        ];
    }

    /**
     * Calculate performance score.
     *
     * @return float Performance score (0-100)
     */
    private function calculatePerformanceScore(): float
    {
        $revenue = $this->total_revenue ?? 0;
        $sales = $this->total_sales ?? 0;
        
        // Simple scoring algorithm
        $revenueScore = min(($revenue / 10000000) * 50, 50);
        $salesScore = min(($sales / 100) * 50, 50);
        
        return round($revenueScore + $salesScore, 2);
    }
}
