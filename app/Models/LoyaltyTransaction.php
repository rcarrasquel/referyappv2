<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class LoyaltyTransaction extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'card_id',
        'program_id',
        'business_user_id',
        'customer_user_id',
        'action',
        'stamp_delta',
        'stamps_before',
        'stamps_after',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'stamp_delta' => 'integer',
            'stamps_before' => 'integer',
            'stamps_after' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (LoyaltyTransaction $tx): void {
            if (! $tx->id) {
                $tx->id = (string) Str::uuid();
            }
        });
    }

    public function card(): BelongsTo
    {
        return $this->belongsTo(LoyaltyCard::class, 'card_id');
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(LoyaltyProgram::class, 'program_id');
    }
}

