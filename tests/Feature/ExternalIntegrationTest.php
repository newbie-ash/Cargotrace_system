<?php

namespace Tests\Feature;

use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\User;
use App\Services\ExternalIntegrationService;
use App\Services\ShipmentService;
use App\Services\TrackingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ExternalIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $customer;
    protected ExternalIntegrationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();

        $this->admin = User::create([
            'name' => 'Admin API Tester',
            'email' => 'admin.api@cargotrace.app',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->customer = User::create([
            'name' => 'Customer API Tester',
            'email' => 'customer.api@cargotrace.app',
            'password' => bcrypt('password'),
            'role' => 'customer',
        ]);

        $this->service = app(ExternalIntegrationService::class);
    }

    public function test_nominatim_geocoding_resolves_coordinates_and_caches_result(): void
    {
        Http::fake([
            'nominatim.openstreetmap.org/search*' => Http::response([
                [
                    'lat' => '1.2644000',
                    'lon' => '103.8400000',
                    'display_name' => 'Port of Singapore, Singapore',
                    'type' => 'harbour',
                ],
            ], 200),
        ]);

        $result1 = $this->service->geocode('Port of Singapore');

        $this->assertNotNull($result1);
        $this->assertEquals(1.2644, $result1['latitude']);
        $this->assertEquals(103.84, $result1['longitude']);
        $this->assertEquals('Port of Singapore, Singapore', $result1['display_name']);

        // Second call should hit Cache without issuing another HTTP call
        $result2 = $this->service->geocode('Port of Singapore');
        $this->assertEquals($result1, $result2);

        Http::assertSentCount(1);
    }

    public function test_nominatim_reverse_geocoding_resolves_address(): void
    {
        Http::fake([
            'nominatim.openstreetmap.org/reverse*' => Http::response([
                'display_name' => 'Port of Rotterdam, Netherlands',
                'address' => [
                    'city' => 'Rotterdam',
                    'country' => 'Netherlands',
                ],
            ], 200),
        ]);

        $result = $this->service->reverseGeocode(51.9244, 4.4777);

        $this->assertNotNull($result);
        $this->assertEquals('Port of Rotterdam, Netherlands', $result['display_name']);
        $this->assertEquals('Rotterdam', $result['city']);
        $this->assertEquals('Netherlands', $result['country']);
    }

    public function test_open_meteo_weather_fetches_conditions_and_caches(): void
    {
        Http::fake([
            'api.open-meteo.com/v1/forecast*' => Http::response([
                'current_weather' => [
                    'temperature' => 28.5,
                    'windspeed' => 14.2,
                    'weathercode' => 2, // Partly Cloudy
                    'is_day' => 1,
                    'time' => '2026-11-01T12:00',
                ],
            ], 200),
        ]);

        $result1 = $this->service->getWeather(-6.1022, 106.8833);

        $this->assertNotNull($result1);
        $this->assertEquals(28.5, $result1['temperature']);
        $this->assertEquals(14.2, $result1['windspeed']);
        $this->assertEquals('Partly Cloudy', $result1['condition']);

        // Cache hit test
        $result2 = $this->service->getWeather(-6.1022, 106.8833);
        $this->assertEquals($result1, $result2);

        Http::assertSentCount(1);
    }

    public function test_tracking_service_automatically_attaches_weather_snapshot(): void
    {
        Http::fake([
            'api.open-meteo.com/v1/forecast*' => Http::response([
                'current_weather' => [
                    'temperature' => 15.0,
                    'windspeed' => 22.5,
                    'weathercode' => 61, // Rain
                    'is_day' => 1,
                    'time' => '2026-11-01T14:00',
                ],
            ], 200),
        ]);

        $carrier = Carrier::create(['code' => 'TEST_C', 'name' => 'Test Carrier', 'transport_mode' => 'SEA']);
        $loc1 = Location::create(['code' => 'T1', 'name' => 'Origin', 'city' => 'Jakarta', 'country' => 'ID', 'type' => 'PORT', 'latitude' => -6.1, 'longitude' => 106.8]);
        $loc2 = Location::create(['code' => 'T2', 'name' => 'Dest', 'city' => 'Rotterdam', 'country' => 'NL', 'type' => 'PORT', 'latitude' => 51.9, 'longitude' => 4.4]);

        $shipmentService = app(ShipmentService::class);
        $trackingService = app(TrackingService::class);

        $shipment = $shipmentService->createShipment([
            'customer_id' => $this->customer->id,
            'carrier_id' => $carrier->id,
            'origin_location_id' => $loc1->id,
            'destination_location_id' => $loc2->id,
            'cargo_type' => 'General',
            'expected_departure' => now(),
            'expected_arrival' => now()->addDays(10),
        ], creatorId: $this->admin->id);

        $shipmentService->transitionStatus($shipment, 'BOOKED');
        $shipmentService->transitionStatus($shipment, 'PICKED_UP');

        // Append tracking event with GPS coordinates and no explicit weather snapshot
        $event = $trackingService->appendEvent($shipment, [
            'status' => 'IN_TRANSIT',
            'latitude' => 1.29027,
            'longitude' => 103.85195,
            'description' => 'Container loaded at Singapore PSA hub.',
            'occurred_at' => now()->addHour(),
        ], creatorId: $this->admin->id);

        $this->assertNotNull($event->weather_snapshot);
        $this->assertEquals(15.0, (float) $event->weather_snapshot['temperature']);
        $this->assertEquals('Rain', $event->weather_snapshot['condition']);
    }

    public function test_external_api_failure_is_handled_gracefully_without_crashing(): void
    {
        Http::fake([
            'nominatim.openstreetmap.org/*' => Http::response(null, 500),
            'api.open-meteo.com/*' => Http::response(null, 503),
        ]);

        $geoResult = $this->service->geocode('Non Existent Port');
        $this->assertNull($geoResult);

        $weatherResult = $this->service->getWeather(99.0, 99.0);
        $this->assertNull($weatherResult);
    }

    public function test_location_lookup_coordinates_endpoint(): void
    {
        Http::fake([
            'nominatim.openstreetmap.org/search*' => Http::response([
                [
                    'lat' => '-6.1022',
                    'lon' => '106.8833',
                    'display_name' => 'Tanjung Priok, Jakarta, Indonesia',
                    'type' => 'harbour',
                ],
            ], 200),
        ]);

        // Admin & Operator can access lookup endpoint
        $this->actingAs($this->admin);
        $response = $this->getJson('/locations/lookup-coordinates?query=Tanjung+Priok');
        $response->assertStatus(200);
        $response->assertJsonPath('status', 'success');
        $response->assertJsonPath('data.latitude', -6.1022);
        $response->assertJsonPath('data.longitude', 106.8833);

        // Customer cannot access lookup endpoint (403)
        $this->actingAs($this->customer);
        $custResponse = $this->getJson('/locations/lookup-coordinates?query=Tanjung+Priok');
        $custResponse->assertStatus(403);
    }
}
