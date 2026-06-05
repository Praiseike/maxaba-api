<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\ApiController;
use App\Models\RoommateRequest;
use Illuminate\Http\Request;

class RoommateController extends ApiController
{
    public function index(Request $request)
    {
        $query = RoommateRequest::with('user');
    
        if ($request->filled('min_price') || $request->filled('max_price')) {
            $min = $request->min_price ?? 0;
            $max = $request->max_price ?? PHP_INT_MAX;
    
            $query->where(function ($q) use ($min, $max) {
                $q->where('min_price', '<=', $max)
                  ->where('max_price', '>=', $min);
            });
        }
    
        if ($request->filled('location')) {
            $query->where('location', 'like', '%' . $request->location . '%');
        }
    
        if ($request->filled('ethnicity')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('ethnicity', $request->ethnicity);
            });
        }
    
        // ✅ Prioritize authenticated user's requests
        $userId = auth()->id();
        $query->orderByRaw("CASE WHEN user_id = ? THEN 0 ELSE 1 END", [$userId])
              ->latest();
    
        $requests = $query->paginate(10);
    
        return $this->respondWithSuccess("Fetched roommates", $requests);
    }
    
    public function show($id)
    {
        $roommateRequest = RoommateRequest::with('user')->find($id);
        
        if (!$roommateRequest) {
            return $this->respondWithError("Roommate request not found", 404);
        }

        return $this->respondWithSuccess("Fetched roommate", $roommateRequest);
    }
    
    public function store(Request $request){
        $validated = $request->validate([
            "name" => "required|string",
            "location" => "required|string",
            "gender" => "required|string|in:male,female,other",
            "min_price" => "required|numeric",
            "max_price" => "required|numeric",
            "category_id" => "required|exists:categories,id",
            "interests" => "required|array",
            "interests.*" => "string",
            "title" => "nullable|string|max:255",
            "house_image" => "nullable|image|mimes:jpeg,png,jpg,webp|max:2048",
            "map" => "nullable|string",
        ]);

        if ($request->hasFile('house_image')) {
            $path = $request->file('house_image')->store('roommates', 'public');
            $validated['house_image'] = $path;
        }

        if (isset($validated['map'])) {
            $validated['map'] = json_decode($validated['map'], true);
        }

        $validated['user_id'] = auth()->user()->id;

        $user = auth()->user();
        $user->roommateRequests()->delete();

        $roommateRequest = RoommateRequest::create($validated);

        return $this->respondWithSuccess("Roommate request created successfully", $roommateRequest);
    }

    public function update(Request $request, $id){
        $roommateRequest = RoommateRequest::find($id);
        if(!$roommateRequest){
            return $this->respondWithError("Roommate request not found",  404);
        }
        if($roommateRequest->user_id != auth()->user()->id){
            return $this->respondWithError("You are not authorized to update this request", 403);
        }

        $validated = $request->validate([
            "name" => "sometimes|required|string",
            "location" => "sometimes|required|string",
            "gender" => "sometimes|required|string|in:male,female,other",
            "min_price" => "sometimes|required|numeric",
            "max_price" => "sometimes|required|numeric",
            "category_id" => "sometimes|required|exists:categories,id",
            "interests" => "sometimes|required|array",
            "interests.*" => "string",
            "title" => "nullable|string|max:255",
            "house_image" => "nullable|image|mimes:jpeg,png,jpg,webp|max:2048",
            "map" => "nullable|string",
        ]);

        if ($request->hasFile('house_image')) {
            // Delete old image if it exists
            if ($roommateRequest->house_image) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($roommateRequest->house_image);
            }
            $path = $request->file('house_image')->store('roommates', 'public');
            $validated['house_image'] = $path;
        }

        if (isset($validated['map'])) {
            $validated['map'] = json_decode($validated['map'], true);
        }

        $roommateRequest->update($validated);

        return $this->respondWithSuccess("Roommate request updated successfully", $roommateRequest);
    }

    public function destroy($id){
        $roommateRequest = RoommateRequest::find($id);
        if(!$roommateRequest){
            return $this->respondWithError("Roommate request not found",  404);
        }
        if($roommateRequest->user_id != auth()->user()->id){
            return $this->respondWithError("You are not authorized to delete this request", 403);
        }
        $roommateRequest->delete();
        return $this->respondWithSuccess("Roommate request deleted successfully", $roommateRequest);
    }
}
