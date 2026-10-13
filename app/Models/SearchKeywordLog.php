<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SearchKeywordLog extends Model
{
    use HasFactory;

    protected $table = 'search_keyword_logs';

    public $timestamps = false;

    protected $fillable = [
        'keyword',
        'source',
        'user_id',
        'visitor_id',
        'results_count',
        'page',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'results_count' => 'integer',
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
            if (!Schema::hasTable('search_keyword_logs')) {
                Schema::create('search_keyword_logs', function (Blueprint $table) {
                    $table->id();
                    $table->string('keyword', 255)->index();
                    $table->string('source', 50)->default('search_hub')->index();
                    $table->unsignedBigInteger('user_id')->nullable()->index();
                    $table->string('visitor_id', 64)->nullable()->index();
                    $table->integer('results_count')->default(0);
                    $table->string('page', 255)->nullable();
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
