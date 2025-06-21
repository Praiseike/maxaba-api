<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthController extends ApiController
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $admin = Admin::where('email', $request->email)->first();

        if (!$admin || !Hash::check($request->password, $admin->password)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }


        $verificationCode = rand(1000, 9999);

        $admin->token = $verificationCode;
        $admin->save();


        Mail::raw("Your verification code is: $verificationCode", function ($message) use ($admin) {
            $message->to($admin->email)
                ->subject('Email Verification Code');
        });

        return $this->respondWithSuccess('Verification code sent to your email');
    }

    public function verifyCode(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'digits:4'],
        ]);

        $admin = Admin::where('email', $request->email)->first();

        // if (! $admin || $admin->token !== $request->code) {
        //     return response()->json([
        //         'message' => 'Invalid verification code'
        //     ], 401);
        // }

        // // Clear the token after successful verification
        $admin->token = null;
        $admin->save();

        // Create Sanctum token
        $token = $admin->createToken('admin-token')->plainTextToken;

        return $this->respondWithSuccess("Verification successful", [
            'token' => $token,
            'admin' => $admin,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out'
        ]);
    }


    public function resetPassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required'],
            'password' => ['required', 'string', 'confirmed'], 
        ]);
    
        $admin = Auth::user();
    
        if (!Hash::check($request->current_password, $admin->password)) {
            return $this->errorForbidden('Current password is incorrect');
        }
    
        $admin->password = Hash::make($request->password);
        $admin->save();
    
        return $this->respondWithSuccess('Password reset successfully');
    }
}
