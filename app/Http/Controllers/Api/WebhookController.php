<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function handleKhaltiWebhook(Request $request)
    {
        // Handle Khalti webhook
        return response()->json(['status' => 'success']);
    }
} 