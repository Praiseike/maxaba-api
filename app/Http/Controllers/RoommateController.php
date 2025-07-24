<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\ApiController;
use App\Models\RoommateRequest;
use Illuminate\Http\Request;

class RoommateController extends ApiController
{
    public function index(Request $request){
        
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

        $requests = $query->paginate(10);
        
        return $this->respondWithSuccess("Fetched roommates",$requests);
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
        ]);
        $validated['user_id'] = auth()->user()->id;

        $roommateRequest = RoommateRequest::create($validated);

        return $this->respondWithSuccess("Roommate request created successfully", $roommateRequest);
    }
}
