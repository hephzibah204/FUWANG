<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\VerificationResultService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class VerificationResultServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('fullname')->nullable();
                $table->string('username')->nullable();
                $table->string('email')->unique();
                $table->string('password')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('verification_results')) {
            Schema::create('verification_results', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('service_type');
                $table->string('identifier');
                $table->string('provider_name');
                $table->json('response_data')->nullable();
                $table->string('status')->nullable();
                $table->string('reference_id')->nullable();
                $table->string('report_path')->nullable();
                $table->text('admin_note')->nullable();
                $table->timestamps();
            });
        }
    }

    public function test_creates_result_with_expected_reference_prefix(): void
    {
        $user = User::create([
            'email' => 'u@example.com',
            'password' => 'secret',
        ]);

        $service = app(VerificationResultService::class);
        $result = $service->create(
            $user,
            'nin_verification',
            '12345678901',
            'ProviderX',
            ['ok' => true],
            'success',
            'NIN'
        );

        $this->assertNotNull($result->id);
        $this->assertSame('nin_verification', $result->service_type);
        $this->assertStringStartsWith('NIN-', (string) $result->reference_id);
    }
}

