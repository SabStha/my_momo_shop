<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SiteContent;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ContentController extends Controller
{
    /**
     * Get content by key
     */
    public function getByKey(Request $request, string $key): JsonResponse
    {
        $platform = $request->get('platform', 'all');
        $content = SiteContent::getContent($key, '', $platform);

        return response()->json([
            'success' => true,
            'data' => [
                'key' => $key,
                'content' => $content,
                'platform' => $platform,
            ],
        ]);
    }

    /**
     * Get content by section
     */
    public function getBySection(Request $request, string $section): JsonResponse
    {
        $platform = $request->get('platform', 'all');
        $content = SiteContent::getBySection($section, $platform);

        return response()->json([
            'success' => true,
            'data' => $content,
        ]);
    }

    /**
     * Get section content as key-value array
     */
    public function getSectionAsArray(Request $request, string $section): JsonResponse
    {
        $platform = $request->get('platform', 'all');
        $content = SiteContent::getSectionAsArray($section, $platform);

        return response()->json([
            'success' => true,
            'data' => $content,
        ]);
    }

    /**
     * Get app configuration
     */
    public function getAppConfig(Request $request): JsonResponse
    {
        $platform = $request->get('platform', 'mobile');
        
        $config = [
            'app_name' => SiteContent::getContent('app_name', 'Amako Shop', $platform),
            'app_tagline' => SiteContent::getContent('app_tagline', 'From our kitchen to your heart', $platform),
            'hero_default_cta' => SiteContent::getContent('hero_default_cta', 'Add to Cart', $platform),
            'empty_hero_message' => SiteContent::getContent('empty_hero_message', 'No featured items available', $platform),
            'product_default_subtitle' => SiteContent::getContent('product_default_subtitle', 'Delicious and authentic', $platform),
        ];

        return response()->json([
            'success' => true,
            'data' => $config,
        ]);
    }

    /**
     * Get multiple sections at once
     */
    public function getMultipleSections(Request $request): JsonResponse
    {
        $request->validate([
            'sections' => 'required|array',
            'sections.*' => 'string',
            'platform' => 'string|in:all,web,mobile',
        ]);

        $sections = $request->get('sections', []);
        $platform = $request->get('platform', 'all');
        $content = [];

        foreach ($sections as $section) {
            $content[$section] = SiteContent::getSectionAsArray($section, $platform);
        }

        return response()->json([
            'success' => true,
            'data' => $content,
        ]);
    }

    /**
     * List all content (admin only)
     */
    public function index(Request $request): JsonResponse
    {
        $query = SiteContent::query();

        // Filter by platform
        if ($request->has('platform') && $request->platform !== 'all') {
            $query->where(function ($q) use ($request) {
                $q->where('platform', $request->platform)
                  ->orWhere('platform', 'all');
            });
        }

        // Filter by section
        if ($request->has('section') && $request->section !== 'all') {
            $query->where('section', $request->section);
        }

        // Filter by type
        if ($request->has('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('key', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $content = $query->orderBy('section')
                        ->orderBy('sort_order')
                        ->get();

        return response()->json([
            'success' => true,
            'data' => $content,
        ]);
    }

    /**
     * Store new content (admin only)
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'key' => 'required|string|unique:site_content,key',
            'title' => 'required|string',
            'content' => 'required|string',
            'type' => 'required|string|in:text,html,image,json,boolean',
            'section' => 'required|string',
            'component' => 'nullable|string',
            'platform' => 'required|string|in:all,web,mobile',
            'sort_order' => 'integer|min:0',
            'is_active' => 'boolean',
            'description' => 'nullable|string',
        ]);

        $content = SiteContent::create($validated);
        SiteContent::clearCache();

        return response()->json([
            'success' => true,
            'data' => $content,
            'message' => 'Content created successfully',
        ], 201);
    }

    /**
     * Update content (admin only)
     */
    public function update(Request $request, SiteContent $content): JsonResponse
    {
        $validated = $request->validate([
            'key' => 'sometimes|string|unique:site_content,key,' . $content->id,
            'title' => 'sometimes|string',
            'content' => 'sometimes|string',
            'type' => 'sometimes|string|in:text,html,image,json,boolean',
            'section' => 'sometimes|string',
            'component' => 'nullable|string',
            'platform' => 'sometimes|string|in:all,web,mobile',
            'sort_order' => 'integer|min:0',
            'is_active' => 'boolean',
            'description' => 'nullable|string',
        ]);

        $content->update($validated);
        SiteContent::clearCache();

        return response()->json([
            'success' => true,
            'data' => $content,
            'message' => 'Content updated successfully',
        ]);
    }

    /**
     * Delete content (admin only)
     */
    public function destroy(SiteContent $content): JsonResponse
    {
        $content->delete();
        SiteContent::clearCache();

        return response()->json([
            'success' => true,
            'message' => 'Content deleted successfully',
        ]);
    }
}






