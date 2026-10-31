<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $stats = [
            'total_shipments' => Shipment::count(),
            'active_carriers' => Carrier::active()->count(),
            'total_locations' => Location::count(),
            'total_users' => User::count(),
            'recent_audits' => $user->isAdmin() ? AuditLog::with('user')->latest('id')->take(6)->get() : collect(),
        ];

        return view('dashboard.index', compact('stats'));
    }
}
