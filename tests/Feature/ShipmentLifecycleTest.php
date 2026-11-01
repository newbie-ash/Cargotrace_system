<?php

namespace Tests\Feature;

use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\TrackingEvent;
use App\Models\User;
use App\Services\ShipmentService;
use App\Services\TrackingService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ShipmentLifecycleTest extends TestCase
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
            'name' => 'Admin Logistics',
            'email' => 'admin.shipment@cargotrace.app',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->operator = User::create([
            'name' => 'Operator Logistics',
            'email' => 'operator.shipment@cargotrace.app',
            'password' => bcrypt('password'),
            'role' => 'operator',
        ]);

        $this->customerA = User::create([
            'name' => 'PT Global Exporter',
            'email' => 'customera@cargotrace.app',
            'password' => bcrypt('password'),
            'role' => 'customer',
            'company_name' => 'PT Global Exporter Indonesia',
        ]);

        $this->customerB = User::create([
            'name' => 'Siemens AG Cargo',
            'email' => 'customerb@cargotrace.app',
            'password' => bcrypt('password'),
            'role' => 'customer',
            'company_name' => 'Siemens Logistics Germany',
        ]);

        $this->carrier = Carrier::create([
            'code' => 'MSK',
            'name' => 'Maersk Line',
            'transport_mode' => 'SEA',
            'is_active' => true,
        ]);

        $this->origin = Location::create([
            'code' => 'IDTPP',
            'name' => 'Tanjung Priok Port',
            'city' => 'Jakarta',
            'country' => 'Indonesia',
            'type' => 'PORT',
            'latitude' => -6.1018,
            'longitude' => 106.8833,
            'is_active' => true,
        ]);

        $this->destination = Location::create([
            'code' => 'NLRTM',
            'name' => 'Port of Rotterdam',
            'city' => 'Rotterdam',
            'country' => 'Netherlands',
            'type' => 'PORT',
            'latitude' => 51.9244,
            'longitude' => 4.4777,
            'is_active' => true,
        ]);
    }

    public function test_admin_and_operator_can_create_shipment_with_items_and_initial_checkpoint_is_generated(): void
    {
        $this->actingAs($this->operator);

        $payload = [
            'customer_id' => $this->customerA->id,
            'carrier_id' => $this->carrier->id,
            'origin_location_id' => $this->origin->id,
            'destination_location_id' => $this->destination->id,
            'cargo_type' => 'Electronics & Tech',
            'expected_departure' => now()->format('Y-m-d H:i:s'),
            'expected_arrival' => now()->addDays(20)->format('Y-m-d H:i:s'),
            'reference_number' => 'PO-GLOBAL-9901',
            'notes' => 'Handle temperature sensitive items with care.',
            'items' => [
                [
                    'sku' => 'SKU-MICRO-01',
                    'description' => 'Industrial Controllers',
                    'quantity' => 10,
                    'weight_kg' => 50.0, // 50 * 10 = 500 kg
                    'volume_cbm' => 0.2, // 0.2 * 10 = 2 CBM
                    'declared_value' => 5000,
                ],
                [
                    'sku' => 'SKU-SENS-02',
                    'description' => 'IoT Sensor Nodes',
                    'quantity' => 20,
                    'weight_kg' => 10.0, // 10 * 20 = 200 kg
                    'volume_cbm' => 0.1, // 0.1 * 20 = 2 CBM
                    'declared_value' => 2000,
                ],
            ],
        ];

        $response = $this->post('/shipments', $payload);

        $shipment = Shipment::where('reference_number', 'PO-GLOBAL-9901')->first();
        $this->assertNotNull($shipment);
        $response->assertRedirect("/shipments/{$shipment->id}");

        // Validate auto-calculated total weight and volume
        $this->assertEquals('CREATED', $shipment->current_status);
        $this->assertEquals(700.00, (float) $shipment->total_weight_kg);
        $this->assertEquals(4.00, (float) $shipment->total_volume_cbm);

        // Validate line items
        $this->assertCount(2, $shipment->items);

        // Validate initial tracking event at origin
        $this->assertCount(1, $shipment->trackingEvents);
        $initialEvent = $shipment->trackingEvents->first();
        $this->assertEquals('CREATED', $initialEvent->status);
        $this->assertEquals($this->origin->id, $initialEvent->location_id);
        $this->assertEquals(-6.1018, (float) $initialEvent->latitude);
        $this->assertEquals(106.8833, (float) $initialEvent->longitude);

        // Audit log exists
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->operator->id,
            'action' => 'shipment.created',
            'auditable_id' => $shipment->id,
        ]);
    }

    public function test_customer_access_isolation_customer_cannot_view_another_customer_shipment(): void
    {
        $shipmentService = app(ShipmentService::class);
        $shipmentA = $shipmentService->createShipment([
            'customer_id' => $this->customerA->id,
            'carrier_id' => $this->carrier->id,
            'origin_location_id' => $this->origin->id,
            'destination_location_id' => $this->destination->id,
            'cargo_type' => 'General Cargo',
            'expected_departure' => now(),
            'expected_arrival' => now()->addDays(15),
        ], creatorId: $this->admin->id);

        // Customer A can view their own shipment
        $this->actingAs($this->customerA);
        $responseA = $this->get("/shipments/{$shipmentA->id}");
        $responseA->assertStatus(200);

        // Customer B cannot view Customer A's shipment (403 Forbidden)
        $this->actingAs($this->customerB);
        $responseB = $this->get("/shipments/{$shipmentA->id}");
        $responseB->assertStatus(403);

        // Customer B listing only shows their shipments
        $indexResponse = $this->get('/shipments');
        $indexResponse->assertStatus(200);
        $indexResponse->assertDontSee($shipmentA->shipment_number);
    }

    public function test_valid_state_machine_transitions_succeed(): void
    {
        $shipmentService = app(ShipmentService::class);
        $shipment = $shipmentService->createShipment([
            'customer_id' => $this->customerA->id,
            'carrier_id' => $this->carrier->id,
            'origin_location_id' => $this->origin->id,
            'destination_location_id' => $this->destination->id,
            'cargo_type' => 'Dry Bulk',
            'expected_departure' => now(),
            'expected_arrival' => now()->addDays(10),
        ], creatorId: $this->admin->id);

        $this->assertEquals('CREATED', $shipment->current_status);

        // CREATED -> BOOKED
        $shipment = $shipmentService->transitionStatus($shipment, 'BOOKED');
        $this->assertEquals('BOOKED', $shipment->current_status);

        // BOOKED -> PICKED_UP
        $shipment = $shipmentService->transitionStatus($shipment, 'PICKED_UP');
        $this->assertEquals('PICKED_UP', $shipment->current_status);

        // PICKED_UP -> IN_TRANSIT
        $shipment = $shipmentService->transitionStatus($shipment, 'IN_TRANSIT');
        $this->assertEquals('IN_TRANSIT', $shipment->current_status);

        // IN_TRANSIT -> DELIVERED
        $shipment = $shipmentService->transitionStatus($shipment, 'DELIVERED');
        $this->assertEquals('DELIVERED', $shipment->current_status);
        $this->assertNotNull($shipment->actual_arrival);
    }

    public function test_invalid_state_machine_transition_throws_domain_exception(): void
    {
        $this->expectException(DomainException::class);

        $shipmentService = app(ShipmentService::class);
        $shipment = $shipmentService->createShipment([
            'customer_id' => $this->customerA->id,
            'carrier_id' => $this->carrier->id,
            'origin_location_id' => $this->origin->id,
            'destination_location_id' => $this->destination->id,
            'cargo_type' => 'Container FCL',
            'expected_departure' => now(),
            'expected_arrival' => now()->addDays(10),
        ], creatorId: $this->admin->id);

        // Invalid direct jump: CREATED -> DELIVERED is not allowed
        $shipmentService->transitionStatus($shipment, 'DELIVERED');
    }

    public function test_append_only_chronological_tracking_engine(): void
    {
        $shipmentService = app(ShipmentService::class);
        $trackingService = app(TrackingService::class);

        $now = now();
        $shipment = $shipmentService->createShipment([
            'customer_id' => $this->customerA->id,
            'carrier_id' => $this->carrier->id,
            'origin_location_id' => $this->origin->id,
            'destination_location_id' => $this->destination->id,
            'cargo_type' => 'Container FCL',
            'expected_departure' => $now,
            'expected_arrival' => $now->copy()->addDays(10),
        ], creatorId: $this->admin->id);

        // Move to BOOKED -> PICKED_UP
        $shipmentService->transitionStatus($shipment, 'BOOKED');
        $shipmentService->transitionStatus($shipment, 'PICKED_UP');

        // Append forward event: Singapore transshipment
        $eventTime = $now->copy()->addDays(2);
        $event = $trackingService->appendEvent($shipment, [
            'status' => 'IN_TRANSIT',
            'event_type' => 'NORMAL',
            'latitude' => 1.290270,
            'longitude' => 103.851959,
            'description' => 'Vessel departed Singapore port heading Malacca Strait.',
            'occurred_at' => $eventTime,
        ], creatorId: $this->admin->id);

        $this->assertNotNull($event);
        $shipment->refresh();
        $this->assertEquals('IN_TRANSIT', $shipment->current_status);
        $this->assertEquals(1.290270, (float) $shipment->current_lat);
        $this->assertEquals(103.851959, (float) $shipment->current_lng);

        // Attempting to append backdated event (earlier than eventTime) must throw DomainException
        $this->expectException(DomainException::class);
        $trackingService->appendEvent($shipment, [
            'status' => 'IN_TRANSIT',
            'description' => 'Backdated checkpoint anomaly',
            'occurred_at' => $eventTime->copy()->subHours(5),
        ], creatorId: $this->admin->id);
    }

    public function test_customer_cannot_create_or_mutate_shipments(): void
    {
        $this->actingAs($this->customerA);

        // Customer cannot access create form
        $createResponse = $this->get('/shipments/create');
        $createResponse->assertStatus(403);

        // Customer cannot submit shipment
        $postResponse = $this->post('/shipments', [
            'customer_id' => $this->customerA->id,
            'carrier_id' => $this->carrier->id,
            'origin_location_id' => $this->origin->id,
            'destination_location_id' => $this->destination->id,
            'cargo_type' => 'General',
            'expected_departure' => now(),
            'expected_arrival' => now()->addDays(5),
        ]);
        $postResponse->assertStatus(403);
    }

    public function test_shipment_show_renders_inertia_component_with_relations(): void
    {
        $this->actingAs($this->admin);

        $shipmentService = app(ShipmentService::class);
        $shipment = $shipmentService->createShipment([
            'customer_id' => $this->customerA->id,
            'carrier_id' => $this->carrier->id,
            'origin_location_id' => $this->origin->id,
            'destination_location_id' => $this->destination->id,
            'cargo_type' => 'Inertia Test Cargo',
            'expected_departure' => now()->addDay(),
            'expected_arrival' => now()->addDays(6),
        ], creatorId: $this->admin->id);

        $this->get("/shipments/{$shipment->id}")
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Shipments/Show')
                ->has('shipment.id')
                ->has('shipment.tracking_events')
                ->has('locations')
            );
    }
}
