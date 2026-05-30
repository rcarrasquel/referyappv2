<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Client extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'identity_type',
        'identity_value',
        'first_name',
        'last_name',
        'full_name',
        'email',
        'phone',
        'address_1',
        'address_2',
        'city',
        'country',
        'zip',
        'state',
        'status',
        'interest',
        'notes',
        'last_interaction_at',
    ];

    protected function casts(): array
    {
        return [
            'last_interaction_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Client $client): void {
            if (! $client->id) {
                $client->id = (string) Str::uuid();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'client_product', 'client_id', 'product_id')->withTimestamps();
    }
}
