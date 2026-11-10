<?php

namespace App\Services;

use App\Models\Location;
use App\Models\ShippingTariff;
use DomainException;

class TariffEstimatorService
{
    public const USD_TO_IDR_RATE = 15800;

    /**
     * Calculate Haversine distance in kilometers between two coordinates.
     */
    public function calculateDistanceKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadiusKm = 6371.0;

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadiusKm * $c, 1);
    }

    /**
     * Estimate shipping cost and transit duration from database tariff table.
     *
     * @throws DomainException
     */
    public function estimate(Location $origin, Location $destination, string $transportMode, float $weightKg): array
    {
        $tariff = ShippingTariff::active()
            ->where('transport_mode', strtoupper($transportMode))
            ->first();

        if (! $tariff) {
            throw new DomainException("Tarif layanan untuk moda '{$transportMode}' tidak tersedia.");
        }

        $distanceKm = $this->calculateDistanceKm(
            (float) $origin->latitude,
            (float) $origin->longitude,
            (float) $destination->latitude,
            (float) $destination->longitude
        );

        $effectiveWeight = max($weightKg, (float) $tariff->min_weight_kg);

        $baseFeeUsd = (float) $tariff->base_fee_usd;
        $weightCostUsd = round($effectiveWeight * (float) $tariff->rate_per_kg_usd, 2);
        $distanceCostUsd = round($distanceKm * (float) $tariff->rate_per_km_usd, 2);
        $totalCostUsd = round($baseFeeUsd + $weightCostUsd + $distanceCostUsd, 2);
        $totalCostIdr = (int) round($totalCostUsd * self::USD_TO_IDR_RATE);

        $transitHours = (int) ceil(($distanceKm / max(1, $tariff->avg_speed_kmh)) + $tariff->handling_hours);
        $transitDays = max(1, (int) ceil($transitHours / 24));

        return [
            'service_name' => $tariff->service_name,
            'transport_mode' => $tariff->transport_mode,
            'origin' => [
                'code' => $origin->code,
                'name' => $origin->name,
                'city' => $origin->city,
                'country' => $origin->country,
            ],
            'destination' => [
                'code' => $destination->code,
                'name' => $destination->name,
                'city' => $destination->city,
                'country' => $destination->country,
            ],
            'distance_km' => $distanceKm,
            'input_weight_kg' => $weightKg,
            'effective_weight_kg' => $effectiveWeight,
            'min_weight_kg' => $tariff->min_weight_kg,
            'estimated_transit_hours' => $transitHours,
            'estimated_transit_days' => $transitDays,
            'breakdown' => [
                'base_fee_usd' => $baseFeeUsd,
                'weight_cost_usd' => $weightCostUsd,
                'distance_cost_usd' => $distanceCostUsd,
            ],
            'total_cost_usd' => $totalCostUsd,
            'total_cost_idr' => $totalCostIdr,
        ];
    }
}
