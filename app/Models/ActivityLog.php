<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'action',
        'description',
        'entity_type',
        'entity_id',
        'metadata',
        'user_id'
    ];

    protected $casts = [
        'metadata' => 'array'
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function entity()
    {
        return $this->morphTo();
    }
} 