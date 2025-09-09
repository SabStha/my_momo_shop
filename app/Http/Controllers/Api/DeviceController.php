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
    public function store(Request $request): Response
    {
        $data = $request->validate([
            'token' => 'required|string',
            'platform' => 'required|in:android,ios',
        ]);

        Device::updateOrCreate(
            ['token' => $data['token']],
            [
                'user_id' => $request->user()->id,
                'platform' => $data['platform'],
                'last_used_at' => now()
            ]
        );

        return response()->noContent();
    }
}
