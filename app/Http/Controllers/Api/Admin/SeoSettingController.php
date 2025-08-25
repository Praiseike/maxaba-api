<?php
namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\SeoSetting;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SeoSettingController extends Controller
{
    /**
     * Get the landing page SEO settings
     */
    public function getLandingPageSEO()
    {
        $seoSetting = SeoSetting::where('page_slug', 'home')->first();
        
        if (!$seoSetting) {
            return response()->json([
                'success' => false,
                'message' => 'Landing page SEO settings not found',
                'data' => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $seoSetting
        ]);
    }

    /**
     * Create or update landing page SEO settings
     */
    public function saveLandingPageSEO(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'keywords' => 'nullable|string|max:1000',
            'og_image' => 'nullable|url|max:500',
        ]);

        // Check if landing page SEO already exists
        $seoSetting = SeoSetting::where('page_slug', 'home')->first();

        $data = [
            'page_slug' => 'home',
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'keywords' => $request->input('keywords'),
            'og_image' => $request->input('og_image'),
            'is_active' => true, // Always active for landing page
        ];

        if ($seoSetting) {
            // Update existing record
            $seoSetting->update($data);
            $message = 'Landing page SEO updated successfully';
        } else {
            // Create new record
            $seoSetting = SeoSetting::create($data);
            $message = 'Landing page SEO created successfully';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $seoSetting->fresh()
        ]);
    }

    /**
     * Get landing page SEO for public use (frontend)
     */
    public function getPublicLandingPageSEO()
    {
        $seoSetting = SeoSetting::where('page_slug', 'home')
            ->where('is_active', true)
            ->first();

        if (!$seoSetting) {
            // Return default values if no SEO settings found
            return response()->json([
                'success' => true,
                'data' => [
                    'title' => config('app.name', 'Your Website'),
                    'description' => 'Welcome to our website',
                    'keywords' => '',
                    'og_image' => null
                ]
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'title' => $seoSetting->title,
                'description' => $seoSetting->description,
                'keywords' => $seoSetting->keywords,
                'og_image' => $seoSetting->og_image
            ]
        ]);
    }

    /**
     * Reset landing page SEO to defaults
     */
    public function resetLandingPageSEO()
    {
        $seoSetting = SeoSetting::where('page_slug', 'home')->first();

        if (!$seoSetting) {
            return response()->json([
                'success' => false,
                'message' => 'No landing page SEO settings found to reset'
            ], 404);
        }

        $seoSetting->update([
            'title' => '',
            'description' => '',
            'keywords' => '',
            'og_image' => '',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Landing page SEO reset successfully',
            'data' => $seoSetting->fresh()
        ]);
    }

    // Keep these methods if you still need them for other pages
    public function index()
    {
        $seoSettings = SeoSetting::where('page_slug', '!=', 'home')
            ->paginate(10);
        return response()->json($seoSettings);
    }

    public function show($id)
    {
        $seoSetting = SeoSetting::findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $seoSetting
        ]);
    }

    public function store(Request $request)
    {
        // Prevent creating 'home' page through this method
        if ($request->input('page_slug') === 'home') {
            throw ValidationException::withMessages([
                'page_slug' => 'Use the landing page SEO endpoint for home page settings.'
            ]);
        }

        $request->validate([
            'page_slug' => 'required|string|unique:seo_settings,page_slug',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
            'keywords' => 'nullable|string|max:1000',
            'og_image' => 'nullable|url|max:500',
        ]);

        $seoSetting = SeoSetting::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'SEO setting created successfully',
            'data' => $seoSetting
        ], 201);
    }

    public function update(Request $request, SeoSetting $seoSetting)
    {
        // Prevent updating 'home' page through this method
        if ($seoSetting->page_slug === 'home') {
            return response()->json([
                'success' => false,
                'message' => 'Use the landing page SEO endpoint for home page settings.'
            ], 400);
        }

        $request->validate([
            'page_slug' => 'required|string|unique:seo_settings,page_slug,' . $seoSetting->id,
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
            'keywords' => 'nullable|string|max:1000',
            'og_image' => 'nullable|url|max:500',
        ]);

        $seoSetting->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'SEO setting updated successfully',
            'data' => $seoSetting->fresh()
        ]);
    }

    public function destroy(SeoSetting $seoSetting)
    {
        // Prevent deleting landing page SEO
        if ($seoSetting->page_slug === 'home') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete landing page SEO settings.'
            ], 400);
        }

        $seoSetting->delete();

        return response()->json([
            'success' => true,
            'message' => 'SEO setting deleted successfully'
        ]);
    }
}