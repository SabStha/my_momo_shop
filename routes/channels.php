<?php

use Illuminate\Support\Facades\Broadcast;

// Payment channel — public, no auth required (customer viewer is unauthenticated)
Broadcast::channel('payment.{branchId}', function () {
    return true; // Public channel — no auth check needed
});
