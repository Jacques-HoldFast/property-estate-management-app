<?php

namespace App\Http\Controllers;

use App\Models\Resident;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ResidentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        try {
            $residents = Resident::with('property')->orderBy('created_at', 'desc')->get();
            
            return response()->json([
                'success' => true,
                'data' => $residents
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch residents: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'property_id' => 'required|exists:properties,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:residents,email',
            'phone' => 'nullable|string|max:20',
            'id_number' => 'required|string|unique:residents,id_number|max:13',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'occupation' => 'nullable|string|max:255',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'lease_start_date' => 'required|date',
            'lease_end_date' => 'nullable|date|after:lease_start_date',
            'monthly_rent' => 'required|numeric|min:0',
            'deposit_amount' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,inactive,pending,terminated',
            'notes' => 'nullable|string'
        ]);

        try {
            $resident = Resident::create($validated);
            $resident->load('property');
            
            return response()->json([
                'message' => 'Resident created successfully',
                'resident' => $resident
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create resident. Please try again.'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $resident = Resident::findOrFail($id);
            $residentName = $resident->full_name;
            
            $resident->delete();
            
            return response()->json([
                'message' => "Resident '{$residentName}' has been deleted successfully"
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Resident not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete resident. Please try again.'
            ], 500);
        }
    }
}
