<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
}
