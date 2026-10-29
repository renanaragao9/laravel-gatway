<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GatewayAccount extends Model
{
    protected $fillable = [
        'provider',
        'mode',
        'credentials',
        'active',
    ];

    protected $casts = [
        'credentials' => 'encrypted:array',
        'active' => 'boolean',
    ];
}
