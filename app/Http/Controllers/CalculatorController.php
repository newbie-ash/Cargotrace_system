<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\ShippingTariff;
use App\Services\TariffEstimatorService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CalculatorController extends Controller
{
    public function __construct(
        protected TariffEstimatorService $tariffService
    ) {}

    /**
     * Display the dedicated tariff and carbon footprint simulation tool.
     */
    public function index(): Response
    {
        $locations = Location::orderBy('name')->get(['id', 'code', 'name', 'city', 'country', 'type', 'latitude', 'longitude']);
        $tariffs = ShippingTariff::active()->get();

        return Inertia::render('Calculator/Index', [
            'locations' => $locations,
            'tariffs' => $tariffs,
        ]);
    }

    /**
     * Calculate shipping tariff and transit estimation.
     */
    public function estimate(Request $request): JsonResponse
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
