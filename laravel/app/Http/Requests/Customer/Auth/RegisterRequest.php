<?php

namespace App\Http\Requests\Customer\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|email|unique:customers,email', // 	Email must be unique in the customers table, specifically in the email column (no duplicate emails)
            'password'   => 'required|confirmed|min:6', // confirmed = There must be another field called password_confirmation, and it must match password
            'phone'      => 'nullable|string|max:20',
            'address'    => 'nullable|string|max:255',
        ];
    }
}
