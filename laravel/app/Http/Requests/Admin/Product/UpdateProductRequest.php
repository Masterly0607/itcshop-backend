<?php

namespace App\Http\Requests\Admin\Product;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:2000'],
            'description' => ['sometimes', 'nullable', 'string'],
            'price' => ['sometimes', 'nullable', 'numeric'],
            'category_id' => ['sometimes', 'exists:categories,id'],
            'flash_sale_start' => ['sometimes', 'nullable', 'date'],
            'flash_sale_end' => ['sometimes', 'nullable', 'date', 'after_or_equal:flash_sale_start'],

            'images' => ['nullable', 'array'], // ✅ added
            'images.*' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],

            'removed_image_ids' => ['nullable', 'array'],
            'removed_image_ids.*' => ['integer', 'exists:product_images,id'],
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->has('price') && is_string($this->price)) {
            $this->merge([
                'price' => floatval(str_replace(',', '', $this->price)),
            ]);
        }
    }

    public function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        // ❌ Remove or comment in production
        // dd($validator->errors());
    }
}
