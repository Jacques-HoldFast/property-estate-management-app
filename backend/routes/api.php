<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MaintenanceRequestController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\ResidentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('dashboard/stats', [DashboardController::class, 'stats']);
    Route::apiResource('properties', PropertyController::class);
    Route::apiResource('residents', ResidentController::class);
    Route::apiResource('maintenance-requests', MaintenanceRequestController::class);
});
