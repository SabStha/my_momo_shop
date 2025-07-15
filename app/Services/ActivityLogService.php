<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLogService
{
    public static function log($action, $module, $description, $metadata = null)
    {
        $user = Auth::user();
        $branchId = session('selected_branch_id');

        if (!$branchId && request()->has('branch')) {
            $branchId = request()->branch;
        }

        return ActivityLog::create([
            'log_name' => $module,
            'description' => $description,
            'event' => $action,
            'subject_type' => 'App\Models\Branch',
            'subject_id' => $branchId,
            'causer_type' => $user ? 'App\Models\User' : null,
            'causer_id' => $user ? $user->id : null,
            'properties' => $metadata
        ]);
    }

    public static function logPosActivity($action, $description, $metadata = null)
    {
        return self::log($action, 'pos', $description, $metadata);
    }

    public static function logPaymentActivity($action, $description, $metadata = null)
    {
        return self::log($action, 'payment', $description, $metadata);
    }

    public static function logInventoryActivity($action, $description, $metadata = null)
    {
        return self::log($action, 'inventory', $description, $metadata);
    }

    public static function logUserActivity($action, $description, $metadata = null)
    {
        return self::log($action, 'user', $description, $metadata);
    }
} 