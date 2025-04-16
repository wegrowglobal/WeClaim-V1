<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Auth\RegistrationRequest;
use App\Models\User\User;

class StoreRegistrationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Guests should be able to make this request.
     */
    public function authorize(): bool
    {
        return true; // Anyone can request an account
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'second_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 
                'string', 
                'email', 
                'max:255', 
                Rule::unique(User::class), // Check if email exists in users table
                Rule::unique(RegistrationRequest::class)->where(function ($query) {
                    // Check if email exists in pending/approved registration requests
                    return $query->whereIn('status', ['Pending', 'Approved']);
                }),
            ],
            'role_id' => ['required', 'integer', Rule::exists('roles', 'id')],
            'department_id' => ['required', 'integer', Rule::exists('departments', 'id')],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.unique' => 'An account with this email already exists.',
            'role_id.exists' => 'The selected role is invalid.',
            'department_id.exists' => 'The selected department is invalid.',
        ];
    }
} 