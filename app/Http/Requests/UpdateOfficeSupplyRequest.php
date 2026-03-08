<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOfficeSupplyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id' => ['required', 'string', 'max:50'],
            'item_name' => ['required', 'string', 'max:255'],
            'current_quantity' => ['required', 'integer', 'min:0'],
            'quantity' => ['required', 'integer', 'min:0'],
            'stock_action' => ['required', 'in:add_stock,withdraw_stock'],
            'department_requested' => ['nullable', 'string', 'in:Engineering,Production,Finance,Taxation,Store Room'],
            'notes' => ['nullable', 'string'],
            'brand' => ['nullable', 'string', 'max:100'],
            'location' => ['nullable', 'string', 'max:255'],
            'date_arrived' => ['nullable', 'date'],
            'expiration_date' => ['nullable', 'date'],
            'updated_at' => ['nullable', 'date'],
            'min_stock' => ['nullable', 'integer', 'min:0'],
            'max_stock' => ['nullable', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'max:2048'],
        ];
    }
}
