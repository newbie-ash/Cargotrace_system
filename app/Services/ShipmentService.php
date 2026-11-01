<?php

namespace App\Services;

use App\Models\Shipment;
use App\Models\ShipmentItem;
use App\Models\User;
use App\Notifications\ShipmentEventNotification;
use Carbon\Carbon;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ShipmentService
{
    public const ALLOWED_TRANSITIONS = [
        'CREATED'    => ['BOOKED', 'CANCELLED'],
        'BOOKED'     => ['PICKED_UP', 'CANCELLED'],
        'PICKED_UP'  => ['IN_TRANSIT'],
        'IN_TRANSIT' => ['DELIVERED'],
        'DELIVERED'  => [], // Terminal state
        'CANCELLED'  => [], // Terminal state
    ];

    public function __construct(
        protected AuditLogService $auditLogService
    ) {}

    /**
     * Determine whether a status transition is permitted by the state machine.
     */
    public function canTransition(string $from, string $to): bool
    {
        return in_array($to, self::ALLOWED_TRANSITIONS[$from] ?? [], true);
    }

    /**
     * Generate an unambiguous, unique shipment number (format: CT-YYYY-XXXXXX).
     */
    public function generateShipmentNumber(): string
    {
        $year = date('Y');
        do {
            $random = strtoupper(Str::random(6));
            $number = "CT-{$year}-{$random}";
        } while (Shipment::where('shipment_number', $number)->exists());

        return $number;
    }

    /**
     * Atomically create a shipment with its line items.
     */
    public function createShipment(array $data, array $items = [], ?int $creatorId = null): Shipment
    {
        return DB::transaction(function () use ($data, $items, $creatorId) {
            $data['shipment_number'] = $data['shipment_number'] ?? $this->generateShipmentNumber();
            $data['created_by'] = $creatorId ?? auth()->id();
            $data['current_status'] = 'CREATED';

            // Calculate total weight and volume from line items if present
            if (! empty($items)) {
                $calcWeight = 0;
                $calcVolume = 0;
                foreach ($items as $item) {
                    $qty = (int) ($item['quantity'] ?? 1);
                    $calcWeight += ((float) ($item['weight_kg'] ?? 0)) * $qty;
                    $calcVolume += ((float) ($item['volume_cbm'] ?? 0)) * $qty;
                }
                $data['total_weight_kg'] = $calcWeight > 0 ? $calcWeight : ($data['total_weight_kg'] ?? 0);
                $data['total_volume_cbm'] = $calcVolume > 0 ? $calcVolume : ($data['total_volume_cbm'] ?? 0);
            }

            // Default estimated arrival equals expected arrival at creation
            if (empty($data['estimated_arrival']) && ! empty($data['expected_arrival'])) {
                $data['estimated_arrival'] = $data['expected_arrival'];
            }

            // Extract initial checkpoint timestamp and sanitize payload
            $initialCheckpointAt = isset($data['initial_checkpoint_at']) 
                ? Carbon::parse($data['initial_checkpoint_at']) 
                : (isset($data['expected_departure']) && Carbon::parse($data['expected_departure'])->isPast() 
                    ? Carbon::parse($data['expected_departure']) 
                    : now());
            unset($data['initial_checkpoint_at']);

            $shipment = Shipment::create($data);

            if (! empty($items)) {
                foreach ($items as $item) {
                    $shipment->items()->create([
                        'sku' => $item['sku'] ?? null,
                        'description' => $item['description'],
                        'quantity' => (int) ($item['quantity'] ?? 1),
                        'weight_kg' => (float) ($item['weight_kg'] ?? 0),
                        'volume_cbm' => (float) ($item['volume_cbm'] ?? 0),
                        'declared_value' => (float) ($item['declared_value'] ?? 0),
                    ]);
                }
            }

            // Create initial tracking checkpoint
            $shipment->trackingEvents()->create([
                'location_id' => $shipment->origin_location_id,
                'created_by' => $data['created_by'],
                'status' => 'CREATED',
                'event_type' => 'NORMAL',
                'latitude' => $shipment->originLocation?->latitude,
                'longitude' => $shipment->originLocation?->longitude,
                'description' => 'Shipment order registered in CargoTrace system.',
                'occurred_at' => $initialCheckpointAt,
            ]);

            // Sync shipment current coordinates
            $shipment->update([
                'current_lat' => $shipment->originLocation?->latitude,
                'current_lng' => $shipment->originLocation?->longitude,
            ]);

            $this->auditLogService->log('shipment.created', $shipment, null, $shipment->toArray());

            return $shipment->load(['items', 'trackingEvents', 'carrier', 'originLocation', 'destinationLocation']);
        });
    }

    /**
     * Transition shipment status through the strict state machine.
     *
     * @throws DomainException
     */
    public function transitionStatus(Shipment $shipment, string $newStatus, ?string $reason = null): Shipment
    {
        $current = $shipment->current_status;

        if (! $this->canTransition($current, $newStatus)) {
            throw new DomainException(
                "Invalid status transition from '{$current}' to '{$newStatus}' for shipment {$shipment->shipment_number}."
            );
        }

        return DB::transaction(function () use ($shipment, $current, $newStatus, $reason) {
            $oldValues = $shipment->toArray();

            $updateData = ['current_status' => $newStatus];

            if ($newStatus === 'DELIVERED') {
                $updateData['actual_arrival'] = now();
            }

            if ($reason) {
                $updateData['notes'] = trim(($shipment->notes ? $shipment->notes . "\n" : '') . "[Status Change: {$newStatus}] " . $reason);
            }

            $shipment->update($updateData);

            $this->auditLogService->log('shipment.status_changed', $shipment, $oldValues, $shipment->fresh()->toArray());

            // Dispatch customer in-app notification
            $shipment->customer?->notify(new ShipmentEventNotification(
                $shipment,
                "Status Kargo {$shipment->shipment_number}: {$newStatus}",
                "Pengiriman Anda telah beralih ke status {$newStatus}." . ($reason ? " Catatan: {$reason}" : ""),
                'STATUS_CHANGE',
                $newStatus
            ));

            return $shipment;
        });
    }
}
