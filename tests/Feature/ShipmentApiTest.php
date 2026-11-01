<?php

namespace Tests\Feature;

use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\User;
use App\Services\ShipmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShipmentApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $operator;
    protected User $customerA;
    protected User $customerB;
    protected Carrier $carrier;
    protected Location $origin;
    protected Location $destination;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'API Admin',
            'email' => 'admin.api@cargotrace.app',
            'password' => bcrypt('password123'),
            'role' => 'admin',
        ]);

        $this->operator = User::create([
            'name' => 'API Operator',
            'email' => 'operator.api@cargotrace.app',
            'password' => bcrypt('password123'),
            'role' => 'operator',
        ]);

        $this->customerA = User::create([
            'name' => 'Customer Alpha',
            'email' => 'alpha@cargotrace.app',
            'password' => bcrypt('password123'),
            'role' => 'customer',
            'company_name' => 'PT Alpha Nusantara',
        ]);

        $this->customerB = User::create([
            'name' => 'Customer Bravo',
            'email' => 'bravo@cargotrace.app',
            'password' => bcrypt('password123'),
            'role' => 'customer',
            'company_name' => 'PT Bravo Samudera',
        ]);

        $this->carrier = Carrier::create([
            'code' => 'MAERSK',
            'name' => 'Maersk Line',
            'transport_mode' => 'SEA',
            'is_active' => true,
        ]);

        $this->origin = Location::create([
            'code' => 'IDJKT',
            'name' => 'Tanjung Priok',
            'city' => 'Jakarta',
            'country' => 'Indonesia',
            'type' => 'PORT',
            'latitude' => -6.1028,
            'longitude' => 106.8833,
        ]);

        $this->destination = Location::create([
            'code' => 'SGSIN',
            'name' => 'Port of Singapore',
            'city' => 'Singapore',
            'country' => 'Singapore',
            'type' => 'PORT',
            'latitude' => 1.29027,
            'longitude' => 103.851959,
        ]);
    }

    public function test_user_can_issue_token_and_access_protected_endpoint(): void
    {
        $response = $this->postJson('/api/v1/auth/token', [
            'email' => 'admin.api@cargotrace.app',
            'password' => 'password123',
            'device_name' => 'PHPUnit-Runner',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'token',
                    'token_type',
                    'user' => ['id', 'name', 'email', 'role'],
                ],
            ]);

        $token = $response->json('data.token');
        $this->assertNotEmpty($token);

        // Access /api/v1/auth/me with Bearer token
        $meResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/auth/me');

        $meResponse->assertStatus(200)
            ->assertJsonPath('data.email', 'admin.api@cargotrace.app');

        // Revoke token
        $revokeResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/auth/revoke');

        $revokeResponse->assertStatus(200);

        // Clear resolved guard in test runner
        $this->app['auth']->forgetGuards();

        // Subsequent call with revoked token must fail with 401
        $afterRevoke = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/auth/me');

        $afterRevoke->assertStatus(401);
    }

    public function test_invalid_login_credentials_rejected(): void
    {
        $response = $this->postJson('/api/v1/auth/token', [
            'email' => 'admin.api@cargotrace.app',
            'password' => 'wrong-secret',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_carrier_and_location_master_data_endpoints(): void
    {
        $token = $this->operator->createToken('test')->plainTextToken;

        $carrierResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/carriers');

        $carrierResponse->assertStatus(200)
            ->assertJsonFragment(['code' => 'MAERSK']);

        $locationResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/locations');

        $locationResponse->assertStatus(200)
            ->assertJsonFragment(['code' => 'IDJKT'])
            ->assertJsonFragment(['code' => 'SGSIN']);
    }

    public function test_admin_can_create_shipment_via_api(): void
    {
        $token = $this->admin->createToken('test')->plainTextToken;

        $payload = [
            'customer_id' => $this->customerA->id,
            'carrier_id' => $this->carrier->id,
            'origin_location_id' => $this->origin->id,
            'destination_location_id' => $this->destination->id,
            'cargo_type' => 'Integrated Circuits',
            'expected_departure' => now()->addDays(2)->toDateTimeString(),
            'expected_arrival' => now()->addDays(7)->toDateTimeString(),
            'line_items' => [
                [
                    'item_description' => 'Silicon Microcontrollers Box',
                    'hs_code' => '8542.31',
                    'quantity' => 10,
                    'package_type' => 'BOX',
                    'gross_weight_kg' => 250.5,
                    'volume_cbm' => 1.8,
                    'declared_value_usd' => 45000.0,
                ],
            ],
        ];

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/shipments', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.cargo_type', 'Integrated Circuits')
            ->assertJsonPath('data.current_status', 'CREATED')
            ->assertJsonPath('data.origin_location.code', 'IDJKT')
            ->assertJsonPath('data.destination_location.code', 'SGSIN');

        $this->assertDatabaseHas('shipments', [
            'cargo_type' => 'Integrated Circuits',
            'customer_id' => $this->customerA->id,
        ]);

        $this->assertDatabaseHas('shipment_items', [
            'description' => 'Silicon Microcontrollers Box',
            'sku' => '8542.31',
        ]);
    }

    public function test_customer_cannot_create_shipment_via_api(): void
    {
        $token = $this->customerA->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/shipments', [
                'customer_id' => $this->customerA->id,
                'carrier_id' => $this->carrier->id,
                'origin_location_id' => $this->origin->id,
                'destination_location_id' => $this->destination->id,
                'cargo_type' => 'Unauthorized Cargo',
                'expected_departure' => now()->addDay()->toDateTimeString(),
                'expected_arrival' => now()->addDays(5)->toDateTimeString(),
            ]);

        $response->assertStatus(403);
    }

    public function test_customer_shipment_list_is_strictly_scoped(): void
    {
        $shipmentService = app(ShipmentService::class);

        // Shipment 1 for Customer A
        $shipmentA = $shipmentService->createShipment([
            'customer_id' => $this->customerA->id,
            'carrier_id' => $this->carrier->id,
            'origin_location_id' => $this->origin->id,
            'destination_location_id' => $this->destination->id,
            'cargo_type' => 'Alpha Cargo',
            'expected_departure' => now()->addDay(),
            'expected_arrival' => now()->addDays(4),
        ], creatorId: $this->admin->id);

        // Shipment 2 for Customer B
        $shipmentB = $shipmentService->createShipment([
            'customer_id' => $this->customerB->id,
            'carrier_id' => $this->carrier->id,
            'origin_location_id' => $this->origin->id,
            'destination_location_id' => $this->destination->id,
            'cargo_type' => 'Bravo Cargo',
            'expected_departure' => now()->addDay(),
            'expected_arrival' => now()->addDays(5),
        ], creatorId: $this->admin->id);

        // Customer A listing
        $tokenA = $this->customerA->createToken('test')->plainTextToken;
        $responseA = $this->withHeader('Authorization', "Bearer {$tokenA}")
            ->getJson('/api/v1/shipments');

        $responseA->assertStatus(200);
        $this->assertCount(1, $responseA->json('data'));
        $this->assertEquals($shipmentA->shipment_number, $responseA->json('data.0.shipment_number'));

        // Customer A cannot access Customer B shipment detail
        $forbiddenDetail = $this->withHeader('Authorization', "Bearer {$tokenA}")
            ->getJson("/api/v1/shipments/{$shipmentB->shipment_number}");
        $forbiddenDetail->assertStatus(403);

        // Clear guard cache before switching user
        $this->app['auth']->forgetGuards();

        // Admin listing gets all shipments
        $tokenAdmin = $this->admin->createToken('test')->plainTextToken;
        $responseAdmin = $this->withHeader('Authorization', "Bearer {$tokenAdmin}")
            ->getJson('/api/v1/shipments');

        $responseAdmin->assertStatus(200);
        $this->assertCount(2, $responseAdmin->json('data'));
    }

    public function test_operator_can_append_checkpoint_via_api(): void
    {
        $shipmentService = app(ShipmentService::class);
        $departure = now()->addHour();

        $shipment = $shipmentService->createShipment([
            'customer_id' => $this->customerA->id,
            'carrier_id' => $this->carrier->id,
            'origin_location_id' => $this->origin->id,
            'destination_location_id' => $this->destination->id,
            'cargo_type' => 'API Tracking Test Cargo',
            'expected_departure' => $departure,
            'expected_arrival' => now()->addDays(5),
        ], creatorId: $this->admin->id);

        // Advance to PICKED_UP so state machine allows transition to IN_TRANSIT
        $shipment->update(['current_status' => 'PICKED_UP']);

        $token = $this->operator->createToken('test')->plainTextToken;

        $eventTime = $departure->copy()->addHours(6);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/shipments/{$shipment->shipment_number}/events", [
                'status' => 'IN_TRANSIT',
                'location_id' => $this->origin->id,
                'latitude' => -5.85,
                'longitude' => 106.90,
                'occurred_at' => $eventTime->toDateTimeString(),
                'event_type' => 'NORMAL',
                'description' => 'Vessel departed harbour waters into Java Sea.',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.status', 'IN_TRANSIT')
            ->assertJsonPath('data.latitude', -5.85);

        $this->assertDatabaseHas('tracking_events', [
            'shipment_id' => $shipment->id,
            'status' => 'IN_TRANSIT',
            'latitude' => -5.85,
        ]);

        $this->assertDatabaseHas('shipments', [
            'id' => $shipment->id,
            'current_status' => 'IN_TRANSIT',
        ]);
    }

    public function test_chronological_violation_returns_422(): void
    {
        $shipmentService = app(ShipmentService::class);
        $departure = now()->addHour();

        $shipment = $shipmentService->createShipment([
            'customer_id' => $this->customerA->id,
            'carrier_id' => $this->carrier->id,
            'origin_location_id' => $this->origin->id,
            'destination_location_id' => $this->destination->id,
            'cargo_type' => 'Chronological Anomaly Cargo',
            'expected_departure' => $departure,
            'expected_arrival' => now()->addDays(5),
        ], creatorId: $this->admin->id);

        $token = $this->admin->createToken('test')->plainTextToken;

        // Try backdated timestamp earlier than initial checkpoint
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/shipments/{$shipment->shipment_number}/events", [
                'status' => 'IN_TRANSIT',
                'location_id' => $this->origin->id,
                'occurred_at' => $departure->copy()->subHours(10)->toDateTimeString(),
                'description' => 'Backdated checkpoint test',
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('status', 'error');
    }
}
