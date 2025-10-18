<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ForecastFeedback extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_item_id',
        'branch_id',
        'forecast_type',
        'forecasted_quantity',
        'actual_quantity_used',
        'accuracy_percentage',
        'forecast_reasoning',
        'forecast_context',
        'actual_usage_context',
        'feedback_type',
        'manual_feedback',
        'was_accurate',
        'forecast_date',
        'usage_date'
    ];

    protected $casts = [
        'forecasted_quantity' => 'decimal:2',
        'actual_quantity_used' => 'decimal:2',
        'accuracy_percentage' => 'decimal:2',
        'forecast_context' => 'array',
        'actual_usage_context' => 'array',
        'was_accurate' => 'boolean',
        'forecast_date' => 'datetime',
        'usage_date' => 'datetime'
    ];

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Calculate accuracy percentage
     */
    public function calculateAccuracy(): float
    {
        if ($this->actual_quantity_used === null || $this->forecasted_quantity == 0) {
            return 0;
        }

        $difference = abs($this->actual_quantity_used - $this->forecasted_quantity);
        $accuracy = (($this->forecasted_quantity - $difference) / $this->forecasted_quantity) * 100;
        
        return max(0, min(100, $accuracy));
    }

    /**
     * Determine if forecast was accurate (within 20% tolerance)
     */
    public function isAccurate(): bool
    {
        return $this->accuracy_percentage >= 80;
    }

    /**
     * Get forecast performance insights
     */
    public static function getPerformanceInsights($itemId = null, $branchId = null, $days = 30)
    {
        $query = self::where('forecast_date', '>=', now()->subDays($days));
        
        if ($itemId) {
            $query->where('inventory_item_id', $itemId);
        }
        
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $feedbacks = $query->get();

        if ($feedbacks->isEmpty()) {
            return [
                'total_forecasts' => 0,
                'accurate_forecasts' => 0,
                'accuracy_rate' => 0,
                'average_accuracy' => 0,
                'accuracy_trend' => 'stable',
                'improvement_suggestions' => []
            ];
        }

        $totalForecasts = $feedbacks->count();
        $accurateForecasts = $feedbacks->where('was_accurate', true)->count();
        $averageAccuracy = $feedbacks->avg('accuracy_percentage');

        // Calculate trend (comparing first half vs second half)
        $sortedFeedbacks = $feedbacks->sortBy('forecast_date');
        $halfPoint = ceil($totalForecasts / 2);
        $firstHalf = $sortedFeedbacks->take($halfPoint);
        $secondHalf = $sortedFeedbacks->skip($halfPoint);

        $firstHalfAccuracy = $firstHalf->avg('accuracy_percentage');
        $secondHalfAccuracy = $secondHalf->avg('accuracy_percentage');

        $trend = 'stable';
        if ($secondHalfAccuracy > $firstHalfAccuracy + 5) {
            $trend = 'improving';
        } elseif ($secondHalfAccuracy < $firstHalfAccuracy - 5) {
            $trend = 'declining';
        }

        // Generate improvement suggestions
        $suggestions = [];
        if ($averageAccuracy < 70) {
            $suggestions[] = 'Consider increasing safety stock levels';
        }
        if ($accurateForecasts / $totalForecasts < 0.6) {
            $suggestions[] = 'Review forecasting parameters and demand patterns';
        }
        if ($trend === 'declining') {
            $suggestions[] = 'Investigate recent changes in demand patterns';
        }

        return [
            'total_forecasts' => $totalForecasts,
            'accurate_forecasts' => $accurateForecasts,
            'accuracy_rate' => $totalForecasts > 0 ? ($accurateForecasts / $totalForecasts) * 100 : 0,
            'average_accuracy' => round($averageAccuracy, 2),
            'accuracy_trend' => $trend,
            'improvement_suggestions' => $suggestions
        ];
    }

    /**
     * Record a new forecast
     */
    public static function recordForecast($itemId, $branchId, $forecastType, $quantity, $reasoning = null, $context = null)
    {
        return self::create([
            'inventory_item_id' => $itemId,
            'branch_id' => $branchId,
            'forecast_type' => $forecastType,
            'forecasted_quantity' => $quantity,
            'forecast_reasoning' => $reasoning,
            'forecast_context' => $context,
            'forecast_date' => now(),
            'feedback_type' => 'automatic'
        ]);
    }

    /**
     * Update with actual usage data
     */
    public function updateWithActualUsage($actualQuantity, $usageContext = null)
    {
        $this->update([
            'actual_quantity_used' => $actualQuantity,
            'actual_usage_context' => $usageContext,
            'usage_date' => now(),
            'accuracy_percentage' => $this->calculateAccuracy(),
            'was_accurate' => $this->isAccurate()
        ]);
    }
}
