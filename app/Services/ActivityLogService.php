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
            'user_id' => $user ? $user->id : null,
            'branch_id' => $branchId,
            'action' => $action,
            'module' => $module,
            'description' => $description,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'metadata' => $metadata
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