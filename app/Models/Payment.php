<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    public const TYPES = [
        'dp' => 'DP (Uang Muka)',
        'tambahan' => 'Pembayaran Tambahan',
        'pelunasan' => 'Pelunasan',
    ];

    public const METHODS = [
        'tunai' => 'Tunai (Cash)',
        'transfer' => 'Transfer Bank',
        'qris' => 'QRIS',
        'debit' => 'Kartu Debit / EDC',
    ];

    protected $fillable = ['order_id', 'type', 'amount', 'paid_at', 'method', 'notes'];
    protected $casts = ['paid_at' => 'date', 'amount' => 'integer'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function getMethodLabelAttribute(): string
    {
        return self::METHODS[$this->method] ?? ucfirst($this->method);
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? ucfirst($this->type);
    }
}
