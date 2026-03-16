<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StripeSetting extends Model
{
    protected $fillable = [
        'publishable_key',
        'secret_key',
        'webhook_secret',
        'currency',
        'monthly_price_cents',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'monthly_price_cents' => 'integer',
        ];
    }
}

