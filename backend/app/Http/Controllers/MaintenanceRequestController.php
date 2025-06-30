<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class MaintenanceRequestController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = MaintenanceRequest::with(['property', 'resident']);

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('priority')) {
                $query->where('priority', $request->priority);
            }

            if ($request->has('category')) {
                $query->where('category', $request->category);
            }

            if ($request->has('property_id')) {
                $query->where('property_id', $request->property_id);
            }

            $maintenanceRequests = $query->orderBy('reported_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => $maintenanceRequests
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch maintenance requests: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'property_id' => 'required|exists:properties,id',
                'resident_id' => 'required|exists:residents,id',
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'category' => 'required|in:plumbing,electrical,hvac,appliances,structural,painting,landscaping,security,cleaning,other',
                'priority' => 'required|in:low,medium,high,urgent',
                'estimated_cost' => 'nullable|numeric|min:0',
                'notes' => 'nullable|string',
            ]);

            $validatedData['status'] = 'pending';
            $validatedData['reported_at'] = Carbon::now();

            $maintenanceRequest = MaintenanceRequest::create($validatedData);
            $maintenanceRequest->load(['property', 'resident']);

            return response()->json([
                'success' => true,
                'message' => 'Maintenance request created successfully',
                'data' => $maintenanceRequest
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create maintenance request: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(MaintenanceRequest $maintenanceRequest): JsonResponse
    {
        try {
            $maintenanceRequest->load(['property', 'resident']);
            
            return response()->json([
                'success' => true,
                'data' => $maintenanceRequest
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch maintenance request: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, MaintenanceRequest $maintenanceRequest): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'title' => 'sometimes|string|max:255',
                'description' => 'sometimes|string',
                'category' => 'sometimes|in:plumbing,electrical,hvac,appliances,structural,painting,landscaping,security,cleaning,other',
                'priority' => 'sometimes|in:low,medium,high,urgent',
                'status' => 'sometimes|in:pending,assigned,in_progress,on_hold,completed,cancelled',
                'estimated_cost' => 'nullable|numeric|min:0',
                'actual_cost' => 'nullable|numeric|min:0',
                'assigned_to' => 'nullable|string|max:255',
                'notes' => 'nullable|string',
            ]);

            if (isset($validatedData['status'])) {
                $newStatus = $validatedData['status'];
                $currentStatus = $maintenanceRequest->status;

                if ($newStatus === 'assigned' && $currentStatus === 'pending') {
                    $validatedData['assigned_at'] = Carbon::now();
                } elseif ($newStatus === 'in_progress' && in_array($currentStatus, ['assigned', 'on_hold'])) {
                    $validatedData['started_at'] = Carbon::now();
                } elseif ($newStatus === 'completed' && $currentStatus !== 'completed') {
                    $validatedData['completed_at'] = Carbon::now();
                }
            }

            $maintenanceRequest->update($validatedData);
            $maintenanceRequest->load(['property', 'resident']);

            return response()->json([
                'success' => true,
                'message' => 'Maintenance request updated successfully',
                'data' => $maintenanceRequest
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update maintenance request: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(MaintenanceRequest $maintenanceRequest): JsonResponse
    {
        try {
            $maintenanceRequest->delete();

            return response()->json([
                'success' => true,
                'message' => 'Maintenance request deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete maintenance request: ' . $e->getMessage()
            ], 500);
        }
    }
}
