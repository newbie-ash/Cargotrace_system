<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MasterDataCrudTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $operator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin Controller',
            'email' => 'admin.crud@cargotrace.app',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->operator = User::create([
            'name' => 'Operator Controller',
            'email' => 'operator.crud@cargotrace.app',
            'password' => bcrypt('password'),
            'role' => 'operator',
        ]);
    }

    public function test_admin_can_create_carrier_and_audit_log_is_recorded(): void
    {
        $this->actingAs($this->admin);

        $payload = [
            'code' => 'COSCO',
            'name' => 'China Ocean Shipping Company',
            'transport_mode' => 'SEA',
            'contact_email' => 'cs@cosco.com',
            'contact_phone' => '+86 21 6596 6666',
            'is_active' => '1',
        ];

        $response = $this->post('/carriers', $payload);
        $response->assertRedirect('/carriers');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('carriers', [
            'code' => 'COSCO',
            'name' => 'China Ocean Shipping Company',
            'transport_mode' => 'SEA',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->admin->id,
            'action' => 'carrier.created',
            'auditable_type' => Carrier::class,
        ]);
    }

    public function test_carrier_validation_rejects_duplicate_code(): void
    {
        $this->actingAs($this->admin);

        Carrier::create([
            'code' => 'EVERGREEN',
            'name' => 'Evergreen Marine Corp',
            'transport_mode' => 'SEA',
        ]);

        $response = $this->post('/carriers', [
            'code' => 'EVERGREEN',
            'name' => 'Duplicate Evergreen',
            'transport_mode' => 'SEA',
        ]);

        $response->assertSessionHasErrors('code');
    }

    public function test_admin_can_update_carrier_and_audit_log_captures_diff(): void
    {
        $this->actingAs($this->admin);

        $carrier = Carrier::create([
            'code' => 'ONE',
            'name' => 'Ocean Network Express',
            'transport_mode' => 'SEA',
            'contact_email' => 'old@one.com',
        ]);

        $response = $this->put("/carriers/{$carrier->id}", [
            'code' => 'ONE',
            'name' => 'Ocean Network Express Holdings',
            'transport_mode' => 'SEA',
            'contact_email' => 'updated@one.com',
            'is_active' => '1',
        ]);

        $response->assertRedirect('/carriers');
        $this->assertDatabaseHas('carriers', [
            'id' => $carrier->id,
            'name' => 'Ocean Network Express Holdings',
            'contact_email' => 'updated@one.com',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->admin->id,
            'action' => 'carrier.updated',
            'auditable_id' => $carrier->id,
        ]);
    }

    public function test_admin_cannot_delete_carrier_with_active_shipments(): void
    {
        $this->actingAs($this->admin);

        $carrier = Carrier::create([
            'code' => 'YML',
            'name' => 'Yang Ming Marine Transport',
            'transport_mode' => 'SEA',
        ]);

        $origin = Location::create([
            'code' => 'LOC1',
            'name' => 'Origin Hub',
            'city' => 'Jakarta',
            'country' => 'Indonesia',
            'type' => 'PORT',
            'latitude' => -6.1,
            'longitude' => 106.8,
        ]);

        $destination = Location::create([
            'code' => 'LOC2',
            'name' => 'Destination Hub',
            'city' => 'Rotterdam',
            'country' => 'Netherlands',
            'type' => 'PORT',
            'latitude' => 51.9,
            'longitude' => 4.4,
        ]);

        Shipment::create([
            'shipment_number' => 'CT-TEST-001',
            'customer_id' => $this->admin->id,
            'carrier_id' => $carrier->id,
            'origin_location_id' => $origin->id,
            'destination_location_id' => $destination->id,
            'created_by' => $this->admin->id,
            'cargo_type' => 'Electronics',
            'expected_departure' => now(),
            'expected_arrival' => now()->addDays(5),
            'estimated_arrival' => now()->addDays(5),
            'current_status' => 'CREATED',
        ]);

        $response = $this->delete("/carriers/{$carrier->id}");
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('carriers', ['id' => $carrier->id]);
    }

    public function test_admin_can_create_location_with_valid_coordinates(): void
    {
        $this->actingAs($this->admin);

        $payload = [
            'code' => 'KRPUS',
            'name' => 'Port of Busan',
            'city' => 'Busan',
            'country' => 'South Korea',
            'type' => 'PORT',
            'latitude' => 35.1028000,
            'longitude' => 129.0403000,
        ];

        $response = $this->post('/locations', $payload);
        $response->assertRedirect('/locations');

        $this->assertDatabaseHas('locations', [
            'code' => 'KRPUS',
            'name' => 'Port of Busan',
            'city' => 'Busan',
            'country' => 'South Korea',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->admin->id,
            'action' => 'location.created',
            'auditable_type' => Location::class,
        ]);
    }

    public function test_admin_can_create_user_and_password_is_properly_hashed(): void
    {
        $this->actingAs($this->admin);

        $payload = [
            'name' => 'Dispatcher Baru',
            'email' => 'dispatcher.baru@cargotrace.app',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'role' => 'operator',
            'phone' => '+62 812 9999 8888',
            'company_name' => 'CargoTrace Hub Operations',
        ];

        $response = $this->post('/users', $payload);
        $response->assertRedirect('/users');

        $user = User::where('email', 'dispatcher.baru@cargotrace.app')->first();
        $this->assertNotNull($user);
        $this->assertEquals('operator', $user->role);
        $this->assertTrue(Hash::check('secret123', $user->password));

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->admin->id,
            'action' => 'user.created',
            'auditable_type' => User::class,
        ]);
    }
}
