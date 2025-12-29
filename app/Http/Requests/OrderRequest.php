<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'status' => ['sometimes', 'string', 'in:pending,processing,shipped,completed,cancelled,refunded'],
            'tracking_number' => ['sometimes', 'nullable', 'string', 'max:191'],
            'assigned_to' => ['sometimes', 'nullable', 'integer'],
            'admin_note' => ['sometimes', 'nullable', 'string'],
            'cancel_reason' => ['sometimes', 'nullable', 'string'],
        ];

        if (in_array($this->method(), ['POST'])) {
            // creation via admin not typical, but allow basic fields
            $rules['customer_name'] = ['sometimes', 'string', 'max:191'];
            $rules['customer_email'] = ['sometimes', 'email', 'max:191'];
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'status.in' => 'Invalid status value',
        ];
    }
}
