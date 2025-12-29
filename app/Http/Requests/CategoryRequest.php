<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CategoryRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'name' => ['required', 'string', 'max:191'],
            'description' => ['nullable', 'string'],
            'parent_id' => ['nullable', 'integer'],
            'status' => ['nullable', 'in:active,inactive,archived'],
            'image' => ['sometimes', 'nullable', 'image', 'max:2048'],
        ];

        if (in_array($this->method(), ['PUT', 'PATCH'])) {
            $rules['name'] = ['sometimes', 'string', 'max:191'];
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'name.required' => 'Category name is required',
        ];
    }
}
