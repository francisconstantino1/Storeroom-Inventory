<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionSuppliesInventory extends Model
{
    protected $table = 'production_supplies_inventory';
    public $incrementing = false;
    protected $keyType = 'string';
    /** @var list<string> */
    protected $fillable = ['id', 'item_name', 'image_path', 'quantity', 'min_stock', 'max_stock', 'status', 'notes', 'brand', 'date_arrived', 'expiration_date', 'location'];
    /** @return array<string, string> */
    protected function casts(): array { return ['quantity' => 'integer', 'min_stock' => 'integer', 'max_stock' => 'integer', 'date_arrived' => 'date', 'expiration_date' => 'date']; }
}
