<?php

namespace App\Http\Controllers;

use App\Http\Requests\CarrierRequest;
use App\Models\Carrier;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CarrierController extends Controller
{
    public function __construct(
        protected AuditLogService $auditLogService
    ) {}

    public function index(Request $request): View
    {
        $query = Carrier::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('mode')) {
            $query->where('transport_mode', $request->input('mode'));
        }

        $carriers = $query->withCount('shipments')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('carriers.index', compact('carriers'));
    }

    public function create(): View
    {
        return view('carriers.create');
    }

    public function store(CarrierRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['is_active'] = $request->boolean('is_active', true);

        $carrier = Carrier::create($validated);

        $this->auditLogService->log('carrier.created', $carrier, null, $carrier->toArray());

        return redirect()->route('carriers.index')
            ->with('success', "Carrier {$carrier->name} ({$carrier->code}) created successfully.");
    }

    public function show(Carrier $carrier): View
    {
        $carrier->loadCount('shipments');

        return view('carriers.show', compact('carrier'));
    }

    public function edit(Carrier $carrier): View
    {
        return view('carriers.edit', compact('carrier'));
    }

    public function update(CarrierRequest $request, Carrier $carrier): RedirectResponse
    {
        $oldValues = $carrier->toArray();
        $validated = $request->validated();
        $validated['is_active'] = $request->boolean('is_active', false);

        $carrier->update($validated);

        $this->auditLogService->log('carrier.updated', $carrier, $oldValues, $carrier->fresh()->toArray());

        return redirect()->route('carriers.index')
            ->with('success', "Carrier {$carrier->name} updated successfully.");
    }

    public function destroy(Carrier $carrier): RedirectResponse
    {
        if ($carrier->shipments()->exists()) {
            return back()->with('error', "Cannot delete carrier {$carrier->name} because active shipments are associated with it.");
        }

        $oldValues = $carrier->toArray();
        $carrier->delete();

        $this->auditLogService->log('carrier.deleted', $carrier, $oldValues, null);

        return redirect()->route('carriers.index')
            ->with('success', "Carrier {$carrier->name} deleted successfully.");
    }
}
