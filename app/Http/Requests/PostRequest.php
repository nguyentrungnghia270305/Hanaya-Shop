<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PostRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'slug' => ['nullable', 'string', 'max:255'],
            'author_id' => ['nullable', 'integer'],
            'status' => ['nullable', 'in:draft,published,archived'],
            'tags' => ['sometimes'],
            'featured_image' => ['sometimes', 'nullable', 'image', 'max:3072'],
        ];

        if (in_array($this->method(), ['PUT', 'PATCH'])) {
            $rules['title'] = ['sometimes', 'string', 'max:255'];
            $rules['body'] = ['sometimes', 'string'];
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'title.required' => 'Title is required',
            'body.required' => 'Content is required',
        ];
    }
}
