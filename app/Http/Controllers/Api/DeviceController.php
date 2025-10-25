<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DeviceController extends Controller
{
    /**
     * Store or update a device token for push notifications.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'token' => 'required|string',
            'platform' => 'required|in:android,ios',
        ]);

        $device = Device::updateOrCreate(
            ['token' => $data['token']],
            [
                'user_id' => $request->user()->id,
                'platform' => $data['platform'],
                'last_used_at' => now()
            ]
        );

        \Log::info('Device token registered', [
            'user_id' => $request->user()->id,
            'platform' => $data['platform'],
            'token_length' => strlen($data['token'])
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Device registered successfully',
            'device_id' => $device->id
        ], 200);
    }
}
