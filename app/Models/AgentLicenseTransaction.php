<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class AgentLicenseTransaction extends Model
{
    use HasFactory;

    protected $table = 'agent_license_transactions';

    protected $fillable = [
        'agent_id',
        'user_id',
        'amount',
        'payment_method',
        'reference',
        'gateway_reference',
        'proof_path',
        'status',
        'admin_id',
        'meta',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'meta' => 'array',
    ];

    /**
     * Cache flag to avoid checking schema on every single query in a request.
     */
    protected static bool $schemaVerified = false;

    /**
     * Self-healing schema validation: ensures table and critical columns exist.
     */
    public static function ensureSchemaIntegrity(): void
    {
        if (self::$schemaVerified) {
            return;
        }

        try {
            if (!Schema::hasTable('agent_license_transactions')) {
                Schema::create('agent_license_transactions', function ($table) {
                    $table->id();
                    $table->foreignId('agent_id')->constrained('enrollment_agents')->onDelete('cascade');
                    $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                    $table->decimal('amount', 12, 2);
                    $table->string('payment_method', 50);
                    $table->string('reference', 120)->unique();
                    $table->string('gateway_reference', 120)->nullable()->index();
                    $table->string('proof_path')->nullable();
                    $table->enum('status', ['pending', 'completed', 'failed', 'rejected'])->default('pending')->index();
                    $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete();
                    $table->json('meta')->nullable();
                    $table->text('notes')->nullable();
                    $table->timestamps();
                });
                self::$schemaVerified = true;
                return;
            }

            // Verify and add missing columns if needed
            Schema::table('agent_license_transactions', function ($table) {
                if (!Schema::hasColumn('agent_license_transactions', 'reference')) {
                    if (Schema::hasColumn('agent_license_transactions', 'payment_reference')) {
                        $table->renameColumn('payment_reference', 'reference');
                    } elseif (Schema::hasColumn('agent_license_transactions', 'transaction_reference')) {
                        $table->renameColumn('transaction_reference', 'reference');
                    } else {
                        $table->string('reference', 120)->nullable()->index();
                    }
                }
                if (!Schema::hasColumn('agent_license_transactions', 'gateway_reference')) {
                    $table->string('gateway_reference', 120)->nullable()->index();
                }
                if (!Schema::hasColumn('agent_license_transactions', 'proof_path')) {
                    $table->string('proof_path')->nullable();
                }
                if (!Schema::hasColumn('agent_license_transactions', 'status')) {
                    $table->string('status', 30)->default('pending')->index();
                }
                if (!Schema::hasColumn('agent_license_transactions', 'admin_id')) {
                    $table->foreignId('admin_id')->nullable();
                }
                if (!Schema::hasColumn('agent_license_transactions', 'meta')) {
                    $table->json('meta')->nullable();
                }
                if (!Schema::hasColumn('agent_license_transactions', 'notes')) {
                    $table->text('notes')->nullable();
                }
                if (!Schema::hasColumn('agent_license_transactions', 'payment_method')) {
                    $table->string('payment_method', 50)->nullable();
                }
                if (!Schema::hasColumn('agent_license_transactions', 'amount')) {
                    $table->decimal('amount', 12, 2)->default(0);
                }
                if (!Schema::hasColumn('agent_license_transactions', 'agent_id')) {
                    $table->foreignId('agent_id')->nullable();
                }
                if (!Schema::hasColumn('agent_license_transactions', 'user_id')) {
                    $table->foreignId('user_id')->nullable();
                }
            });

            self::$schemaVerified = true;
        } catch (\Throwable $e) {
            Log::warning('Notice ensuring agent_license_transactions schema: ' . $e->getMessage());
        }
    }

    /**
     * Defensive creation helper that ensures columns exist before insertion.
     */
    public static function createSafe(array $attributes): self
    {
        self::ensureSchemaIntegrity();

        try {
            $columns = Schema::getColumnListing('agent_license_transactions');
            $filtered = [];
            foreach ($attributes as $key => $val) {
                if (in_array($key, $columns)) {
                    $filtered[$key] = $val;
                }
            }

            // Map alternate reference column names if primary 'reference' was not found
            if (!isset($filtered['reference']) && isset($attributes['reference'])) {
                if (in_array('payment_reference', $columns)) {
                    $filtered['payment_reference'] = $attributes['reference'];
                } elseif (in_array('transaction_reference', $columns)) {
                    $filtered['transaction_reference'] = $attributes['reference'];
                }
            }

            return static::create($filtered);
        } catch (\Throwable $e) {
            return static::create($attributes);
        }
    }

    /**
     * Defensive updateOrCreate helper that guarantees column compatibility.
     */
    public static function updateOrCreateSafe(array $match, array $values): self
    {
        self::ensureSchemaIntegrity();

        try {
            $columns = Schema::getColumnListing('agent_license_transactions');
            $cleanMatch = [];
            $cleanValues = [];

            foreach ($match as $k => $v) {
                if (in_array($k, $columns)) {
                    $cleanMatch[$k] = $v;
                }
            }

            foreach ($values as $k => $v) {
                if (in_array($k, $columns)) {
                    $cleanValues[$k] = $v;
                }
            }

            return static::updateOrCreate($cleanMatch, $cleanValues);
        } catch (\Throwable $e) {
            return static::updateOrCreate($match, $values);
        }
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(EnrollmentAgent::class, 'agent_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }
}
