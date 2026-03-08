<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MechanicalInventory extends Model
{
    protected $table = 'mechanical_inventory';

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
        'date_arrived',
        'expiration_date',
        'category',
        'location',
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
