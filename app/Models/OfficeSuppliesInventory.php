<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfficeSuppliesInventory extends Model
{
    protected $table = 'office_supplies_inventory';

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'id',
        'item_name',
        'image_path',
        'quantity',
        'min_stock',
        'max_stock',
        'notes',
        'brand',
        'location',
        'date_arrived',
        'expiration_date',
        'category',
        'unit',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'min_stock' => 'integer',
            'max_stock' => 'integer',
            'date_arrived' => 'date',
            'expiration_date' => 'date',
        ];
    }
}
