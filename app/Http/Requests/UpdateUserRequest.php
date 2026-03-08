<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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
        $userId = $this->input('user_id');

        return [
            'user_id' => ['required', 'exists:users,id'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username,'.$userId],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$userId],
            'contact_number' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'role' => [
                'required',
                'string',
                'in:Store Room Supervisor,Store Room Assistant,Engineering Supervisor,Production Supervisor,HR Supervisor,Finance Supervisor,Taxation Supervisor',
            ],
        ];
    }
}
