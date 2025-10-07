<?php

/**
 * Add this route to your routes/api.php file
 */

use App\Http\Controllers\ProfileController;

// Protected routes (require authentication)
Route::middleware(['auth:sanctum'])->group(function () {
    // Profile picture upload
    Route::post('/profile/update-picture', [ProfileController::class, 'updateProfilePicture']);
    
    // ... your other routes ...
});

