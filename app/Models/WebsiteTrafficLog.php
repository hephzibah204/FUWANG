<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class WebsiteTrafficLog extends Model
{
    use HasFactory;

    protected $table = 'website_traffic_logs';

    public $timestamps = false;

    protected $fillable = [
        'visitor_id',
        'session_id',
        'user_id',
        'ip',
        'path',
        'url',
        'method',
        'origination_source',
        'referrer',
        'referrer_host',
        'device_type',
        'browser',
        'platform',
        'country_code',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_term',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    protected static bool $schemaVerified = false;

    /**
     * Self-healing table check to guarantee table exists.
     */
    public static function ensureSchemaIntegrity(): void
    {
        if (self::$schemaVerified) {
            return;
        }

        try {
            if (!Schema::hasTable('website_traffic_logs')) {
                Schema::create('website_traffic_logs', function (Blueprint $table) {
                    $table->id();
                    $table->string('visitor_id', 64)->index();
                    $table->string('session_id', 100)->nullable()->index();
                    $table->unsignedBigInteger('user_id')->nullable()->index();
                    $table->string('ip', 45)->nullable();
                    $table->string('path', 255)->index();
                    $table->text('url')->nullable();
                    $table->string('method', 10)->default('GET');
                    $table->string('origination_source', 50)->default('Direct')->index();
                    $table->text('referrer')->nullable();
                    $table->string('referrer_host', 150)->nullable()->index();
                    $table->string('device_type', 20)->default('Desktop')->index();
                    $table->string('browser', 50)->nullable();
                    $table->string('platform', 50)->nullable();
                    $table->string('country_code', 10)->nullable();
                    $table->string('utm_source', 100)->nullable();
                    $table->string('utm_medium', 100)->nullable();
                    $table->string('utm_campaign', 100)->nullable();
                    $table->string('utm_term', 100)->nullable();
                    $table->timestamp('created_at')->useCurrent()->index();
                });
            }
            self::$schemaVerified = true;
        } catch (\Throwable $e) {
            // Schema creation failure fallback
        }
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
