<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTheme extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'theme_name',
        'theme_display_name',
        'is_unlocked',
        'is_active',
        'unlocked_at'
    ];

    protected $casts = [
        'is_unlocked' => 'boolean',
        'is_active' => 'boolean',
        'unlocked_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeUnlocked($query)
    {
        return $query->where('is_unlocked', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function unlock()
    {
        $this->update([
            'is_unlocked' => true,
            'unlocked_at' => now()
        ]);
    }

    public function activate()
    {
        // Deactivate all other themes for this user
        $this->user->themes()->update(['is_active' => false]);
        
        // Activate this theme
        $this->update(['is_active' => true]);
    }

    public function getThemeColorsAttribute()
    {
        $colors = [
            'bronze' => [
                'primary' => '#CD7F32',
                'secondary' => '#B8860B',
                'accent' => '#DAA520',
                'background' => '#FFF8DC',
                'text' => '#8B4513',
                'border' => '#D2691E'
            ],
            'silver' => [
                'primary' => '#C0C0C0',
                'secondary' => '#A9A9A9',
                'accent' => '#D3D3D3',
                'background' => '#F8F8FF',
                'text' => '#696969',
                'border' => '#808080'
            ],
            'gold' => [
                'primary' => '#FFD700',
                'secondary' => '#FFA500',
                'accent' => '#FF8C00',
                'background' => '#FFFACD',
                'text' => '#B8860B',
                'border' => '#DAA520'
            ],
            'elite' => [
                'primary' => '#9370DB',
                'secondary' => '#8A2BE2',
                'accent' => '#9932CC',
                'background' => '#F0F8FF',
                'text' => '#4B0082',
                'border' => '#8A2BE2'
            ]
        ];

        return $colors[$this->theme_name] ?? $colors['bronze'];
    }

    public function getThemeStylesAttribute()
    {
        $colors = $this->theme_colors;
        
        return [
            'background' => "background: linear-gradient(135deg, {$colors['background']} 0%, {$colors['primary']}20 100%);",
            'card' => "background: linear-gradient(145deg, {$colors['primary']}15 0%, {$colors['secondary']}10 100%); border: 2px solid {$colors['border']};",
            'text' => "color: {$colors['text']};",
            'accent' => "color: {$colors['accent']};",
            'border' => "border-color: {$colors['border']};",
            'shadow' => "box-shadow: 0 4px 15px {$colors['primary']}30;"
        ];
    }
}
