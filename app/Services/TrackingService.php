<?php

namespace App\Services;

use App\Models\Shipment;
use App\Models\TrackingEvent;
use Carbon\Carbon;
use DomainException;
use Illuminate\Support\Facades\DB;

class TrackingService
{
    public function __construct(
        protected ShipmentService $shipmentService,
        protected AuditLogService $auditLogService,
        protected ExternalIntegrationService $externalIntegrationService
    ) {}

    /**
     * Append a new immutable tracking event with chronological validation.
     *
     * @throws DomainException
     */
    public function appendEvent(Shipment $shipment, array $data, ?int $creatorId = null): TrackingEvent
    {
        return DB::transaction(function () use ($shipment, $data, $creatorId) {
            $occurredAt = isset($data['occurred_at']) ? Carbon::parse($data['occurred_at']) : now();

            // Chronological Validation: Cannot be earlier than the most recent existing event
            $latestEvent = $shipment->trackingEvents()->reorder()->latest('occurred_at')->first();
            if ($latestEvent && $occurredAt->lt(Carbon::parse($latestEvent->occurred_at))) {
                throw new DomainException(
                    "Chronological violation: Event timestamp ({$occurredAt->toDateTimeString()}) cannot be earlier than the latest event recorded at {$latestEvent->occurred_at->toDateTimeString()}."
                );
            }

            $eventStatus = $data['status'] ?? $shipment->current_status;

            // Fetch live weather snapshot if coordinates are provided and snapshot is absent
            $weatherSnapshot = $data['weather_snapshot'] ?? null;
            if ($weatherSnapshot === null && ! empty($data['latitude']) && ! empty($data['longitude'])) {
                $weatherSnapshot = $this->externalIntegrationService->getWeather((float) $data['latitude'], (float) $data['longitude']);
            }

            $trackingEvent = $shipment->trackingEvents()->create([
                'location_id' => $data['location_id'] ?? null,
                'created_by' => $creatorId ?? auth()->id(),
                'status' => $eventStatus,
                'event_type' => $data['event_type'] ?? 'NORMAL',
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'description' => $data['description'],
                'weather_snapshot' => $weatherSnapshot,
                'occurred_at' => $occurredAt,
            ]);

            // Sync shipment current coordinates if provided
            $shipmentUpdates = [];
            if (! empty($data['latitude']) && ! empty($data['longitude'])) {
                $shipmentUpdates['current_lat'] = $data['latitude'];
                $shipmentUpdates['current_lng'] = $data['longitude'];
            }

            // Forward status transition if applicable
            if ($eventStatus !== $shipment->current_status && $this->shipmentService->canTransition($shipment->current_status, $eventStatus)) {
                $shipmentUpdates['current_status'] = $eventStatus;
                if ($eventStatus === 'DELIVERED') {
                    $shipmentUpdates['actual_arrival'] = $occurredAt;
                }
            }

            if (! empty($shipmentUpdates)) {
                $shipment->update($shipmentUpdates);
            }

            $this->auditLogService->log('tracking_event.appended', $trackingEvent, null, $trackingEvent->toArray());

            return $trackingEvent->load('location');
        });
    }
}
