<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    public const SOURCES = [
        'datang_langsung' => 'Datang Langsung (Offline)',
        'telepon' => 'Telepon (Offline)',
        'pelanggan_tetap' => 'Pelanggan Tetap (Offline)',
        'whatsapp' => 'WhatsApp (Online)',
        'lainnya' => 'Lainnya',
    ];

    public const STATUSES = [
        'baru' => 'Baru',
        'dikonfirmasi' => 'Dikonfirmasi',
        'diproses' => 'Diproses',
        'selesai' => 'Selesai',
        'dibatalkan' => 'Dibatalkan',
    ];

    protected $fillable = [
        'order_number', 'customer_id', 'source', 'order_date', 'event_date',
        'event_location', 'status', 'total_amount', 'notes',
    ];

    protected $casts = [
        'order_date' => 'date',
        'event_date' => 'date',
        'total_amount' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            $order->order_number ??= 'ORD-' . str_pad((string) ((static::max('id') ?? 0) + 1), 4, '0', STR_PAD_LEFT);
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('status', '!=', 'dibatalkan');
    }

    public function scopeOffline(Builder $q): Builder
    {
        return $q->whereIn('source', ['datang_langsung', 'telepon', 'pelanggan_tetap']);
    }

    public function scopeOnline(Builder $q): Builder
    {
        return $q->whereIn('source', ['whatsapp', 'lainnya']);
    }

    public function isOffline(): bool
    {
        return in_array($this->source, ['datang_langsung', 'telepon', 'pelanggan_tetap'], true);
    }

    public function getChannelAttribute(): string
    {
        return $this->isOffline() ? 'Offline' : 'Online';
    }

    public function getSourceLabelAttribute(): string
    {
        return self::SOURCES[$this->source] ?? ucfirst($this->source);
    }

    /** Uang yang sudah diterima. Pakai withSum('payments','amount') agar tidak N+1. */
    public function getPaidAmountAttribute(): int
    {
        return (int) ($this->payments_sum_amount ?? $this->payments()->sum('amount'));
    }

    public function getRemainingAmountAttribute(): int
    {
        return max(0, $this->total_amount - $this->paid_amount);
    }

    public function getPaymentStatusAttribute(): string
    {
        return match (true) {
            $this->paid_amount <= 0 => 'Belum Bayar',
            $this->paid_amount >= $this->total_amount => 'Lunas',
            default => 'DP',
        };
    }
}
