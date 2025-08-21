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
}
