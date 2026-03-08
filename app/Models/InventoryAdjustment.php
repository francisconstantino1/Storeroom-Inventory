<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryAdjustment extends Model
{
    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'inventory_type',
        'type_label',
        'item_id',
        'item_name',
        'change_amount',
        'quantity_before',
        'quantity_after',
        'department_requested',
        'adjusted_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'change_amount' => 'integer',
            'quantity_before' => 'integer',
            'quantity_after' => 'integer',
            'adjusted_at' => 'datetime',
        ];
    }
}
