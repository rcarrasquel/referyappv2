<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Card extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'name',
        'username',
        'slug',
        'description',
        'phone',
        'email',
        'address',
        'google_maps_url',
        'profile_image',
        'profile_image_style',
        'header_image',
        'header_color',
        'background_type',
        'background_color',
        'background_gradient',
        'background_image',
        'button_style',
        'template_style',
        'text_color',
        'button_background_color',
        'button_text_color',
        'links',
        'schedule',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'links' => 'array',
            'schedule' => 'array',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            if (!$model->id) {
                $model->id = (string) Str::uuid();
            }

            if (! $model->slug && $model->username) {
                $model->slug = Str::lower($model->username);
            }

            $user = User::query()->find($model->user_id);

            if ($user && $user->plan === 'free' && $user->cards()->exists()) {
                throw ValidationException::withMessages([
                    'limit' => 'Free plan allows only one card.',
                ]);
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty('username')) {
                $model->slug = Str::lower($model->username);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function visits(): HasMany
    {
        return $this->hasMany(CardVisit::class);
    }

    public function linkClicks(): HasMany
    {
        return $this->hasMany(CardLinkClick::class);
    }

    public function shareEvents(): HasMany
    {
        return $this->hasMany(CardShareEvent::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }
}
