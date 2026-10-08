<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class WebinarRegistration extends Model
{
    use HasFactory;

    protected $table = 'webinar_registrations';

    protected $fillable = [
        'webinar_id',
        'user_id',
        'full_name',
        'email',
        'phone_number',
        'organization',
        'access_code',
        'status',
        'amount_paid',
        'payment_reference',
        'attended_at',
    ];

    protected $casts = [
        'amount_paid' => 'decimal:2',
        'attended_at' => 'datetime',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($reg) {
            if (empty($reg->access_code)) {
                $reg->access_code = 'WEB-' . strtoupper(Str::random(8));
            }
        });
    }

    public function webinar(): BelongsTo
    {
        return $this->belongsTo(Webinar::class, 'webinar_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
