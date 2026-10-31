<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CarrierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        $carrierId = $this->route('carrier')?->id ?? $this->route('carrier');

        return [
            'code' => [
                'required',
                'string',
                'max:20',
                'regex:/^[A-Z0-9_\-]+$/i',
                Rule::unique('carriers', 'code')->ignore($carrierId),
            ],
            'name' => ['required', 'string', 'max:100'],
            'transport_mode' => ['required', Rule::in(['SEA', 'AIR', 'LAND', 'MULTIMODAL'])],
            'contact_email' => ['nullable', 'email', 'max:150'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Carrier code is required.',
            'code.unique' => 'A carrier with this code already exists.',
            'code.regex' => 'Carrier code may only contain alphanumeric characters, dashes, and underscores.',
            'name.required' => 'Carrier name is required.',
            'transport_mode.required' => 'Transport mode is required.',
            'transport_mode.in' => 'Transport mode must be SEA, AIR, LAND, or MULTIMODAL.',
        ];
    }
}
