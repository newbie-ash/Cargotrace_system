<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShipmentLineItemResource extends JsonResource
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
            'sku' => $this->sku,
            'description' => $this->description,
            'quantity' => (int) $this->quantity,
            'weight_kg' => (float) $this->weight_kg,
            'volume_cbm' => (float) $this->volume_cbm,
            'declared_value' => (float) $this->declared_value,
        ];
    }
}
