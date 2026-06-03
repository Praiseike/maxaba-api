<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceMode;
use Illuminate\Http\Request;

class ServerController extends Controller
{
    public function index(){
        return response()->json([
            'status' => 'success',
            'message' => 'Welcome to MAXABA API V1, may your requests be successful',
            'system_message' => 'Server is running',
        ]);
    }

    public function status()
    {
        $status = MaintenanceMode::getStatus();
        
        return response()->json([
            'data' => $status
        ]);
    }

    public function getBranding()
    {
        $logoUrl = null;
        $faviconUrl = null;

        // Search public directory for logo.*
        $logoFiles = glob(public_path('logo.*'));
        if (!empty($logoFiles)) {
            $logoUrl = url(basename($logoFiles[0]));
        }

        // Search public directory for favicon.*
        $faviconFiles = glob(public_path('favicon.*'));
        if (!empty($faviconFiles)) {
            $faviconUrl = url(basename($faviconFiles[0]));
        }

        return response()->json([
            'logo_url' => $logoUrl,
            'favicon_url' => $faviconUrl,
        ]);
    }
}
