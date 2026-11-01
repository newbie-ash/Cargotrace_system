<?php

namespace App\Http\Controllers;

use App\Http\Requests\TrackingEventRequest;
use App\Models\Shipment;
use App\Services\TrackingService;
use DomainException;

class TrackingEventController extends Controller
{
    public function __construct(
        protected TrackingService $trackingService
    ) {}

    public function store(TrackingEventRequest $request, Shipment $shipment)
    {
        $user = auth()->user();
        if (! $user || (! $user->isAdmin() && ! $user->isOperator())) {
            abort(403, 'Unauthorized. Checkpoint registration requires administrator or operator privileges.');
        }

        try {
            $this->trackingService->appendEvent($shipment, $request->validated());

            return redirect()->route('shipments.show', $shipment)
                ->with('success', 'Checkpoint tracking event successfully recorded.');
        } catch (DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }
}
