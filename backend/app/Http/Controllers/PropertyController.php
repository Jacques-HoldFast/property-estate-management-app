<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PropertyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        try {
            $properties = Property::orderBy('created_at', 'desc')->get();
            
            return response()->json([
                'success' => true,
                'data' => $properties
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch properties: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'bedrooms' => 'required|integer|min:0',
            'bathrooms' => 'required|integer|min:1',
            'parking_spaces' => 'integer|min:0|nullable',
            'size_sqm' => 'required|numeric|min:1',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'province' => 'required|string|max:255',
            'postal_code' => 'required|string|max:10',
            'latitude' => 'numeric|nullable',
            'longitude' => 'numeric|nullable',
            'status' => 'string|in:available,sold,rented,under_offer|nullable',
            'is_featured' => 'boolean|nullable',
        ]);

        // Set defaults for optional fields
        $validated['parking_spaces'] = $validated['parking_spaces'] ?? 0;
        $validated['status'] = $validated['status'] ?? 'available';
        $validated['is_featured'] = $validated['is_featured'] ?? false;

        $property = Property::create($validated);

        return response()->json([
            'message' => 'Property created successfully',
            'property' => $property
        ], 201);
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
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $property = Property::findOrFail($id);
            
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'type' => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
                'bedrooms' => 'required|integer|min:0',
                'bathrooms' => 'required|integer|min:1',
                'parking_spaces' => 'integer|min:0|nullable',
                'size_sqm' => 'required|numeric|min:1',
                'address' => 'required|string|max:255',
                'city' => 'required|string|max:255',
                'province' => 'required|string|max:255',
                'postal_code' => 'required|string|max:10',
                'latitude' => 'numeric|nullable',
                'longitude' => 'numeric|nullable',
                'status' => 'string|in:available,sold,rented,under_offer|nullable',
                'is_featured' => 'boolean|nullable',
            ]);

            // Set defaults for optional fields
            $validated['parking_spaces'] = $validated['parking_spaces'] ?? 0;
            $validated['status'] = $validated['status'] ?? 'available';
            $validated['is_featured'] = $validated['is_featured'] ?? false;

            $property->update($validated);

            return response()->json([
                'message' => 'Property updated successfully',
                'property' => $property->fresh()
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Property not found'
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update property. Please try again.'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $property = Property::findOrFail($id);
            $propertyTitle = $property->title;
            
            $property->delete();
            
            return response()->json([
                'message' => "Property '{$propertyTitle}' has been deleted successfully"
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Property not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete property. Please try again.'
            ], 500);
        }
    }
}
