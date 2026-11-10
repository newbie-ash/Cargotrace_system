<?php

namespace App\Http\Controllers;

use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\ShippingTariff;
use App\Services\TariffEstimatorService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LandingController extends Controller
{
    public function __construct(
        protected TariffEstimatorService $tariffService
    ) {}

    /**
     * Render the public CargoTrace landing page with live database statistics.
     */
    public function index(): Response
    {
        $totalShipments = Shipment::count();
        $delayedCount = Shipment::where('is_delayed', true)->count();
        $onTimeRate = $totalShipments > 0
            ? (int) round((($totalShipments - $delayedCount) / $totalShipments) * 100)
            : 100;

        $stats = [
            'total_shipments' => $totalShipments,
            'active_carriers' => Carrier::active()->count(),
            'total_locations' => Location::count(),
            'delivered_count' => Shipment::where('current_status', 'DELIVERED')->count(),
            'on_time_rate' => $onTimeRate,
        ];

        $carriers = Carrier::active()->orderBy('name')->get(['id', 'code', 'name', 'transport_mode']);
        $locations = Location::orderBy('name')->get(['id', 'code', 'name', 'city', 'country', 'type', 'latitude', 'longitude']);
        $tariffs = ShippingTariff::active()->get();

        // Sample shipment numbers for 1-click test chips
        $sampleShipments = Shipment::latest('id')->take(3)->pluck('shipment_number')->toArray();

        return Inertia::render('Welcome', [
            'stats' => $stats,
            'carriers' => $carriers,
            'locations' => $locations,
            'tariffs' => $tariffs,
            'sampleShipments' => $sampleShipments,
        ]);
    }

    /**
     * Publicly track a consignment with strict PII data sanitization.
     */
    public function trackPublic(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tracking_number' => ['required', 'string', 'max:50'],
        ]);

        $queryNumber = trim($validated['tracking_number']);

        $shipment = Shipment::with([
            'carrier:id,code,name,transport_mode',
            'originLocation:id,code,name,city,country,latitude,longitude',
            'destinationLocation:id,code,name,city,country,latitude,longitude',
            'trackingEvents' => function ($q) {
                $q->with('location:id,code,name,city,country')
                    ->reorder()
                    ->latest('occurred_at')
                    ->take(3);
            },
        ])
        ->where('shipment_number', $queryNumber)
        ->orWhere('reference_number', $queryNumber)
        ->first();

        if (! $shipment) {
            return response()->json([
                'found' => false,
                'message' => "Nomor pengiriman atau referensi '{$queryNumber}' tidak ditemukan dalam sistem.",
            ], 404);
        }

        // Strictly sanitized public payload — zero customer PII or confidential cargo value exposed
        return response()->json([
            'found' => true,
            'shipment' => [
                'shipment_number' => $shipment->shipment_number,
                'reference_number' => $shipment->reference_number,
                'current_status' => $shipment->current_status,
                'is_delayed' => (bool) $shipment->is_delayed,
                'delay_duration_hours' => (int) $shipment->delay_duration_hours,
                'delay_severity' => $shipment->delay_severity,
                'expected_departure' => $shipment->expected_departure?->toISOString(),
                'estimated_arrival' => ($shipment->actual_arrival ?? $shipment->estimated_arrival ?? $shipment->expected_arrival)?->toISOString(),
                'carrier' => $shipment->carrier,
                'origin' => $shipment->originLocation,
                'destination' => $shipment->destinationLocation,
                'recent_events' => $shipment->trackingEvents->map(fn ($e) => [
                    'id' => $e->id,
                    'status' => $e->status,
                    'event_type' => $e->event_type,
                    'description' => $e->description,
                    'occurred_at' => $e->occurred_at?->toISOString(),
                    'location' => $e->location,
                    'weather_snapshot' => $e->weather_snapshot,
                ]),
            ],
        ]);
    }

    /**
     * Public tariff estimation calculation backed by database shipping_tariffs.
     */
    public function estimateTariff(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'origin_id' => ['required', 'exists:locations,id'],
            'destination_id' => ['required', 'exists:locations,id', 'different:origin_id'],
            'transport_mode' => ['required', 'string', 'in:SEA,AIR,LAND'],
            'weight_kg' => ['required', 'numeric', 'min:1', 'max:500000'],
        ]);

        $origin = Location::findOrFail($validated['origin_id']);
        $destination = Location::findOrFail($validated['destination_id']);

        try {
            $estimate = $this->tariffService->estimate(
                $origin,
                $destination,
                $validated['transport_mode'],
                (float) $validated['weight_kg']
            );

            return response()->json([
                'success' => true,
                'data' => $estimate,
            ]);
        } catch (DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
