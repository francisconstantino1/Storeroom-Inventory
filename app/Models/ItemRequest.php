<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemRequest extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'inventory_type',
        'item_id',
        'item_name',
        'requested_quantity',
        'status',
        'requested_by_user_id',
        'requested_department',
        'approved_by_user_id',
        'decision_notes',
        'decision_at',
    ];

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }
}
