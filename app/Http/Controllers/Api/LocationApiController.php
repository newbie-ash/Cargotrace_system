<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\LocationResource;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LocationApiController extends Controller
{
    /**
     * List locations with geo coordinates.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Location::query();

        if ($request->filled('type')) {
            $query->where('type', strtoupper($request->type));
        }

        if ($request->filled('country')) {
            $query->where('country', $request->country);
        }

        $locations = $query->orderBy('name')->get();

        return LocationResource::collection($locations);
    }
}
