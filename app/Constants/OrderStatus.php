<?php

namespace App\Constants;

class OrderStatus
{
    const PENDING = 'pending';
    const PROCESSING = 'processing';
    const SHIPPED = 'shipped';
    const DELIVERED = 'delivered';
    const CANCELLED = 'cancelled';

    /**
     * Lấy tất cả các trạng thái có sẵn
     */
    public static function all(): array
    {
        return [
            self::PENDING,
            self::PROCESSING,
            self::SHIPPED,
            self::DELIVERED,
            self::CANCELLED,
        ];
    }

    /**
     * Kiểm tra trạng thái có hợp lệ không
     */
    public static function isValid(string $status): bool
    {
        return in_array($status, self::all());
    }

    /**
     * Các trạng thái có thể hủy
     */
    public static function cancellable(): array
    {
        return [
            self::PENDING,
            self::PROCESSING,
        ];
    }

    /**
     * Các trạng thái đã hoàn tất (không thể thay đổi)
     */
    public static function final(): array
    {
        return [
            self::DELIVERED,
            self::CANCELLED,
        ];
    }

    /**
     * Lấy mô tả tiếng Việt của trạng thái
     */
    public static function getLabel(string $status): string
    {
        return match($status) {
            self::PENDING => 'Đang chờ xử lý',
            self::PROCESSING => 'Đang xử lý',
            self::SHIPPED => 'Đã gửi hàng',
            self::DELIVERED => 'Đã giao hàng',
            self::CANCELLED => 'Đã hủy',
            default => 'Không xác định',
        };
    }
}
