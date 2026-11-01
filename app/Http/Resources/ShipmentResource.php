<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShipmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'shipment_number' => $this->shipment_number,
            'current_status' => $this->current_status,
            'cargo_type' => $this->cargo_type,
            'expected_departure' => $this->expected_departure?->toISOString(),
            'expected_arrival' => $this->expected_arrival?->toISOString(),
            'estimated_arrival' => $this->estimated_arrival?->toISOString(),
            'actual_arrival' => $this->actual_arrival?->toISOString(),
            'is_delayed' => (bool) $this->is_delayed,
            'delay_duration_hours' => (int) $this->delay_duration_hours,
            'carrier' => new CarrierResource($this->whenLoaded('carrier')),
            'origin_location' => new LocationResource($this->whenLoaded('originLocation')),
            'destination_location' => new LocationResource($this->whenLoaded('destinationLocation')),
            'customer' => $this->whenLoaded('customer', function () {
                return [
                    'id' => $this->customer->id,
                    'name' => $this->customer->name,
                    'email' => $this->customer->email,
                    'company_name' => $this->customer->company_name,
                ];
            }),
            'line_items' => ShipmentLineItemResource::collection($this->whenLoaded('lineItems')),
            'tracking_events' => TrackingEventResource::collection($this->whenLoaded('trackingEvents')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
