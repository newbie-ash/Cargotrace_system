<?php

namespace Database\Seeders;

use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\ShipmentService;
use App\Services\TrackingService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with Phase 1 master data.
     */
    public function run(): void
    {
        // 1. Seed Core RBAC Users
        User::firstOrCreate(
            ['email' => 'admin@cargotrace.app'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'phone' => '+62 811 0000 0001',
                'company_name' => 'CargoTrace Global HQ',
            ]
        );

        User::firstOrCreate(
            ['email' => 'operator@cargotrace.app'],
            [
                'name' => 'Logistics Dispatcher',
                'password' => Hash::make('password'),
                'role' => 'operator',
                'phone' => '+62 811 0000 0002',
                'company_name' => 'Maritime Operations Division',
            ]
        );

        User::firstOrCreate(
            ['email' => 'customer@cargotrace.app'],
            [
                'name' => 'Alexander Wright',
                'password' => Hash::make('password'),
                'role' => 'customer',
                'phone' => '+62 811 0000 0003',
                'company_name' => 'Indo-Pacific Electronics Ltd.',
            ]
        );

        // 2. Seed Global Multimodal Carriers
        $carriers = [
            [
                'code' => 'MAERSK',
                'name' => 'A.P. Moller - Maersk Line',
                'transport_mode' => 'SEA',
                'contact_email' => 'support@maersk.com',
                'contact_phone' => '+45 33 63 33 63',
                'is_active' => true,
            ],
            [
                'code' => 'MSC',
                'name' => 'Mediterranean Shipping Company (MSC)',
                'transport_mode' => 'SEA',
                'contact_email' => 'info@msc.com',
                'contact_phone' => '+41 22 703 8888',
                'is_active' => true,
            ],
            [
                'code' => 'CMACGM',
                'name' => 'CMA CGM Global Logistics',
                'transport_mode' => 'SEA',
                'contact_email' => 'contact@cma-cgm.com',
                'contact_phone' => '+33 4 88 91 90 00',
                'is_active' => true,
            ],
            [
                'code' => 'DHL',
                'name' => 'DHL Global Forwarding Express',
                'transport_mode' => 'AIR',
                'contact_email' => 'aircargo@dhl.com',
                'contact_phone' => '+49 228 1820',
                'is_active' => true,
            ],
            [
                'code' => 'FEDEX',
                'name' => 'FedEx Trade Networks',
                'transport_mode' => 'MULTIMODAL',
                'contact_email' => 'freight@fedex.com',
                'contact_phone' => '+1 800 463 3339',
                'is_active' => true,
            ],
        ];

        foreach ($carriers as $carrier) {
            Carrier::firstOrCreate(['code' => $carrier['code']], $carrier);
        }

        // 3. Seed Global Intercontinental Hubs & Ports
        $locations = [
            [
                'code' => 'IDTPP',
                'name' => 'Port of Tanjung Priok',
                'city' => 'Jakarta',
                'country' => 'Indonesia',
                'type' => 'PORT',
                'latitude' => -6.1022000,
                'longitude' => 106.8833000,
            ],
            [
                'code' => 'SGSIN',
                'name' => 'Port of Singapore (PSA Hub)',
                'city' => 'Singapore',
                'country' => 'Singapore',
                'type' => 'PORT',
                'latitude' => 1.2644000,
                'longitude' => 103.8400000,
            ],
            [
                'code' => 'CNSHA',
                'name' => 'Port of Shanghai (Yangshan Deepwater)',
                'city' => 'Shanghai',
                'country' => 'China',
                'type' => 'PORT',
                'latitude' => 31.2304000,
                'longitude' => 121.4737000,
            ],
            [
                'code' => 'NLRTM',
                'name' => 'Port of Rotterdam',
                'city' => 'Rotterdam',
                'country' => 'Netherlands',
                'type' => 'PORT',
                'latitude' => 51.9244000,
                'longitude' => 4.4777000,
            ],
            [
                'code' => 'DEHAM',
                'name' => 'Port of Hamburg',
                'city' => 'Hamburg',
                'country' => 'Germany',
                'type' => 'PORT',
                'latitude' => 53.5511000,
                'longitude' => 9.9937000,
            ],
            [
                'code' => 'USLAX',
                'name' => 'Port of Los Angeles',
                'city' => 'Los Angeles',
                'country' => 'United States',
                'type' => 'PORT',
                'latitude' => 33.7432000,
                'longitude' => -118.2673000,
            ],
            [
                'code' => 'USJFK',
                'name' => 'John F. Kennedy International Airport',
                'city' => 'New York',
                'country' => 'United States',
                'type' => 'AIRPORT',
                'latitude' => 40.6413000,
                'longitude' => -73.7781000,
            ],
        ];

        foreach ($locations as $loc) {
            Location::firstOrCreate(['code' => $loc['code']], $loc);
        }

        // 4. Seed Dynamic System Settings for Group Scenario
        SystemSetting::set('delay_threshold_low', 12, 'integer', 'Batas jam keterlambatan tingkat LOW');
        SystemSetting::set('delay_threshold_medium', 48, 'integer', 'Batas jam keterlambatan tingkat MEDIUM');
        SystemSetting::set('primary_route_origin', 'IDTPP', 'string', 'Kode lokasi keberangkatan rute utama');
        SystemSetting::set('primary_route_destination', 'NLRTM', 'string', 'Kode lokasi tujuan rute utama');
        SystemSetting::set('kpi_target_on_time_rate', 90.0, 'float', 'Target KPI persentase on-time delivery (%)');

        // 5. Seed Demo Shipments with Lifecycle & Historical Tracking Engine (Phase 2)
        $customer = User::where('email', 'customer@cargotrace.app')->first();
        $admin = User::where('email', 'admin@cargotrace.app')->first();
        $maersk = Carrier::where('code', 'MAERSK')->first();
        $msc = Carrier::where('code', 'MSC')->first();
        $idtpp = Location::where('code', 'IDTPP')->first();
        $sgsin = Location::where('code', 'SGSIN')->first();
        $nlrtm = Location::where('code', 'NLRTM')->first();
        $uslax = Location::where('code', 'USLAX')->first();

        if ($customer && $maersk && $idtpp && $nlrtm) {
            $shipmentService = app(ShipmentService::class);
            $trackingService = app(TrackingService::class);

            if (! Shipment::where('reference_number', 'PO-DEMO-001')->exists()) {
                $shipment1 = $shipmentService->createShipment([
                    'customer_id' => $customer->id,
                    'carrier_id' => $maersk->id,
                    'origin_location_id' => $idtpp->id,
                    'destination_location_id' => $nlrtm->id,
                    'cargo_type' => 'Electronics & Precision Tech',
                    'reference_number' => 'PO-DEMO-001',
                    'expected_departure' => now()->subDays(5),
                    'expected_arrival' => now()->addDays(15),
                    'notes' => 'Primary Intercontinental Asia-Europe maritime lane. High value electronics manifest.',
                ], [
                    [
                        'sku' => 'EL-MCU-001',
                        'description' => 'Automotive Microcontroller Units',
                        'quantity' => 150,
                        'weight_kg' => 12.5,
                        'volume_cbm' => 0.08,
                        'declared_value' => 45000,
                    ],
                    [
                        'sku' => 'EL-PCB-002',
                        'description' => 'Industrial Gateway Motherboards',
                        'quantity' => 80,
                        'weight_kg' => 15.0,
                        'volume_cbm' => 0.12,
                        'declared_value' => 32000,
                    ],
                ], creatorId: $admin->id);

                $shipmentService->transitionStatus($shipment1, 'BOOKED');
                $shipmentService->transitionStatus($shipment1, 'PICKED_UP');

                // Singapore Hub Checkpoint
                $trackingService->appendEvent($shipment1, [
                    'location_id' => $sgsin->id,
                    'status' => 'IN_TRANSIT',
                    'event_type' => 'NORMAL',
                    'latitude' => $sgsin->latitude,
                    'longitude' => $sgsin->longitude,
                    'description' => 'Transshipment container transfer completed at PSA Singapore Hub. Vessel departed for Suez Canal.',
                    'occurred_at' => now()->subDays(2),
                ], creatorId: $admin->id);
            }

            if (! Shipment::where('reference_number', 'PO-DEMO-002')->exists()) {
                $shipment2 = $shipmentService->createShipment([
                    'customer_id' => $customer->id,
                    'carrier_id' => $msc->id,
                    'origin_location_id' => $sgsin->id,
                    'destination_location_id' => $uslax->id,
                    'cargo_type' => 'Container FCL',
                    'reference_number' => 'PO-DEMO-002',
                    'expected_departure' => now()->subDays(25),
                    'expected_arrival' => now()->subDays(2),
                    'notes' => 'Trans-Pacific trade route.',
                ], [
                    [
                        'sku' => 'FCL-TEX-99',
                        'description' => 'Commercial Textiles & Fabrics',
                        'quantity' => 400,
                        'weight_kg' => 35.0,
                        'volume_cbm' => 0.25,
                        'declared_value' => 85000,
                    ],
                ], creatorId: $admin->id);

                $shipmentService->transitionStatus($shipment2, 'BOOKED');
                $shipmentService->transitionStatus($shipment2, 'PICKED_UP');
                $shipmentService->transitionStatus($shipment2, 'IN_TRANSIT');

                $trackingService->appendEvent($shipment2, [
                    'location_id' => $uslax->id,
                    'status' => 'DELIVERED',
                    'event_type' => 'NORMAL',
                    'latitude' => $uslax->latitude,
                    'longitude' => $uslax->longitude,
                    'description' => 'Consignment successfully discharged and cleared US customs at Port of Los Angeles terminal.',
                    'occurred_at' => now()->subDays(1),
                ], creatorId: $admin->id);
            }
        }
    }
}
