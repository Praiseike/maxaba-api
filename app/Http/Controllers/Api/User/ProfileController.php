<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class ProfileController extends ApiController
{

    public function getUser(Request $request, User $user = null)
    {
        $user ??= $request->user();
        if(!$user) {
            return $this->respondWithError("User not found", 404);
        }
        return $this->respondWithSuccess("User profile", [
            "id" => $user->id,
            "uuid" => $user->uuid,
            "first_name" => $user->first_name,
            "last_name" => $user->last_name,
            "phone_number" => $user->phone_number,
            "profile_image_url" => $user->profile_image_url,
            "listings" => $user->properties->count(),
            "ethnicity" => $user->ethnicity,
            'account_type' => $user->account_type,
            'followers' => $user->followers()->count(),
            'following' => $user->following()->count(),
            'is_following' => (bool) $request->user()?->isFollowing($user) ,
            'bio' => $user->bio,
            'properties' => $user->properties()->with('category')->get(),
            'location' => $user->location,
        ]);

    }
    public function index(Request $request)
    {
        $user = $request->user();
        return $this->getUser($request, $user);
    }

    public function save(Request $request)
    {
        $request->validate([
            "first_name" => "required|string",
            "last_name" => "required|string",
            "ethnicity" => "nullable|string",
            "phone_number" => "required|string",
            "password" => "required|string",
            "password_confirmation" => "required|string|same:password",
        ]);


        $user = $request->user();
        $user->update($request->only("first_name", "ethnicity" , "last_name", "phone_number", "password"));
        $user->save();

        return $this->respondWithSuccess("Updated profile", $user);
    }

    public function uploadProfilePic(Request $request)
    {
        $user = $request->user();

        $request->validate([
            "profile_image" => "required|image|mimes:jpeg,png,jpg,gif|max:2048",
        ]);

        if ($user->profile_image) {
            $oldProfilePic = $user->profile_image;
            \Storage::disk('local')->delete($oldProfilePic);
        }

        $user->profile_image = $request->file("profile_image")->store("profile_images", "public");
        $user->save();
        return $this->respondWithSuccess("Profile picture updated successfully", [
            "profile_image" => url("/storage/" . $user->profile_image)
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $request->validate([
            //     "first_name" => "sometimes|nullable|string|max:255", 
            //     "last_name" => "sometimes|nullable|string|max:255",  
            "phone_number" => "sometimes|nullable|string|max:15",
            "address" => "sometimes|nullable|string|max:255",
            "ethnicity" => "sometimes|nullable|string|max:255",
            "location" => "sometimes|nullable|string|max:255",
            "bio" => "sometimes|nullable|string|max:500",
            "password" => "sometimes|nullable|string|min:8|confirmed",
        ]);

        $user->update($request->only([
            // "first_name",
            // "last_name",
            "ethnicity",
            "phone_number",
            "address",
            "location",
            "bio",
        ]));

        // Update password if provided
        if ($request->filled('password')) {
            $user->password = bcrypt($request->password);
        }

        $user->save();

        return $this->respondWithSuccess("Profile updated successfully", [
            "first_name" => $user->first_name,
            "last_name" => $user->last_name,
            "ethnicity" => $user->ethnicity,
            "phone_number" => $user->phone_number,
            "address" => $user->address,
            "location" => $user->location,
            "bio" => $user->bio,
            "profile_image_url" => $user->profile_image_url,
        ]);
    }
}
