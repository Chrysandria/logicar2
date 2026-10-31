<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationRateLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_returns_429_after_five_failed_attempts_from_same_account_and_ip(): void
    {
        User::create([
            'name' => 'rate-limited-user',
            'service' => 'SNR',
            'password' => 'secret123',
        ]);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->postJson('/auth/login', [
                'name' => 'rate-limited-user',
                'password' => 'wrong-password',
            ])->assertForbidden();
        }

        $this->postJson('/auth/login', [
            'name' => 'rate-limited-user',
            'password' => 'wrong-password',
        ])->assertTooManyRequests();

        $this->postJson('/auth/login', [
            'name' => 'another-user',
            'password' => 'wrong-password',
        ])->assertForbidden();
    }
}
