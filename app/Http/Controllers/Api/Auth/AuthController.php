<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Controller;
use Google_Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Mail\VerificationTokenMail;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends ApiController
{

    public function login(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
        ]);

        $user = User::firstOrCreate(
            ['email' => $request->email],
            ['account_type' => 'user', 'account_status' => 'pending']
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
                    ->where('verification_token', $request->token)
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

    /**
     * Handle Google OAuth callback (for redirect flow)
     */
    public function handleGoogleCallback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            
            $user = $this->findOrCreateGoogleUser($googleUser);
            $token = $user->createToken('google-auth')->plainTextToken;

            return $this->respondWithSuccess('Google login successful.', [
                'user' => $user,
                'token' => $token
            ]);

        } catch (\Exception $e) {
            return $this->respondWithError('Google authentication failed.', 400);
        }
    }

    /**
     * Verify Google ID token directly (for client-side integration)
     */
    public function verifyGoogleToken(Request $request)
    {
        $this->validate($request, [
            'token' => 'required|string'
        ]);

        try {
            // Verify Google ID token
            $client = new \Google_Client(['client_id' => config('services.google.client_id')]);
            $payload = $client->verifyIdToken($request->token);
            
            if (!$payload) {
                return $this->respondWithError('Invalid Google token.', 400);
            }

            // Create user object from Google payload
            $googleUser = (object) [
                'id' => $payload['sub'],
                'email' => $payload['email'],
                'name' => $payload['name'],
                'given_name' => $payload['given_name'] ?? '',
                'family_name' => $payload['family_name'] ?? '',
                'picture' => $payload['picture'] ?? null,
                'email_verified' => $payload['email_verified'] ?? false,
            ];

            // Only allow verified Google emails
            if (!$googleUser->email_verified) {
                return $this->respondWithError('Please use a verified Google account.', 400);
            }

            $user = $this->findOrCreateGoogleUser($googleUser);
            $token = $user->createToken('google-auth')->plainTextToken;

            return $this->respondWithSuccess('Google login successful.', [
                'user' => $user,
                'token' => $token
            ]);

        } catch (Google_Exception $e) {
            return $this->respondWithError('Google token verification failed.', 400);
        } catch (\Exception $e) {
            return $this->respondWithError('Authentication failed.', 500);
        }
    }

    /**
     * Find or create user from Google data
     */
    private function findOrCreateGoogleUser($googleUser)
    {
        // First try to find by email or google_id
        $user = User::where('email', $googleUser->email)
            ->orWhere('google_id', $googleUser->id)
            ->first();

        if ($user) {
            // Update Google ID if not set
            if (!$user->google_id) {
                $user->update([
                    'google_id' => $googleUser->id,
                    'email_verified_at' => now(), // Mark as verified since Google verified it
                ]);
            }

            // Update profile image if user doesn't have one and Google provides one
            if (!$user->profile_image && isset($googleUser->picture)) {
                $user->update(['profile_image' => $googleUser->picture]);
            }
        } else {
            // Create new user
            $user = User::create([
                'email' => $googleUser->email,
                'first_name' => $googleUser->given_name ?? explode(' ', $googleUser->name)[0] ?? '',
                'last_name' => $googleUser->family_name ?? (explode(' ', $googleUser->name)[1] ?? ''),
                'google_id' => $googleUser->id,
                'profile_image' => $googleUser->picture ?? null,
                'email_verified_at' => now(), // Google has already verified the email
                'account_type' => User::TYPE_USER,
                'account_status' => 'active', // Skip pending status for Google users
            ]);
        }

        return $user;
    }

    /**
     * Get Google OAuth redirect URL (optional - for redirect flow)
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')
            // ->scopes(['email', 'profile'])
            ->redirect();
    }
}