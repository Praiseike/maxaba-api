<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\ApiController;
use App\Models\MaintenanceMode;
use Request;

class MaintenanceModeController extends ApiController
{
    public function index()
    {
        $maintenance = MaintenanceMode::first();
        
        return response()->json([
            'data' => $maintenance ?? [
                'is_enabled' => false,
                'message' => '',
                'scheduled_start' => null,
                'scheduled_end' => null,
            ]
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'is_enabled' => 'required|boolean',
            'message' => 'nullable|string|max:1000',
            'scheduled_start' => 'nullable|date',
            'scheduled_end' => 'nullable|date|after:scheduled_start',
        ]);

        $maintenance = MaintenanceMode::first();
        
        if ($maintenance) {
            $maintenance->update($validated);
        } else {
            MaintenanceMode::create($validated);
        }

        return response()->json([
            'message' => 'Maintenance mode updated successfully',
            'data' => MaintenanceMode::getStatus()
        ]);
    }

    public function toggle()
    {
        $maintenance = MaintenanceMode::first();
        
        if ($maintenance) {
            $maintenance->update(['is_enabled' => !$maintenance->is_enabled]);
        } else {
            MaintenanceMode::create(['is_enabled' => true]);
        }

        return response()->json([
            'message' => 'Maintenance mode toggled successfully',
            'data' => MaintenanceMode::getStatus()
        ]);
    }
}
