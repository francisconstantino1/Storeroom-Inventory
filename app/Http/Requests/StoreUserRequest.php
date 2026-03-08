<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'Store Room Supervisor';
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'contact_number' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'role' => [
                'required',
                'string',
                'in:Store Room Supervisor,Store Room Assistant,Engineering Supervisor,Production Supervisor,HR Supervisor,Finance Supervisor,Taxation Supervisor',
            ],
        ];
    }
}
