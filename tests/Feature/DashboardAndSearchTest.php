<?php

namespace Tests\Feature;

use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\User;
use App\Services\ShipmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardAndSearchTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $customer;
    protected Carrier $carrierA;
    protected Carrier $carrierB;
    protected Location $origin;
    protected Location $destination;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin Ops',
            'email' => 'admin.ops@cargotrace.app',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->customer = User::create([
            'name' => 'Customer Log',
            'email' => 'customer.log@cargotrace.app',
            'password' => bcrypt('password'),
            'role' => 'customer',
            'company_name' => 'Logistics Corp',
        ]);

        $this->carrierA = Carrier::create([
            'code' => 'MSC',
            'name' => 'Mediterranean Shipping Company',
            'transport_mode' => 'SEA',
            'is_active' => true,
        ]);

        $this->carrierB = Carrier::create([
            'code' => 'HAPAG',
            'name' => 'Hapag-Lloyd',
            'transport_mode' => 'SEA',
            'is_active' => true,
        ]);

        $this->origin = Location::create([
            'code' => 'IDSRG',
            'name' => 'Tanjung Emas',
            'city' => 'Semarang',
            'country' => 'Indonesia',
            'type' => 'PORT',
            'latitude' => -6.95,
            'longitude' => 110.42,
        ]);

        $this->destination = Location::create([
            'code' => 'NLRTM',
            'name' => 'Port of Rotterdam',
            'city' => 'Rotterdam',
            'country' => 'Netherlands',
            'type' => 'PORT',
            'latitude' => 51.95,
            'longitude' => 4.13,
        ]);
    }

    public function test_dashboard_renders_dynamic_kpis_and_chart_data(): void
    {
        $this->actingAs($this->admin);
        $shipmentService = app(ShipmentService::class);

        // Shipment 1: In Transit, delayed
        $s1 = $shipmentService->createShipment([
            'customer_id' => $this->customer->id,
            'carrier_id' => $this->carrierA->id,
            'origin_location_id' => $this->origin->id,
            'destination_location_id' => $this->destination->id,
            'cargo_type' => 'Garments',
            'expected_departure' => now()->addDay(),
            'expected_arrival' => now()->addDays(10),
        ], creatorId: $this->admin->id);

        $s1->update([
            'current_status' => 'IN_TRANSIT',
            'is_delayed' => true,
            'delay_duration_hours' => 24,
        ]);

        // Shipment 2: Delivered, on time
        $s2 = $shipmentService->createShipment([
            'customer_id' => $this->customer->id,
            'carrier_id' => $this->carrierB->id,
            'origin_location_id' => $this->origin->id,
            'destination_location_id' => $this->destination->id,
            'cargo_type' => 'Furniture',
            'expected_departure' => now()->subDays(5),
            'expected_arrival' => now()->subDay(),
        ], creatorId: $this->admin->id);

        $s2->update([
            'current_status' => 'DELIVERED',
            'is_delayed' => false,
        ]);

        $this->get('/dashboard')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Dashboard/Index')
                ->where('stats.total_shipments', 2)
                ->where('stats.in_transit', 1)
                ->where('stats.delivered', 1)
                ->where('stats.delayed', 1)
                ->where('stats.delay_rate', 50)
                ->has('stats.status_distribution')
                ->has('stats.carrier_distribution')
                ->has('stats.recent_shipments', 2)
            );
    }

    public function test_shipments_multi_parameter_filters_and_sorting(): void
    {
        $this->actingAs($this->admin);
        $shipmentService = app(ShipmentService::class);

        $s1 = $shipmentService->createShipment([
            'customer_id' => $this->customer->id,
            'carrier_id' => $this->carrierA->id,
            'origin_location_id' => $this->origin->id,
            'destination_location_id' => $this->destination->id,
            'cargo_type' => 'Apparel Export',
            'expected_departure' => now()->addDays(2),
            'expected_arrival' => now()->addDays(8),
        ], creatorId: $this->admin->id);

        $s1->update(['current_status' => 'IN_TRANSIT', 'is_delayed' => true]);

        $s2 = $shipmentService->createShipment([
            'customer_id' => $this->customer->id,
            'carrier_id' => $this->carrierB->id,
            'origin_location_id' => $this->origin->id,
            'destination_location_id' => $this->destination->id,
            'cargo_type' => 'Automotive Parts',
            'expected_departure' => now()->addDays(5),
            'expected_arrival' => now()->addDays(12),
        ], creatorId: $this->admin->id);

        // Filter 1: By Status IN_TRANSIT
        $this->get('/shipments?status=IN_TRANSIT')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Shipments/Index')
                ->has('shipments.data', 1)
                ->where('shipments.data.0.shipment_number', $s1->shipment_number)
            );

        // Filter 2: By Carrier B
        $this->get("/shipments?carrier_id={$this->carrierB->id}")
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Shipments/Index')
                ->has('shipments.data', 1)
                ->where('shipments.data.0.shipment_number', $s2->shipment_number)
            );

        // Filter 3: By Delay Status
        $this->get('/shipments?delay=delayed')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Shipments/Index')
                ->has('shipments.data', 1)
                ->where('shipments.data.0.is_delayed', true)
            );

        // Filter 4: By Search keyword
        $this->get('/shipments?search=Automotive')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Shipments/Index')
                ->has('shipments.data', 1)
                ->where('shipments.data.0.cargo_type', 'Automotive Parts')
            );

        // Filter 5: Sorting asc
        $this->get('/shipments?sort_by=expected_departure&sort_direction=asc')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Shipments/Index')
                ->has('shipments.data', 2)
                ->where('shipments.data.0.id', $s1->id)
            );
    }

    public function test_customer_dashboard_renders_isolated_shipments_with_realtime_tracking_props(): void
    {
        $shipmentService = app(ShipmentService::class);

        // Other customer
        $otherCustomer = User::create([
            'name' => 'Other Client',
            'email' => 'other.client@cargotrace.app',
            'password' => bcrypt('password'),
            'role' => 'customer',
            'company_name' => 'Foreign Cargo Inc',
        ]);

        // Shipment belonging to this->customer
        $myShipment = $shipmentService->createShipment([
            'customer_id' => $this->customer->id,
            'carrier_id' => $this->carrierA->id,
            'origin_location_id' => $this->origin->id,
            'destination_location_id' => $this->destination->id,
            'cargo_type' => 'Textile Rolls',
            'expected_departure' => now()->addDay(),
            'expected_arrival' => now()->addDays(7),
        ], creatorId: $this->admin->id);

        // Shipment belonging to other customer
        $otherShipment = $shipmentService->createShipment([
            'customer_id' => $otherCustomer->id,
            'carrier_id' => $this->carrierB->id,
            'origin_location_id' => $this->origin->id,
            'destination_location_id' => $this->destination->id,
            'cargo_type' => 'Steel Pipes',
            'expected_departure' => now()->addDays(2),
            'expected_arrival' => now()->addDays(14),
        ], creatorId: $this->admin->id);

        $this->actingAs($this->customer);

        $this->get('/dashboard')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Dashboard/Index')
                ->where('stats.total_shipments', 1)
                ->has('stats.recent_shipments', 1)
                ->where('stats.recent_shipments.0.id', $myShipment->id)
                ->where('stats.recent_shipments.0.shipment_number', $myShipment->shipment_number)
                ->has('stats.recent_shipments.0.carrier')
                ->has('stats.recent_shipments.0.origin_location')
                ->has('stats.recent_shipments.0.destination_location')
                ->has('stats.recent_shipments.0.tracking_events')
                ->has('stats.synced_at')
            );
    }
}
