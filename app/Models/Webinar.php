<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Webinar extends Model
{
    use HasFactory;

    protected $table = 'webinars';

    protected $fillable = [
        'slug',
        'title',
        'subtitle',
        'description',
        'category',
        'presenter_name',
        'presenter_title',
        'presenter_avatar',
        'scheduled_at',
        'duration_minutes',
        'status',
        'meeting_provider',
        'join_url',
        'recording_url',
        'price',
        'max_attendees',
        'banner_image',
        'agenda',
        'features',
        'created_by',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'duration_minutes' => 'integer',
        'price' => 'decimal:2',
        'max_attendees' => 'integer',
        'agenda' => 'array',
        'features' => 'array',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($webinar) {
            if (empty($webinar->slug)) {
                $webinar->slug = Str::slug($webinar->title) . '-' . Str::random(5);
            }
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(WebinarRegistration::class, 'webinar_id');
    }

    public function isFree(): bool
    {
        return (float) $this->price <= 0;
    }

    public function isLive(): bool
    {
        return $this->status === 'live';
    }

    public function isUpcoming(): bool
    {
        return $this->status === 'upcoming' && $this->scheduled_at->isFuture();
    }

    public function isEnded(): bool
    {
        return $this->status === 'ended' || $this->scheduled_at->addMinutes($this->duration_minutes)->isPast();
    }

    public function registeredCount(): int
    {
        return $this->registrations()->where('status', '!=', 'cancelled')->count();
    }

    public function isSeatsAvailable(): bool
    {
        if ($this->max_attendees === null) {
            return true;
        }

        return $this->registeredCount() < $this->max_attendees;
    }
}
