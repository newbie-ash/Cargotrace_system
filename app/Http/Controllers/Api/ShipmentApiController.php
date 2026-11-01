<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ShipmentResource;
use App\Http\Resources\TrackingEventResource;
use App\Models\Shipment;
use App\Services\ShipmentService;
use App\Services\TrackingService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ShipmentApiController extends Controller
{
    /**
     * List shipments with server-side pagination and filters.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();

        $query = Shipment::with(['carrier', 'originLocation', 'destinationLocation', 'customer']);

        // RBAC: Customer only sees their own shipments
        if ($user->isCustomer()) {
            $query->where('customer_id', $user->id);
        } elseif ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        // Filters
        if ($request->filled('status')) {
            $query->where('current_status', strtoupper($request->status));
        }

        if ($request->filled('carrier_id')) {
            $query->where('carrier_id', $request->carrier_id);
        }

        if ($request->has('is_delayed')) {
            $query->where('is_delayed', $request->boolean('is_delayed'));
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('shipment_number', 'like', "%{$search}%")
                    ->orWhere('cargo_type', 'like', "%{$search}%");
            });
        }

        $perPage = min(max((int) $request->input('per_page', 15), 1), 100);
        $shipments = $query->latest('id')->paginate($perPage);

        return ShipmentResource::collection($shipments);
    }

    /**
     * Get detailed shipment information by shipment number.
     */
    public function show(Request $request, string $shipmentNumber): ShipmentResource
    {
        $shipment = Shipment::with([
            'carrier',
            'originLocation',
            'destinationLocation',
            'customer',
            'lineItems',
            'trackingEvents.location',
        ])->where('shipment_number', $shipmentNumber)->firstOrFail();

        // RBAC: Customer can only view their own shipment
        if ($request->user()->isCustomer() && $shipment->customer_id !== $request->user()->id) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat pengiriman ini.');
        }

        return new ShipmentResource($shipment);
    }

    /**
     * Create a new shipment (Admin/Operator only).
     */
    public function store(Request $request, ShipmentService $shipmentService): JsonResponse
    {
        if ($request->user()->isCustomer()) {
            abort(403, 'Customer tidak memiliki otorisasi untuk membuat shipment.');
        }

        $validated = $request->validate([
            'customer_id' => ['required', 'exists:users,id'],
            'carrier_id' => ['required', 'exists:carriers,id'],
            'origin_location_id' => ['required', 'exists:locations,id'],
            'destination_location_id' => ['required', 'exists:locations,id', 'different:origin_location_id'],
            'cargo_type' => ['required', 'string', 'max:255'],
            'expected_departure' => ['required', 'date'],
            'expected_arrival' => ['required', 'date', 'after:expected_departure'],
            'initial_checkpoint_at' => ['nullable', 'date'],
            'line_items' => ['nullable', 'array'],
            'line_items.*.item_description' => ['nullable', 'string', 'max:255'],
            'line_items.*.description' => ['nullable', 'string', 'max:255'],
            'line_items.*.sku' => ['nullable', 'string', 'max:50'],
            'line_items.*.hs_code' => ['nullable', 'string', 'max:20'],
            'line_items.*.quantity' => ['required_with:line_items', 'integer', 'min:1'],
            'line_items.*.package_type' => ['nullable', 'string', 'max:50'],
            'line_items.*.gross_weight_kg' => ['nullable', 'numeric', 'min:0'],
            'line_items.*.weight_kg' => ['nullable', 'numeric', 'min:0'],
            'line_items.*.volume_cbm' => ['nullable', 'numeric', 'min:0'],
            'line_items.*.declared_value_usd' => ['nullable', 'numeric', 'min:0'],
            'line_items.*.declared_value' => ['nullable', 'numeric', 'min:0'],
        ]);

        $rawItems = $request->input('line_items', $request->input('items', []));
        $formattedItems = [];

        foreach ($rawItems as $it) {
            $formattedItems[] = [
                'description' => $it['description'] ?? $it['item_description'] ?? 'Manifest Item',
                'sku' => $it['sku'] ?? $it['hs_code'] ?? null,
                'quantity' => (int) ($it['quantity'] ?? 1),
                'weight_kg' => (float) ($it['weight_kg'] ?? $it['gross_weight_kg'] ?? 0),
                'volume_cbm' => (float) ($it['volume_cbm'] ?? 0),
                'declared_value' => (float) ($it['declared_value'] ?? $it['declared_value_usd'] ?? 0),
            ];
        }

        unset($validated['line_items']);

        try {
            $shipment = $shipmentService->createShipment($validated, items: $formattedItems, creatorId: $request->user()->id);
            $shipment->load(['carrier', 'originLocation', 'destinationLocation', 'customer', 'lineItems', 'trackingEvents']);

            return (new ShipmentResource($shipment))
                ->response()
                ->setStatusCode(201);
        } catch (DomainException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Append a tracking checkpoint to a shipment (Admin/Operator only).
     */
    public function appendEvent(Request $request, string $shipmentNumber, TrackingService $trackingService): JsonResponse
    {
        if ($request->user()->isCustomer()) {
            abort(403, 'Customer tidak memiliki otorisasi untuk mencatat event tracking.');
        }

        $shipment = Shipment::where('shipment_number', $shipmentNumber)->firstOrFail();

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:CREATED,BOOKED,PICKED_UP,IN_TRANSIT,OUT_FOR_DELIVERY,DELIVERED,EXCEPTION,CANCELLED'],
            'location_id' => ['nullable', 'exists:locations,id'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'occurred_at' => ['required', 'date'],
            'event_type' => ['nullable', 'string', 'in:NORMAL,DELAY,EXCEPTION,CUSTOMS_HOLD,WEATHER_ALERT,ACCIDENT,ROUTE_CHANGE'],
            'description' => ['required', 'string', 'max:1000'],
        ]);

        try {
            $event = $trackingService->appendEvent($shipment, $validated, creatorId: $request->user()->id);
            $event->load('location');

            return (new TrackingEventResource($event))
                ->response()
                ->setStatusCode(201);
        } catch (DomainException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
