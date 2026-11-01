<?php

namespace App\Http\Controllers;

use App\Http\Requests\LocationRequest;
use App\Models\Location;
use App\Services\AuditLogService;
use App\Services\ExternalIntegrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LocationController extends Controller
{
    public function __construct(
        protected AuditLogService $auditLogService
    ) {}

    public function index(Request $request): Response
    {
        $query = Location::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('country', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        $locations = $query->withCount(['originShipments', 'destinationShipments'])
            ->orderBy('country')
            ->orderBy('city')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Locations/Index', [
            'locations' => $locations,
            'filters' => $request->only(['search', 'type']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Locations/Create');
    }

    public function store(LocationRequest $request): RedirectResponse
    {
        $location = Location::create($request->validated());

        $this->auditLogService->log('location.created', $location, null, $location->toArray());

        return redirect()->route('locations.index')
            ->with('success', "Location {$location->name} ({$location->code}) added successfully.");
    }

    public function show(Location $location): Response
    {
        $location->loadCount(['originShipments', 'destinationShipments', 'trackingEvents']);

        return Inertia::render('Locations/Show', [
            'location' => $location,
        ]);
    }

    public function edit(Location $location): Response
    {
        return Inertia::render('Locations/Edit', [
            'location' => $location,
        ]);
    }

    public function update(LocationRequest $request, Location $location): RedirectResponse
    {
        $oldValues = $location->toArray();
        $location->update($request->validated());

        $this->auditLogService->log('location.updated', $location, $oldValues, $location->fresh()->toArray());

        return redirect()->route('locations.index')
            ->with('success', "Location {$location->name} updated successfully.");
    }

    public function lookupCoordinates(Request $request, ExternalIntegrationService $integrationService): JsonResponse
    {
        $request->validate(['query' => 'required|string|max:200']);
        $result = $integrationService->geocode($request->input('query'));

        if (! $result) {
            return response()->json([
                'status' => 'error',
                'message' => 'Koordinat tidak ditemukan untuk lokasi tersebut.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $result,
        ]);
    }

    public function destroy(Location $location): RedirectResponse
    {
        if ($location->originShipments()->exists() || $location->destinationShipments()->exists()) {
            return back()->with('error', "Cannot delete location {$location->name} because active shipments use it as origin or destination.");
        }

        $oldValues = $location->toArray();
        $location->delete();

        $this->auditLogService->log('location.deleted', $location, $oldValues, null);

        return redirect()->route('locations.index')
            ->with('success', "Location {$location->name} deleted successfully.");
    }
}
