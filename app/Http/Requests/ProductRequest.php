<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'sku' => ['nullable', 'string', 'max:64'],
            'category_id' => ['nullable', 'integer'],
            'status' => ['nullable', 'in:active,inactive,draft'],
            'image' => ['sometimes', 'nullable', 'image', 'max:2048'],
        ];

        // For PUT/PATCH requests, make some fields optional
        if (in_array($this->method(), ['PUT', 'PATCH'])) {
            $rules['name'] = ['sometimes', 'string', 'max:255'];
            $rules['price'] = ['sometimes', 'numeric', 'min:0'];
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'name.required' => 'Product name is required',
            'price.required' => 'Product price is required',
        ];
    }
}
