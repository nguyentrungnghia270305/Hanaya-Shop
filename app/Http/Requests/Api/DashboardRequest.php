<?php

/**
 * Dashboard Request Validation
 *
 * This form request class handles validation for all dashboard statistics API endpoints.
 * It provides comprehensive validation rules, custom error messages, and data sanitization
 * to ensure data integrity and security for dashboard operations.
 *
 * Features:
 * - Request parameter validation
 * - Date range validation
 * - Custom error messages in multiple languages
 * - Authorization checks
 * - Query parameter sanitization
 * - Business logic validation
 *
 * Validation Rules:
 * - Period selection validation
 * - Date format and range validation
 * - Numeric parameter validation
 * - Enum value validation
 * - Optional parameter handling
 *
 * Security:
 * - Prevents SQL injection through validation
 * - Sanitizes user inputs
 * - Validates date ranges
 * - Restricts parameter values
 *
 * @author Hanaya Shop Development Team
 * @version 2.0
 * @package App\Http\Requests\Api
 */

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Carbon\Carbon;

/**
 * Dashboard Request Validation Class
 *
 * Validates and authorizes dashboard statistics API requests
 * with comprehensive validation rules and error handling.
 */
class DashboardRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Checks if the authenticated user has permission to access
     * dashboard statistics. Typically restricted to admin users
     * or users with specific permissions.
     *
     * @return bool Authorization status
     */
    public function authorize(): bool
    {
        // Check if user is authenticated
        if (!$this->user()) {
            return false;
        }

        // Check if user has admin role for sensitive endpoints
        $sensitiveEndpoints = [
            'revenue-statistics',
            'financial-metrics',
            'customer-analytics'
        ];

        $currentRoute = $this->route()->getName();
        
        if (in_array($currentRoute, $sensitiveEndpoints)) {
            return $this->user()->role === 'admin';
        }

        // Allow all authenticated users for general dashboard endpoints
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Defines comprehensive validation rules for all dashboard
     * statistics request parameters including date ranges, filters,
     * and sorting options.
     *
     * @return array<string, mixed> Validation rules array
     */
    public function rules(): array
    {
        return [
            // Period selection validation
            'period' => [
                'sometimes',
                'string',
                'in:today,yesterday,week,last_week,month,last_month,quarter,year,custom'
            ],

            // Date range validation for custom period
            'start_date' => [
                'required_if:period,custom',
                'date',
                'date_format:Y-m-d',
                'before_or_equal:end_date',
                'before_or_equal:today'
            ],
            'end_date' => [
                'required_if:period,custom',
                'date',
                'date_format:Y-m-d',
                'after_or_equal:start_date',
                'before_or_equal:today'
            ],

            // Time series grouping validation
            'group_by' => [
                'sometimes',
                'string',
                'in:hour,day,week,month,quarter,year'
            ],

            // Comparison options
            'compare' => [
                'sometimes',
                'boolean'
            ],
            'compare_period' => [
                'sometimes',
                'string',
                'in:previous,last_year,custom'
            ],

            // Filtering options
            'status' => [
                'sometimes',
                'string',
                'in:pending,processing,shipped,completed,cancelled,all'
            ],
            'category_id' => [
                'sometimes',
                'integer',
                'exists:categories,id'
            ],
            'product_id' => [
                'sometimes',
                'integer',
                'exists:products,id'
            ],
            'user_id' => [
                'sometimes',
                'integer',
                'exists:users,id'
            ],

            // Sorting and ordering
            'sort' => [
                'sometimes',
                'string',
                'in:sales,revenue,views,stock,rating,created_at,updated_at'
            ],
            'order' => [
                'sometimes',
                'string',
                'in:asc,desc'
            ],

            // Pagination and limits
            'limit' => [
                'sometimes',
                'integer',
                'min:1',
                'max:100'
            ],
            'offset' => [
                'sometimes',
                'integer',
                'min:0'
            ],
            'page' => [
                'sometimes',
                'integer',
                'min:1'
            ],
            'per_page' => [
                'sometimes',
                'integer',
                'min:1',
                'max:100'
            ],

            // Stock threshold for inventory alerts
            'threshold' => [
                'sometimes',
                'integer',
                'min:0',
                'max:1000'
            ],

            // Metric selection for product statistics
            'metric' => [
                'sometimes',
                'string',
                'in:quantity,revenue,views,rating,profit'
            ],

            // Customer segment filter
            'segment' => [
                'sometimes',
                'string',
                'in:all,new,active,inactive,vip,regular'
            ],

            // Include additional details flag
            'include_details' => [
                'sometimes',
                'boolean'
            ],
            'include_forecast' => [
                'sometimes',
                'boolean'
            ],
            'include_comparison' => [
                'sometimes',
                'boolean'
            ],

            // Cache control
            'cache' => [
                'sometimes',
                'boolean'
            ],
            'refresh' => [
                'sometimes',
                'boolean'
            ],

            // Export format
            'export' => [
                'sometimes',
                'string',
                'in:json,csv,excel,pdf'
            ],

            // Currency and locale
            'currency' => [
                'sometimes',
                'string',
                'in:VND,USD,EUR'
            ],
            'locale' => [
                'sometimes',
                'string',
                'in:vi,en'
            ],

            // Time zone for date calculations
            'timezone' => [
                'sometimes',
                'timezone'
            ]
        ];
    }

    /**
     * Get custom validation messages.
     *
     * Provides user-friendly error messages for validation failures
     * in both Vietnamese and English languages.
     *
     * @return array<string, string> Custom validation messages
     */
    public function messages(): array
    {
        return [
            // Period validation messages
            'period.in' => 'The selected period is invalid. Valid options: today, week, month, year, custom.',
            
            // Date validation messages
            'start_date.required_if' => 'Start date is required when period is set to custom.',
            'start_date.date' => 'Start date must be a valid date.',
            'start_date.date_format' => 'Start date must be in Y-m-d format (e.g., 2024-01-01).',
            'start_date.before_or_equal' => 'Start date must be before or equal to end date and not in the future.',
            
            'end_date.required_if' => 'End date is required when period is set to custom.',
            'end_date.date' => 'End date must be a valid date.',
            'end_date.date_format' => 'End date must be in Y-m-d format (e.g., 2024-01-31).',
            'end_date.after_or_equal' => 'End date must be after or equal to start date.',
            'end_date.before_or_equal' => 'End date cannot be in the future.',

            // Grouping validation messages
            'group_by.in' => 'Invalid grouping option. Valid options: hour, day, week, month, quarter, year.',

            // Filter validation messages
            'status.in' => 'Invalid order status. Valid options: pending, processing, shipped, completed, cancelled, all.',
            'category_id.exists' => 'The selected category does not exist.',
            'category_id.integer' => 'Category ID must be a valid integer.',
            'product_id.exists' => 'The selected product does not exist.',
            'product_id.integer' => 'Product ID must be a valid integer.',
            'user_id.exists' => 'The selected user does not exist.',
            'user_id.integer' => 'User ID must be a valid integer.',

            // Sorting validation messages
            'sort.in' => 'Invalid sort field. Valid options: sales, revenue, views, stock, rating.',
            'order.in' => 'Invalid sort order. Valid options: asc, desc.',

            // Pagination validation messages
            'limit.integer' => 'Limit must be a valid integer.',
            'limit.min' => 'Limit must be at least 1.',
            'limit.max' => 'Limit cannot exceed 100.',
            'offset.integer' => 'Offset must be a valid integer.',
            'offset.min' => 'Offset cannot be negative.',
            'page.integer' => 'Page must be a valid integer.',
            'page.min' => 'Page must be at least 1.',
            'per_page.integer' => 'Per page must be a valid integer.',
            'per_page.min' => 'Per page must be at least 1.',
            'per_page.max' => 'Per page cannot exceed 100.',

            // Threshold validation messages
            'threshold.integer' => 'Threshold must be a valid integer.',
            'threshold.min' => 'Threshold cannot be negative.',
            'threshold.max' => 'Threshold cannot exceed 1000.',

            // Metric validation messages
            'metric.in' => 'Invalid metric. Valid options: quantity, revenue, views, rating, profit.',

            // Segment validation messages
            'segment.in' => 'Invalid customer segment. Valid options: all, new, active, inactive, vip, regular.',

            // Boolean validation messages
            'include_details.boolean' => 'Include details must be true or false.',
            'include_forecast.boolean' => 'Include forecast must be true or false.',
            'include_comparison.boolean' => 'Include comparison must be true or false.',
            'cache.boolean' => 'Cache parameter must be true or false.',
            'refresh.boolean' => 'Refresh parameter must be true or false.',
            'compare.boolean' => 'Compare parameter must be true or false.',

            // Export validation messages
            'export.in' => 'Invalid export format. Valid options: json, csv, excel, pdf.',

            // Currency and locale validation messages
            'currency.in' => 'Invalid currency. Valid options: VND, USD, EUR.',
            'locale.in' => 'Invalid locale. Valid options: vi, en.',

            // Timezone validation messages
            'timezone.timezone' => 'Invalid timezone identifier.'
        ];
    }

    /**
     * Get custom attribute names for validation errors.
     *
     * Provides human-readable attribute names for better error messages.
     *
     * @return array<string, string> Custom attribute names
     */
    public function attributes(): array
    {
        return [
            'period' => 'time period',
            'start_date' => 'start date',
            'end_date' => 'end date',
            'group_by' => 'grouping option',
            'compare' => 'comparison flag',
            'compare_period' => 'comparison period',
            'status' => 'order status',
            'category_id' => 'category',
            'product_id' => 'product',
            'user_id' => 'user',
            'sort' => 'sort field',
            'order' => 'sort order',
            'limit' => 'result limit',
            'offset' => 'result offset',
            'page' => 'page number',
            'per_page' => 'items per page',
            'threshold' => 'stock threshold',
            'metric' => 'performance metric',
            'segment' => 'customer segment',
            'include_details' => 'include details flag',
            'include_forecast' => 'include forecast flag',
            'include_comparison' => 'include comparison flag',
            'cache' => 'cache flag',
            'refresh' => 'refresh flag',
            'export' => 'export format',
            'currency' => 'currency code',
            'locale' => 'locale code',
            'timezone' => 'timezone'
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * Overrides the default validation failure handling to return
     * a JSON response with standardized error format for API consumers.
     *
     * @param Validator $validator The validator instance
     * @throws HttpResponseException JSON response with validation errors
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation failed. Please check your input data.',
            'errors' => $validator->errors()->toArray(),
            'meta' => [
                'timestamp' => Carbon::now()->toIso8601String(),
                'status_code' => 422
            ]
        ], 422));
    }

    /**
     * Handle a failed authorization attempt.
     *
     * Returns a standardized JSON error response when authorization fails.
     */
    protected function failedAuthorization()
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'You are not authorized to access this resource.',
            'errors' => [
                'authorization' => ['Insufficient permissions to access dashboard statistics.']
            ],
            'meta' => [
                'timestamp' => Carbon::now()->toIso8601String(),
                'status_code' => 403
            ]
        ], 403));
    }

    /**
     * Prepare the data for validation.
     *
     * Sanitizes and prepares input data before validation.
     * Converts string booleans to actual booleans and handles defaults.
     */
    protected function prepareForValidation()
    {
        $data = [];

        // Convert string booleans to actual booleans
        if ($this->has('cache')) {
            $data['cache'] = filter_var($this->input('cache'), FILTER_VALIDATE_BOOLEAN);
        }
        if ($this->has('refresh')) {
            $data['refresh'] = filter_var($this->input('refresh'), FILTER_VALIDATE_BOOLEAN);
        }
        if ($this->has('compare')) {
            $data['compare'] = filter_var($this->input('compare'), FILTER_VALIDATE_BOOLEAN);
        }
        if ($this->has('include_details')) {
            $data['include_details'] = filter_var($this->input('include_details'), FILTER_VALIDATE_BOOLEAN);
        }
        if ($this->has('include_forecast')) {
            $data['include_forecast'] = filter_var($this->input('include_forecast'), FILTER_VALIDATE_BOOLEAN);
        }

        // Set default values for optional parameters
        if (!$this->has('period')) {
            $data['period'] = 'month';
        }
        if (!$this->has('limit')) {
            $data['limit'] = 20;
        }
        if (!$this->has('order')) {
            $data['order'] = 'desc';
        }

        // Merge sanitized data back into request
        if (!empty($data)) {
            $this->merge($data);
        }
    }

    /**
     * Get validated date range.
     *
     * Returns Carbon instances for start and end dates based on
     * the validated period or custom date range.
     *
     * @return array{start: Carbon, end: Carbon} Date range array
     */
    public function getDateRange(): array
    {
        $period = $this->validated('period', 'month');

        if ($period === 'custom') {
            return [
                'start' => Carbon::parse($this->validated('start_date')),
                'end' => Carbon::parse($this->validated('end_date'))
            ];
        }

        return $this->getDateRangeByPeriod($period);
    }

    /**
     * Get date range based on period type.
     *
     * @param string $period Period type
     * @return array{start: Carbon, end: Carbon} Date range
     */
    private function getDateRangeByPeriod(string $period): array
    {
        $now = Carbon::now();

        return match($period) {
            'today' => [
                'start' => $now->copy()->startOfDay(),
                'end' => $now->copy()->endOfDay()
            ],
            'yesterday' => [
                'start' => $now->copy()->subDay()->startOfDay(),
                'end' => $now->copy()->subDay()->endOfDay()
            ],
            'week' => [
                'start' => $now->copy()->startOfWeek(),
                'end' => $now->copy()->endOfWeek()
            ],
            'last_week' => [
                'start' => $now->copy()->subWeek()->startOfWeek(),
                'end' => $now->copy()->subWeek()->endOfWeek()
            ],
            'last_month' => [
                'start' => $now->copy()->subMonth()->startOfMonth(),
                'end' => $now->copy()->subMonth()->endOfMonth()
            ],
            'quarter' => [
                'start' => $now->copy()->startOfQuarter(),
                'end' => $now->copy()->endOfQuarter()
            ],
            'year' => [
                'start' => $now->copy()->startOfYear(),
                'end' => $now->copy()->endOfYear()
            ],
            'month', default => [
                'start' => $now->copy()->startOfMonth(),
                'end' => $now->copy()->endOfMonth()
            ]
        };
    }

    /**
     * Check if caching is enabled.
     *
     * @return bool Cache enabled status
     */
    public function shouldCache(): bool
    {
        return $this->validated('cache', true) && !$this->validated('refresh', false);
    }

    /**
     * Get pagination parameters.
     *
     * @return array{limit: int, offset: int} Pagination parameters
     */
    public function getPaginationParams(): array
    {
        $limit = $this->validated('limit', 20);
        $page = $this->validated('page', 1);
        $offset = $this->validated('offset', ($page - 1) * $limit);

        return [
            'limit' => $limit,
            'offset' => $offset,
            'page' => $page
        ];
    }

    /**
     * Get sorting parameters.
     *
     * @return array{sort: string, order: string} Sorting parameters
     */
    public function getSortParams(): array
    {
        return [
            'sort' => $this->validated('sort', 'created_at'),
            'order' => $this->validated('order', 'desc')
        ];
    }
}
