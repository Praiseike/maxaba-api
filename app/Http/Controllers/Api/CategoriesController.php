<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Amenity;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CategoriesController extends ApiController
{
    public function createAmenity(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'image' => ['nullable'],
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('amenities', 'public');
        } elseif ($request->filled('image')) {
            $imagePath = $request->image;
        }

        $amenity = Amenity::create([
            'name' => $request->name,
            'image' => $imagePath,
        ]);

        return $this->respondWithSuccess("Amenity created successfully", $amenity);
    }

    public function getAmenities()
    {
        return $this->respondWithSuccess("Fetched amenities", Amenity::all());
    }

    public function updateAmenity(Request $request, $id)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'image' => ['nullable'],
        ]);

        $amenity = Amenity::find($id);

        if (!$amenity) {
            return response()->json(['message' => 'Amenity not found'], 404);
        }

        $imagePath = $amenity->getRawOriginal('image');
        if ($request->hasFile('image')) {
            if ($amenity->getRawOriginal('image')) {
                Storage::disk('public')->delete($amenity->getRawOriginal('image'));
            }
            $imagePath = $request->file('image')->store('amenities', 'public');
        } elseif ($request->filled('image')) {
            $imagePath = $request->image;
        }

        $amenity->update([
            'name' => $request->name,
            'image' => $imagePath,
        ]);

        return $this->respondWithSuccess("Amenity updated successfully", $amenity);
    }

    public function deleteAmenity($id)
    {
        $amenity = Amenity::find($id);

        if (!$amenity) {
            return response()->json(['message' => 'Amenity not found'], 404);
        }

        if ($amenity->getRawOriginal('image')) {
            Storage::disk('public')->delete($amenity->getRawOriginal('image'));
        }

        $amenity->delete();

        return $this->respondWithSuccess("Amenity deleted successfully");
    }

    public function getCategories()
    {
        return $this->respondWithSuccess("Fetched categories", Category::all());
    }

    public function addCategory(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'image' => ['nullable'],
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('categories', 'public');
        } elseif ($request->filled('image')) {
            $imagePath = $request->image;
        }

        $category = Category::create([
            'name' => $request->name,
            'image' => $imagePath,
        ]);

        return $this->respondWithSuccess("Category added successfully", $category);
    }

    public function updateCategory(Request $request, $id)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'image' => ['nullable'],
        ]);

        $category = Category::find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $imagePath = $category->getRawOriginal('image');
        if ($request->hasFile('image')) {
            if ($category->getRawOriginal('image')) {
                Storage::disk('public')->delete($category->getRawOriginal('image'));
            }
            $imagePath = $request->file('image')->store('categories', 'public');
        } elseif ($request->filled('image')) {
            $imagePath = $request->image;
        }

        $category->update([
            'name' => $request->name,
            'image' => $imagePath,
        ]);

        return $this->respondWithSuccess("Category updated successfully", $category);
    }

    public function deleteCategory($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $category->properties()->update(['category_id' => null]);

        if ($category->getRawOriginal('image')) {
            Storage::disk('public')->delete($category->getRawOriginal('image'));
        }

        $category->delete();

        return $this->respondWithSuccess("Category deleted successfully");
    }
}
