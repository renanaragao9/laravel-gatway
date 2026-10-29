<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class LedgerEntry extends Model
{
    protected $fillable = [
        'ong_id',
        'type',
        'amount_cents',
        'reference_type',
        'reference_id',
        'balance_after_cents',
    ];

    protected $casts = [
        'amount_cents' => 'integer',
        'balance_after_cents' => 'integer',
    ];

    public function ong(): BelongsTo
    {
        return $this->belongsTo(Ong::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
