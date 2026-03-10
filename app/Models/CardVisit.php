<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CardVisit extends Model
{
    use HasFactory;

    protected $fillable = [
        'card_id',
        'ip_address',
        'browser',
        'os',
        'device_type',
        'session_id',
        'fingerprint',
        'accept_language',
        'referer',
        'path',
        'user_agent',
        'visited_at',
    ];

    protected function casts(): array
    {
        return [
            'visited_at' => 'datetime',
        ];
    }

    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }
}
