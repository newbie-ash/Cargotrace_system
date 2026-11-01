<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrackingEventResource extends JsonResource
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
            'shipment_id' => $this->shipment_id,
            'location_id' => $this->location_id,
            'location' => new LocationResource($this->whenLoaded('location')),
            'status' => $this->status,
            'event_type' => $this->event_type,
            'description' => $this->description,
            'latitude' => $this->latitude ? (float) $this->latitude : null,
            'longitude' => $this->longitude ? (float) $this->longitude : null,
            'weather_snapshot' => $this->weather_snapshot,
            'occurred_at' => $this->occurred_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
