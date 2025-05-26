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
            Storage::disk('public')->getAdapter()->getClient()->setPathPrefix(
                public_path('storage')
            );
        }
    }
} 