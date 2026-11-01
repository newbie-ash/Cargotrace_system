<?php

namespace Tests\Feature;

use App\Models\Carrier;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AuthAndRbacTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $operator;
    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin Tester',
            'email' => 'admin.test@cargotrace.app',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->operator = User::create([
            'name' => 'Operator Tester',
            'email' => 'operator.test@cargotrace.app',
            'password' => bcrypt('password'),
            'role' => 'operator',
        ]);

        $this->customer = User::create([
            'name' => 'Customer Tester',
            'email' => 'customer.test@cargotrace.app',
            'password' => bcrypt('password'),
            'role' => 'customer',
        ]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');

        $responseCarriers = $this->get('/carriers');
        $responseCarriers->assertRedirect('/login');
    }

    public function test_login_screen_renders_inertia_component(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Auth/Login'));
    }

    public function test_dashboard_renders_inertia_component_with_props(): void
    {
        $this->actingAs($this->admin);

        $this->get('/dashboard')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Dashboard/Index')
                ->has('stats')
                ->has('stats.total_shipments')
                ->has('stats.recent_audits')
            );
    }

    public function test_admin_can_access_dashboard_and_all_master_data(): void
    {
        $this->actingAs($this->admin);

        $this->get('/dashboard')->assertOk();
        $this->get('/carriers')->assertOk();
        $this->get('/carriers/create')->assertOk();
        $this->get('/locations')->assertOk();
        $this->get('/locations/create')->assertOk();
        $this->get('/users')->assertOk();
        $this->get('/users/create')->assertOk();
    }

    public function test_operator_can_view_master_data_but_cannot_mutate(): void
    {
        $this->actingAs($this->operator);

        // Can view lists
        $this->get('/dashboard')->assertOk();
        $this->get('/carriers')->assertOk();
        $this->get('/locations')->assertOk();

        // Forbidden from mutation screens
        $this->get('/carriers/create')->assertForbidden();
        $this->get('/locations/create')->assertForbidden();

        // Forbidden from user management
        $this->get('/users')->assertForbidden();
        $this->get('/users/create')->assertForbidden();

        // POST mutation directly rejected with 403
        $response = $this->post('/carriers', [
            'code' => 'HACK_CARRIER',
            'name' => 'Unauthorized Carrier',
            'transport_mode' => 'SEA',
        ]);
        $response->assertForbidden();
    }

    public function test_customer_is_forbidden_from_master_data(): void
    {
        $this->actingAs($this->customer);

        // Can access dashboard
        $this->get('/dashboard')->assertOk();

        // Strictly forbidden from carriers, locations, and users
        $this->get('/carriers')->assertForbidden();
        $this->get('/locations')->assertForbidden();
        $this->get('/users')->assertForbidden();
    }
}
