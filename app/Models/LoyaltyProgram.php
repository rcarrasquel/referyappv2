<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class LoyaltyProgram extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'business_user_id',
        'name',
        'description',
        'stamps_required',
        'reward',
        'start_date',
        'expires_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'stamps_required' => 'integer',
            'start_date' => 'date',
            'expires_at' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (LoyaltyProgram $program): void {
            if (! $program->id) {
                $program->id = (string) Str::uuid();
            }
        });
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(User::class, 'business_user_id');
    }

    public function cards(): HasMany
    {
        return $this->hasMany(LoyaltyCard::class, 'program_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(LoyaltyTransaction::class, 'program_id');
    }
}

