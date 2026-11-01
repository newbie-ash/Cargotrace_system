<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CarrierResource;
use App\Models\Carrier;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CarrierApiController extends Controller
{
    /**
     * List active carriers.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Carrier::query();

        if ($request->boolean('active_only', true)) {
            $query->active();
        }

        if ($request->filled('mode')) {
            $query->where('transport_mode', strtoupper($request->mode));
        }

        $carriers = $query->orderBy('name')->get();

        return CarrierResource::collection($carriers);
    }
}
