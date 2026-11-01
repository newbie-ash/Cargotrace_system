<?php

namespace Tests\Feature;

use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\User;
use App\Services\ShipmentService;
use App\Services\TrackingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $customer;
    protected Carrier $carrier;
    protected Location $origin;
    protected Location $destination;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin Notif',
            'email' => 'admin.notif@cargotrace.app',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->customer = User::create([
            'name' => 'Customer Notif',
            'email' => 'customer.notif@cargotrace.app',
            'password' => bcrypt('password'),
            'role' => 'customer',
            'company_name' => 'Notification Recipient Co',
        ]);

        $this->carrier = Carrier::create([
            'code' => 'CMA',
            'name' => 'CMA CGM Group',
            'transport_mode' => 'SEA',
            'is_active' => true,
        ]);

        $this->origin = Location::create([
            'code' => 'IDBLW',
            'name' => 'Belawan Port',
            'city' => 'Medan',
            'country' => 'Indonesia',
            'type' => 'PORT',
            'latitude' => 3.78,
            'longitude' => 98.69,
        ]);

        $this->destination = Location::create([
            'code' => 'MYPKG',
            'name' => 'Port Klang',
            'city' => 'Klang',
            'country' => 'Malaysia',
            'type' => 'PORT',
            'latitude' => 3.0,
            'longitude' => 101.4,
        ]);
    }

    public function test_customer_receives_database_notification_on_status_transition(): void
    {
        $shipmentService = app(ShipmentService::class);

        $shipment = $shipmentService->createShipment([
            'customer_id' => $this->customer->id,
            'carrier_id' => $this->carrier->id,
            'origin_location_id' => $this->origin->id,
            'destination_location_id' => $this->destination->id,
            'cargo_type' => 'Palm Kernel Oil',
            'expected_departure' => now()->addDay(),
            'expected_arrival' => now()->addDays(3),
        ], creatorId: $this->admin->id);

        $this->assertEquals(0, $this->customer->unreadNotifications()->count());

        // Advance status from CREATED to BOOKED
        $shipmentService->transitionStatus($shipment, 'BOOKED', 'Carrier booking reference confirmed');

        $this->customer->refresh();
        $this->assertEquals(1, $this->customer->unreadNotifications()->count());

        $notification = $this->customer->unreadNotifications()->first();
        $this->assertEquals('STATUS_CHANGE', $notification->data['event_type']);
        $this->assertEquals('BOOKED', $notification->data['status']);
        $this->assertEquals($shipment->shipment_number, $notification->data['shipment_number']);
    }

    public function test_customer_receives_notification_on_tracking_checkpoint_append(): void
    {
        $shipmentService = app(ShipmentService::class);
        $trackingService = app(TrackingService::class);

        $departure = now()->addHour();
        $shipment = $shipmentService->createShipment([
            'customer_id' => $this->customer->id,
            'carrier_id' => $this->carrier->id,
            'origin_location_id' => $this->origin->id,
            'destination_location_id' => $this->destination->id,
            'cargo_type' => 'Rubber Bales',
            'expected_departure' => $departure,
            'expected_arrival' => now()->addDays(3),
        ], creatorId: $this->admin->id);

        $trackingService->appendEvent($shipment, [
            'status' => 'CREATED',
            'location_id' => $this->origin->id,
            'description' => 'Container sealed and inspected at CFS Belawan.',
            'event_type' => 'NORMAL',
            'occurred_at' => $departure->copy()->addMinutes(10),
        ], creatorId: $this->admin->id);

        $this->customer->refresh();
        $this->assertGreaterThanOrEqual(1, $this->customer->notifications()->count());
    }

    public function test_user_can_mark_notification_as_read_and_read_all(): void
    {
        $this->actingAs($this->customer);

        $shipmentService = app(ShipmentService::class);
        $shipment = $shipmentService->createShipment([
            'customer_id' => $this->customer->id,
            'carrier_id' => $this->carrier->id,
            'origin_location_id' => $this->origin->id,
            'destination_location_id' => $this->destination->id,
            'cargo_type' => 'Coffee Beans',
            'expected_departure' => now()->addDay(),
            'expected_arrival' => now()->addDays(4),
        ], creatorId: $this->admin->id);

        // Generate 2 notifications
        $shipmentService->transitionStatus($shipment, 'BOOKED');
        $shipmentService->transitionStatus($shipment, 'PICKED_UP');

        $this->customer->refresh();
        $this->assertEquals(2, $this->customer->unreadNotifications()->count());

        $firstNotification = $this->customer->unreadNotifications()->first();

        // 1. Mark single notification as read
        $response1 = $this->post("/notifications/{$firstNotification->id}/read");
        $response1->assertRedirect();

        $this->customer->refresh();
        $this->assertEquals(1, $this->customer->unreadNotifications()->count());

        // 2. Mark all as read
        $response2 = $this->post('/notifications/read-all');
        $response2->assertRedirect();

        $this->customer->refresh();
        $this->assertEquals(0, $this->customer->unreadNotifications()->count());
    }
}
