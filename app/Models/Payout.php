<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payout extends Model
{
    protected $fillable = [
        'ong_id',
        'period',
        'gross_cents',
        'fee_cents',
        'net_cents',
        'status',
        'gateway',
        'gateway_transfer_id',
        'receipt_path',
        'scheduled_for',
        'paid_at',
    ];

    protected $casts = [
        'gross_cents' => 'integer',
        'fee_cents' => 'integer',
        'net_cents' => 'integer',
        'scheduled_for' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function ong(): BelongsTo
    {
        return $this->belongsTo(Ong::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PayoutItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
