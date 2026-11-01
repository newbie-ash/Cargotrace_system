<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        return $user && ($user->isAdmin() || $user->isOperator());
    }

    public function rules(): array
    {
        return [
            'reference_number' => ['nullable', 'string', 'max:100'],
            'customer_id' => ['required', 'exists:users,id'],
            'carrier_id' => ['required', 'exists:carriers,id'],
            'origin_location_id' => ['required', 'exists:locations,id'],
            'destination_location_id' => ['required', 'exists:locations,id', 'different:origin_location_id'],
            'cargo_type' => ['required', 'string', 'max:100'],
            'total_weight_kg' => ['nullable', 'numeric', 'min:0'],
            'total_volume_cbm' => ['nullable', 'numeric', 'min:0'],
            'expected_departure' => ['required', 'date'],
            'expected_arrival' => ['required', 'date', 'after_or_equal:expected_departure'],
            'estimated_arrival' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['nullable', 'array'],
            'items.*.sku' => ['nullable', 'string', 'max:50'],
            'items.*.description' => ['required_with:items', 'string', 'max:255'],
            'items.*.quantity' => ['required_with:items', 'integer', 'min:1'],
            'items.*.weight_kg' => ['nullable', 'numeric', 'min:0'],
            'items.*.volume_cbm' => ['nullable', 'numeric', 'min:0'],
            'items.*.declared_value' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_id.required' => 'Customer account is required.',
            'carrier_id.required' => 'Transport carrier is required.',
            'origin_location_id.required' => 'Origin location is required.',
            'destination_location_id.required' => 'Destination location is required.',
            'destination_location_id.different' => 'Destination location must be different from origin location.',
            'cargo_type.required' => 'Cargo classification is required.',
            'expected_departure.required' => 'Expected departure date and time is required.',
            'expected_arrival.required' => 'Expected arrival date and time is required.',
            'expected_arrival.after_or_equal' => 'Expected arrival cannot be earlier than departure date.',
        ];
    }
}
