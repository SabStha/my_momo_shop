<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerFeedback extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'customer_feedback';

    protected $fillable = [
        'customer_id',
        'order_id',
        'rating',
        'comment',
        'category',
        'sentiment_analysis',
        'is_resolved',
        'resolution_notes'
    ];

    protected $casts = [
        'rating' => 'integer',
        'sentiment_analysis' => 'array',
        'is_resolved' => 'boolean'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function scopeResolved($query)
    {
        return $query->where('is_resolved', true);
    }

    public function scopeUnresolved($query)
    {
        return $query->where('is_resolved', false);
    }

    public function scopeByRating($query, $rating)
    {
        return $query->where('rating', $rating);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function getSentimentAttribute()
    {
        if (!$this->sentiment_analysis) return null;
        return $this->sentiment_analysis['sentiment'] ?? null;
    }

    public function getSentimentScoreAttribute()
    {
        if (!$this->sentiment_analysis) return null;
        return $this->sentiment_analysis['score'] ?? null;
    }

    public function getKeywordsAttribute()
    {
        if (!$this->sentiment_analysis) return [];
        return $this->sentiment_analysis['keywords'] ?? [];
    }
} 