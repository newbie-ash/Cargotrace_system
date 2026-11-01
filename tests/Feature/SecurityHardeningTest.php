<?php

namespace Tests\Feature;

use App\Models\Carrier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        RateLimiter::clear('127.0.0.1');

        $this->admin = User::create([
            'name' => 'Sec Admin',
            'email' => 'admin.sec@cargotrace.app',
            'password' => bcrypt('password123'),
            'role' => 'admin',
        ]);
    }

    public function test_security_headers_are_present_in_responses(): void
    {
        $response = $this->get('/login');

        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
    }

    public function test_xss_scripts_are_stripped_from_input_by_sanitizing_middleware(): void
    {
        $this->actingAs($this->admin);

        $payload = [
            'code' => 'XSS_TEST',
            'name' => '<script>alert("Hacked")</script>Pacific Star Lines',
            'transport_mode' => 'SEA',
            'contact_email' => 'safe@carrier.com',
        ];

        $response = $this->post('/carriers', $payload);
        $response->assertRedirect('/carriers');

        // Verify the <script> tags were completely stripped and harmless
        $this->assertDatabaseHas('carriers', [
            'code' => 'XSS_TEST',
            'name' => 'alert("Hacked")Pacific Star Lines',
        ]);

        $this->assertDatabaseMissing('carriers', [
            'name' => '<script>alert("Hacked")</script>Pacific Star Lines',
        ]);
    }

    public function test_web_login_rate_limiting_locks_out_brute_force(): void
    {
        // 5 failed login attempts
        for ($i = 0; $i < 5; $i++) {
            $response = $this->post('/login', [
                'email' => 'admin.sec@cargotrace.app',
                'password' => 'wrong-password',
            ]);
            $response->assertSessionHasErrors('email');
        }

        // 6th attempt must trigger rate limiter lockout
        $blockedResponse = $this->post('/login', [
            'email' => 'admin.sec@cargotrace.app',
            'password' => 'wrong-password',
        ]);

        $blockedResponse->assertSessionHasErrors('email');
        $this->assertStringContainsString('Terlalu banyak percobaan login', session('errors')->first('email'));
    }

    public function test_api_auth_token_rate_limiting_returns_429(): void
    {
        // 5 failed API attempts
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/auth/token', [
                'email' => 'admin.sec@cargotrace.app',
                'password' => 'wrong-password',
            ]);
        }

        // 6th attempt must return 429 Too Many Requests
        $response = $this->postJson('/api/v1/auth/token', [
            'email' => 'admin.sec@cargotrace.app',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(429)
            ->assertJsonPath('status', 'error');
    }
}
