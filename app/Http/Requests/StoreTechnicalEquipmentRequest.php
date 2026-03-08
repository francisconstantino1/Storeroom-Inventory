<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTechnicalEquipmentRequest extends FormRequest
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
            'id' => ['required', 'string', 'max:50', 'unique:technical_equipments_inventory,id'],
            'item_name' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:0'],
            'min_stock' => ['nullable', 'integer', 'min:0'],
            'max_stock' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'string', 'in:Working,Not Working'],
            'notes' => ['nullable', 'string'],
            'brand' => ['nullable', 'string', 'max:100'],
            'location' => ['nullable', 'string', 'max:255'],
            'date_arrived' => ['nullable', 'date'],
            'expiration_date' => ['nullable', 'date'],
            'updated_at' => ['nullable', 'date'],
            'category' => ['nullable', 'string', 'max:100'],
        ];
    }
}
