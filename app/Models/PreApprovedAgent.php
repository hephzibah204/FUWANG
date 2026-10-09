<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class PreApprovedAgent extends Model
{
    use HasFactory;

    protected $table = 'pre_approved_agents';

    protected $fillable = [
        'agent_code',
        'first_name',
        'last_name',
        'full_name',
        'email',
        'phone_number',
        'is_claimed',
        'claimed_at',
        'claimed_by_user_id',
        'has_paid_license',
        'license_payment_method',
        'license_notes',
    ];

    protected $casts = [
        'is_claimed' => 'boolean',
        'claimed_at' => 'datetime',
        'has_paid_license' => 'boolean',
    ];

    protected static bool $schemaVerified = false;

    /**
     * Self-healing schema validation: ensures license columns exist on pre_approved_agents.
     */
    public static function ensureSchemaIntegrity(): void
    {
        if (self::$schemaVerified) {
            return;
        }

        try {
            if (!Schema::hasTable('pre_approved_agents')) {
                return;
            }

            Schema::table('pre_approved_agents', function ($table) {
                if (!Schema::hasColumn('pre_approved_agents', 'has_paid_license')) {
                    $table->boolean('has_paid_license')->default(false)->after('is_claimed');
                }
                if (!Schema::hasColumn('pre_approved_agents', 'license_payment_method')) {
                    $table->string('license_payment_method', 50)->nullable()->after('has_paid_license');
                }
                if (!Schema::hasColumn('pre_approved_agents', 'license_notes')) {
                    $table->text('license_notes')->nullable()->after('license_payment_method');
                }
            });

            self::$schemaVerified = true;
        } catch (\Throwable $e) {
            Log::warning('Notice ensuring pre_approved_agents schema: ' . $e->getMessage());
        }
    }

    public function claimedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'claimed_by_user_id');
    }

    public function getFormattedPhoneAttribute(): string
    {
        $raw = (string) $this->phone_number;
        if (strlen($raw) === 10 && !str_starts_with($raw, '0')) {
            return '0' . $raw;
        }
        return $raw;
    }
}
