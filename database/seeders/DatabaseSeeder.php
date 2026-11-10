<?php

namespace Database\Seeders;

use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Models\ShippingTariff;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\ShipmentService;
use App\Services\TrackingService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed realistic multimodal demo data and unique group scenarios.
     */
    public function run(): void
    {
        // 1. Seed Core RBAC Users (Demo accounts for Code Ownership Interview)
        $admin = User::firstOrCreate(
            ['email' => 'admin@cargotrace.app'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'phone' => '+62 811 0000 0001',
                'company_name' => 'CargoTrace Global HQ',
            ]
        );

        $operator = User::firstOrCreate(
            ['email' => 'operator@cargotrace.app'],
            [
                'name' => 'Logistics Dispatcher',
                'password' => Hash::make('password'),
                'role' => 'operator',
                'phone' => '+62 811 0000 0002',
                'company_name' => 'Maritime Operations Division',
            ]
        );

        $customer1 = User::firstOrCreate(
            ['email' => 'customer@cargotrace.app'],
            [
                'name' => 'Alexander Wright',
                'password' => Hash::make('password'),
                'role' => 'customer',
                'phone' => '+62 811 0000 0003',
                'company_name' => 'Indo-Pacific Electronics Ltd.',
            ]
        );

        $customer2 = User::firstOrCreate(
            ['email' => 'customer2@cargotrace.app'],
            [
                'name' => 'Siti Rahmawati',
                'password' => Hash::make('password'),
                'role' => 'customer',
                'phone' => '+62 811 0000 0004',
                'company_name' => 'PT Nusantara Tekstil Mandiri',
            ]
        );

        // 2. Seed Multimodal Carriers
        $carriers = [
            [
                'code' => 'MAERSK',
                'name' => 'A.P. Moller - Maersk Line',
                'transport_mode' => 'SEA',
                'contact_email' => 'booking@maersk.com',
                'contact_phone' => '+45 33 63 33 63',
                'is_active' => true,
            ],
            [
                'code' => 'MSC',
                'name' => 'Mediterranean Shipping Company',
                'transport_mode' => 'SEA',
                'contact_email' => 'info@msc.com',
                'contact_phone' => '+41 22 703 88 88',
                'is_active' => true,
            ],
            [
                'code' => 'CMA',
                'name' => 'CMA CGM Group',
                'transport_mode' => 'SEA',
                'contact_email' => 'ho.contact@cma-cgm.com',
                'contact_phone' => '+33 4 88 91 90 00',
                'is_active' => true,
            ],
            [
                'code' => 'COSCO',
                'name' => 'China Ocean Shipping Company',
                'transport_mode' => 'SEA',
                'contact_email' => 'cs@coscoshipping.com',
                'contact_phone' => '+86 21 6596 6666',
                'is_active' => true,
            ],
            [
                'code' => 'HAPAG',
                'name' => 'Hapag-Lloyd AG',
                'transport_mode' => 'SEA',
                'contact_email' => 'service@hlag.com',
                'contact_phone' => '+49 40 3001 0',
                'is_active' => true,
            ],
            [
                'code' => 'SQCARGO',
                'name' => 'Singapore Airlines Cargo',
                'transport_mode' => 'AIR',
                'contact_email' => 'cargo_ops@singaporeair.com.sg',
                'contact_phone' => '+65 6542 5588',
                'is_active' => true,
            ],
            [
                'code' => 'INLANDEX',
                'name' => 'Deliveree Intercity Logistics',
                'transport_mode' => 'LAND',
                'contact_email' => 'support@deliveree-fleet.id',
                'contact_phone' => '+62 21 5086 8080',
                'is_active' => true,
            ],
        ];

        foreach ($carriers as $c) {
            Carrier::firstOrCreate(['code' => $c['code']], $c);
        }

        // 3. Seed Global Maritime Ports & Strategic Hubs
        $locations = [
            [
                'code' => 'IDJKT',
                'name' => 'Port of Tanjung Priok',
                'city' => 'Jakarta',
                'country' => 'Indonesia',
                'type' => 'PORT',
                'latitude' => -6.1028000,
                'longitude' => 106.8833000,
            ],
            [
                'code' => 'IDSUB',
                'name' => 'Port of Tanjung Perak',
                'city' => 'Surabaya',
                'country' => 'Indonesia',
                'type' => 'PORT',
                'latitude' => -7.1994000,
                'longitude' => 112.7303000,
            ],
            [
                'code' => 'SGSIN',
                'name' => 'Port of Singapore',
                'city' => 'Singapore',
                'country' => 'Singapore',
                'type' => 'PORT',
                'latitude' => 1.2902700,
                'longitude' => 103.8519590,
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
                'code' => 'CNSHA',
                'name' => 'Port of Shanghai',
                'city' => 'Shanghai',
                'country' => 'China',
                'type' => 'PORT',
                'latitude' => 31.2304000,
                'longitude' => 121.4737000,
            ],
            [
                'code' => 'USLAX',
                'name' => 'Port of Los Angeles',
                'city' => 'Los Angeles',
                'country' => 'United States',
                'type' => 'PORT',
                'latitude' => 33.7431840,
                'longitude' => -118.2672580,
            ],
            [
                'code' => 'KRPUS',
                'name' => 'Port of Busan',
                'city' => 'Busan',
                'country' => 'South Korea',
                'type' => 'PORT',
                'latitude' => 35.1028000,
                'longitude' => 129.0403000,
            ],
            [
                'code' => 'AEDXB',
                'name' => 'Jebel Ali Port',
                'city' => 'Dubai',
                'country' => 'United Arab Emirates',
                'type' => 'PORT',
                'latitude' => 25.0112000,
                'longitude' => 55.0617000,
            ],
        ];

        foreach ($locations as $l) {
            Location::firstOrCreate(['code' => $l['code']], $l);
        }

        // 4. Seed System Settings
        $settings = [
            ['key' => 'cargotrace_delay_threshold_hours', 'value' => '24', 'type' => 'integer', 'description' => 'Threshold to mark shipment as DELAYED in hours'],
            ['key' => 'cargotrace_geocoding_provider', 'value' => 'nominatim', 'type' => 'string', 'description' => 'Geocoding service provider'],
            ['key' => 'cargotrace_weather_provider', 'value' => 'open-meteo', 'type' => 'string', 'description' => 'Weather snapshot provider'],
        ];

        foreach ($settings as $s) {
            SystemSetting::firstOrCreate(['key' => $s['key']], $s);
        }

        // 4b. Seed Shipping Tariffs Table (Sea, Air, Land rate matrix)
        $tariffs = [
            [
                'transport_mode' => 'SEA',
                'service_name' => 'Ocean Freight FCL/LCL',
                'base_fee_usd' => 150.00,
                'rate_per_kg_usd' => 0.6500,
                'rate_per_km_usd' => 0.0450,
                'min_weight_kg' => 50,
                'avg_speed_kmh' => 35,
                'handling_hours' => 48,
                'is_active' => true,
            ],
            [
                'transport_mode' => 'AIR',
                'service_name' => 'Air Freight Express Cold-Chain',
                'base_fee_usd' => 95.00,
                'rate_per_kg_usd' => 4.2500,
                'rate_per_km_usd' => 0.1800,
                'min_weight_kg' => 10,
                'avg_speed_kmh' => 750,
                'handling_hours' => 12,
                'is_active' => true,
            ],
            [
                'transport_mode' => 'LAND',
                'service_name' => 'Inland Intermodal Trucking',
                'base_fee_usd' => 45.00,
                'rate_per_kg_usd' => 0.4500,
                'rate_per_km_usd' => 0.3200,
                'min_weight_kg' => 20,
                'avg_speed_kmh' => 55,
                'handling_hours' => 6,
                'is_active' => true,
            ],
        ];

        foreach ($tariffs as $t) {
            ShippingTariff::firstOrCreate(['transport_mode' => $t['transport_mode']], $t);
        }

        // 5. Seed End-to-End Realistic Shipments
        $shipmentService = app(ShipmentService::class);
        $trackingService = app(TrackingService::class);

        $jkt = Location::where('code', 'IDJKT')->first();
        $sub = Location::where('code', 'IDSUB')->first();
        $sin = Location::where('code', 'SGSIN')->first();
        $rtm = Location::where('code', 'NLRTM')->first();
        $sha = Location::where('code', 'CNSHA')->first();
        $lax = Location::where('code', 'USLAX')->first();
        $pus = Location::where('code', 'KRPUS')->first();
        $dxb = Location::where('code', 'AEDXB')->first();

        $maersk = Carrier::where('code', 'MAERSK')->first();
        $msc = Carrier::where('code', 'MSC')->first();
        $cma = Carrier::where('code', 'CMA')->first();
        $cosco = Carrier::where('code', 'COSCO')->first();
        $hapag = Carrier::where('code', 'HAPAG')->first();
        $sq = Carrier::where('code', 'SQCARGO')->first();

        // -------------------------------------------------------------------------
        // Skenario 1: Flagship On-Time Asian-European Route (IN_TRANSIT)
        // -------------------------------------------------------------------------
        if (! Shipment::where('reference_number', 'PO-JKT-RTM-001')->exists()) {
            $dep1 = now()->subDays(4);
            $arr1 = now()->addDays(18);

            $s1 = $shipmentService->createShipment([
                'customer_id' => $customer1->id,
                'carrier_id' => $maersk->id,
                'origin_location_id' => $jkt->id,
                'destination_location_id' => $rtm->id,
                'cargo_type' => 'Electronics & Semiconductor FCL',
                'reference_number' => 'PO-JKT-RTM-001',
                'expected_departure' => $dep1,
                'expected_arrival' => $arr1,
                'initial_checkpoint_at' => $dep1,
                'notes' => 'Priority maritime shipment via Suez transit.',
            ], [
                [
                    'sku' => 'IC-MICRO-8542',
                    'description' => 'Silicon Microcontrollers (HS 8542.31)',
                    'quantity' => 120,
                    'weight_kg' => 18.5,
                    'volume_cbm' => 0.12,
                    'declared_value' => 240000,
                ],
                [
                    'sku' => 'PCB-MOD-8534',
                    'description' => 'Multi-layer Printed Circuit Boards (HS 8534.00)',
                    'quantity' => 80,
                    'weight_kg' => 14.0,
                    'volume_cbm' => 0.08,
                    'declared_value' => 95000,
                ],
            ], creatorId: $admin->id);

            $shipmentService->transitionStatus($s1, 'BOOKED');
            $shipmentService->transitionStatus($s1, 'PICKED_UP');
            $shipmentService->transitionStatus($s1, 'IN_TRANSIT');

            // Checkpoint 2: Malacca Strait
            $trackingService->appendEvent($s1, [
                'location_id' => $sin->id,
                'status' => 'IN_TRANSIT',
                'event_type' => 'NORMAL',
                'latitude' => 1.29027,
                'longitude' => 103.851959,
                'description' => 'Vessel berthed at PSA Singapore Terminal for transshipment bunker operations.',
                'weather_snapshot' => [
                    'temperature' => 29.4,
                    'condition' => 'Scattered Clouds',
                    'wind_speed' => 12.8,
                    'humidity' => 78,
                ],
                'occurred_at' => $dep1->copy()->addDays(2),
            ], creatorId: $admin->id);

            // Checkpoint 3: Indian Ocean
            $trackingService->appendEvent($s1, [
                'location_id' => $sin->id,
                'status' => 'IN_TRANSIT',
                'event_type' => 'NORMAL',
                'latitude' => 5.92,
                'longitude' => 80.55,
                'description' => 'Vessel en route south of Sri Lanka. All container reefer temps optimal.',
                'weather_snapshot' => [
                    'temperature' => 28.1,
                    'condition' => 'Clear Sky',
                    'wind_speed' => 18.5,
                    'humidity' => 70,
                ],
                'occurred_at' => now()->subDay(),
            ], creatorId: $admin->id);
        }

        // -------------------------------------------------------------------------
        // Skenario 2: Trans-Pacific Route Tuntas (DELIVERED)
        // -------------------------------------------------------------------------
        if (! Shipment::where('reference_number', 'PO-SIN-LAX-002')->exists()) {
            $dep2 = now()->subDays(28);
            $arr2 = now()->subDays(2);

            $s2 = $shipmentService->createShipment([
                'customer_id' => $customer1->id,
                'carrier_id' => $msc->id,
                'origin_location_id' => $sin->id,
                'destination_location_id' => $lax->id,
                'cargo_type' => 'Consumer Audio Equipment',
                'reference_number' => 'PO-SIN-LAX-002',
                'expected_departure' => $dep2,
                'expected_arrival' => $arr2,
                'initial_checkpoint_at' => $dep2,
                'notes' => 'Trans-Pacific linehaul.',
            ], [
                [
                    'sku' => 'AUD-SPK-8518',
                    'description' => 'Active Studio Monitor Speakers (HS 8518.22)',
                    'quantity' => 250,
                    'weight_kg' => 22.0,
                    'volume_cbm' => 0.15,
                    'declared_value' => 145000,
                ],
            ], creatorId: $admin->id);

            $shipmentService->transitionStatus($s2, 'BOOKED');
            $shipmentService->transitionStatus($s2, 'PICKED_UP');
            $shipmentService->transitionStatus($s2, 'IN_TRANSIT');

            $trackingService->appendEvent($s2, [
                'location_id' => $lax->id,
                'status' => 'DELIVERED',
                'event_type' => 'NORMAL',
                'latitude' => $lax->latitude,
                'longitude' => $lax->longitude,
                'description' => 'Consignment cleared US Customs and successfully discharged at Long Beach Pier 400.',
                'occurred_at' => now()->subDays(2),
            ], creatorId: $admin->id);
        }

        // -------------------------------------------------------------------------
        // Skenario 3: Kargo Terlambat Akibat Port Congestion (DELAYED)
        // -------------------------------------------------------------------------
        if (! Shipment::where('reference_number', 'PO-SUB-DXB-003')->exists()) {
            $dep3 = now()->subDays(8);
            $expectedArr3 = now()->subDay(); // Expected arrival was yesterday!
            $estimatedArr3 = now()->addDays(2); // Estimated delayed by +3 days

            $s3 = $shipmentService->createShipment([
                'customer_id' => $customer2->id,
                'carrier_id' => $hapag->id,
                'origin_location_id' => $sub->id,
                'destination_location_id' => $dxb->id,
                'cargo_type' => 'Specialty Arabica Coffee Beans',
                'reference_number' => 'PO-SUB-DXB-003',
                'expected_departure' => $dep3,
                'expected_arrival' => $expectedArr3,
                'initial_checkpoint_at' => $dep3,
                'notes' => 'Container delay due to port berth congestion.',
            ], [
                [
                    'sku' => 'COF-ARA-0901',
                    'description' => 'Grade-1 Java Arabica Coffee Beans (HS 0901.11)',
                    'quantity' => 300,
                    'weight_kg' => 60.0,
                    'volume_cbm' => 0.11,
                    'declared_value' => 72000,
                ],
            ], creatorId: $admin->id);

            $shipmentService->transitionStatus($s3, 'BOOKED');
            $shipmentService->transitionStatus($s3, 'PICKED_UP');
            $shipmentService->transitionStatus($s3, 'IN_TRANSIT');

            // Set delay status
            $s3->update([
                'is_delayed' => true,
                'delay_duration_hours' => 48,
                'delay_severity' => 'MEDIUM',
                'delay_reason' => 'Port berth congestion at gateway and tropical storm delay',
                'estimated_arrival' => $estimatedArr3,
            ]);

            $trackingService->appendEvent($s3, [
                'location_id' => $sub->id,
                'status' => 'IN_TRANSIT',
                'event_type' => 'EXCEPTION',
                'latitude' => 12.8,
                'longitude' => 74.8,
                'description' => 'Vessel anchor speed reduced due to Arabian Sea weather alert. Arrival delayed by 48 hours.',
                'weather_snapshot' => [
                    'temperature' => 26.5,
                    'condition' => 'Thunderstorm',
                    'wind_speed' => 42.0,
                    'humidity' => 92,
                ],
                'occurred_at' => now()->subDays(2),
            ], creatorId: $operator->id);
        }

        // -------------------------------------------------------------------------
        // Skenario 4: Anomali Bea Cukai (OPEN EXCEPTION)
        // -------------------------------------------------------------------------
        if (! Shipment::where('reference_number', 'PO-SHA-JKT-004')->exists()) {
            $dep4 = now()->subDays(5);
            $arr4 = now()->addDays(4);

            $s4 = $shipmentService->createShipment([
                'customer_id' => $customer2->id,
                'carrier_id' => $cosco->id,
                'origin_location_id' => $sha->id,
                'destination_location_id' => $jkt->id,
                'cargo_type' => 'Industrial Textile Roll Goods',
                'reference_number' => 'PO-SHA-JKT-004',
                'expected_departure' => $dep4,
                'expected_arrival' => $arr4,
                'initial_checkpoint_at' => $dep4,
                'notes' => 'Customs document verification under progress.',
            ], [
                [
                    'sku' => 'TEX-COT-5208',
                    'description' => 'Dyed Woven Cotton Fabric (HS 5208.32)',
                    'quantity' => 150,
                    'weight_kg' => 45.0,
                    'volume_cbm' => 0.22,
                    'declared_value' => 65000,
                ],
            ], creatorId: $admin->id);

            $shipmentService->transitionStatus($s4, 'BOOKED');
            $shipmentService->transitionStatus($s4, 'PICKED_UP');
            $shipmentService->transitionStatus($s4, 'IN_TRANSIT');

            $evtException = $trackingService->appendEvent($s4, [
                'location_id' => $sha->id,
                'status' => 'IN_TRANSIT',
                'event_type' => 'EXCEPTION',
                'latitude' => $sha->latitude,
                'longitude' => $sha->longitude,
                'description' => 'Container flagged for supplementary physical cargo examination by port customs inspection unit.',
                'occurred_at' => now()->subDays(3),
            ], creatorId: $operator->id);

            // Record in shipment_exceptions table
            ShipmentException::create([
                'shipment_id' => $s4->id,
                'tracking_event_id' => $evtException->id,
                'reported_by' => $operator->id,
                'exception_type' => 'CUSTOMS_HOLD',
                'severity' => 'HIGH',
                'status' => 'OPEN',
                'description' => 'Fumigation certification discrepancy requiring physical inspection by customs authorities.',
                'detected_at' => now()->subDays(3),
            ]);
        }

        // -------------------------------------------------------------------------
        // Skenario 5: Air Freight Express (DELIVERED)
        // -------------------------------------------------------------------------
        if (! Shipment::where('reference_number', 'AWB-SIN-SHA-005')->exists()) {
            $dep5 = now()->subDays(2);
            $arr5 = now()->subDay();

            $s5 = $shipmentService->createShipment([
                'customer_id' => $customer1->id,
                'carrier_id' => $sq->id,
                'origin_location_id' => $sin->id,
                'destination_location_id' => $sha->id,
                'cargo_type' => 'Precision Medical Diagnostics',
                'reference_number' => 'AWB-SIN-SHA-005',
                'expected_departure' => $dep5,
                'expected_arrival' => $arr5,
                'initial_checkpoint_at' => $dep5,
                'notes' => 'Time-sensitive air cargo cold-chain.',
            ], [
                [
                    'sku' => 'MED-DIAG-9027',
                    'description' => 'Photometric Clinical Laboratory Reagents (HS 3822.00)',
                    'quantity' => 20,
                    'weight_kg' => 8.0,
                    'volume_cbm' => 0.05,
                    'declared_value' => 88000,
                ],
            ], creatorId: $admin->id);

            $shipmentService->transitionStatus($s5, 'BOOKED');
            $shipmentService->transitionStatus($s5, 'PICKED_UP');
            $shipmentService->transitionStatus($s5, 'IN_TRANSIT');

            $trackingService->appendEvent($s5, [
                'location_id' => $sha->id,
                'status' => 'DELIVERED',
                'event_type' => 'NORMAL',
                'latitude' => $sha->latitude,
                'longitude' => $sha->longitude,
                'description' => 'Flight SQ781 landed at Shanghai Pudong. Cargo handed over to consignee.',
                'occurred_at' => now()->subDay(),
            ], creatorId: $admin->id);
        }

        // -------------------------------------------------------------------------
        // Skenario 6: Kargo Baru Terdaftar (CREATED)
        // -------------------------------------------------------------------------
        if (! Shipment::where('reference_number', 'PO-PUS-RTM-006')->exists()) {
            $shipmentService->createShipment([
                'customer_id' => $customer2->id,
                'carrier_id' => $cma->id,
                'origin_location_id' => $pus->id,
                'destination_location_id' => $rtm->id,
                'cargo_type' => 'Shipbuilding Machinery Spare Parts',
                'reference_number' => 'PO-PUS-RTM-006',
                'expected_departure' => now()->addDays(2),
                'expected_arrival' => now()->addDays(28),
                'notes' => 'Booking request submitted, awaiting carrier slot allocation.',
            ], [
                [
                    'sku' => 'MCH-VALV-8481',
                    'description' => 'Heavy Duty Hydraulic Control Valves (HS 8481.20)',
                    'quantity' => 40,
                    'weight_kg' => 85.0,
                    'volume_cbm' => 0.35,
                    'declared_value' => 110000,
                ],
            ], creatorId: $admin->id);
        }

        // -------------------------------------------------------------------------
        // Skenario 7: Kargo Terkonfirmasi Slot (BOOKED)
        // -------------------------------------------------------------------------
        if (! Shipment::where('reference_number', 'PO-JKT-PUS-007')->exists()) {
            $s7 = $shipmentService->createShipment([
                'customer_id' => $customer1->id,
                'carrier_id' => $maersk->id,
                'origin_location_id' => $jkt->id,
                'destination_location_id' => $pus->id,
                'cargo_type' => 'Automotive Cable Harnesses',
                'reference_number' => 'PO-JKT-PUS-007',
                'expected_departure' => now()->addDay(),
                'expected_arrival' => now()->addDays(9),
                'notes' => 'Booking reference MAE-99212 confirmed.',
            ], [
                [
                    'sku' => 'AUT-HARN-8544',
                    'description' => 'Insulated Wire Harnesses for Motor Vehicles (HS 8544.30)',
                    'quantity' => 200,
                    'weight_kg' => 12.0,
                    'volume_cbm' => 0.08,
                    'declared_value' => 54000,
                ],
            ], creatorId: $admin->id);

            $shipmentService->transitionStatus($s7, 'BOOKED');
        }

        // -------------------------------------------------------------------------
        // Skenario 8: Kontainer Siap di Depo Pelabuhan (PICKED_UP)
        // -------------------------------------------------------------------------
        if (! Shipment::where('reference_number', 'PO-SUB-SIN-008')->exists()) {
            $s8 = $shipmentService->createShipment([
                'customer_id' => $customer2->id,
                'carrier_id' => $hapag->id,
                'origin_location_id' => $sub->id,
                'destination_location_id' => $sin->id,
                'cargo_type' => 'Processed Marine Shrimp Frozen',
                'reference_number' => 'PO-SUB-SIN-008',
                'expected_departure' => now()->addHours(6),
                'expected_arrival' => now()->addDays(3),
                'notes' => 'Reefer container set at -18 deg C.',
            ], [
                [
                    'sku' => 'SEA-SHRM-0306',
                    'description' => 'Frozen Vannamei Shrimps IQF (HS 0306.17)',
                    'quantity' => 500,
                    'weight_kg' => 10.0,
                    'volume_cbm' => 0.03,
                    'declared_value' => 62000,
                ],
            ], creatorId: $operator->id);

            $shipmentService->transitionStatus($s8, 'BOOKED');
            $shipmentService->transitionStatus($s8, 'PICKED_UP');
        }
    }
}
