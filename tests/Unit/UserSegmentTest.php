<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\UserTargeting\UserSegment;
use App\Services\UserTargeting\UserSegmentValidator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class UserSegmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_reseller_id_eq_filter(): void
    {
        $u1 = User::factory()->create(['reseller_id' => 'R-1']);
        $u2 = User::factory()->create(['reseller_id' => 'R-2']);

        $segment = [
            'operator' => 'AND',
            'rules' => [
                ['field' => 'reseller_id', 'op' => 'eq', 'value' => 'R-1'],
            ],
        ];

        $ids = UserSegment::apply(User::query(), $segment)->pluck('id')->all();
        $this->assertEquals([$u1->id], $ids);
        $this->assertNotContains($u2->id, $ids);
    }

    public function test_user_status_in_filter(): void
    {
        $u1 = User::factory()->create(['user_status' => 'active']);
        $u2 = User::factory()->create(['user_status' => 'suspended']);
        $u3 = User::factory()->create(['user_status' => 'inactive']);

        $segment = [
            'operator' => 'AND',
            'rules' => [
                ['field' => 'user_status', 'op' => 'in', 'value' => ['active', 'suspended']],
            ],
        ];

        $ids = UserSegment::apply(User::query(), $segment)->pluck('id')->all();
        sort($ids);
        $expected = [$u1->id, $u2->id];
        sort($expected);
        $this->assertEquals($expected, $ids);
        $this->assertNotContains($u3->id, $ids);
    }

    public function test_signup_date_between_filter(): void
    {
        $u1 = User::factory()->create(['created_at' => Carbon::parse('2026-01-05')]);
        $u2 = User::factory()->create(['created_at' => Carbon::parse('2026-02-10')]);
        $u3 = User::factory()->create(['created_at' => Carbon::parse('2026-03-10')]);

        $segment = [
            'operator' => 'AND',
            'rules' => [
                ['field' => 'signup_date', 'op' => 'between', 'value' => ['from' => '2026-01-01', 'to' => '2026-02-28']],
            ],
        ];

        $ids = UserSegment::apply(User::query(), $segment)->pluck('id')->all();
        sort($ids);
        $expected = [$u1->id, $u2->id];
        sort($expected);
        $this->assertEquals($expected, $ids);
        $this->assertNotContains($u3->id, $ids);
    }

    public function test_wallet_balance_thresholds(): void
    {
        $u1 = User::factory()->create(['email' => 'a@example.com']);
        $u2 = User::factory()->create(['email' => 'b@example.com']);
        $u3 = User::factory()->create(['email' => 'c@example.com']);

        DB::table('account_balances')->insert([
            ['email' => 'a@example.com', 'user_balance' => 50, 'api_key' => 'user', 'created_at' => now(), 'updated_at' => now()],
            ['email' => 'b@example.com', 'user_balance' => 5000, 'api_key' => 'user', 'created_at' => now(), 'updated_at' => now()],
            ['email' => 'c@example.com', 'user_balance' => 150, 'api_key' => 'user', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $segmentGt = [
            'operator' => 'AND',
            'rules' => [
                ['field' => 'wallet_balance', 'op' => 'gt', 'value' => 100],
            ],
        ];
        $idsGt = UserSegment::apply(User::query(), $segmentGt)->pluck('id')->all();
        sort($idsGt);
        $expectedGt = [$u2->id, $u3->id];
        sort($expectedGt);
        $this->assertEquals($expectedGt, $idsGt);
        $this->assertNotContains($u1->id, $idsGt);

        $segmentBetween = [
            'operator' => 'AND',
            'rules' => [
                ['field' => 'wallet_balance', 'op' => 'between', 'value' => ['min' => 0, 'max' => 200]],
            ],
        ];
        $idsBetween = UserSegment::apply(User::query(), $segmentBetween)->pluck('id')->all();
        sort($idsBetween);
        $expectedBetween = [$u1->id, $u3->id];
        sort($expectedBetween);
        $this->assertEquals($expectedBetween, $idsBetween);
        $this->assertNotContains($u2->id, $idsBetween);
    }

    public function test_nested_or_groups(): void
    {
        $u1 = User::factory()->create(['reseller_id' => 'R-1']);
        $u2 = User::factory()->create(['reseller_id' => 'R-2']);
        $u3 = User::factory()->create(['reseller_id' => 'R-3']);

        $segment = [
            'operator' => 'AND',
            'rules' => [
                [
                    'group' => [
                        'operator' => 'OR',
                        'rules' => [
                            ['field' => 'reseller_id', 'op' => 'eq', 'value' => 'R-1'],
                            ['field' => 'reseller_id', 'op' => 'eq', 'value' => 'R-3'],
                        ],
                    ],
                ],
            ],
        ];

        $ids = UserSegment::apply(User::query(), $segment)->pluck('id')->all();
        sort($ids);
        $expected = [$u1->id, $u3->id];
        sort($expected);
        $this->assertEquals($expected, $ids);
        $this->assertNotContains($u2->id, $ids);
    }

    public function test_validator_rejects_invalid_status(): void
    {
        $this->expectException(ValidationException::class);
        UserSegmentValidator::validateAndNormalize('{"operator":"AND","rules":[{"field":"user_status","op":"eq","value":"blocked"}]}');
    }
}

