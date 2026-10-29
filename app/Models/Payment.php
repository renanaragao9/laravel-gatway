<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'ong_id',
        'gateway',
        'gateway_payment_id',
        'method',
        'amount_cents',
        'fee_cents',
        'net_cents',
        'status',
        'period',
        'payer_name',
        'payer_email',
        'payout_id',
        'paid_at',
    ];

    protected $casts = [
        'amount_cents' => 'integer',
        'fee_cents' => 'integer',
        'net_cents' => 'integer',
        'paid_at' => 'datetime',
    ];

    public function ong(): BelongsTo
    {
        return $this->belongsTo(Ong::class);
    }

    public function payout(): BelongsTo
    {
        return $this->belongsTo(Payout::class);
    }
}
