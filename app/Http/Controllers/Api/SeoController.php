<?php
// app/Http/Controllers/Api/SeoController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SeoSetting;
use Illuminate\Http\JsonResponse;

class SeoController extends Controller
{
    public function show(string $slug): JsonResponse
    {
        $seoSetting = SeoSetting::findBySlug($slug);

        if (!$seoSetting) {
            return response()->json([
                'success' => false,
                'message' => 'SEO settings not found for this page',
                'data' => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $seoSetting,
            'message' => 'SEO settings retrieved successfully'
        ]);
    }

    public function index(): JsonResponse
    {
        $seoSettings = SeoSetting::active()
            ->select(['page_slug', 'title', 'description', 'keywords', 'og_image'])
            ->orderBy('page_slug')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $seoSettings,
            'message' => 'SEO settings retrieved successfully'
        ]);
    }
}