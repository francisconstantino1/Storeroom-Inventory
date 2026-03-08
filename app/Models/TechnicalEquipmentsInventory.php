<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TechnicalEquipmentsInventory extends Model
{
    protected $table = 'technical_equipments_inventory';

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'id',
        'item_name',
        'quantity',
        'min_stock',
        'max_stock',
        'status',
        'notes',
        'brand',
        'date_arrived',
        'expiration_date',
        'category',
        'location',
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
