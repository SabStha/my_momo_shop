<?php

namespace App\Services;

use Spatie\Activitylog\Facades\Activity;
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

        $activity = Activity::log($description)
            ->causedBy($user)
            ->withProperties($metadata ?? [])
            ->log($action);

        // Add custom properties for module and branch
        if ($branchId) {
            $activity->properties = $activity->properties->merge([
                'module' => $module,
                'branch_id' => $branchId
            ]);
            $activity->save();
        }

        return $activity;
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