<?php

namespace Tests\Feature;

use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\ShippingTariff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerPortalEnhancementTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;
    protected User $otherCustomer;
    protected Carrier $carrier;
    protected Location $origin;
    protected Location $destination;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::factory()->create(['role' => 'customer', 'name' => 'Alexander Wright']);
        $this->otherCustomer = User::factory()->create(['role' => 'customer', 'name' => 'Other Client']);

        $this->carrier = Carrier::create([
            'code' => 'EVG',
            'name' => 'Evergreen Marine',
            'transport_mode' => 'SEA',
            'is_active' => true,
        ]);

        $this->origin = Location::create([
            'code' => 'SGSIN',
            'name' => 'Port of Singapore',
            'city' => 'Singapore',
            'country' => 'Singapore',
            'type' => 'PORT',
            'latitude' => 1.290270,
            'longitude' => 103.851959,
        ]);

        $this->destination = Location::create([
            'code' => 'IDTPP',
            'name' => 'Tanjung Priok Port',
            'city' => 'Jakarta',
            'country' => 'Indonesia',
            'type' => 'PORT',
            'latitude' => -6.101897,
            'longitude' => 106.883392,
        ]);

        ShippingTariff::create([
            'service_name' => 'Standard Ocean Freight',
            'transport_mode' => 'SEA',
            'base_fee_usd' => 250.00,
            'rate_per_kg_usd' => 0.15,
            'rate_per_km_usd' => 0.08,
            'min_weight_kg' => 500.00,
            'avg_speed_kmh' => 35.0,
            'handling_hours' => 24,
            'is_active' => true,
        ]);
    }

    public function test_guest_is_redirected_from_documents_and_calculator(): void
    {
        $this->get('/documents')->assertRedirect('/login');
        $this->get('/calculator')->assertRedirect('/login');
    }

    public function test_customer_can_view_documents_with_isolation(): void
    {
        // Own shipment
        $own = Shipment::create([
            'shipment_number' => 'CT-DOC-001',
            'customer_id' => $this->customer->id,
            'carrier_id' => $this->carrier->id,
            'origin_location_id' => $this->origin->id,
            'destination_location_id' => $this->destination->id,
            'created_by' => $this->customer->id,
            'cargo_type' => 'ELECTRONICS',
            'expected_departure' => now(),
            'expected_arrival' => now()->addDays(5),
            'estimated_arrival' => now()->addDays(5),
            'current_status' => 'IN_TRANSIT',
        ]);

        // Other customer's shipment
        $other = Shipment::create([
            'shipment_number' => 'CT-DOC-OTHER',
            'customer_id' => $this->otherCustomer->id,
            'carrier_id' => $this->carrier->id,
            'origin_location_id' => $this->origin->id,
            'destination_location_id' => $this->destination->id,
            'created_by' => $this->otherCustomer->id,
            'cargo_type' => 'TEXTILE',
            'expected_departure' => now(),
            'expected_arrival' => now()->addDays(5),
            'estimated_arrival' => now()->addDays(5),
            'current_status' => 'IN_TRANSIT',
        ]);

        $response = $this->actingAs($this->customer)->get('/documents');
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Documents/Index')
            ->has('documents.data', 1)
            ->where('documents.data.0.shipment_number', 'CT-DOC-001')
        );
    }

    public function test_customer_can_access_calculator_and_estimate_tariff(): void
    {
        $this->actingAs($this->customer)
            ->get('/calculator')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Calculator/Index')
                ->has('locations')
                ->has('tariffs')
            );

        $response = $this->actingAs($this->customer)->postJson('/calculator/estimate', [
            'origin_id' => $this->origin->id,
            'destination_id' => $this->destination->id,
            'transport_mode' => 'SEA',
            'weight_kg' => 2000,
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'data' => [
                'transport_mode' => 'SEA',
                'service_name' => 'Standard Ocean Freight',
            ],
        ]);
    }
}
