<?php

namespace App\Http\Requests\Customer\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => 'sometimes|string|max:255',
            'last_name'  => 'sometimes|string|max:255',
            'phone'      => 'nullable|string|max:20',
            'address'    => 'nullable|string|max:255',
            'email'      => 'sometimes|email|unique:customers,email,' . $this->user()->id,
        ];
    }
}
