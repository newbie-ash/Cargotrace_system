<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipment_number',
        'reference_number',
        'customer_id',
        'carrier_id',
        'origin_location_id',
        'destination_location_id',
        'created_by',
        'cargo_type',
        'total_weight_kg',
        'total_volume_cbm',
        'expected_departure',
        'expected_arrival',
        'estimated_arrival',
        'actual_arrival',
        'current_status',
        'current_lat',
        'current_lng',
        'is_delayed',
        'delay_duration_hours',
        'delay_severity',
        'delay_reason',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'total_weight_kg' => 'decimal:2',
            'total_volume_cbm' => 'decimal:2',
            'current_lat' => 'decimal:7',
            'current_lng' => 'decimal:7',
            'delay_duration_hours' => 'decimal:2',
            'is_delayed' => 'boolean',
            'expected_departure' => 'datetime',
            'expected_arrival' => 'datetime',
            'estimated_arrival' => 'datetime',
            'actual_arrival' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function carrier(): BelongsTo
    {
        return $this->belongsTo(Carrier::class);
    }

    public function originLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'origin_location_id');
    }

    public function destinationLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'destination_location_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ShipmentItem::class);
    }

    public function lineItems(): HasMany
    {
        return $this->items();
    }

    public function trackingEvents(): HasMany
    {
        return $this->hasMany(TrackingEvent::class)->orderBy('occurred_at', 'asc');
    }

    public function latestTrackingEvent()
    {
        return $this->hasOne(TrackingEvent::class)->latestOfMany('occurred_at');
    }

    public function exceptions(): HasMany
    {
        return $this->hasMany(ShipmentException::class);
    }
}
