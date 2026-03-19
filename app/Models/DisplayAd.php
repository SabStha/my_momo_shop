<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DisplayAd extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'type',
        'file_path',
        'video_url',
        'youtube_id',
        'vimeo_id',
        'display_order',
        'is_active',
        'branch_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getEmbedUrlAttribute(): ?string
    {
        if ($this->type === 'youtube' && $this->youtube_id) {
            return "https://www.youtube.com/embed/{$this->youtube_id}?autoplay=1&mute=1&loop=1&playlist={$this->youtube_id}&controls=0&rel=0";
        }

        if ($this->type === 'vimeo' && $this->vimeo_id) {
            return "https://player.vimeo.com/video/{$this->vimeo_id}?autoplay=1&muted=1&loop=1&background=1";
        }

        return null;
    }
}
