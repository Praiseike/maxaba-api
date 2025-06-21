<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\ApiController;
use App\Models\RoommateRequest;
use Illuminate\Http\Request;

class RoommateController extends ApiController
{
    public function index(){
        $requests = RoommateRequest::paginate(10);
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
        $roommateRequest = RoommateRequest::create(
            $validated
        );

        return $this->respondWithSuccess("Roommate request created successfully", $roommateRequest);
    }
}
