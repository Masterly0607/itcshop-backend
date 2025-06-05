<?php

namespace App\Http\Requests\Admin\Product;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true; // allow all authenticated admins
  }

  public function rules(): array
  {
    return [
      'title' => ['required', 'max:2000'],
      'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
      'price' => ['required', 'numeric'],
      'description' => ['nullable', 'string'],
      'flash_sale_start' => ['nullable', 'date'],
      'flash_sale_end' => ['nullable', 'date', 'after_or_equal:flash_sale_start'],
      'is_flash_sale' => ['nullable', 'boolean'],
      'category_id' => ['nullable', 'exists:categories,id'],
    ];
  }
}
