<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = ['order_id', 'menu_id', 'package_id', 'item_name', 'unit_price', 'quantity', 'subtotal'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
