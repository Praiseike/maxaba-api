<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\Status;
use App\Http\Controllers\Api\ApiController;
use App\Models\Admin;
use App\Models\Property;
use App\Notifications\NewPropertyNotification;
use App\Notifications\PropertyStatusUpdateNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Notification;

class PropertiesController extends ApiController
{

    public function store(Request $request)
    {
        $user = auth('admin')->user();

        $validated = $request->validate([
            'occupant_type' => 'required|in:single,multiple,both',
            'category_id' => 'nullable|exists:categories,id',
            'location' => 'required|string',
            'title' => 'required|string',
            'price' => 'required|numeric|min:0',
            'description' => 'required|string',
            'bedrooms' => 'required|integer|min:0',
            'bathrooms' => 'required|integer|min:0',
            'kitchens' => 'required|integer|min:0',
            'livingrooms' => 'required|integer|min:0',
            'amenities' => 'required|array|min:1',
            'amenities.*' => 'string|max:255',
            'files' => 'required|array|min:1',
            'files.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
            'offer_type' => 'required|in:rent,sale',
            'offer_duration' => 'nullable|string',
            'other_information' => 'nullable|array',

            // Make charges required as an array if offer_type is rent
            'charges' => 'required_if:offer_type,rent|array',
            'charges.agent_percentage' => 'required_if:offer_type,rent|numeric|min:0',
            'charges.caution_percentage' => 'required_if:offer_type,rent|numeric|min:0',
            'charges.legal_percentage' => 'required_if:offer_type,rent|numeric|min:0',
        ]);

        $imagePaths = [];

        foreach ($request->file('files') as $image) {
            $path = $image->store('properties', 'public');
            $imagePaths[] = $path;
        }

        $property = Property::create([
            'title' => $request->title,
            'occupant_type' => $validated['occupant_type'],
            'rejection_reason' => "",
            'admin_id' => $user->id,
            'category_id' => $validated['category_id'],
            'location' => json_decode($validated['location']) ?? null,
            'price' => $validated['price'],
            'description' => $validated['description'],
            'bedrooms' => $validated['bedrooms'],
            'bathrooms' => $validated['bathrooms'],
            'kitchens' => $validated['kitchens'],
            'livingrooms' => $validated['livingrooms'],
            'offer_type' => $validated['offer_type'],
            'offer_duration' => $validated['offer_duration'] ?? null,
            'amenities' => $validated['amenities'],
            'images' => $imagePaths,
            'status' => 'pending',
            'published' => false,
            'verified' => false,
            'other_information' => $validated['other_information'] ?? null,
            'charges' => $validated['charges'] ?? null,
        ]);



        Notification::send(Admin::all(), new NewPropertyNotification($property));

        return $this->respondWithSuccess(
            message: 'Property created successfully',
            data: $property,
        );
    }

    public function getProperty(Request $request, int $id)
    {
        $property = Property::with('category')->find($id);

        if (!$property) {
            return $this->errorNotFound('Property not found');
        }

        return $this->respondWithSuccess("Property fetched successfully", $property);
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

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        foreach (['bathrooms', 'bedrooms', 'livingrooms'] as $feature) {
            if ($request->filled($feature)) {
                $query->where($feature, $request->{$feature});
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%")
                    ->orWhereJsonContains('amenities', $search);
            });
        }

        if ($request->filled('amenities')) {
            foreach (explode(',', $request->amenities) as $amenity) {
                $query->whereJsonContains('amenities', trim($amenity));
            }
        }

        $properties = $query->with('category')->latest()->paginate();

        return $this->respondWithSuccess("Properties fetched successfully", $properties);
    }

    public function deleteProperty(Request $request, $id)
    {
        $property = Property::find($id);

        if (!$property) {
            return $this->errorNotFound('Property not found');
        }

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

        if ($request->status == Status::REJECTED) {
            $property->rejection_reason = $request->reason;
        }

        $property->save();

        $property->user->notify(new PropertyStatusUpdateNotification($property, $request->status == Status::REJECTED ? $request->reason : "You property has been " . strtolower($property->status->value) . " by our admins"));

        return $this->respondWithSuccess("Property status updated successfully", $property);
    }

    public function searchProperties(Request $request)
    {
        $query = $request->query('query');

        if (!$query) {
            return $this->errorNotFound("No search query provided");
        }

        $properties = Property::where(function ($q) use ($query) {
            $q->where('title', 'LIKE', "%{$query}%")
                ->orWhere('description', 'LIKE', "%{$query}%")
                ->orWhereJsonContains('amenities', $query);
        })
            ->with('category')
            ->paginate();

        return $this->respondWithSuccess("Properties search results", $properties);
    }

    public function getStats()
    {
        return $this->respondWithSuccess('', [
            'total_houses' => Property::withoutTrashed()->where('status', Status::APPROVED)
                ->orWhere('status', Status::PENDING)
                ->count(),
            'pending_houses' => Property::where('status', Status::PENDING)->count(),
        ]);
    }
}
