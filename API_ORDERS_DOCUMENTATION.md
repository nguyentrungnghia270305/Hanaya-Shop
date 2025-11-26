# API Quản Lý Đơn Hàng - Tài Liệu Hướng Dẫn

## Tổng Quan

API này cung cấp các endpoint để quản lý đơn hàng, bao gồm xem danh sách, chi tiết, và cập nhật trạng thái đơn hàng.

## Base URL

```
http://localhost:8000/api
```

## Các Trạng Thái Đơn Hàng

-   `pending`: Đơn hàng đang chờ xử lý
-   `processing`: Đơn hàng đang được xử lý
-   `shipped`: Đơn hàng đã được gửi đi
-   `delivered`: Đơn hàng đã giao thành công
-   `cancelled`: Đơn hàng đã bị hủy

## API Endpoints

### 1. Lấy Danh Sách Tất Cả Đơn Hàng

**GET** `/orders`

**Response:**

```json
{
    "success": true,
    "message": "Lấy danh sách đơn hàng thành công",
    "data": [
        {
            "id": 1,
            "total_price": "299000.00",
            "status": "pending",
            "created_at": "2025-11-26T10:00:00.000000Z",
            "user": {...},
            "orderDetail": [...],
            "payment": [...]
        }
    ]
}
```

### 2. Lấy Chi Tiết Một Đơn Hàng

**GET** `/orders/{id}`

**Response:**

```json
{
    "success": true,
    "message": "Lấy chi tiết đơn hàng thành công",
    "data": {
        "id": 1,
        "total_price": "299000.00",
        "status": "pending",
        "created_at": "2025-11-26T10:00:00.000000Z",
        "user": {...},
        "orderDetail": [...],
        "payment": [...],
        "appliedCoupon": [...]
    }
}
```

### 3. Cập Nhật Trạng Thái Đơn Hàng

**PATCH** `/orders/{id}/status`

**Body:**

```json
{
    "status": "processing"
}
```

**Response thành công:**

```json
{
    "success": true,
    "message": "Cập nhật trạng thái đơn hàng thành công",
    "data": {
        "id": 1,
        "total_price": "299000.00",
        "status": "processing",
        "created_at": "2025-11-26T10:00:00.000000Z"
    }
}
```

**Response lỗi (validation):**

```json
{
    "success": false,
    "message": "Dữ liệu không hợp lệ",
    "errors": {
        "status": [
            "Trạng thái không hợp lệ. Chỉ chấp nhận: pending, processing, shipped, delivered, cancelled"
        ]
    }
}
```

**Response lỗi (logic):**

```json
{
    "success": false,
    "message": "Không thể thay đổi trạng thái của đơn hàng đã hoàn thành hoặc đã hủy"
}
```

### 4. Hủy Đơn Hàng

**PATCH** `/orders/{id}/cancel`

**Response:**

```json
{
    "success": true,
    "message": "Hủy đơn hàng thành công",
    "data": {
        "id": 1,
        "total_price": "299000.00",
        "status": "cancelled",
        "created_at": "2025-11-26T10:00:00.000000Z"
    }
}
```

### 5. Lấy Đơn Hàng Theo Trạng Thái

**GET** `/orders/status/{status}`

**Ví dụ:** `/orders/status/pending`

**Response:**

```json
{
    "success": true,
    "message": "Lấy danh sách đơn hàng thành công",
    "data": [...],
    "count": 5
}
```

## Quy Tắc Chuyển Trạng Thái

1. **Không thể thay đổi** trạng thái của đơn hàng đã `delivered` hoặc `cancelled`
2. **Chỉ có thể hủy** đơn hàng ở trạng thái `pending` hoặc `processing`
3. **Luồng trạng thái chuẩn:**
    - `pending` → `processing` → `shipped` → `delivered`
    - Hoặc: `pending`/`processing` → `cancelled`

## Testing với cURL

### Lấy tất cả đơn hàng:

```bash
curl -X GET http://localhost:8000/api/orders
```

### Cập nhật trạng thái:

```bash
curl -X PATCH http://localhost:8000/api/orders/1/status \
  -H "Content-Type: application/json" \
  -d "{\"status\":\"processing\"}"
```

### Hủy đơn hàng:

```bash
curl -X PATCH http://localhost:8000/api/orders/1/cancel
```

## Testing với Postman

1. Import collection vào Postman
2. Tạo environment với biến `base_url = http://localhost:8000/api`
3. Test từng endpoint theo thứ tự

## Lưu Ý Quan Trọng

1. Đảm bảo database đã được migrate: `php artisan migrate`
2. API trả về status code phù hợp:
    - `200`: Success
    - `400`: Bad Request (logic error)
    - `404`: Not Found
    - `422`: Validation Error
    - `500`: Server Error

## Cài Đặt và Chạy

```bash
# Cài dependencies
composer install

# Migrate database
php artisan migrate

# Chạy server
php artisan serve
```

Sau đó truy cập API tại: `http://localhost:8000/api/orders`
