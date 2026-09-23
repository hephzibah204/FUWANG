<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserUsernameGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_username_generation_never_exceeds_twenty_characters(): void
    {
        $longEmail = 'abiodun.gbadamosi@fuwaagribusiness.com';
        $username = User::generateUniqueUsername($longEmail);

        $this->assertLessThanOrEqual(20, strlen($username));
        $this->assertStringStartsWith('abiodungbadam', $username);
    }

    public function test_extremely_long_email_is_safely_truncated(): void
    {
        $hugeEmail = 'this_is_an_extremely_unusually_long_email_address_for_a_user@domain.com';
        $username = User::generateUniqueUsername($hugeEmail);

        $this->assertLessThanOrEqual(20, strlen($username));
        $this->assertNotEmpty($username);
    }

    public function test_user_creation_with_long_email_automatically_generates_valid_username(): void
    {
        $user = User::create([
            'fullname' => 'Abiodun Gbadamosi',
            'email' => 'abiodun.gbadamosi@fuwaagribusiness.com',
            'password' => bcrypt('secret12345'),
        ]);

        $this->assertNotNull($user->username);
        $this->assertLessThanOrEqual(20, strlen($user->username));
    }

    public function test_user_creation_defensively_guards_explicitly_passed_overlength_username(): void
    {
        $user = User::create([
            'fullname' => 'Abiodun Gbadamosi',
            'email' => 'abiodun2@fuwaagribusiness.com',
            'username' => 'abiodungbadamosi_7892', // 21 characters!
            'password' => bcrypt('secret12345'),
        ]);

        $this->assertLessThanOrEqual(20, strlen($user->username));
    }
}
