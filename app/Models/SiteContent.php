<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteContent extends Model
{
    use HasFactory;

    protected $table = 'site_content';

    protected $fillable = [
        'key',
        'title',
        'content',
        'type',
        'section',
        'component',
        'platform',
        'sort_order',
        'is_active',
        'description'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get content by key for a specific platform
     */
    public static function getContent(string $key, string $default = '', string $platform = 'all'): string
    {
        $cacheKey = "site_content_{$key}_{$platform}";
        
        return Cache::remember($cacheKey, 3600, function () use ($key, $default, $platform) {
            $query = static::where('key', $key)
                ->where('is_active', true);

            if ($platform !== 'all') {
                $query->where(function ($q) use ($platform) {
                    $q->where('platform', $platform)
                      ->orWhere('platform', 'all');
                });
            }

            $content = $query->first();
            
            return $content ? $content->content : $default;
        });
    }

    /**
     * Get all content for a section
     */
    public static function getBySection(string $section, string $platform = 'all'): array
    {
        $cacheKey = "site_content_section_{$section}_{$platform}";
        
        return Cache::remember($cacheKey, 3600, function () use ($section, $platform) {
            $query = static::where('section', $section)
                ->where('is_active', true)
                ->orderBy('sort_order');

            if ($platform !== 'all') {
                $query->where(function ($q) use ($platform) {
                    $q->where('platform', $platform)
                      ->orWhere('platform', 'all');
                });
            }

            return $query->get()->toArray();
        });
    }

    /**
     * Get section content as key-value array
     */
    public static function getSectionAsArray(string $section, string $platform = 'all'): array
    {
        $cacheKey = "site_content_section_array_{$section}_{$platform}";
        
        return Cache::remember($cacheKey, 3600, function () use ($section, $platform) {
            $query = static::where('section', $section)
                ->where('is_active', true)
                ->orderBy('sort_order');

            if ($platform !== 'all') {
                $query->where(function ($q) use ($platform) {
                    $q->where('platform', $platform)
                      ->orWhere('platform', 'all');
                });
            }

            return $query->pluck('content', 'key')->toArray();
        });
    }

    /**
     * Set content programmatically
     */
    public static function setContent(
        string $key,
        string $title,
        string $content,
        string $type = 'text',
        string $section = 'general',
        string $component = null,
        string $platform = 'all',
        string $description = null
    ): self {
        return static::updateOrCreate(
            ['key' => $key],
            [
                'title' => $title,
                'content' => $content,
                'type' => $type,
                'section' => $section,
                'component' => $component,
                'platform' => $platform,
                'description' => $description,
                'is_active' => true,
            ]
        );
    }

    /**
     * Clear content cache
     */
    public static function clearCache(): void
    {
        Cache::flush();
    }
}
