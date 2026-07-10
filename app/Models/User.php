<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use App\Mail\PasswordResetMail;
use App\Models\EmailLog;
use App\Notifications\QueuedVerifyEmail;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;

class User extends Authenticatable implements CanResetPasswordContract, MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use LogsActivity, HasFactory, Notifiable, CanResetPassword;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->description = "{$eventName}d";
    }

    public function sendPasswordResetNotification($token)
    {
        if (!$this->email) {
            return;
        }

        $mailable = new PasswordResetMail($this, (string) $token);

        if (Schema::hasTable('email_logs')) {
            EmailLog::query()->create([
                'id' => $mailable->emailLogId,
                'user_id' => $this->id,
                'to_email' => $this->email,
                'type' => 'password_reset',
                'subject' => $mailable->envelope()->subject,
                'status' => 'queued',
                'metadata' => [
                    'scope' => 'security',
                ],
            ]);
        }

        Mail::to($this->email)->queue($mailable);
    }

    public function sendEmailVerificationNotification()
    {
        $this->notify(new QueuedVerifyEmail);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'fullname',
        'number',
        'username',
        'email',
        'google_id',
        'google_avatar',
        'password',
        'transaction_pin',
        'reseller_id',
        'referral_id',
        'referred_user_id',
        'online_status',
        'user_status',
        'kyc_tier',
        'kyc_rejection_reason',
        'completed_tours',
        'api_access_status',
        'api_application_details',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'transaction_pin',
        'remember_token',
        'google2fa_secret',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'completed_tours' => 'array',
            'api_application_details' => 'array',
        ];
    }

    /**
     * Get the wallet balance record for this user.
     */
    public function balance()
    {
        return $this->hasOne(AccountBalance::class, 'user_id', 'id');
    }

    public function apiTokens()
    {
        return $this->hasMany(ApiToken::class, 'user_id', 'id');
    }

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referred_user_id', 'id');
    }

    public function referredUsers()
    {
        return $this->hasMany(User::class, 'referred_user_id', 'id');
    }

    public function referralsMade()
    {
        return $this->hasMany(Referral::class, 'referrer_user_id', 'id');
    }

    public function referralRecord()
    {
        return $this->hasOne(Referral::class, 'referred_user_id', 'id');
    }

    /**
     * Get the transactions for this user.
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'user_email', 'email');
    }

    /**
     * Get the verification results for this user.
     */
    public function verificationResults()
    {
        return $this->hasMany(VerificationResult::class);
    }
    
    public function hasCompletedTour($tour)
    {
        return in_array($tour, $this->completed_tours ?? []);
    }

    public function logisticsProfile()
    {
        return $this->hasOne(LogisticsProfile::class);
    }

    public function deliveryAgent()
    {
        return $this->hasOne(DeliveryAgent::class);
    }
}
