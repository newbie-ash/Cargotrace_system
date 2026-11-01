<?php

namespace App\Http\Controllers;

use App\Http\Requests\ShipmentRequest;
use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\ShipmentService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShipmentController extends Controller
{
    public function __construct(
        protected ShipmentService $shipmentService,
        protected AuditLogService $auditLogService
    ) {}

    public function index(Request $request): Response
    {
        $user = auth()->user();
        $query = Shipment::with(['carrier', 'originLocation', 'destinationLocation', 'customer']);

        // Customer access isolation
        if ($user->isCustomer()) {
            $query->where('customer_id', $user->id);
        }

        // Multi-parameter Filters
        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('shipment_number', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%")
                  ->orWhere('cargo_type', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('current_status', $request->query('status'));
        }

        if ($request->filled('carrier_id')) {
            $query->where('carrier_id', $request->query('carrier_id'));
        }

        if ($request->filled('origin_id')) {
            $query->where('origin_location_id', $request->query('origin_id'));
        }

        if ($request->filled('destination_id')) {
            $query->where('destination_location_id', $request->query('destination_id'));
        }

        if ($request->filled('delay')) {
            if ($request->query('delay') === 'delayed') {
                $query->where('is_delayed', true);
            } elseif ($request->query('delay') === 'on_time') {
                $query->where('is_delayed', false);
            }
        }

        if ($request->filled('date_from')) {
            $query->whereDate('expected_departure', '>=', $request->query('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('expected_departure', '<=', $request->query('date_to'));
        }

        // Dynamic Sorting
        $allowedSorts = ['id', 'shipment_number', 'expected_departure', 'expected_arrival', 'current_status', 'is_delayed'];
        $sortBy = in_array($request->query('sort_by'), $allowedSorts, true) ? $request->query('sort_by') : 'id';
        $sortDirection = strtolower($request->query('sort_direction')) === 'asc' ? 'asc' : 'desc';

        $query->orderBy($sortBy, $sortDirection);

        $shipments = $query->paginate(15)->withQueryString();
        $carriers = Carrier::where('is_active', true)->orderBy('name')->get();
        $locations = Location::where('is_active', true)->orderBy('name')->get();

        return Inertia::render('Shipments/Index', [
            'shipments' => $shipments,
            'carriers' => $carriers,
            'locations' => $locations,
            'filters' => $request->only([
                'search', 'status', 'carrier_id', 'origin_id', 'destination_id',
                'delay', 'date_from', 'date_to', 'sort_by', 'sort_direction'
            ]),
        ]);
    }

    public function create(): Response
    {
        $this->authorizeAdminOrOperator();

        $carriers = Carrier::where('is_active', true)->orderBy('name')->get();
        $locations = Location::where('is_active', true)->orderBy('name')->get();
        $customers = User::where('role', 'customer')->orderBy('name')->get();

        return Inertia::render('Shipments/Create', [
            'carriers' => $carriers,
            'locations' => $locations,
            'customers' => $customers,
        ]);
    }

    public function store(ShipmentRequest $request): RedirectResponse
    {
        $this->authorizeAdminOrOperator();

        $data = $request->safe()->except('items');
        $items = $request->input('items', []);

        $shipment = $this->shipmentService->createShipment($data, $items);

        return redirect()->route('shipments.show', $shipment)
            ->with('success', "Shipment {$shipment->shipment_number} successfully registered.");
    }

    public function show(Shipment $shipment): Response
    {
        $user = auth()->user();
        if ($user->isCustomer() && $shipment->customer_id !== $user->id) {
            abort(403, 'Unauthorized access to this shipment.');
        }

        $shipment->load([
            'carrier',
            'originLocation',
            'destinationLocation',
            'customer',
            'creator',
            'items',
            'trackingEvents.location',
            'trackingEvents.creator',
            'exceptions',
        ]);

        $locations = Location::where('is_active', true)->orderBy('name')->get();

        return Inertia::render('Shipments/Show', [
            'shipment' => $shipment,
            'locations' => $locations,
        ]);
    }

    public function edit(Shipment $shipment): Response
    {
        $this->authorizeAdminOrOperator();

        $carriers = Carrier::where('is_active', true)->orderBy('name')->get();
        $locations = Location::where('is_active', true)->orderBy('name')->get();
        $customers = User::where('role', 'customer')->orderBy('name')->get();

        return Inertia::render('Shipments/Edit', [
            'shipment' => $shipment,
            'carriers' => $carriers,
            'locations' => $locations,
            'customers' => $customers,
        ]);
    }

    public function update(Request $request, Shipment $shipment): RedirectResponse
    {
        $this->authorizeAdminOrOperator();

        $validated = $request->validate([
            'reference_number' => ['nullable', 'string', 'max:100'],
            'carrier_id' => ['required', 'exists:carriers,id'],
            'expected_arrival' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $old = $shipment->toArray();
        $shipment->update($validated);
        $this->auditLogService->log('shipment.updated', $shipment, $old, $shipment->fresh()->toArray());

        return redirect()->route('shipments.show', $shipment)
            ->with('success', "Shipment {$shipment->shipment_number} updated successfully.");
    }

    public function transition(Request $request, Shipment $shipment): RedirectResponse
    {
        $this->authorizeAdminOrOperator();

        $validated = $request->validate([
            'status' => ['required', 'string'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $this->shipmentService->transitionStatus($shipment, $validated['status'], $validated['reason'] ?? null);

            return redirect()->route('shipments.show', $shipment)
                ->with('success', "Shipment status changed to {$validated['status']}.");
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy(Shipment $shipment): RedirectResponse
    {
        $user = auth()->user();
        if (! $user->isAdmin()) {
            abort(403, 'Only administrators can delete shipments.');
        }

        $old = $shipment->toArray();
        $number = $shipment->shipment_number;

        // Cascade delete relations
        $shipment->items()->delete();
        $shipment->trackingEvents()->delete();
        $shipment->delete();

        $this->auditLogService->log('shipment.deleted', null, $old, null);

        return redirect()->route('shipments.index')
            ->with('success', "Shipment {$number} deleted successfully.");
    }

    protected function authorizeAdminOrOperator(): void
    {
        $user = auth()->user();
        if (! $user || (! $user->isAdmin() && ! $user->isOperator())) {
            abort(403, 'Unauthorized. Requires administrator or operator privileges.');
        }
    }
}
