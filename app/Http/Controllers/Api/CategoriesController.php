<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Amenity;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CategoriesController extends ApiController
{

    public function createAmenity(Request $request){
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'image' => ['nullable', 'string'], 
        ]);

        $amenity = Amenity::create([
            'name' => $request->name,
            'image' => $request->image,
        ]);

        return $this->respondWithSuccess("Amenity created successfully", $amenity);
    }

    public function getAmenities()
    {
        return $this->respondWithSuccess("Fetched amenities", Amenity::all());
    }

    public function getCategories()
    {
        return $this->respondWithSuccess("Fetched categories", Category::all());
    }

    public function addCategory(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'image' => ['nullable', 'file', 'mimes:jpeg,png,jpg,gif', 'max:2048'], // Validate image file
        ]);

        $imagePath = null;

        if ($request->hasFile('image')) {
            // Store the image and get its relative path
            $imagePath = $request->file('image')->store('categories', 'public');
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
            'image' => ['nullable', 'file', 'mimes:jpeg,png,jpg,gif', 'max:2048'], // Validate image file
        ]);

        $category = Category::find($id);

        if (! $category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $imagePath = $category->image;

        if ($request->hasFile('image')) {

            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }

            $imagePath = $request->file('image')->store('categories', 'public');
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

        if (! $category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }

        $category->delete();

        return $this->respondWithSuccess("Category deleted successfully");
    }
}
