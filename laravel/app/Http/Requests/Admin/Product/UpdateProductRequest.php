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
      'title' => ['sometimes', 'max:2000'],
      'image' => ['sometimes', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
      'price' => ['sometimes', 'nullable', 'numeric'],
      'description' => ['sometimes', 'nullable', 'string'],
      'flash_sale_start' => ['sometimes', 'nullable', 'date'],
      'flash_sale_end' => ['sometimes', 'nullable', 'date', 'after_or_equal:flash_sale_start'],
      'is_flash_sale' => ['sometimes', 'boolean'],
      'category_id' => ['sometimes', 'nullable', 'exists:categories,id'],
    ];
  }
}
