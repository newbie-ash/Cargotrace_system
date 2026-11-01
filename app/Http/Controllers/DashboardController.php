<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $stats = [
            'total_shipments' => $user->isCustomer() ? $user->shipments()->count() : Shipment::count(),
            'active_carriers' => Carrier::active()->count(),
            'total_locations' => Location::count(),
            'total_users' => User::count(),
            'recent_audits' => $user->isAdmin() ? AuditLog::with('user')->latest('id')->take(6)->get() : [],
        ];

        return Inertia::render('Dashboard/Index', [
            'stats' => $stats,
        ]);
    }
}
