<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Mail\VerificationTokenMail;

class AuthController extends ApiController
{

    public function login(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
        ]);

        $user = User::firstOrCreate(
            ['email' => $request->email],
            ['account_type', 'user', 'account_status' => 'pending']
        );

        $verificationToken = rand(1000, 9999);
        $user->verification_token = $verificationToken;
        $user->save();


        Mail::to($user->email)->send(new VerificationTokenMail($verificationToken));


        return $this->respondWithSuccess('Verification token sent to your email.');
    }
    public function verifyToken(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
            'token' => 'required|numeric',
        ]);
    
        $user = User::where('email', $request->email)
                    // ->where('verification_token', $request->token)
                    ->first();
    
        if (!$user) {
            return $this->respondWithError('Invalid token or email.', 401);
        }
    

        $user->verification_token = null;
        $user->save();
    
        $token = $user->createToken('auth')->plainTextToken;
    
        return $this->respondWithSuccess('Email verified successfully.', [
            'user' => $user,
            'token' => $token,
        ]);
    }

}
