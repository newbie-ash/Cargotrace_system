<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DocumentController extends Controller
{
    /**
     * Display a centralized archive of Electronic Bill of Lading (e-B/L) documents.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        $query = Shipment::with([
            'carrier',
            'originLocation',
            'destinationLocation',
            'customer',
            'items',
            'creator',
        ]);

        if ($user->isCustomer()) {
            $query->where('customer_id', $user->id);
        }

        if ($request->filled('search')) {
            $search = trim($request->query('search'));
            $query->where(function ($q) use ($search) {
                $q->where('shipment_number', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%")
                  ->orWhere('cargo_type', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('current_status', $request->query('status'));
        }

        $documents = $query->latest('id')->paginate(10)->withQueryString();

        return Inertia::render('Documents/Index', [
            'documents' => $documents,
            'filters' => $request->only(['search', 'status']),
        ]);
    }
}
