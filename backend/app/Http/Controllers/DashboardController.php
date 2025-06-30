<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\Resident;
use App\Models\MaintenanceRequest;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics
     */
    public function stats(): JsonResponse
    {
        // Property statistics
        $totalProperties = Property::count();
        $propertiesByStatus = Property::select('status')
            ->selectRaw('count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Resident statistics
        $totalResidents = Resident::count();
        $residentsByStatus = Resident::select('status')
            ->selectRaw('count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Recent activity
        $recentResidents = Resident::with('property')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentProperties = Property::orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Maintenance request statistics
        $totalMaintenanceRequests = MaintenanceRequest::count();
        $maintenanceByStatus = MaintenanceRequest::select('status')
            ->selectRaw('count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $maintenanceByPriority = MaintenanceRequest::select('priority')
            ->selectRaw('count(*) as count')
            ->groupBy('priority')
            ->pluck('count', 'priority')
            ->toArray();

        $urgentMaintenance = MaintenanceRequest::where('priority', 'urgent')
            ->whereIn('status', ['pending', 'assigned', 'in_progress'])
            ->count();

        $recentMaintenance = MaintenanceRequest::with(['property', 'resident'])
            ->orderBy('reported_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'properties' => [
                'total' => $totalProperties,
                'by_status' => $propertiesByStatus,
                'recent' => $recentProperties
            ],
            'residents' => [
                'total' => $totalResidents,
                'by_status' => $residentsByStatus,
                'recent' => $recentResidents
            ],
            'maintenance' => [
                'total' => $totalMaintenanceRequests,
                'by_status' => $maintenanceByStatus,
                'by_priority' => $maintenanceByPriority,
                'urgent_open' => $urgentMaintenance,
                'recent' => $recentMaintenance
            ]
        ]);
    }
}
