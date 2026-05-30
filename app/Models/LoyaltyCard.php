<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class LoyaltyCard extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'program_id',
        'business_user_id',
        'customer_user_id',
        'stamps_current',
        'stamps_required',
        'status',
        'completed_at',
        'redeemed_at',
    ];

    protected function casts(): array
    {
        return [
            'stamps_current' => 'integer',
            'stamps_required' => 'integer',
            'completed_at' => 'datetime',
            'redeemed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (LoyaltyCard $card): void {
            if (! $card->id) {
                $card->id = (string) Str::uuid();
            }
        });
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(LoyaltyProgram::class, 'program_id');
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(User::class, 'business_user_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_user_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(LoyaltyTransaction::class, 'card_id');
    }
}

