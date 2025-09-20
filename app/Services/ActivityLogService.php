<?php

namespace App\Services;

use Spatie\Activitylog\Facades\Activity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLogService
{
    public static function log($action, $module, $description, $metadata = null)
    {
        // Temporarily disable activity logging to prevent POS system issues
        // TODO: Fix Spatie Activity Log integration
        \Log::info('ActivityLogService called (disabled)', [
            'action' => $action,
            'module' => $module,
            'description' => $description,
            'metadata' => $metadata
        ]);
        
        return (object) [
            'id' => null,
            'properties' => collect($metadata ?? [])
        ];
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