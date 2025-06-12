<?php

namespace App\Models;

use App\Traits\BranchAware;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Table extends Model
{
    use HasFactory, BranchAware;

    protected $fillable = [
        'name',
        'capacity',
        'status',
        'branch_id',
        'is_active',
        'is_occupied',
        'number'
    ];

    protected $casts = [
        'capacity' => 'integer',
        'is_active' => 'boolean',
        'is_occupied' => 'boolean'
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Update table status and occupied state
     * 
     * @param string $status The new status (available, occupied, reserved)
     * @param bool $isOccupied Whether the table is occupied
     * @return bool Whether the update was successful
     */
    public function updateStatus(string $status, bool $isOccupied = null)
    {
        // Validate status
        if (!in_array($status, ['available', 'occupied', 'reserved'])) {
            \Log::error('Invalid table status', [
                'table_id' => $this->id,
                'invalid_status' => $status,
                'timestamp' => now()
            ]);
            return false;
        }

        $oldStatus = $this->status;
        $oldOccupied = $this->is_occupied;

        // If isOccupied is not provided, set it based on status
        if ($isOccupied === null) {
            $isOccupied = $status === 'occupied';
        }

        try {
            $updated = $this->update([
                'status' => $status,
                'is_occupied' => $isOccupied
            ]);

            if ($updated) {
                \Log::info('Table status updated', [
                    'table_id' => $this->id,
                    'branch_id' => $this->branch_id,
                    'old_status' => $oldStatus,
                    'new_status' => $status,
                    'old_occupied' => $oldOccupied,
                    'new_occupied' => $isOccupied,
                    'updated_at' => now()
                ]);
            } else {
                \Log::warning('Table status update failed', [
                    'table_id' => $this->id,
                    'branch_id' => $this->branch_id,
                    'old_status' => $oldStatus,
                    'attempted_status' => $status,
                    'old_occupied' => $oldOccupied,
                    'attempted_occupied' => $isOccupied,
                    'timestamp' => now()
                ]);
            }

            return $updated;
        } catch (\Exception $e) {
            \Log::error('Exception while updating table status', [
                'table_id' => $this->id,
                'branch_id' => $this->branch_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'timestamp' => now()
            ]);
            return false;
        }
    }
} 