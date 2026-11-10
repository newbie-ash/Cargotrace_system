<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingTariff extends Model
{
    use HasFactory;

    protected $fillable = [
        'transport_mode',
        'service_name',
        'base_fee_usd',
        'rate_per_kg_usd',
        'rate_per_km_usd',
        'min_weight_kg',
        'avg_speed_kmh',
        'handling_hours',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'base_fee_usd' => 'decimal:2',
            'rate_per_kg_usd' => 'decimal:4',
            'rate_per_km_usd' => 'decimal:4',
            'min_weight_kg' => 'integer',
            'avg_speed_kmh' => 'integer',
            'handling_hours' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
