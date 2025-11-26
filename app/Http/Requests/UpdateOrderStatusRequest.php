<?php

namespace App\Http\Requests;

use App\Constants\OrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderStatusRequest extends FormRequest
{
    /**
     * Xác định người dùng có quyền thực hiện request này không
     */
    public function authorize(): bool
    {
        // Có thể thêm logic kiểm tra quyền admin ở đây
        return true;
    }

    /**
     * Quy tắc validation cho request
     */
    public function rules(): array
    {
        return [
            'status' => [
                'required',
                Rule::in(OrderStatus::all())
            ]
        ];
    }

    /**
     * Thông báo lỗi tùy chỉnh
     */
    public function messages(): array
    {
        return [
            'status.required' => 'Trạng thái đơn hàng không được để trống',
            'status.in' => 'Trạng thái không hợp lệ. Chỉ chấp nhận: ' . implode(', ', OrderStatus::all())
        ];
    }
}
