<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CardShareEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'card_id',
        'channel',
        'ip_address',
        'browser',
        'os',
        'device_type',
        'session_id',
        'fingerprint',
        'accept_language',
        'referer',
        'user_agent',
        'shared_at',
    ];

    protected function casts(): array
    {
        return [
            'shared_at' => 'datetime',
        ];
    }

    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }
}
