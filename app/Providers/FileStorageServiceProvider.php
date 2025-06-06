<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class FileStorageServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        // Configure storage URL based on environment
        if (app()->environment('production')) {
            // In production, use the public_html/storage path
            URL::forceRootUrl(config('app.url'));
            $adapter = Storage::disk('public')->getAdapter();
            // Only call getClient if the method exists (e.g., for S3)
            if (method_exists($adapter, 'getClient')) {
                $adapter->getClient()->setPathPrefix(public_path('storage'));
            }
        }
    }
} 