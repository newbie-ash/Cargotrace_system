<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ErrorPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_missing_web_route_renders_inertia_404_error_page(): void
    {
        $this->get('/halaman-yang-tidak-tersedia-404')
            ->assertStatus(404)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Error')
                ->where('status', 404)
            );
    }

    public function test_unauthorized_role_renders_inertia_403_error_page(): void
    {
        $customer = User::create([
            'name' => 'Customer Tester',
            'email' => 'customer.err@delixie.app',
            'password' => bcrypt('password'),
            'role' => 'customer',
        ]);

        $this->actingAs($customer)
            ->get('/users')
            ->assertStatus(403)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Error')
                ->where('status', 403)
            );
    }

    public function test_missing_api_route_still_returns_json_404(): void
    {
        $this->getJson('/api/endpoint-tidak-ada')
            ->assertStatus(404)
            ->assertJsonStructure(['message']);
    }
}
