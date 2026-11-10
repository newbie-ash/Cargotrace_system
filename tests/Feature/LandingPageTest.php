<?php

namespace Tests\Feature;

use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\ShippingTariff;
use App\Models\TrackingEvent;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;
    protected User $operator;
    protected Carrier $carrierSea;
    protected Carrier $carrierAir;
    protected Location $origin;
    protected Location $destination;
    protected ShippingTariff $tariffSea;
    protected ShippingTariff $tariffAir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::create([
            'name' => 'John Doe Private',
            'email' => 'john.doe.secret@example.com',
            'password' => bcrypt('password123'),
            'role' => 'customer',
            'phone' => '+6281199998888',
        ]);

        $this->operator = User::create([
            'name' => 'Port Operator',
            'email' => 'operator.hub@example.com',
            'password' => bcrypt('password123'),
            'role' => 'operator',
        ]);

        $this->carrierSea = Carrier::create([
            'code' => 'SEALINE',
            'name' => 'Global Container Line',
            'transport_mode' => 'SEA',
            'is_active' => true,
        ]);

        $this->carrierAir = Carrier::create([
            'code' => 'AIRCARGO',
            'name' => 'Speedy Express Air',
            'transport_mode' => 'AIR',
            'is_active' => true,
        ]);

        $this->origin = Location::create([
            'code' => 'IDJKT',
            'name' => 'Port of Tanjung Priok',
            'city' => 'Jakarta',
            'country' => 'Indonesia',
            'type' => 'PORT',
            'latitude' => -6.1028000,
            'longitude' => 106.8833000,
        ]);

        $this->destination = Location::create([
            'code' => 'SGSIN',
            'name' => 'Port of Singapore',
            'city' => 'Singapore',
            'country' => 'Singapore',
            'type' => 'PORT',
            'latitude' => 1.2902700,
            'longitude' => 103.8519590,
        ]);

        $this->tariffSea = ShippingTariff::create([
            'transport_mode' => 'SEA',
            'service_name' => 'Ocean Freight Standard',
            'base_fee_usd' => 120.00,
            'rate_per_kg_usd' => 0.5000,
            'rate_per_km_usd' => 0.0400,
            'min_weight_kg' => 50,
            'avg_speed_kmh' => 35,
            'handling_hours' => 24,
            'is_active' => true,
        ]);

        $this->tariffAir = ShippingTariff::create([
            'transport_mode' => 'AIR',
            'service_name' => 'Air Freight Express',
            'base_fee_usd' => 90.00,
            'rate_per_kg_usd' => 3.5000,
            'rate_per_km_usd' => 0.1500,
            'min_weight_kg' => 10,
            'avg_speed_kmh' => 700,
            'handling_hours' => 8,
            'is_active' => true,
        ]);
    }

    public function test_landing_page_renders_successfully_with_inertia_props(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Welcome')
                ->has('stats')
                ->has('stats.total_shipments')
                ->has('stats.active_carriers')
                ->has('stats.total_locations')
                ->has('stats.on_time_rate')
                ->has('carriers')
                ->has('locations')
                ->has('tariffs')
                ->has('sampleShipments')
            );
    }

    public function test_public_tracking_returns_sanitized_shipment_data_and_omits_pii(): void
    {
        $shipment = Shipment::create([
            'shipment_number' => 'CT-PUBLIC-001',
            'reference_number' => 'REF-SECRET-999',
            'customer_id' => $this->customer->id,
            'carrier_id' => $this->carrierSea->id,
            'origin_location_id' => $this->origin->id,
            'destination_location_id' => $this->destination->id,
            'created_by' => $this->operator->id,
            'cargo_type' => 'Sensitive Gold & Semiconductors',
            'total_weight_kg' => 1500.00,
            'total_volume_cbm' => 4.50,
            'expected_departure' => Carbon::now()->subDays(4),
            'expected_arrival' => Carbon::now()->addDays(5),
            'estimated_arrival' => Carbon::now()->addDays(5),
            'current_status' => 'IN_TRANSIT',
            'notes' => 'INTERNAL STRICT CONFIDENTIAL NOTE: Vault Code 8831',
        ]);

        // Create 4 events to verify public endpoint caps recent_events at 3 latest checkpoints
        for ($i = 4; $i >= 1; $i--) {
            TrackingEvent::create([
                'shipment_id' => $shipment->id,
                'location_id' => $this->origin->id,
                'created_by' => $this->operator->id,
                'status' => 'IN_TRANSIT',
                'event_type' => 'NORMAL',
                'description' => "Checkpoint event #{$i}",
                'occurred_at' => Carbon::now()->subHours($i * 6),
                'weather_snapshot' => ['temperature' => 30.5, 'condition' => 'Clear'],
            ]);
        }

        $response = $this->getJson('/track-public?tracking_number=CT-PUBLIC-001');

        $response->assertOk()
            ->assertJsonPath('found', true)
            ->assertJsonPath('shipment.shipment_number', 'CT-PUBLIC-001')
            ->assertJsonPath('shipment.reference_number', 'REF-SECRET-999')
            ->assertJsonPath('shipment.current_status', 'IN_TRANSIT')
            ->assertJsonPath('shipment.carrier.name', 'Global Container Line')
            ->assertJsonPath('shipment.origin.code', 'IDJKT')
            ->assertJsonPath('shipment.destination.code', 'SGSIN')
            ->assertJsonCount(3, 'shipment.recent_events')
            ->assertJsonPath('shipment.recent_events.0.description', 'Checkpoint event #1');

        // Verify Strict Privacy Sanitization: sensitive customer PII & internal notes MUST be absent
        $responseData = $response->json('shipment');
        $this->assertArrayNotHasKey('customer', $responseData);
        $this->assertArrayNotHasKey('customer_id', $responseData);
        $this->assertArrayNotHasKey('notes', $responseData);
        $this->assertArrayNotHasKey('declared_value', $responseData);
        $this->assertArrayNotHasKey('items', $responseData);
        $this->assertStringNotContainsString('john.doe.secret@example.com', $response->getContent());
        $this->assertStringNotContainsString('Vault Code 8831', $response->getContent());
    }

    public function test_public_tracking_supports_reference_number_lookup(): void
    {
        Shipment::create([
            'shipment_number' => 'CT-PUBLIC-002',
            'reference_number' => 'PO-LOOKUP-XYZ',
            'customer_id' => $this->customer->id,
            'carrier_id' => $this->carrierAir->id,
            'origin_location_id' => $this->origin->id,
            'destination_location_id' => $this->destination->id,
            'created_by' => $this->operator->id,
            'cargo_type' => 'Air Freight Packages',
            'expected_departure' => Carbon::now()->addDay(),
            'expected_arrival' => Carbon::now()->addDays(3),
            'estimated_arrival' => Carbon::now()->addDays(3),
            'current_status' => 'BOOKED',
        ]);

        $response = $this->getJson('/track-public?tracking_number=PO-LOOKUP-XYZ');

        $response->assertOk()
            ->assertJsonPath('found', true)
            ->assertJsonPath('shipment.shipment_number', 'CT-PUBLIC-002');
    }

    public function test_public_tracking_returns_404_for_unknown_tracking_number(): void
    {
        $response = $this->getJson('/track-public?tracking_number=CT-UNKNOWN-999999');

        $response->assertStatus(404)
            ->assertJsonPath('found', false);
    }

    public function test_public_tracking_validates_required_input(): void
    {
        $response = $this->getJson('/track-public');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['tracking_number']);
    }

    public function test_tariff_estimation_calculates_costs_and_transit_days(): void
    {
        $response = $this->getJson('/estimate-tariff?' . http_build_query([
            'origin_id' => $this->origin->id,
            'destination_id' => $this->destination->id,
            'transport_mode' => 'SEA',
            'weight_kg' => 200,
        ]));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.service_name', 'Ocean Freight Standard')
            ->assertJsonPath('data.transport_mode', 'SEA')
            ->assertJsonPath('data.min_weight_kg', 50);

        $data = $response->json('data');
        $this->assertGreaterThan(0, $data['distance_km']);
        $this->assertGreaterThan(0, $data['total_cost_usd']);
        $this->assertGreaterThan(0, $data['total_cost_idr']);
        $this->assertGreaterThanOrEqual(1, $data['estimated_transit_days']);
        $this->assertArrayHasKey('base_fee_usd', $data['breakdown']);
        $this->assertArrayHasKey('weight_cost_usd', $data['breakdown']);
        $this->assertArrayHasKey('distance_cost_usd', $data['breakdown']);
    }

    public function test_tariff_estimation_enforces_minimum_weight(): void
    {
        // SEA min weight is 50kg, test with 10kg
        $response = $this->getJson('/estimate-tariff?' . http_build_query([
            'origin_id' => $this->origin->id,
            'destination_id' => $this->destination->id,
            'transport_mode' => 'SEA',
            'weight_kg' => 10,
        ]));

        $response->assertOk();
        $this->assertEquals(10, $response->json('data.input_weight_kg'));
        $this->assertEquals(50, $response->json('data.effective_weight_kg'));
    }

    public function test_tariff_estimation_rejects_identical_origin_and_destination(): void
    {
        $response = $this->getJson('/estimate-tariff?' . http_build_query([
            'origin_id' => $this->origin->id,
            'destination_id' => $this->origin->id,
            'transport_mode' => 'SEA',
            'weight_kg' => 100,
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['destination_id']);
    }

    public function test_tariff_estimation_rejects_invalid_transport_mode(): void
    {
        $response = $this->getJson('/estimate-tariff?' . http_build_query([
            'origin_id' => $this->origin->id,
            'destination_id' => $this->destination->id,
            'transport_mode' => 'ROCKET',
            'weight_kg' => 100,
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['transport_mode']);
    }
}
