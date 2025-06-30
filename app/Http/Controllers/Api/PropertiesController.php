<?php

namespace App\Http\Controllers\Api;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Property;
use App\Notifications\NewPropertyNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

class PropertiesController extends ApiController
{
    //

    public function store(Request $request)
    {
        $user = auth()->user();
        if(!$user->isAgent() || $user->account_status != Status::ACTIVE) {
            return $this->errorForbidden("Must be an approved agent");
        }
        $validated = $request->validate([
            'occupant_type' => 'required|in:single,multiple,both',
            'category_id' => 'nullable|exists:categories,id',
            'location' => 'required|string',
            'title' => 'required|string',
            // 'location.address' => 'required|string', // Optional: nested location validation
            'price' => 'required|numeric|min:0',
            'description' => 'required|string',
            'bedrooms' => 'required|integer|min:0',
            'bathrooms' => 'required|integer|min:0',
            'livingrooms' => 'required|integer|min:0',
            'amenities' => 'required|array|min:1',
            'amenities.*' => 'string|max:255',
            'files' => 'required|array|min:1',
            'files.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
            'offer_type' => 'required|in:rent,sale',
            'offer_duration' => 'nullable|string',
            'other_information' => 'nullable',
            'charges' => 'required_if:offer_type,rent',
            'charges.agent_percentage' => 'required|numeric',
            'charges.caution_percentage' => 'required|numeric',
            'charges.legal_percentage' => 'required|numeric',
        ]);

        $imagePaths = [];

        foreach ($request->file('files') as $image) {
            $path = $image->store('properties', 'public');
            $imagePaths[] = $path;
        }

        $property = Property::create([
            'title' => $request->title,
            'user_id' => $user->id,
            'occupant_type' => $validated['occupant_type'],
            'rejection_reason' => "",
            'category_id' => $validated['category_id'],
            'location' => json_decode($validated['location']),
            'price' => $validated['price'],
            'description' => $validated['description'],
            'bedrooms' => $validated['bedrooms'],
            'bathrooms' => $validated['bathrooms'],
            'livingrooms' => $validated['livingrooms'],
            'offer_type' => $validated['offer_type'],
            'offer_duration' => $validated['offer_duration'],
            'amenities' => $validated['amenities'],
            'images' => $imagePaths,
            'slug' => str_slug($validated['title']) . '-' . time(),
            'status' => 'pending',
            'published' => false,
            'verified' => false,
            'other_information' => $validated['other_information'],
            'charges' => $validated['charges'],
        ]);

        Notification::send(Admin::all(), new NewPropertyNotification($property));

        $response = $this->respondWithSuccess(
            message: 'Property created successfully',
            data: $property,
        );

        \Log::info( json_encode($response));
        return $response;
    }

    public function getProperties(Request $request)
    {

        $query = Property::query();

        if ($request->filled('type')) {
            $query->where('category_id', $request->type);
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('search')) {
            $query->where('title', 'LIKE', "%{$request->search}%")
                ->orWhere('description', 'LIKE', "%{$request->search}%")
                ->orWhereJsonContains('amenities', $request->search);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        $feats = ["bathrooms", "bedrooms", "livingrooms"];
        foreach ($feats as $feat) {
            if ($request->filled($feat)) {
                $query->where($feat, $request->{$feat});
            }
        }

        if ($request->filled('amenities')) {
            $amenities = explode(',', $request->amenities);

            foreach ($amenities as $amenity) {
                $query->whereJsonContains('amenities', $amenity);
            }
        }

        $properties = $query->where('status', Status::APPROVED)
            ->with('category')
            ->paginate();
        return $this->respondWithSuccess("Properties fetched successfully", $properties);
    }

    public function getProperty(Request $request, int $id)
    {
        $property = Property::with('category')->find($id);

        if (!$property) {
            return $this->errorNotFound('Property not found');
        }

        return $this->respondWithSuccess("Property fetched successfully", $property);
    }

    public function deleteProperty(Request $request, $id)
    {
        $property = Property::find($id);

        if (!$property) {
            return $this->errorNotFound('Property not found');
        }

        // Delete associated images
        foreach ($property->images as $image) {
            Storage::disk('public')->delete($image);
        }

        $property->delete();

        return $this->respondWithSuccess("Property deleted successfully");
    }

    public function updatePropertyStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected',
            'reason' => 'required_if:status,rejected'
        ]);

        $property = Property::find($id);

        if (!$property) {
            return $this->errorNotFound('Property not found');
        }

        $property->status = $request->status;
        if ($request->status == Status::REJECTED)
            $property->rejection_reason = $request->reason;
        $property->save();

        return $this->respondWithSuccess("Property status updated successfully", $property);
    }


    public function searchProperties(Request $request)
    {
        $query = $request->query('query');

        if (!$query) {
            return $this->errorNotFound("No search query provided");
        }

        $properties = Property::where('title', 'LIKE', "%{$query}%")
            ->orWhere('description', 'LIKE', "%{$query}%")
            ->orWhereJsonContains('amenities', $query)
            ->with('category')
            ->paginate();

        return $this->respondWithSuccess("Properties search results", $properties);
    }

    public function favourite(Property $property)
    {
        auth()->user()->favourites()->syncWithoutDetaching([$property->id]);
        return $this->respondWithSuccess('Property favourited');
    }

    public function unfavourite(Property $property)
    {
        auth()->user()->favourites()->detach($property->id);
        return $this->respondWithSuccess('Property unfavourited');

    }
    public function myFavourites()
    {
        $favourites = auth()->user()->favourites()->paginate(10);
        return $this->respondWithSuccess("Fetched favourites", $favourites);
    }


}