<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $shipmentQuery = Shipment::query();
        if ($user->isCustomer()) {
            $shipmentQuery->where('customer_id', $user->id);
        }

        $totalShipments = (clone $shipmentQuery)->count();
        $inTransit = (clone $shipmentQuery)->where('current_status', 'IN_TRANSIT')->count();
        $delivered = (clone $shipmentQuery)->where('current_status', 'DELIVERED')->count();
        $delayed = (clone $shipmentQuery)->where('is_delayed', true)->count();

        // Exception count
        $exceptionQuery = ShipmentException::where('status', 'OPEN');
        if ($user->isCustomer()) {
            $exceptionQuery->whereHas('shipment', fn ($q) => $q->where('customer_id', $user->id));
        }
        $openExceptions = $exceptionQuery->count();

        $delayRate = $totalShipments > 0 ? round(($delayed / $totalShipments) * 100, 1) : 0;
        $exceptionRate = $totalShipments > 0 ? round(($openExceptions / $totalShipments) * 100, 1) : 0;

        // Status Distribution
        $statusCounts = (clone $shipmentQuery)
            ->select('current_status', DB::raw('count(*) as count'))
            ->groupBy('current_status')
            ->pluck('count', 'current_status')
            ->toArray();

        $allStatuses = ['CREATED', 'BOOKED', 'PICKED_UP', 'IN_TRANSIT', 'DELIVERED', 'CANCELLED'];
        $statusDistribution = [];
        foreach ($allStatuses as $st) {
            $statusDistribution[$st] = (int) ($statusCounts[$st] ?? 0);
        }

        // Carrier Distribution
        $carrierCounts = (clone $shipmentQuery)
            ->join('carriers', 'shipments.carrier_id', '=', 'carriers.id')
            ->select('carriers.name', DB::raw('count(shipments.id) as count'))
            ->groupBy('carriers.name')
            ->orderByDesc('count')
            ->take(6)
            ->pluck('count', 'name')
            ->toArray();

        // Recent Shipments
        $recentShipments = (clone $shipmentQuery)
            ->with(['carrier', 'originLocation', 'destinationLocation'])
            ->latest('id')
            ->take(5)
            ->get();

        $stats = [
            'total_shipments' => $totalShipments,
            'in_transit' => $inTransit,
            'delivered' => $delivered,
            'delayed' => $delayed,
            'open_exceptions' => $openExceptions,
            'delay_rate' => $delayRate,
            'exception_rate' => $exceptionRate,
            'active_carriers' => Carrier::active()->count(),
            'total_locations' => Location::count(),
            'total_users' => User::count(),
            'status_distribution' => $statusDistribution,
            'carrier_distribution' => $carrierCounts,
            'recent_shipments' => $recentShipments,
            'recent_audits' => $user->isAdmin() ? AuditLog::with('user')->latest('id')->take(6)->get() : [],
        ];

        return Inertia::render('Dashboard/Index', [
            'stats' => $stats,
        ]);
    }
}
